<?php defined('APP_NAME') or exit('No direct script access allowed');

class Gym extends BaseModel {
    protected $table = 'gyms';

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
