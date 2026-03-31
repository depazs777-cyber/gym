<?php

namespace App\Middleware;

class TenantMiddleware {
    public function handle() {
        if (!isset($_SESSION['gym_id'])) {
            // Si es Super Admin, podría no tener gym_id, pero para rutas de Tenant es obligatorio
            if (isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin') {
                return true;
            }

            http_response_code(403);
            echo "Acceso denegado: No se ha seleccionado un gimnasio válido.";
            exit;
        }
        return true;
    }
}
