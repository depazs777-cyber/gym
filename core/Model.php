<?php

abstract class Model {
    protected $db;
    protected $table = '';

    public function __construct() {
        $this->db = new Database();
    }

    public function findAll() {
        $this->db->query("SELECT * FROM {$this->table}");
        return $this->db->resultSet();
    }

    public function findById($id) {
        $this->db->query("SELECT * FROM {$this->table} WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function delete($id) {
        $this->db->query("DELETE FROM {$this->table} WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // Additional generic methods can go here (create, update using arrays etc)
    // Custom SQL will be run inside specific models
}
