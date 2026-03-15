<?php defined('APP_NAME') or exit('No direct script access allowed');
require_once __DIR__ . '/BaseModel.php';

class CashReceipt extends BaseModel {
    protected $table = 'accounting_documents';

    public function create($gymId, $orderId, $amount, $method, $ref, $concept, $notes, $userId, $clientId = null) {
        try {
            // Find Third Party for Client
            $thirdPartyId = null;
            if ($clientId) {
                // Check if client has a linked third party
                // Assuming clients table has 'third_party_id' or we map by document number
                // For MVP, lets assume we search by client ID in third_parties if we linked them,
                // OR we search by client identification
                $stmt = $this->pdo->prepare("SELECT identification, name, email, phone, address FROM clients WHERE id = ?");
                $stmt->execute([$clientId]);
                $client = $stmt->fetch();

                if ($client) {
                    $stmt = $this->pdo->prepare("SELECT id FROM third_parties WHERE gym_id = ? AND doc_number = ?");
                    $stmt->execute([$gymId, $client['identification']]);
                    $tp = $stmt->fetch();

                    if ($tp) {
                        $thirdPartyId = $tp['id'];
                    } else {
                        // Auto-create Third Party from Client
                        $sqlTp = "INSERT INTO third_parties (gym_id, type_persona, doc_type, doc_number, name, email, phone, address, is_client, created_at) VALUES (?, 'NATURAL', 'CC', ?, ?, ?, ?, ?, 1, NOW())";
                        $stmtTp = $this->pdo->prepare($sqlTp);
                        // Handle potential dupes if race condition, catch error
                        try {
                            $stmtTp->execute([
                                $gymId,
                                $client['identification'],
                                $client['name'],
                                $client['email'],
                                $client['phone'],
                                $client['address'] ?? ''
                            ]);
                            $thirdPartyId = $this->pdo->lastInsertId();
                        } catch (PDOException $ex) {
                            // Retry fetch if create failed (likely exists)
                            $stmt = $this->pdo->prepare("SELECT id FROM third_parties WHERE gym_id = ? AND doc_number = ?");
                            $stmt->execute([$gymId, $client['identification']]);
                            $tp = $stmt->fetch();
                            $thirdPartyId = $tp ? $tp['id'] : null;
                        }
                    }
                }
            }

            $desc = "Payment Method: $method. Ref: $ref. $concept. $notes";

            $sql = "INSERT INTO accounting_documents (gym_id, doc_type, total_net, status, created_by_user_id, description, doc_number_full, issue_date, third_party_id) VALUES (?, 'RC', ?, 'POSTED', ?, ?, ?, CURDATE(), ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$gymId, $amount, $userId, $desc, $ref, $thirdPartyId]);
            $docId = $this->pdo->lastInsertId();

            // Create Line Item
            if ($docId) {
                $sqlLine = "INSERT INTO accounting_document_lines (document_id, concept, quantity, unit_price, line_total) VALUES (?, ?, 1, ?, ?)";
                $stmtLine = $this->pdo->prepare($sqlLine);
                $stmtLine->execute([$docId, $concept, $amount, $amount]);
            }

            return $docId;
        } catch (PDOException $e) {
            return 0;
        }
    }
}
