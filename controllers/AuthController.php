<?php

class AuthController extends Controller {
    protected $userModel;
    public function __construct() {
        $this->userModel = $this->model('UserModel');
    }

    public function index() {
        if (Auth::check()) {
            if (Auth::user()->role_id == 1) {
                Helpers::redirect('superadmin/dashboard');
            } else {
                Helpers::redirect('gym/dashboard');
            }
        }
        $this->view('auth/login'); // Vista unificada
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!Session::verifyCsrfToken($_POST['csrf_token'])) {
                Helpers::flash('login_error', 'Token de seguridad inválido.', 'alert alert-danger');
                Helpers::redirect('auth/login');
            }

            $username = Helpers::sanitize($_POST['username']);
            $password = $_POST['password'];

            $user = $this->userModel->login($username, $password);

            if ($user) {
                if ($user->estado !== 'activo') {
                    Helpers::flash('login_error', 'Tu cuenta está inactiva.', 'alert alert-danger');
                    Helpers::redirect('auth/login');
                }

                Auth::login($user);

                if ($user->rol_id == 1) { // Super Admin
                    Helpers::redirect('superadmin/dashboard');
                } else { // Gym User
                    Helpers::redirect('gym/dashboard');
                }
            } else {
                Helpers::flash('login_error', 'Credenciales incorrectas.', 'alert alert-danger');
                Helpers::redirect('auth/login');
            }
        } else {
            // Display the login page for GET requests
            $this->index();
        }
    }

    public function logout() {
        Auth::logout();
        Helpers::redirect('auth/login');
    }
}
