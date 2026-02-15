<?php defined('APP_NAME') or exit('No direct script access allowed');

class PlanController extends BaseController {
    
    public function __construct() {
        $this->checkRole(['ADMIN_GYM']);
        if (!isset($_SESSION['gym_id'])) {
            die("No Gym ID associated with this session.");
        }
    }

    public function index() {
        $gymId = $_SESSION['gym_id'];
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("SELECT * FROM plans WHERE gym_id = ? ORDER BY name ASC");
        $stmt->execute([$gymId]);
        $plans = $stmt->fetchAll();

        $this->view('layouts/main', [
            'childView' => 'gym/plans_list',
            'plans' => $plans
        ]);
    }

    public function create() {
        $this->view('layouts/main', [
            'childView' => 'gym/plans_create'
        ]);
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('/gym/plans');
        }

        $gymId = $_SESSION['gym_id'];
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM plans WHERE id = ? AND gym_id = ?");
        $stmt->execute([$id, $gymId]);
        $plan = $stmt->fetch();

        if (!$plan) {
            $_SESSION['error'] = 'Plan not found.';
            $this->redirect('/gym/plans');
        }

        $this->view('layouts/main', [
            'childView' => 'gym/plans_edit',
            'plan' => $plan
        ]);
    }

    public function store() {
        $this->verifyCsrf();
        $gymId = $_SESSION['gym_id'];
        $name = $_POST['name'] ?? '';
        $type = $_POST['type'] ?? '';
        $price = $_POST['price'] ?? 0;
        $duration = $_POST['duration'] ?? 0;
        $sessions = $_POST['sessions'] ?? 0;

        if (empty($name) || empty($type) || empty($price)) {
            $_SESSION['error'] = 'Name, Type and Price are required.';
            $this->redirect('/gym/plans/create');
        }

        $db = Database::getInstance()->getConnection();

        try {
            $stmt = $db->prepare("INSERT INTO plans (gym_id, name, type, duration_days, sessions_count, price, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$gymId, $name, $type, $duration, $sessions, $price]);

            $_SESSION['success'] = 'Plan created successfully.';
            $this->redirect('/gym/plans');
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error creating plan: ' . $e->getMessage();
            $this->redirect('/gym/plans/create');
        }
    }

    public function update() {
        $this->verifyCsrf();
        $gymId = $_SESSION['gym_id'];
        $id = $_POST['id'] ?? null;
        $name = $_POST['name'] ?? '';
        $price = $_POST['price'] ?? 0;
        $duration = $_POST['duration'] ?? 0;
        $sessions = $_POST['sessions'] ?? 0;
        $status = $_POST['status'] ?? 'active';

        if (!$id) {
            $this->redirect('/gym/plans');
        }

        $db = Database::getInstance()->getConnection();

        try {
            // Verify ownership
            $stmt = $db->prepare("SELECT id FROM plans WHERE id = ? AND gym_id = ?");
            $stmt->execute([$id, $gymId]);
            if (!$stmt->fetch()) {
                die("Unauthorized");
            }

            $stmt = $db->prepare("UPDATE plans SET name = ?, price = ?, duration_days = ?, sessions_count = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $price, $duration, $sessions, $status, $id]);

            $_SESSION['success'] = 'Plan updated successfully.';
            $this->redirect('/gym/plans');
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error updating plan: ' . $e->getMessage();
            $this->redirect("/gym/plans/edit?id=$id");
        }
    }
}
