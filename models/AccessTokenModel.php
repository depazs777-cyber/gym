<?php

class AccessTokenModel extends Model {
    protected $table = 'access_tokens';

    public function generateToken($tenant_id, $member_id) {
        // Desactivar tokens anteriores
        $this->db->query("UPDATE {$this->table} SET estado = 'expirado' WHERE member_id = :member_id AND estado = 'activo'");
        $this->db->bind(':member_id', $member_id);
        $this->db->execute();

        // Crear nuevo token UUID
        $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );

        $creado_en = date('Y-m-d H:i:s');
        $expira_en = date('Y-m-d H:i:s', strtotime('+24 hours')); // QR expira en 24h

        $this->db->query("INSERT INTO {$this->table} (tenant_id, member_id, token_uuid, creado_en, expira_en, estado) VALUES (:tenant_id, :member_id, :token_uuid, :creado_en, :expira_en, 'activo')");
        $this->db->bind(':tenant_id', $tenant_id);
        $this->db->bind(':member_id', $member_id);
        $this->db->bind(':token_uuid', $uuid);
        $this->db->bind(':creado_en', $creado_en);
        $this->db->bind(':expira_en', $expira_en);

        if ($this->db->execute()) {
            return $uuid;
        }
        return false;
    }

    public function validateToken($uuid, $tenant_id) {
        $this->db->query("SELECT t.*, m.estado as member_estado, m.fecha_vencimiento, m.nombre, m.apellidos, m.foto
                          FROM {$this->table} t
                          JOIN members m ON t.member_id = m.id
                          WHERE t.token_uuid = :uuid AND t.tenant_id = :tenant_id AND t.estado = 'activo'");
        $this->db->bind(':uuid', $uuid);
        $this->db->bind(':tenant_id', $tenant_id);

        $token = $this->db->single();

        if (!$token) {
            return ['valid' => false, 'message' => 'Token inválido o expirado.'];
        }

        // Validar expiración de fecha del QR
        if (strtotime($token->expira_en) < time()) {
            $this->markAsExpired($token->id);
            return ['valid' => false, 'message' => 'El código QR ha expirado. Genera uno nuevo.'];
        }

        // Validar estado del miembro
        if ($token->member_estado !== 'activo') {
            return ['valid' => false, 'message' => 'Membresía inactiva o con morosidad.'];
        }

        if (strtotime($token->fecha_vencimiento) < strtotime(date('Y-m-d'))) {
            return ['valid' => false, 'message' => 'Membresía vencida.'];
        }

        return ['valid' => true, 'member' => $token];
    }

    public function markAsExpired($id) {
        $this->db->query("UPDATE {$this->table} SET estado = 'expirado' WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
