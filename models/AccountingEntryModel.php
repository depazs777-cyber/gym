<?php

class AccountingEntryModel extends Model {
    protected $table = 'accounting_entries';

    public function createEntry($tenant_id, $tipo, $descripcion, $fecha) {
        // Obtener el siguiente número de comprobante para este tipo
        $this->db->query("SELECT MAX(numero) as max_num FROM {$this->table} WHERE tipo_comprobante = :tipo AND tenant_id = :tenant_id");
        $this->db->bind(':tipo', $tipo);
        $this->db->bind(':tenant_id', $tenant_id);
        $result = $this->db->single();
        $numero = $result->max_num ? $result->max_num + 1 : 1;

        $this->db->query("INSERT INTO {$this->table} (tenant_id, tipo_comprobante, numero, fecha, descripcion) VALUES (:tenant_id, :tipo, :numero, :fecha, :descripcion)");
        $this->db->bind(':tenant_id', $tenant_id);
        $this->db->bind(':tipo', $tipo);
        $this->db->bind(':numero', $numero);
        $this->db->bind(':fecha', $fecha);
        $this->db->bind(':descripcion', $descripcion);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function addDetail($entry_id, $account_id, $descripcion, $debito, $credito) {
        $this->db->query("INSERT INTO accounting_entry_details (entry_id, account_id, descripcion, debito, credito) VALUES (:entry_id, :account_id, :descripcion, :debito, :credito)");
        $this->db->bind(':entry_id', $entry_id);
        $this->db->bind(':account_id', $account_id);
        $this->db->bind(':descripcion', $descripcion);
        $this->db->bind(':debito', $debito);
        $this->db->bind(':credito', $credito);
        return $this->db->execute();
    }

    public function updateTotals($entry_id) {
        // Sumar débitos y créditos
        $this->db->query("SELECT SUM(debito) as total_debito, SUM(credito) as total_credito FROM accounting_entry_details WHERE entry_id = :entry_id");
        $this->db->bind(':entry_id', $entry_id);
        $totals = $this->db->single();

        $this->db->query("UPDATE {$this->table} SET total_debito = :total_debito, total_credito = :total_credito WHERE id = :entry_id");
        $this->db->bind(':total_debito', $totals->total_debito);
        $this->db->bind(':total_credito', $totals->total_credito);
        $this->db->bind(':entry_id', $entry_id);
        return $this->db->execute();
    }
}
