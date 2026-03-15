<?php defined('APP_NAME') or exit('No direct script access allowed');

class AuthController extends BaseController {

    public function login() {
        if (isset($_SESSION['user_id'])) {
            $this->redirectBasedOnRole($_SESSION['user_role']);
        }
        $this->view('auth/login');
    }

    public function authenticate() {
        $this->verifyCsrf();
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Please fill in all fields.';
            $this->redirect('/');
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['status'] !== 'active') {
                $_SESSION['error'] = 'Account is inactive or suspended.';
                $this->redirect('/');
            }

            // SaaS License Check
            if ($user['gym_id']) {
                $gymModel = new Gym();
                $gym = $gymModel->findById($user['gym_id']);

                if (!$gym) {
                     $_SESSION['error'] = 'Gym not found.';
                     $this->redirect('/');
                }

                if ($gym['status'] !== 'active') {
                    // Log attempt?
                    $_SESSION['error'] = 'Licencia vencida o suspendida. Contacte a soporte para renovar.';
                    $this->redirect('/');
                }

                $today = date('Y-m-d');
                if ($gym['license_end'] < $today) {
                    $_SESSION['error'] = 'Licencia vencida o suspendida. Contacte a soporte para renovar.';
                    $this->redirect('/');
                }
            }

            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['gym_id'] = $user['gym_id'];

            $this->redirectBasedOnRole($user['role']);

        } else {
            $_SESSION['error'] = 'Invalid credentials.';
            $this->redirect('/');
        }
    }

    public function logout() {
        session_destroy();
        $this->redirect('/');
    }

    private function redirectBasedOnRole($role) {
        $saasRoles = ['SUPER_ADMIN', 'VENDEDOR', 'MARKETING', 'CALL_CENTER', 'FINANZAS', 'SOPORTE', 'DEV', 'SEGURIDAD'];
        $gymRoles = ['ADMIN_GYM', 'RECEPCION', 'ENTRENADOR', 'CONSULTA_REPORTES'];

        if (in_array($role, $saasRoles)) {
            $this->redirect('/admin/dashboard');
        } elseif (in_array($role, $gymRoles)) {
            $this->redirect('/gym/dashboard');
        } else {
            // Unexpected role
            session_destroy();
            $this->redirect('/');
        }
    }
}
