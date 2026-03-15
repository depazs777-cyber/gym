<?php

class AccessLogModel extends Model {
    protected $table = 'access_logs';

    public function logAccess($tenant_id, $member_id, $token_uuid, $estado_acceso, $motivo = null) {
        // Determinar si es entrada o salida basado en el último registro
        $this->db->query("SELECT tipo FROM {$this->table} WHERE member_id = :member_id AND tenant_id = :tenant_id ORDER BY id DESC LIMIT 1");
        $this->db->bind(':member_id', $member_id);
        $this->db->bind(':tenant_id', $tenant_id);
        $lastLog = $this->db->single();

        $tipo = 'entrada';
        if ($lastLog && $lastLog->tipo === 'entrada' && $estado_acceso === 'permitido') {
            $tipo = 'salida';
        }

        $this->db->query("INSERT INTO {$this->table} (tenant_id, member_id, token_usado, tipo, estado_acceso, motivo_denegacion) VALUES (:tenant_id, :member_id, :token_usado, :tipo, :estado_acceso, :motivo)");
        $this->db->bind(':tenant_id', $tenant_id);
        $this->db->bind(':member_id', $member_id);
        $this->db->bind(':token_usado', $token_uuid);
        $this->db->bind(':tipo', $tipo);
        $this->db->bind(':estado_acceso', $estado_acceso);
        $this->db->bind(':motivo', $motivo);

        return $this->db->execute();
    }

    // Regla Anti-passback: verifica si el último acceso permitido fue una entrada
    public function checkAntiPassback($tenant_id, $member_id) {
        // Obtenemos los accesos de hoy
        $hoy = date('Y-m-d');
        $this->db->query("SELECT tipo FROM {$this->table} WHERE member_id = :member_id AND tenant_id = :tenant_id AND DATE(fecha_hora) = :hoy AND estado_acceso = 'permitido' ORDER BY id DESC LIMIT 1");
        $this->db->bind(':member_id', $member_id);
        $this->db->bind(':tenant_id', $tenant_id);
        $this->db->bind(':hoy', $hoy);

        $lastLog = $this->db->single();

        if ($lastLog && $lastLog->tipo === 'entrada') {
            // Ya está adentro, podría ser un intento de passback a menos que permitamos reingresos,
            // pero para una estricta anti-passback, lo bloqueamos.
            return false;
        }
        return true;
    }
}
