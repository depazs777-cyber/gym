<?php defined('APP_NAME') or exit('No direct script access allowed');
class AttendanceController extends BaseController {
    
    public function __construct() {
        $this->checkRole(['ADMIN_GYM', 'RECEPCION', 'ENTRENADOR']);
        if (!isset($_SESSION['gym_id'])) {
            die("No Gym ID associated with this session.");
        }
    }

    public function index() {
        $gymId = $_SESSION['gym_id'];
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT a.*, c.name as client_name, c.identification
            FROM attendance a
            JOIN clients c ON a.client_id = c.id
            WHERE a.gym_id = ?
            ORDER BY a.access_time DESC
            LIMIT 50
        ");
        $stmt->execute([$gymId]);
        $logs = $stmt->fetchAll();

        $this->view('layouts/main', [
            'childView' => 'gym/attendance_list',
            'logs' => $logs
        ]);
    }

    public function checkin() {
        $this->view('layouts/main', [
            'childView' => 'gym/attendance_checkin'
        ]);
    }

    public function verify() {
        $this->verifyCsrf();
        $gymId = $_SESSION['gym_id'];
        $identification = $_POST['identification'] ?? '';

        if (empty($identification)) {
            $_SESSION['error'] = 'Identification required.';
            $this->redirect('/gym/attendance/checkin');
        }

        $db = Database::getInstance()->getConnection();

        // 1. Find Client
        // Check if identification is "CLIENT:{ID}" format (QR) or plain identification
        if (strpos($identification, 'CLIENT:') === 0) {
            $clientId = substr($identification, 7);
            $stmt = $db->prepare("SELECT * FROM clients WHERE gym_id = ? AND id = ?");
            $stmt->execute([$gymId, $clientId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM clients WHERE gym_id = ? AND identification = ?");
            $stmt->execute([$gymId, $identification]);
        }
        $client = $stmt->fetch();

        if (!$client) {
            $_SESSION['error'] = 'Client not found in this gym.';
            $this->redirect('/gym/attendance/checkin');
        }

        if ($client['status'] !== 'active') {
             $this->logAccess($client['id'], 0, 'Client status: ' . $client['status']);
             $_SESSION['error'] = 'Access Denied: Client is ' . $client['status'];
             $this->redirect('/gym/attendance/checkin');
        }

        // 2. Check Active Membership
        // We look for any membership that covers today (Time based) OR has sessions left (Session based)
        $today = date('Y-m-d');
        
        $sql = "
            SELECT m.*, p.type, p.name as plan_name 
            FROM memberships m
            JOIN plans p ON m.plan_id = p.id
            WHERE m.client_id = ? AND m.status = 'active'
            AND (
                (p.type = 'TIME' AND m.end_date >= ?)
                OR
                (p.type = 'SESSIONS' AND m.sessions_used < m.sessions_total)
            )
            LIMIT 1
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$client['id'], $today]);
        $membership = $stmt->fetch();

        if ($membership) {
            // Access Granted
            
            // Check Config for Session Deduction
            $stmt = $db->prepare("SELECT config_deduct_session FROM gyms WHERE id = ?");
            $stmt->execute([$gymId]);
            $gymConfig = $stmt->fetch();
            $shouldDeduct = $gymConfig['config_deduct_session'] ?? 1;

            // If Session based, deduct session
            if ($membership['type'] === 'SESSIONS' && $shouldDeduct) {
                $upd = $db->prepare("UPDATE memberships SET sessions_used = sessions_used + 1 WHERE id = ?");
                $upd->execute([$membership['id']]);
                
                // Check if used up
                if ($membership['sessions_used'] + 1 >= $membership['sessions_total']) {
                    $upd = $db->prepare("UPDATE memberships SET status = 'expired' WHERE id = ?");
                    $upd->execute([$membership['id']]);
                }
            }

            $this->logAccess($client['id'], 1, null);
            $_SESSION['success'] = "Welcome, {$client['name']}! (Plan: {$membership['plan_name']})";
        } else {
            // Access Denied
            $this->logAccess($client['id'], 0, 'No active membership');
            $_SESSION['error'] = "Access Denied: No active membership.";
        }
        
        $this->redirect('/gym/attendance/checkin');
    }

    private function logAccess($clientId, $granted, $reason) {
        $gymId = $_SESSION['gym_id'];
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO attendance (gym_id, client_id, method, access_granted, rejection_reason) VALUES (?, ?, 'MANUAL', ?, ?)");
        $stmt->execute([$gymId, $clientId, $granted, $reason]);
    }
}
