<?php

class DashboardController extends Controller {
    protected $tenant;
    protected $memberModel;
    public function __construct() {
        Auth::requireLogin();
        $this->tenant = Tenant::current();
        if (!$this->tenant || Auth::user()->tenant_id != $this->tenant->id) {
            Auth::logout();
            Helpers::redirect('auth/login');
        }
        $this->memberModel = $this->model('MemberModel');
    }

    public function index() {
        $data = [
            'title' => 'Dashboard Gym',
            'tenant' => $this->tenant,
            'members' => $this->memberModel->findAllByTenant($this->tenant->id)
        ];
        $this->view('gym/dashboard', $data);
    }
}
