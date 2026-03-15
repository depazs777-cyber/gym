<?php defined('APP_NAME') or exit('No direct script access allowed');

class AdminController extends BaseController {

    public function __construct() {
        // Allow all SaaS roles to access the controller, but specific methods will be restricted
        $this->checkRole(['SUPER_ADMIN', 'VENDEDOR', 'MARKETING', 'CALL_CENTER', 'FINANZAS', 'SOPORTE', 'DEV', 'SEGURIDAD']);
    }

    public function dashboard() {
        if ($_SESSION['user_role'] === 'CALL_CENTER') {
            return $this->callCenterDashboard();
        }

        // Everyone sees the dashboard, but stats might be filtered?
        // For MVP, everyone sees the stats.

        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT COUNT(*) as total FROM gyms");
        $totalGyms = $stmt->fetch()['total'];

        $stmt = $db->query("SELECT COUNT(*) as total FROM gyms WHERE status = 'active'");
        $activeGyms = $stmt->fetch()['total'];

        // 2. Revenue (SaaS Sales)
        $revenue = 0;
        try {
             // Check if sales_orders table exists by attempting a query
             $stmt = $db->query("SELECT SUM(total_amount) as revenue FROM sales_orders WHERE status = 'PAID'");
             $revenue = $stmt->fetch()['revenue'] ?? 0;
        } catch (Exception $e) {
             // Table might not exist yet if migration failed, safe fallback
             $revenue = 0;
        }

        // 3. Pending Renewals (Next 30 days)
        $today = date('Y-m-d');
        $nextMonth = date('Y-m-d', strtotime('+30 days'));

        $stmt = $db->prepare("SELECT COUNT(*) as total FROM gyms WHERE status = 'active' AND license_end BETWEEN ? AND ?");
        $stmt->execute([$today, $nextMonth]);
        $renewals = $stmt->fetch()['total'];

        $this->view('layouts/main', [
            'childView' => 'admin/dashboard',
            'stats' => [
                'total_gyms' => $totalGyms,
                'active_gyms' => $activeGyms,
                'revenue' => $revenue,
                'renewals' => $renewals
            ]
        ]);
    }

    private function callCenterDashboard() {
        $db = Database::getInstance()->getConnection();
        $userId = $_SESSION['user_id'];

        // 1. Motivation
        $today = date('Y-m-d');
        $stmt = $db->prepare("SELECT * FROM motivation_posts WHERE show_date = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$today]);
        $motivation = $stmt->fetch();

        // 2. Stats
        // Leads Assigned
        $stmt = $db->prepare("SELECT COUNT(*) FROM leads WHERE assigned_to_user_id = ?");
        $stmt->execute([$userId]);
        $leadsCount = $stmt->fetchColumn();

        // Calls Today
        $driver = getenv('DB_DRIVER') ?: 'mysql';
        if ($driver === 'sqlite') {
            $sql = "SELECT COUNT(*) FROM call_logs WHERE user_id = ? AND date(call_start) = date('now')";
            $sqlSales = "SELECT COUNT(*) FROM leads WHERE assigned_to_user_id = ? AND status = 'WON' AND strftime('%Y-%m', updated_at) = strftime('%Y-%m', 'now')";
        } else {
            $sql = "SELECT COUNT(*) FROM call_logs WHERE user_id = ? AND DATE(call_start) = CURDATE()";
            $sqlSales = "SELECT COUNT(*) FROM leads WHERE assigned_to_user_id = ? AND status = 'WON' AND YEAR(updated_at) = YEAR(CURDATE()) AND MONTH(updated_at) = MONTH(CURDATE())";
        }

        $stmt = $db->prepare($sql);
        $stmt->execute([$userId]);
        $callsToday = $stmt->fetchColumn();

        $stmt = $db->prepare($sqlSales);
        $stmt->execute([$userId]);
        $salesMonth = $stmt->fetchColumn();

        // 3. Settings (Time)
        $stmt = $db->query("SELECT * FROM saas_settings");
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        // 4. Agenda (Top 5 today)
        if ($driver === 'sqlite') {
            $sqlAgenda = "SELECT * FROM leads WHERE assigned_to_user_id = ? AND next_followup IS NOT NULL AND date(next_followup) <= date('now') ORDER BY next_followup ASC LIMIT 5";
        } else {
             $sqlAgenda = "SELECT * FROM leads WHERE assigned_to_user_id = ? AND next_followup IS NOT NULL AND DATE(next_followup) <= CURDATE() ORDER BY next_followup ASC LIMIT 5";
        }
        $stmt = $db->prepare($sqlAgenda);
        $stmt->execute([$userId]);
        $agenda = $stmt->fetchAll();

        $this->view('layouts/main', [
            'childView' => 'admin/dashboard_call_center',
            'motivation' => $motivation,
            'stats' => [
                'leads' => $leadsCount,
                'calls_today' => $callsToday,
                'sales_month' => $salesMonth
            ],
            'settings' => $settings,
            'agenda' => $agenda
        ]);
    }

    public function listGyms() {
        // Restricted to SUPER_ADMIN and VENDEDOR
        $this->checkRole(['SUPER_ADMIN', 'VENDEDOR']);

        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM gyms ORDER BY created_at DESC");
        $gyms = $stmt->fetchAll();

        $this->view('layouts/main', [
            'childView' => 'admin/gyms_list',
            'gyms' => $gyms
        ]);
    }

    public function createGymForm() {
        $this->checkRole(['SUPER_ADMIN', 'VENDEDOR']);

        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM saas_plans WHERE is_active = 1");
        $plans = $stmt->fetchAll();

        $this->view('layouts/main', [
            'childView' => 'admin/gyms_create',
            'plans' => $plans
        ]);
    }

    public function storeGym() {
        $this->checkRole(['SUPER_ADMIN', 'VENDEDOR']);
        $this->verifyCsrf();

        $name = $_POST['name'] ?? '';
        $admin_email = $_POST['admin_email'] ?? '';
        $admin_password = $_POST['admin_password'] ?? '';

        // Subscription
        $planId = $_POST['plan_id'] ?? null;
        $periodMultiplier = (int)($_POST['period_multiplier'] ?? 1);
        $amountTotal = $_POST['amount_total'] ?? 0;
        $licenseStart = $_POST['license_start'] ?? date('Y-m-d');
        $licenseEnd = $_POST['license_end'] ?? null;

        // Discount
        $discountValue = $_POST['discount_value'] ?? 0;
        $discountType = $_POST['discount_type'] ?? null;
        $discountReason = $_POST['discount_reason'] ?? null;

        if (empty($name) || empty($admin_email) || empty($planId) || empty($licenseEnd)) {
            $_SESSION['error'] = 'All fields are required.';
            $this->redirect('/admin/gyms/create');
        }

        $db = Database::getInstance()->getConnection();

        // Fetch Plan
        $stmt = $db->prepare("SELECT * FROM saas_plans WHERE id = ?");
        $stmt->execute([$planId]);
        $plan = $stmt->fetch();
        if(!$plan || !$plan['is_active'] || $plan['is_archived']) {
            $_SESSION['error'] = 'Invalid or Inactive Plan';
            $this->redirect('/admin/gyms/create');
        }

        // Calculate Period
        // Use database fields instead of name parsing if possible, fallback to old logic
        $baseMonths = $plan['period_months'] ?? 1;
        $periodMonths = $baseMonths * $periodMultiplier;
        $yearsPaid = ($baseMonths >= 12) ? $periodMultiplier : 0; // Rough approximation for years count

        // Discount Validation
        $discountApprovedBy = null;
        if ($discountValue > 0) {
            if ($_SESSION['user_role'] === 'SUPER_ADMIN') {
                $discountApprovedBy = $_SESSION['user_id'];
            } else {
                $discountValue = 0; // Ignore unauthorized discount
                $amountTotal = $plan['current_price'] * $periodMultiplier; // Reset total
            }
        }

        try {
            $db->beginTransaction();

            // 1. Create Gym (Status PENDING_PAYMENT) - Updated Logic
            $now = date('Y-m-d H:i:s');
            $planCode = $plan['code'] ?? 'CUSTOM';

            // Note: We set status to 'pending_payment' and subscription_status to 'PENDING'
            $stmt = $db->prepare("INSERT INTO gyms (name, license_start, license_end, status, registered_at, subscription_status, activated_at, subscription_plan_code, subscription_price_snapshot, saas_plan_id, subscription_period_months_snapshot) VALUES (?, ?, ?, 'pending_payment', ?, 'PENDING', NULL, ?, ?, ?, ?)");
            $stmt->execute([$name, $licenseStart, $licenseEnd, $now, $planCode, $plan['current_price'], $plan['id'], $baseMonths]);
            $gymId = $db->lastInsertId();

            // 2. Create Gym Admin
            $hash = password_hash($admin_password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (gym_id, name, email, password_hash, role, status) VALUES (?, ?, ?, ?, 'ADMIN_GYM', 'active')");
            $stmt->execute([$gymId, 'Admin ' . $name, $admin_email, $hash]);

            // 3. Create Sales Order (Contract)
            $salesOrderModel = new SalesOrder();
            $notes = "Initial License Sale. Plan: {$plan['name']} ($periodMonths months).";

            // Amount = Gross Price, Discount = Value, Total = Net
            $grossAmount = $plan['current_price'] * $periodMultiplier;
            $salesOrderModel->create($gymId, $plan['id'], $grossAmount, $discountValue, $amountTotal, $notes, $_SESSION['user_id']);

            // 4. Notify Finance
            try {
                $title = "Nueva Venta (Pendiente de Pago)";
                $msg = "Gym: $name. Total: $" . number_format($amountTotal);
                $stmt = $db->prepare("INSERT INTO notifications (title, message, target_role, type) VALUES (?, ?, 'FINANZAS', 'INFO')");
                $stmt->execute([$title, $msg]);
            } catch(Exception $ex) {}

            $db->commit();
            $_SESSION['success'] = 'Gym registered. Please process payment in Accounting -> Orders.';
            $this->redirect('/admin/accounting/orders');

        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = 'Error creating gym: ' . $e->getMessage();
            $this->redirect('/admin/gyms/create');
        }
    }

    public function editGymForm() {
        $this->checkRole(['SUPER_ADMIN', 'VENDEDOR']);
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $_SESSION['error'] = 'Gym ID required.';
            $this->redirect('/admin/gyms');
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM gyms WHERE id = ?");
        $stmt->execute([$id]);
        $gym = $stmt->fetch();

        if (!$gym) {
            $_SESSION['error'] = 'Gym not found.';
            $this->redirect('/admin/gyms');
        }

        $this->view('layouts/main', [
            'childView' => 'admin/gyms_edit',
            'gym' => $gym
        ]);
    }

    public function updateGym() {
        $this->checkRole(['SUPER_ADMIN', 'VENDEDOR']);
        $this->verifyCsrf();

        $id = $_POST['id'] ?? null;
        $name = $_POST['name'] ?? '';
        $license_start = $_POST['license_start'] ?? '';
        $license_end = $_POST['license_end'] ?? '';
        $status = $_POST['status'] ?? '';

        if (!$id || empty($name) || empty($license_start) || empty($license_end)) {
            $_SESSION['error'] = 'All fields are required.';
            $this->redirect('/admin/gyms/edit?id=' . $id);
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE gyms SET name = ?, license_start = ?, license_end = ?, status = ? WHERE id = ?");
        $stmt->execute([$name, $license_start, $license_end, $status, $id]);

        $_SESSION['success'] = 'Gym updated successfully.';
        $this->redirect('/admin/gyms');
    }

    public function getGlobalData() {
        $this->checkRole(['SUPER_ADMIN', 'CALL_CENTER', 'MARKETING', 'VENDEDOR']);

        $db = Database::getInstance()->getConnection();

        // Scripts
        $stmt = $db->query("SELECT id, title, script_body FROM call_scripts WHERE is_active = 1 ORDER BY title ASC");
        $scripts = $stmt->fetchAll();

        // Motivation (Last 5 active)
        $stmt = $db->query("SELECT title, quote_text, image_url FROM motivation_posts WHERE is_active = 1 ORDER BY show_date DESC LIMIT 5");
        $motivation = $stmt->fetchAll();

        header('Content-Type: application/json');
        echo json_encode([
            'scripts' => $scripts,
            'motivation' => $motivation
        ]);
        exit;
    }

    public function createGymAdmin() {
        $this->checkRole(['SUPER_ADMIN', 'VENDEDOR']);
        $this->verifyCsrf();

        $gymId = $_POST['gym_id'] ?? null;
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!$gymId || empty($email) || empty($password)) {
            $_SESSION['error'] = 'Gym ID, Email and Password required.';
            $this->redirect('/admin/gyms');
        }

        $db = Database::getInstance()->getConnection();

        // Verify Gym exists
        $stmt = $db->prepare("SELECT name FROM gyms WHERE id = ?");
        $stmt->execute([$gymId]);
        $gym = $stmt->fetch();
        if (!$gym) {
            $_SESSION['error'] = 'Gym not found.';
            $this->redirect('/admin/gyms');
        }

        // Verify Email uniqueness
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
             $_SESSION['error'] = 'Email already exists.';
             $this->redirect('/admin/gyms');
        }

        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (gym_id, name, email, password_hash, role, status) VALUES (?, ?, ?, ?, 'ADMIN_GYM', 'active')");
            $stmt->execute([$gymId, 'Admin ' . $gym['name'], $email, $hash]);

            $_SESSION['success'] = "Admin credentials created for {$gym['name']}.";
        } catch (PDOException $e) {
             $_SESSION['error'] = 'Database Error: ' . $e->getMessage();
        }

        $this->redirect('/admin/gyms');
    }
}
