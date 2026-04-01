<?php

class LeadModel extends Model {
    protected $table = 'leads';

    public function create($data) {
        $this->db->query("INSERT INTO {$this->table} (nombre_gym, contacto, email, telefono, estado, notas) VALUES (:nombre_gym, :contacto, :email, :telefono, :estado, :notas)");
        $this->db->bind(':nombre_gym', $data['nombre_gym']);
        $this->db->bind(':contacto', $data['contacto']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':telefono', $data['telefono']);
        $this->db->bind(':estado', $data['estado']);
        $this->db->bind(':notas', $data['notas']);
        return $this->db->execute();
    }

    public function updateStatus($id, $estado) {
        $this->db->query("UPDATE {$this->table} SET estado = :estado WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->bind(':estado', $estado);
        return $this->db->execute();
    }
}
