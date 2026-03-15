<?php

class AuthController extends Controller {
    public function __construct() {
        $this->userModel = $this->model('UserModel');
    }

    public function index() {
        if (Auth::check() && Auth::user()->role_id == 1) {
            Helpers::redirect('dashboard');
        }
        $this->view('auth/superadmin-login');
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!Session::verifyCsrfToken($_POST['csrf_token'])) {
                Helpers::flash('login_error', 'Token de seguridad inválido.', 'alert alert-danger');
                Helpers::redirect('');
            }

            $username = Helpers::sanitize($_POST['username']);
            $password = $_POST['password'];

            $user = $this->userModel->login($username, $password);

            if ($user && $user->rol_id == 1) { // Super Admin
                Auth::login($user);
                Helpers::redirect('dashboard');
            } else {
                Helpers::flash('login_error', 'Credenciales incorrectas o no tienes permisos.', 'alert alert-danger');
                Helpers::redirect('');
            }
        }
    }

    public function logout() {
        Auth::logout();
        Helpers::redirect('');
    }
}
