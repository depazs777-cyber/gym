<?php

class AuthController extends Controller {
    public function __construct() {
        $this->userModel = $this->model('UserModel');
        $this->tenant = Tenant::current();
        if (!$this->tenant) {
            die("Subdominio no encontrado o gimnasio inactivo.");
        }
    }

    public function index() {
        if (Auth::check() && Auth::user()->tenant_id == $this->tenant->id) {
            Helpers::redirect('dashboard');
        }
        $this->view('auth/gym-login', ['tenant' => $this->tenant]);
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

            if ($user && $user->tenant_id == $this->tenant->id) { // Admin Gym / Recepcion
                Auth::login($user);
                Helpers::redirect('dashboard');
            } else {
                Helpers::flash('login_error', 'Credenciales incorrectas.', 'alert alert-danger');
                Helpers::redirect('auth/login');
            }
        }
    }

    public function logout() {
        Auth::logout();
        Helpers::redirect('auth/login');
    }
}
