<?php

namespace App\Services;

use App\Core\Database;
use Exception;

class AccountingService {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Crea un asiento contable (Journal Entry)
     * @param int $gymId
     * @param string $date (Y-m-d)
     * @param string $description
     * @param array $lines Array de ['account_id', 'debit', 'credit', 'third_party_id' (opcional)]
     * @param string $sourceModule (e.g. 'billing', 'payroll')
     * @param int $sourceId
     */
    public function createEntry($gymId, $date, $description, array $lines, $sourceModule = null, $sourceId = null) {
        try {
            $this->db->beginTransaction();

            // 1. Validar Balance (Partida Doble)
            $totalDebit = 0;
            $totalCredit = 0;
            foreach ($lines as $line) {
                $totalDebit += $line['debit'];
                $totalCredit += $line['credit'];
            }

            if (abs($totalDebit - $totalCredit) > 0.01) {
                throw new Exception("El asiento está desbalanceado. Débito: $totalDebit, Crédito: $totalCredit");
            }

            // 2. Crear Cabecera
            $stmt = $this->db->prepare("
                INSERT INTO journal_entries (gym_id, entry_date, description, source_module, source_id, status, posted_at, created_by)
                VALUES (:gym_id, :date, :desc, :module, :source_id, 'posted', NOW(), :user_id)
            ");

            $stmt->execute([
                ':gym_id' => $gymId,
                ':date' => $date,
                ':desc' => $description,
                ':module' => $sourceModule,
                ':source_id' => $sourceId,
                ':user_id' => $_SESSION['user_id'] ?? null
            ]);

            $entryId = $this->db->lastInsertId();

            // 3. Crear Líneas
            $stmtLine = $this->db->prepare("
                INSERT INTO journal_lines (entry_id, account_id, third_party_id, description, debit, credit)
                VALUES (:entry_id, :account_id, :third_party_id, :desc, :debit, :credit)
            ");

            foreach ($lines as $line) {
                $stmtLine->execute([
                    ':entry_id' => $entryId,
                    ':account_id' => $line['account_id'],
                    ':third_party_id' => $line['third_party_id'] ?? null,
                    ':desc' => $description, // Opcional: línea específica
                    ':debit' => $line['debit'],
                    ':credit' => $line['credit']
                ]);
            }

            $this->db->commit();
            return $entryId;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Anular un asiento (Reverso)
     */
    public function voidEntry($entryId, $reason) {
        // Implementación de anulación creando un asiento inverso
        // ... (Lógica similar a createEntry pero invirtiendo debit/credit)
    }
}
