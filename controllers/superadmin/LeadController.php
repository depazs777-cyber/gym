<?php

class LeadController extends Controller {
    protected $leadModel;
    public function __construct() {
        Auth::requireLogin('superadmin');
        if (Auth::user()->role_id != 1) {
            Helpers::redirect('superadmin/auth/login');
        }
        $this->leadModel = $this->model('LeadModel');
    }

    public function index() {
        $leads = $this->leadModel->findAll();
        $this->view('superadmin/leads/index', ['leads' => $leads]);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = Helpers::sanitize($_POST);
            $this->leadModel->create($_POST);
            Helpers::flash('lead_msg', 'Lead creado exitosamente.');
            Helpers::redirect('lead');
        } else {
            $this->view('superadmin/leads/form');
        }
    }

    public function updateStatus() {
         if ($_SERVER['REQUEST_METHOD'] == 'POST') {
             // For drag and drop ajax updates
             $id = $_POST['id'];
             $estado = $_POST['estado'];
             $this->leadModel->updateStatus($id, $estado);
             echo json_encode(['success' => true]);
             return;
         }
    }
}
