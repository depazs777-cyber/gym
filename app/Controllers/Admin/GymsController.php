<?php

namespace App\Controllers\Admin;

use App\Core\Controller;

class GymsController extends Controller {

    public function index() {
        $stmt = $this->db->query("
            SELECT t.*, p.name as plan_name,
                   (SELECT COUNT(*) FROM members WHERE gym_id = t.id) as total_members
            FROM tenants t
            LEFT JOIN saas_plans p ON t.plan_id = p.id
            ORDER BY t.created_at DESC
        ");
        $gyms = $stmt->fetchAll();
        $this->view('admin/gyms/index', ['gyms' => $gyms]);
    }

    public function create() {
        $plans = $this->db->query("SELECT * FROM saas_plans WHERE status = 'active'")->fetchAll();
        $this->view('admin/gyms/create', ['plans' => $plans]);
    }

    public function store() {
        $name = $_POST['name'] ?? '';
        $plan_id = $_POST['plan_id'] ?? null;
        $domain = $_POST['domain'] ?? '';

        try {
            $stmt = $this->db->prepare("
                INSERT INTO tenants (name, domain, plan_id, valid_until, status)
                VALUES (:name, :domain, :plan_id, DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'active')
            ");
            $stmt->execute([
                ':name' => $name,
                ':domain' => $domain,
                ':plan_id' => $plan_id
            ]);
            $this->redirect(BASE_URL . '/admin/gyms');
        } catch (\PDOException $e) {
            die("Error al registrar gimnasio: " . $e->getMessage());
        }
    }

    public function toggleStatus() {
        $id = $_POST['id'] ?? null;
        $status = $_POST['status'] ?? 'active';

        if ($id) {
            $stmt = $this->db->prepare("UPDATE tenants SET status = :status WHERE id = :id");
            $stmt->execute([':status' => $status, ':id' => $id]);
        }
        $this->redirect(BASE_URL . '/admin/gyms');
    }
}
