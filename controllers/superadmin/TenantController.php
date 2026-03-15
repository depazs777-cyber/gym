<?php

class TenantController extends Controller {
    protected $tenantModel;
    protected $planModel;
    public function __construct() {
        Auth::requireLogin();
        if (Auth::user()->role_id != 1) {
            Helpers::redirect('');
        }
        $this->tenantModel = $this->model('TenantModel');
        $this->planModel = $this->model('PlanModel');
    }

    public function index() {
        $tenants = $this->tenantModel->getAllWithPlans();
        $this->view('superadmin/tenants/index', ['tenants' => $tenants]);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = Helpers::sanitize($_POST);
            $tenantId = $this->tenantModel->create($_POST);

            if ($tenantId) {
                // Here we would create the admin user for the tenant
                // and potentially run tenant-specific database setup if needed
                Helpers::flash('tenant_msg', 'Gimnasio creado exitosamente.');
            } else {
                Helpers::flash('tenant_msg', 'Error al crear el gimnasio.', 'alert alert-danger');
            }
            Helpers::redirect('tenant');
        } else {
            $plans = $this->planModel->findAll();
            $this->view('superadmin/tenants/form', ['plans' => $plans]);
        }
    }
}
