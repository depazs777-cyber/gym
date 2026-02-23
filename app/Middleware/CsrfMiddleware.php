<?php

namespace App\Middleware;

class CsrfMiddleware {
    public function handle() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'DELETE') {
            if (!isset($_POST[CSRF_TOKEN_NAME]) || !hash_equals($_SESSION[CSRF_TOKEN_NAME] ?? '', $_POST[CSRF_TOKEN_NAME])) {
                http_response_code(403);
                die("Error de seguridad: Token CSRF inválido.");
            }
        }
        return true;
    }
}
