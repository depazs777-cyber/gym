<?php

class AccessController extends Controller {
    protected $tenant;
    protected $tokenModel;
    protected $logModel;
    public function __construct() {
        Auth::requireLogin();
        $this->tenant = Tenant::current();
        if (!$this->tenant || Auth::user()->tenant_id != $this->tenant->id) {
            Helpers::redirect('auth/login');
        }
        $this->tokenModel = $this->model('AccessTokenModel');
        $this->logModel = $this->model('AccessLogModel');
    }

    public function index() {
        $this->view('gym/access/index');
    }

    public function validator() {
        $this->view('gym/access/validator');
    }

    public function validate() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $token = $_POST['token'] ?? '';

            $result = $this->tokenModel->validateToken($token, $this->tenant->id);

            if (!$result['valid']) {
                if (isset($result['member'])) {
                     $this->logModel->logAccess($this->tenant->id, $result['member']->member_id, $token, 'denegado', $result['message']);
                }
                echo json_encode(['success' => false, 'message' => $result['message']]);
                return;
            }

            $member = $result['member'];

            // Regla Anti-passback
            $passbackValid = $this->logModel->checkAntiPassback($this->tenant->id, $member->member_id);
            if (!$passbackValid) {
                $this->logModel->logAccess($this->tenant->id, $member->member_id, $token, 'denegado', 'Regla anti-passback: ya registró entrada.');
                echo json_encode(['success' => false, 'message' => 'Error anti-passback: el miembro ya se encuentra dentro del gimnasio.']);
                return;
            }

            // Registrar acceso exitoso
            $this->logModel->logAccess($this->tenant->id, $member->member_id, $token, 'permitido');

            echo json_encode([
                'success' => true,
                'message' => 'Acceso permitido',
                'member' => [
                    'nombre' => $member->nombre . ' ' . $member->apellidos,
                    'foto' => $member->foto
                ]
            ]);
            return;
        }
    }
}
