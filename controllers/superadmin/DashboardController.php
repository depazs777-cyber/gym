<?php

class DashboardController extends Controller {
    protected $tenantModel;
    protected $leadModel;
    public function __construct() {
        Auth::requireLogin();
        if (Auth::user()->role_id != 1) { // Not super admin
            Auth::logout();
            Helpers::redirect('');
        }
        $this->tenantModel = $this->model('TenantModel');
        $this->leadModel = $this->model('LeadModel');
    }

    public function index() {
        $data = [
            'title' => 'Dashboard Super Admin',
            'tenants' => $this->tenantModel->findAll(),
            'leads' => $this->leadModel->findAll()
        ];
        $this->view('superadmin/dashboard', $data);
    }
}
