<?php

namespace App\Controllers\Admin;

use App\Core\Controller;

class PlansController extends Controller {

    public function index() {
        $stmt = $this->db->query("SELECT * FROM saas_plans ORDER BY price ASC");
        $plans = $stmt->fetchAll();
        $this->view('admin/plans/index', ['plans' => $plans]);
    }

    public function create() {
        $this->view('admin/plans/create');
    }

    public function store() {
        $name = $_POST['name'] ?? '';
        $price = $_POST['price'] ?? 0;
        $max_members = $_POST['max_members'] ?? 500;

        try {
            $stmt = $this->db->prepare("
                INSERT INTO saas_plans (name, price, max_members, status)
                VALUES (:name, :price, :max_members, 'active')
            ");
            $stmt->execute([
                ':name' => $name,
                ':price' => $price,
                ':max_members' => $max_members
            ]);
            $this->redirect(BASE_URL . '/admin/plans');
        } catch (\PDOException $e) {
            die("Error al registrar plan: " . $e->getMessage());
        }
    }

    public function toggleStatus() {
        $id = $_POST['id'] ?? null;
        $status = $_POST['status'] ?? 'active';

        if ($id) {
            $stmt = $this->db->prepare("UPDATE saas_plans SET status = :status WHERE id = :id");
            $stmt->execute([':status' => $status, ':id' => $id]);
        }
        $this->redirect(BASE_URL . '/admin/plans');
    }
}
