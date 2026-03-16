<?php

class Tenant {
    public static function current() {
        // Obtenemos el tenant de la sesión (asignado durante el login unificado)
        if (Auth::check() && Session::has('tenant_id')) {
            $tenantId = Session::get('tenant_id');
            if ($tenantId) {
                $db = new Database();
                $db->query("SELECT * FROM tenants WHERE id = :id AND estado = 'activo' LIMIT 1");
                $db->bind(':id', $tenantId);
                $tenant = $db->single();
                return $tenant ? $tenant : null;
            }
        }

        return null;
    }
}
