<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuthService;

class AuthController extends Controller {
    protected $authService;

    public function __construct() {
        parent::__construct();
        $this->authService = new AuthService();
    }

    public function login() {
        if (isset($_SESSION['user_id'])) {
            $this->redirect(BASE_URL . '/dashboard');
        }
        $this->view('auth/login');
    }

    public function authenticate() {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $result = $this->authService->login($email, $password);

        if ($result['success']) {
            $this->redirect(BASE_URL . '/dashboard');
        } else {
            $this->view('auth/login', ['error' => $result['message']]);
        }
    }

    public function logout() {
        $this->authService->logout();
        $this->redirect(BASE_URL . '/login');
    }
}
