<?php

class AuthController extends Controller {
    protected $userModel;
    protected $tenant;
    public function __construct() {
        $this->userModel = $this->model('UserModel');
        $this->tenant = Tenant::current();
        if (!$this->tenant) {
            // Check if we're dealing with a database connection error directly
            // or if the tenant really doesn't exist
            $msg = "Error: Subdominio no encontrado, gimnasio inactivo, o falta configurar el tenant.<br><br>";
            $msg .= "<b>Sugerencia para entorno local:</b> Si estás probando en localhost, añade <code>?tenant=subdominio_del_gimnasio</code> a la URL (por ejemplo: <code>?tenant=gimnasio1</code>) o asegúrate de que el gimnasio exista en la base de datos.";
            die($msg);
        }
    }

    public function index() {
        if (Auth::check() && Auth::user()->tenant_id == $this->tenant->id) {
            $urlParam = isset($_GET['tenant']) ? '?tenant=' . $_GET['tenant'] : '';
            Helpers::redirect('gym/dashboard' . $urlParam);
        }
        $this->view('auth/gym-login', ['tenant' => $this->tenant]);
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!Session::verifyCsrfToken($_POST['csrf_token'])) {
                Helpers::flash('login_error', 'Token de seguridad inválido.', 'alert alert-danger');
                Helpers::redirect('gym/auth/login');
            }

            $username = Helpers::sanitize($_POST['username']);
            $password = $_POST['password'];

            $user = $this->userModel->login($username, $password);

            if ($user && $user->tenant_id == $this->tenant->id) { // Admin Gym / Recepcion
                Auth::login($user);
                // In local dev, pass tenant along to maintain session scope on subdomains/url params
                $urlParam = isset($_GET['tenant']) ? '?tenant=' . $_GET['tenant'] : '';
                Helpers::redirect('gym/dashboard' . $urlParam);
            } else {
                Helpers::flash('login_error', 'Credenciales incorrectas o usuario no pertenece a este gimnasio.', 'alert alert-danger');
                $urlParam = isset($_GET['tenant']) ? '?tenant=' . $_GET['tenant'] : '';
                Helpers::redirect('gym/auth/login' . $urlParam);
            }
        } else {
            // Display the login page for GET requests
            $this->index();
        }
    }

    public function logout() {
        Auth::logout();
        Helpers::redirect('gym/auth/login');
    }
}
