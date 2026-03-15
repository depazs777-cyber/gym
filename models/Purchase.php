<?php defined('APP_NAME') or exit('No direct script access allowed');
require_once __DIR__ . '/BaseModel.php';

class Purchase extends BaseModel {
    protected $table = 'accounting_documents';

    public function getAll($gymId = null) {
        if ($gymId === null) {
            return [];
        }
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM accounting_documents WHERE gym_id = ? AND doc_type IN ('FC', 'DS', 'CE') ORDER BY created_at DESC");
            $stmt->execute([$gymId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function create($data) {
        try {
            $sql = "INSERT INTO accounting_documents (
                gym_id, doc_type, third_party_id, doc_number_full, issue_date,
                total_net, description, created_by_user_id, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'POSTED')";

            $docType = $data['doc_type'] ?? 'FC';
            $total = $data['total_payable'] ?? 0;
            $issueDate = $data['issue_date'] ?? date('Y-m-d');
            $desc = "Purchase from " . ($data['third_party_name'] ?? 'Unknown');

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['gym_id'],
                $docType,
                $data['third_party_id'],
                $data['doc_number'],
                $issueDate,
                $total,
                $desc,
                $data['created_by']
            ]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            return 0;
        }
    }
}
