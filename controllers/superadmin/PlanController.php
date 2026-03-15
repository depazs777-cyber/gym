<?php

class PlanController extends Controller {
    protected $planModel;
    public function __construct() {
        Auth::requireLogin();
        if (Auth::user()->role_id != 1) {
            Helpers::redirect('');
        }
        $this->planModel = $this->model('PlanModel');
    }

    public function index() {
        $plans = $this->planModel->findAll();
        $this->view('superadmin/planes/index', ['plans' => $plans]);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = Helpers::sanitize($_POST);
            $this->planModel->create($_POST);
            Helpers::flash('plan_msg', 'Plan creado exitosamente.');
            Helpers::redirect('plan');
        } else {
            $this->view('superadmin/planes/form');
        }
    }
}
