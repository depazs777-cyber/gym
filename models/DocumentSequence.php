<?php

class DocumentSequence {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getNextNumber($docType, $gymId = 0) {
        $this->db->beginTransaction();
        try {
            // Select for update
            $stmt = $this->db->prepare("SELECT prefix, current_number, padding FROM document_sequences WHERE gym_id = ? AND doc_type = ?");
            $stmt->execute([$gymId, $docType]);
            $seq = $stmt->fetch();

            if (!$seq) {
                // Should exist from seeding, but fallback
                $prefix = $docType . '-';
                $stmt = $this->db->prepare("INSERT INTO document_sequences (gym_id, doc_type, prefix, current_number) VALUES (?, ?, ?, 0)");
                $stmt->execute([$gymId, $docType, $prefix]);
                $seq = ['prefix' => $prefix, 'current_number' => 0, 'padding' => 6];
            }

            $nextNum = $seq['current_number'] + 1;
            
            // Update
            $update = $this->db->prepare("UPDATE document_sequences SET current_number = ? WHERE gym_id = ? AND doc_type = ?");
            $update->execute([$nextNum, $gymId, $docType]);

            $this->db->commit();

            return $seq['prefix'] . str_pad($nextNum, $seq['padding'], '0', STR_PAD_LEFT);
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
