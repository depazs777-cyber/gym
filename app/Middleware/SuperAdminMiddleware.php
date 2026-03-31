<?php

namespace App\Middleware;

class SuperAdminMiddleware {
    public function handle() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        if ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'sales') {
            http_response_code(403);
            die("Acceso Denegado: Área exclusiva de la empresa matriz (SaaS).");
        }
        return true;
    }
}
