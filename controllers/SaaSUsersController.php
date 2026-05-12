<?php defined('APP_NAME') or exit('No direct script access allowed');

class SaaSUsersController extends BaseController {
    
    public function __construct() {
        // Only Super Admin can manage internal users
        $this->checkRole(['SUPER_ADMIN']);
    }

    public function index() {
        $db = (new Database())->getConnection();
        // Fetch users where gym_id is NULL
        $stmt = $db->query("SELECT * FROM users WHERE gym_id IS NULL ORDER BY created_at DESC");
        $users = $stmt->fetchAll();

        $this->view('layouts/main', [
            'childView' => 'admin/users',
            'users' => $users
        ]);
    }

    public function store() {
        $this->verifyCsrf();
        
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $role = $_POST['role'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($name) || empty($email) || empty($role) || empty($password)) {
            $_SESSION['error'] = 'All fields are required.';
            $this->redirect('/admin/users');
        }

        // Validate Role (Must be a SaaS role)
        $allowedRoles = ['SUPER_ADMIN', 'VENDEDOR', 'MARKETING', 'CALL_CENTER', 'FINANZAS', 'SOPORTE', 'DEV', 'SEGURIDAD'];
        if (!in_array($role, $allowedRoles)) {
            $_SESSION['error'] = 'Invalid Role.';
            $this->redirect('/admin/users');
        }

        $db = (new Database())->getConnection();

        // Check if email exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Email already exists.';
            $this->redirect('/admin/users');
        }

        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (gym_id, name, email, password_hash, role, status) VALUES (NULL, ?, ?, ?, ?, 'active')");
            $stmt->execute([$name, $email, $hash, $role]);

            $_SESSION['success'] = 'Internal user created successfully.';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        }

        $this->redirect('/admin/users');
    }

    public function update() {
        $this->verifyCsrf();
        
        $id = $_POST['id'] ?? null;
        $name = $_POST['name'] ?? '';
        $role = $_POST['role'] ?? '';

        if (!$id || empty($name) || empty($role)) {
            $_SESSION['error'] = 'Invalid data.';
            $this->redirect('/admin/users');
        }

        // Validate Role
        $allowedRoles = ['SUPER_ADMIN', 'VENDEDOR', 'MARKETING', 'CALL_CENTER', 'FINANZAS', 'SOPORTE', 'DEV', 'SEGURIDAD'];
        if (!in_array($role, $allowedRoles)) {
            $_SESSION['error'] = 'Invalid Role.';
            $this->redirect('/admin/users');
        }

        $db = (new Database())->getConnection();

        try {
            // Ensure we are updating a SaaS user (gym_id IS NULL)
            $stmt = $db->prepare("UPDATE users SET name = ?, role = ? WHERE id = ? AND gym_id IS NULL");
            $result = $stmt->execute([$name, $role, $id]);
            
            if ($stmt->rowCount() > 0) {
                 $_SESSION['success'] = 'User updated successfully.';
            } else {
                 $_SESSION['error'] = 'User not found or not a SaaS user.';
            }

        } catch (PDOException $e) {
            $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        }
        
        $this->redirect('/admin/users');
    }

    public function toggleStatus() {
        $this->verifyCsrf();
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            $this->redirect('/admin/users');
        }
        
        // Prevent disabling self
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = 'You cannot disable your own account.';
            $this->redirect('/admin/users');
        }

        $db = (new Database())->getConnection();
        $stmt = $db->prepare("SELECT status FROM users WHERE id = ? AND gym_id IS NULL");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if ($user) {
            $newStatus = ($user['status'] === 'active') ? 'inactive' : 'active';
            $upd = $db->prepare("UPDATE users SET status = ? WHERE id = ?");
            $upd->execute([$newStatus, $id]);
            $_SESSION['success'] = "User status changed to $newStatus.";
        } else {
            $_SESSION['error'] = 'User not found.';
        }

        $this->redirect('/admin/users');
    }

    public function resetPassword() {
        $this->verifyCsrf();
        $id = $_POST['id'] ?? null;
        $password = $_POST['password'] ?? '';
        
        if (!$id || empty($password)) {
             $_SESSION['error'] = 'Password required.';
             $this->redirect('/admin/users');
        }

        $db = (new Database())->getConnection();
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ? AND gym_id IS NULL");
        $stmt->execute([$hash, $id]);
        
        $_SESSION['success'] = 'Password reset successfully.';
        $this->redirect('/admin/users');
    }
}
