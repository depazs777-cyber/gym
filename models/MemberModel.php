<?php

class MemberModel extends Model {
    protected $table = 'members';

    public function findAllByTenant($tenant_id) {
        $this->db->query("SELECT * FROM {$this->table} WHERE tenant_id = :tenant_id ORDER BY nombre ASC");
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->resultSet();
    }

    public function create($data) {
        $this->db->query("INSERT INTO {$this->table} (tenant_id, identificacion, nombre, apellidos, email, telefono, fecha_inscripcion, estado) VALUES (:tenant_id, :identificacion, :nombre, :apellidos, :email, :telefono, :fecha_inscripcion, :estado)");
        $this->db->bind(':tenant_id', $data['tenant_id']);
        $this->db->bind(':identificacion', $data['identificacion']);
        $this->db->bind(':nombre', $data['nombre']);
        $this->db->bind(':apellidos', $data['apellidos']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':telefono', $data['telefono']);
        $this->db->bind(':fecha_inscripcion', date('Y-m-d'));
        $this->db->bind(':estado', 'activo');

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function renew($id, $tenant_id, $newDate) {
        $this->db->query("UPDATE {$this->table} SET fecha_vencimiento = :fecha_vencimiento, estado = 'activo' WHERE id = :id AND tenant_id = :tenant_id");
        $this->db->bind(':id', $id);
        $this->db->bind(':tenant_id', $tenant_id);
        $this->db->bind(':fecha_vencimiento', $newDate);
        return $this->db->execute();
    }
}
