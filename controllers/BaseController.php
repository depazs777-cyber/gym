<?php defined('APP_NAME') or exit('No direct script access allowed');

class BaseController {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
    }

    protected function view($viewPath, $data = []) {
        // Extract data to make variables available in view
        extract($data);

        // Path to view file
        $fullPath = VIEW_PATH . '/' . $viewPath . '.php';

        if (file_exists($fullPath)) {
            require $fullPath;
        } else {
            die("View file not found: $viewPath");
        }
    }

    protected function redirect($url) {
        // Check if URL is absolute or relative to app
        if (strpos($url, 'http') === 0) {
            header("Location: $url");
        } else {
            // Use url() helper
            header("Location: " . url($url));
        }
        exit;
    }

    protected function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/');
        }
    }

    protected function checkRole($allowedRoles) {
        $this->checkAuth();
        
        // Enforce Gym Status Check for Gym Users (Tenant Isolation & Licensing)
        // If gym_id is set, the user is acting within a gym context.
        if (isset($_SESSION['gym_id']) && $_SESSION['gym_id'] > 0) {
            $this->verifyGymStatus($_SESSION['gym_id']);
        }

        if (!in_array($_SESSION['user_role'], $allowedRoles)) {
            die("Unauthorized access.");
        }
    }

    private function verifyGymStatus($gymId) {
        $db = Database::getInstance()->getConnection();
        // Check both status and license_end
        $stmt = $db->prepare("SELECT status, license_end FROM gyms WHERE id = ?");
        $stmt->execute([$gymId]);
        $gym = $stmt->fetch();

        if (!$gym) {
            $this->forceLogout('Gym not found.');
        }

        if ($gym['status'] !== 'active') {
             $this->forceLogout('Gimnasio suspendido o inactivo.');
        }

        $today = date('Y-m-d');
        if (empty($gym['license_end']) || $gym['license_end'] < $today) {
             $this->forceLogout('Licencia del Gimnasio vencida. Contacte a soporte.');
        }
    }

    private function forceLogout($message) {
        // Destroy session and redirect
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        // Restart session to flash message
        session_start();
        $_SESSION['error'] = $message;
        $this->redirect('/');
    }

    protected function verifyCsrf() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST[CSRF_TOKEN_NAME]) || $_POST[CSRF_TOKEN_NAME] !== $_SESSION[CSRF_TOKEN_NAME]) {
                die("CSRF Token Validation Failed.");
            }
        }
    }
}
