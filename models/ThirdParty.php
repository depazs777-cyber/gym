<?php defined('APP_NAME') or exit('No direct script access allowed');
require_once __DIR__ . '/BaseModel.php';

class ThirdParty extends BaseModel {
    protected $table = 'third_parties';

    public function getAll($gymId = 0) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE gym_id = ? ORDER BY name ASC");
            $stmt->execute([$gymId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function create($data) {
        try {
            $fields = ['gym_id', 'type_persona', 'doc_type', 'doc_number', 'dv', 'name', 'email', 'phone', 'address', 'city', 'is_client', 'is_provider', 'reteiva_percent', 'reteica_percent'];
            $cols = [];
            $vals = [];
            $placeholders = [];

            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $cols[] = $field;
                    $vals[] = $data[$field];
                    $placeholders[] = '?';
                }
            }

            $sql = "INSERT INTO {$this->table} (" . implode(',', $cols) . ") VALUES (" . implode(',', $placeholders) . ")";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($vals);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Error creating Third Party: " . $e->getMessage());
        }
    }
}
