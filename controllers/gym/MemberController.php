<?php

class MemberController extends Controller {
    protected $tenant;
    protected $memberModel;
    protected $tokenModel;
    public function __construct() {
        Auth::requireLogin();
        $this->tenant = Tenant::current();
        if (!$this->tenant || Auth::user()->tenant_id != $this->tenant->id) {
            Helpers::redirect('gym/auth/login');
        }
        $this->memberModel = $this->model('MemberModel');
        $this->tokenModel = $this->model('AccessTokenModel');
    }

    public function index() {
        $members = $this->memberModel->findAllByTenant($this->tenant->id);
        $this->view('gym/members/index', ['members' => $members]);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = Helpers::sanitize($_POST);
            $_POST['tenant_id'] = $this->tenant->id;

            $memberId = $this->memberModel->create($_POST);

            if ($memberId) {
                // Generate initial QR token
                $this->tokenModel->generateToken($this->tenant->id, $memberId);
                Helpers::flash('member_msg', 'Miembro creado y código QR generado exitosamente.');
            } else {
                Helpers::flash('member_msg', 'Error al crear miembro.', 'alert alert-danger');
            }
            Helpers::redirect('member/index');
        } else {
            $this->view('gym/members/form');
        }
    }
}
