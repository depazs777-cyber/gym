<?php defined('APP_NAME') or exit('No direct script access allowed');
defined("APP_NAME") or exit("No direct script access allowed");

class User extends BaseModel {
    protected $table = 'users';

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
}
