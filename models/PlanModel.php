<?php

class PlanModel extends Model {
    protected $table = 'plans';

    public function create($data) {
        $this->db->query("INSERT INTO {$this->table} (nombre, precio, max_miembros, descripcion, estado) VALUES (:nombre, :precio, :max_miembros, :descripcion, :estado)");
        $this->db->bind(':nombre', $data['nombre']);
        $this->db->bind(':precio', $data['precio']);
        $this->db->bind(':max_miembros', $data['max_miembros']);
        $this->db->bind(':descripcion', $data['descripcion']);
        $this->db->bind(':estado', $data['estado']);
        return $this->db->execute();
    }

    public function update($id, $data) {
        $this->db->query("UPDATE {$this->table} SET nombre = :nombre, precio = :precio, max_miembros = :max_miembros, descripcion = :descripcion, estado = :estado WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->bind(':nombre', $data['nombre']);
        $this->db->bind(':precio', $data['precio']);
        $this->db->bind(':max_miembros', $data['max_miembros']);
        $this->db->bind(':descripcion', $data['descripcion']);
        $this->db->bind(':estado', $data['estado']);
        return $this->db->execute();
    }
}
