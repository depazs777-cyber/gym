<?php

class AccountingAccountModel extends Model {
    protected $table = 'accounting_accounts';

    public function getAccounts($tenant_id) {
        // Cuentas base PUC + cuentas propias del tenant
        $this->db->query("SELECT * FROM {$this->table} WHERE tenant_id IS NULL OR tenant_id = :tenant_id ORDER BY codigo ASC");
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->resultSet();
    }

    public function getByCode($codigo, $tenant_id) {
        $this->db->query("SELECT * FROM {$this->table} WHERE codigo = :codigo AND (tenant_id IS NULL OR tenant_id = :tenant_id) LIMIT 1");
        $this->db->bind(':codigo', $codigo);
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->single();
    }
}
