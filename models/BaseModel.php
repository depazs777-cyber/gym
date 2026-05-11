<?php defined('APP_NAME') or exit('No direct script access allowed');
defined("APP_NAME") or exit("No direct script access allowed");

class BaseModel {
    protected $pdo;
    protected $table;

    public function __construct() {
        $this->pdo = new Database()->getConnection();
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
