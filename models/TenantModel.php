<?php

class TenantModel extends Model {
    protected $table = 'tenants';

    public function getAllWithPlans() {
        $this->db->query("SELECT t.*, p.nombre as plan_nombre FROM {$this->table} t JOIN plans p ON t.plan_id = p.id");
        return $this->db->resultSet();
    }

    public function create($data) {
        $this->db->query("INSERT INTO {$this->table} (nombre, subdominio, plan_id, fecha_vencimiento, estado) VALUES (:nombre, :subdominio, :plan_id, :fecha_vencimiento, :estado)");
        $this->db->bind(':nombre', $data['nombre']);
        $this->db->bind(':subdominio', $data['subdominio']);
        $this->db->bind(':plan_id', $data['plan_id']);
        $this->db->bind(':fecha_vencimiento', $data['fecha_vencimiento']);
        $this->db->bind(':estado', $data['estado']);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }
}
