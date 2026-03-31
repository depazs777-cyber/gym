<?php

namespace App\Controllers\Admin;

use App\Core\Controller;

class LeadsController extends Controller {

    public function index() {
        $stmt = $this->db->query("SELECT * FROM leads ORDER BY created_at DESC");
        $leads = $stmt->fetchAll();
        $this->view('admin/leads/index', ['leads' => $leads]);
    }

    public function create() {
        $this->view('admin/leads/create');
    }

    public function store() {
        $data = [
            'company_name' => $_POST['company_name'] ?? '',
            'contact_name' => $_POST['contact_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'notes' => $_POST['notes'] ?? '',
            'status' => 'new'
        ];

        try {
            $stmt = $this->db->prepare("
                INSERT INTO leads (company_name, contact_name, email, phone, notes, status)
                VALUES (:company_name, :contact_name, :email, :phone, :notes, :status)
            ");
            $stmt->execute($data);
            $this->redirect(BASE_URL . '/admin/leads');
        } catch (\PDOException $e) {
            die("Error al registrar lead: " . $e->getMessage());
        }
    }

    public function updateStatus() {
        $id = $_POST['id'] ?? null;
        $status = $_POST['status'] ?? 'new';

        if ($id) {
            $stmt = $this->db->prepare("UPDATE leads SET status = :status WHERE id = :id");
            $stmt->execute([':status' => $status, ':id' => $id]);
        }
        $this->redirect(BASE_URL . '/admin/leads');
    }
}
