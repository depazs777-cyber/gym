<?php

class SaleModel extends Model {
    protected $table = 'sales';

    public function createSale($tenant_id, $member_id, $total, $metodo) {
        $this->db->query("INSERT INTO {$this->table} (tenant_id, member_id, total, metodo_pago) VALUES (:tenant_id, :member_id, :total, :metodo)");
        $this->db->bind(':tenant_id', $tenant_id);
        $this->db->bind(':member_id', $member_id);
        $this->db->bind(':total', $total);
        $this->db->bind(':metodo', $metodo);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function addDetail($sale_id, $product_id, $cantidad, $precio, $subtotal) {
        $this->db->query("INSERT INTO sale_details (sale_id, product_id, cantidad, precio_unitario, subtotal) VALUES (:sale_id, :product_id, :cantidad, :precio, :subtotal)");
        $this->db->bind(':sale_id', $sale_id);
        $this->db->bind(':product_id', $product_id);
        $this->db->bind(':cantidad', $cantidad);
        $this->db->bind(':precio', $precio);
        $this->db->bind(':subtotal', $subtotal);

        // Disminuir stock
        $this->db->query("UPDATE products SET stock = stock - :cantidad WHERE id = :product_id");
        $this->db->bind(':cantidad', $cantidad);
        $this->db->bind(':product_id', $product_id);
        $this->db->execute();

        return true;
    }
}
