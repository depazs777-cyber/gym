<?php defined('APP_NAME') or exit('No direct script access allowed');

class LeadsController extends BaseController {

    public function __construct() {
        // CALL_CENTER, MARKETING, SUPER_ADMIN, VENDEDOR
        $this->checkRole(['SUPER_ADMIN', 'CALL_CENTER', 'MARKETING', 'VENDEDOR']);
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];

        // Force "Assigned to Me" for CALL_CENTER
        if ($userRole === 'CALL_CENTER') {
            $_GET['assigned_me'] = 1;
        }

        // Base Query
        $sql = "SELECT l.*, u.name as assigned_user_name
                FROM leads l
                LEFT JOIN users u ON l.assigned_to_user_id = u.id
                WHERE 1=1";

        $params = [];

        // Filters
        if (!empty($_GET['search'])) {
            $search = '%' . $_GET['search'] . '%';
            $sql .= " AND (l.name LIKE ? OR l.phone LIKE ?)";
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($_GET['status'])) {
            $sql .= " AND l.status = ?";
            $params[] = $_GET['status'];
        }

        if (!empty($_GET['city'])) {
            $sql .= " AND l.city = ?";
            $params[] = $_GET['city'];
        }

        if (!empty($_GET['assigned_me']) && $_GET['assigned_me'] == 1) {
            $sql .= " AND l.assigned_to_user_id = ?";
            $params[] = $userId;
        }

        $sql .= " ORDER BY l.next_followup ASC, l.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $leads = $stmt->fetchAll();

        // Fetch Cities for filter
        $cities = $db->query("SELECT DISTINCT city FROM leads WHERE city IS NOT NULL ORDER BY city")->fetchAll(PDO::FETCH_COLUMN);

        // Fetch today's motivation
        $today = date('Y-m-d');
        $stmt = $db->prepare("SELECT * FROM motivation_posts WHERE show_date = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$today]);
        $motivation = $stmt->fetch();

        $this->view('layouts/main', [
            'childView' => 'admin/leads_list',
            'leads' => $leads,
            'cities' => $cities,
            'motivation' => $motivation
        ]);
    }

    public function callLogs() {
        $db = Database::getInstance()->getConnection();
        $userId = $_SESSION['user_id'];

        $sql = "SELECT cl.*, l.name as lead_name, l.phone, s.title as script_title
                FROM call_logs cl
                JOIN leads l ON cl.lead_id = l.id
                LEFT JOIN call_scripts s ON cl.script_id = s.id
                WHERE 1=1";

        $params = [];
        // If Call Center, only own logs? Or all? Usually own + team if manager.
        // Rule: "Solo leads, llamadas, agenda..."
        if ($_SESSION['user_role'] === 'CALL_CENTER') {
            $sql .= " AND cl.user_id = ?";
            $params[] = $userId;
        }

        $sql .= " ORDER BY cl.call_start DESC LIMIT 100";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll();

        $this->view('layouts/main', [
            'childView' => 'admin/call_logs',
            'logs' => $logs
        ]);
    }

    public function agenda() {
        $db = Database::getInstance()->getConnection();
        $userId = $_SESSION['user_id'];

        $driver = getenv('DB_DRIVER') ?: 'mysql';
        if ($driver === 'sqlite') {
            $sql = "SELECT * FROM leads WHERE assigned_to_user_id = ? AND next_followup IS NOT NULL AND date(next_followup) <= date('now') ORDER BY next_followup ASC";
        } else {
            $sql = "SELECT * FROM leads WHERE assigned_to_user_id = ? AND next_followup IS NOT NULL AND DATE(next_followup) <= CURDATE() ORDER BY next_followup ASC";
        }

        $stmt = $db->prepare($sql);
        $stmt->execute([$userId]);
        $agenda = $stmt->fetchAll();

        $this->view('layouts/main', [
            'childView' => 'admin/agenda',
            'agenda' => $agenda
        ]);
    }

    public function create() {
        if ($_SESSION['user_role'] === 'CALL_CENTER') {
             $_SESSION['error'] = 'Unauthorized.';
             $this->redirect('/admin/leads');
        }
        $this->view('layouts/main', [
            'childView' => 'admin/leads_create'
        ]);
    }

    public function store() {
        if ($_SESSION['user_role'] === 'CALL_CENTER') {
             $_SESSION['error'] = 'Unauthorized.';
             $this->redirect('/admin/leads');
        }
        $this->verifyCsrf();
        $name = $_POST['name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $type = $_POST['customer_type'] ?? 'SMALL_GYM';

        if (empty($name) || empty($phone)) {
            $_SESSION['error'] = 'Name and Phone required.';
            $this->redirect('/admin/leads/create');
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO leads (name, phone, customer_type) VALUES (?, ?, ?)");
        $stmt->execute([$name, $phone, $type]);

        $_SESSION['success'] = 'Lead created.';
        $this->redirect('/admin/leads');
    }

    public function update() {
        if ($_SESSION['user_role'] === 'CALL_CENTER') {
             $_SESSION['error'] = 'Unauthorized to edit lead details.';
             $this->redirect('/admin/leads');
        }
        $this->verifyCsrf();
        $id = $_POST['id'];
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $gym = $_POST['gym_name'] ?? '';
        $owner = $_POST['owner_name'] ?? '';
        $city = $_POST['city'] ?? '';

        if (!$id || empty($name) || empty($phone)) {
            $_SESSION['error'] = 'Name and Phone required.';
            $this->redirect('/admin/leads');
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE leads SET name = ?, phone = ?, gym_name = ?, owner_name = ?, city = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $gym, $owner, $city, $id]);

        $_SESSION['success'] = 'Lead updated.';
        $this->redirect('/admin/leads');
    }

    public function assign() {
        $this->checkRole(['SUPER_ADMIN', 'MARKETING', 'VENDEDOR']);
        $this->verifyCsrf();

        $leadId = $_POST['lead_id'] ?? null;
        $userId = $_POST['user_id'] ?? null;

        if (!$leadId || !$userId) {
            $_SESSION['error'] = 'Lead and User required.';
            $this->redirect('/admin/leads');
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE leads SET assigned_to_user_id = ? WHERE id = ?");
        $stmt->execute([$userId, $leadId]);

        $_SESSION['success'] = 'Lead assigned successfully.';
        $this->redirect('/admin/leads');
    }

    public function getCallCenterUsers() {
        $this->checkRole(['SUPER_ADMIN', 'MARKETING', 'VENDEDOR']);

        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT id, name FROM users WHERE role = 'CALL_CENTER' AND status = 'active' ORDER BY name ASC");
        $users = $stmt->fetchAll();

        header('Content-Type: application/json');
        echo json_encode(['users' => $users]);
        exit;
    }

    // Modal data for calling
    public function callData() {
        $leadId = $_GET['id'] ?? null;
        if (!$leadId) exit(json_encode(['error' => 'No ID']));

        $db = Database::getInstance()->getConnection();

        // Lead
        $sql = "SELECT * FROM leads WHERE id = ?";
        $params = [$leadId];

        // Security Check for Call Center
        if ($_SESSION['user_role'] === 'CALL_CENTER') {
            $sql .= " AND assigned_to_user_id = ?";
            $params[] = $_SESSION['user_id'];
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $lead = $stmt->fetch();

        if (!$lead) {
             exit(json_encode(['error' => 'Lead not found or unauthorized.']));
        }

        // Scripts matching type OR generic ones (if customer_type is missing or no specific scripts)
        // Improved logic: Fetch specific scripts, if none, fetch all active scripts?
        // Or just fetch all active scripts and let JS filter?
        // For now: Fetch all active scripts to ensure dropdown isn't empty.
        $stmt = $db->query("SELECT * FROM call_scripts WHERE is_active = 1");
        $scripts = $stmt->fetchAll();

        // Motivation
        $today = date('Y-m-d');
        $stmt = $db->prepare("SELECT * FROM motivation_posts WHERE show_date = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$today]);
        $motivation = $stmt->fetch();

        // Settings (Time)
        $stmt = $db->query("SELECT * FROM saas_settings");
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        echo json_encode([
            'lead' => $lead,
            'scripts' => $scripts,
            'motivation' => $motivation,
            'settings' => $settings
        ]);
        exit;
    }

    public function logCall() {
        $this->verifyCsrf();
        $leadId = $_POST['lead_id'];
        $outcome = $_POST['outcome'];
        $notes = $_POST['notes'];
        $scriptId = $_POST['script_id'] ?: null;
        $duration = $_POST['duration'] ?? 0;
        $nextFollowup = $_POST['next_followup'] ?? null;

        $db = Database::getInstance()->getConnection();

        // Permission Check: Call Center agent can only log calls for assigned leads
        if ($_SESSION['user_role'] === 'CALL_CENTER') {
            $stmt = $db->prepare("SELECT assigned_to_user_id FROM leads WHERE id = ?");
            $stmt->execute([$leadId]);
            $assignedId = $stmt->fetchColumn();

            if ($assignedId != $_SESSION['user_id']) {
                $_SESSION['error'] = 'Unauthorized. You can only log calls for your assigned leads.';
                $this->redirect('/admin/leads');
            }
        }

        // Log Call
        $stmt = $db->prepare("INSERT INTO call_logs (lead_id, user_id, outcome, notes, script_id, duration_seconds) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$leadId, $_SESSION['user_id'], $outcome, $notes, $scriptId, $duration]);

        // Update Lead Status logic?
        $status = null;
        if ($outcome === 'WON') $status = 'WON';
        if ($outcome === 'LOST') $status = 'LOST';
        if ($outcome === 'DNC') $status = 'DNC';

        if ($status) {
            $stmt = $db->prepare("UPDATE leads SET status = ? WHERE id = ?");
            $stmt->execute([$status, $leadId]);
        }

        // Update Next Followup
        if ($nextFollowup) {
            $stmt = $db->prepare("UPDATE leads SET next_followup = ? WHERE id = ?");
            $stmt->execute([$nextFollowup, $leadId]);
        }

        // Update Last Call
        $now = date('Y-m-d H:i:s');
        $stmt = $db->prepare("UPDATE leads SET last_call_at = ? WHERE id = ?");
        $stmt->execute([$now, $leadId]);

        $_SESSION['success'] = 'Call logged.';
        $this->redirect('/admin/leads');
    }
}
