<?php

namespace App\Services;

use App\Core\Database;

class AuthService {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['status'] !== 'active') {
                return ['success' => false, 'message' => 'Cuenta inactiva.'];
            }

            // Regenerar ID de sesión para evitar fijación
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['gym_id'] = $user['gym_id'];

            // Generar Token CSRF
            if (empty($_SESSION[CSRF_TOKEN_NAME])) {
                $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
            }

            return ['success' => true];
        }

        return ['success' => false, 'message' => 'Credenciales incorrectas.'];
    }

    public function logout() {
        session_unset();
        session_destroy();
    }
}
