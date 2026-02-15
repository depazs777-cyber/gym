<?php defined('APP_NAME') or exit('No direct script access allowed');

class GymStaffController extends BaseController {
    
    public function __construct() {
        $this->checkRole(['ADMIN_GYM']);
        if (!isset($_SESSION['gym_id'])) {
            die("No Gym ID.");
        }
    }

    public function index() {
        $gymId = $_SESSION['gym_id'];
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("SELECT * FROM users WHERE gym_id = ? ORDER BY name ASC");
        $stmt->execute([$gymId]);
        $users = $stmt->fetchAll();

        $this->view('layouts/main', [
            'childView' => 'gym/staff_list',
            'users' => $users
        ]);
    }

    public function store() {
        $this->verifyCsrf();
        $gymId = $_SESSION['gym_id'];
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';

        if (empty($name) || empty($email) || empty($password) || empty($role)) {
            $_SESSION['error'] = 'All fields required.';
            $this->redirect('/gym/staff');
        }

        $allowed = ['RECEPCION', 'ENTRENADOR', 'CONSULTA_REPORTES'];
        if (!in_array($role, $allowed)) {
            $_SESSION['error'] = 'Invalid Role.';
            $this->redirect('/gym/staff');
        }

        $db = Database::getInstance()->getConnection();
        
        // Unique Email Check
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Email already exists.';
            $this->redirect('/gym/staff');
        }

        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (gym_id, name, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$gymId, $name, $email, $hash, $role]);
            $_SESSION['success'] = 'Staff created.';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        $this->redirect('/gym/staff');
    }

    public function toggle() {
        $this->verifyCsrf();
        $gymId = $_SESSION['gym_id'];
        $id = $_POST['id'] ?? null;

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT status FROM users WHERE id = ? AND gym_id = ?");
        $stmt->execute([$id, $gymId]);
        $user = $stmt->fetch();

        if ($user) {
            $new = ($user['status'] === 'active') ? 'inactive' : 'active';
            $upd = $db->prepare("UPDATE users SET status = ? WHERE id = ?");
            $upd->execute([$new, $id]);
            $_SESSION['success'] = 'Status updated.';
        }
        $this->redirect('/gym/staff');
    }
}
