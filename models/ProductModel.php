<?php

class ProductModel extends Model {
    protected $table = 'products';

    public function getAllByTenant($tenant_id) {
        $this->db->query("SELECT * FROM {$this->table} WHERE tenant_id = :tenant_id AND estado = 'activo'");
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->resultSet();
    }
}
