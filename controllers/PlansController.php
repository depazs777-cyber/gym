<?php defined('APP_NAME') or exit('No direct script access allowed');

class PlansController extends BaseController {

    public function __construct() {
        // Only SUPER_ADMIN can manage plans. FINANCE can view.
        $this->checkRole(['SUPER_ADMIN', 'FINANZAS']);
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        
        // Fetch Plans with Gym count
        $stmt = $db->query("
            SELECT p.*, 
                   (SELECT COUNT(*) FROM gyms g WHERE g.saas_plan_id = p.id) as gym_count,
                   (SELECT name FROM saas_plans WHERE id = p.merged_into_plan_id) as merged_into_name
            FROM saas_plans p 
            ORDER BY p.is_archived ASC, p.is_active DESC, p.name ASC
        ");
        $plans = $stmt->fetchAll();
        
        // Fetch Active Plans for Dropdown (for Merge modal)
        $stmt = $db->query("SELECT id, name FROM saas_plans WHERE is_active = 1 AND is_archived = 0 ORDER BY name ASC");
        $activePlans = $stmt->fetchAll();

        // Fetch Scheduled Changes
        $stmt = $db->query("
            SELECT c.*, p.name as plan_name, u.name as created_by
            FROM saas_plan_price_changes c
            JOIN saas_plans p ON c.saas_plan_id = p.id
            LEFT JOIN users u ON c.created_by_user_id = u.id
            ORDER BY c.effective_date ASC
        ");
        $changes = $stmt->fetchAll();

        $this->view('layouts/main', [
            'childView' => 'admin/plans/index',
            'plans' => $plans,
            'activePlans' => $activePlans,
            'priceChanges' => $changes,
            'canManage' => ($_SESSION['user_role'] === 'SUPER_ADMIN')
        ]);
    }

    public function store() {
        $this->checkRole(['SUPER_ADMIN']);
        $this->verifyCsrf();

        $name = $_POST['name'] ?? '';
        $code = $_POST['code'] ?? '';
        $period = (int)($_POST['period_months'] ?? 1);
        $price = $_POST['current_price'] ?? 0;
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (empty($name) || empty($code) || $price < 0) {
            $_SESSION['error'] = 'Name, Code and valid Price are required.';
            $this->redirect('/admin/plans');
        }

        $db = Database::getInstance()->getConnection();
        
        // Check duplicate code
        $stmt = $db->prepare("SELECT id FROM saas_plans WHERE code = ?");
        $stmt->execute([$code]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Plan code must be unique.';
            $this->redirect('/admin/plans');
        }

        $stmt = $db->prepare("INSERT INTO saas_plans (name, code, period_months, current_price, is_active, currency) VALUES (?, ?, ?, ?, ?, 'COP')");
        $stmt->execute([$name, $code, $period, $price, $isActive]);

        $_SESSION['success'] = 'Plan created successfully.';
        $this->redirect('/admin/plans');
    }

    public function update() {
        $this->checkRole(['SUPER_ADMIN']);
        $this->verifyCsrf();

        $id = $_POST['id'] ?? null;
        $name = $_POST['name'] ?? '';
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        // Note: Code and Price are NOT editable here directly to preserve integrity/history.
        // Price is changed via schedulePrice. Code is generally immutable or carefully changed manually.

        if (!$id || empty($name)) {
            $_SESSION['error'] = 'ID and Name are required.';
            $this->redirect('/admin/plans');
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE saas_plans SET name = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$name, $isActive, $id]);

        $_SESSION['success'] = 'Plan updated successfully.';
        $this->redirect('/admin/plans');
    }

    public function schedulePrice() {
        $this->checkRole(['SUPER_ADMIN']);
        $this->verifyCsrf();

        $planId = $_POST['plan_id'] ?? null;
        $newPrice = $_POST['new_price'] ?? null;
        $effectiveDate = $_POST['effective_date'] ?? null;
        $notifyDate = $_POST['notify_date'] ?? null;

        if (!$planId || !$newPrice || !$effectiveDate || !$notifyDate) {
            $_SESSION['error'] = 'All fields are required.';
            $this->redirect('/admin/plans');
        }

        $db = Database::getInstance()->getConnection();
        
        // Get current price
        $stmt = $db->prepare("SELECT current_price FROM saas_plans WHERE id = ?");
        $stmt->execute([$planId]);
        $current = $stmt->fetch();

        if (!$current) {
            $_SESSION['error'] = 'Plan not found.';
            $this->redirect('/admin/plans');
        }

        $stmt = $db->prepare("INSERT INTO saas_plan_price_changes (saas_plan_id, old_price, new_price, effective_date, notify_date, status, created_by_user_id) VALUES (?, ?, ?, ?, ?, 'SCHEDULED', ?)");
        $stmt->execute([$planId, $current['current_price'], $newPrice, $effectiveDate, $notifyDate, $_SESSION['user_id']]);

        $_SESSION['success'] = 'Price change scheduled.';
        $this->redirect('/admin/plans');
    }

    public function merge() {
        $this->checkRole(['SUPER_ADMIN']);
        $this->verifyCsrf();

        $sourceId = $_POST['source_plan_id'] ?? null;
        $targetId = $_POST['target_plan_id'] ?? null;
        // update_snapshots checkbox is tricky. Requirements say:
        // "Mantener subscription_price_snapshot ... a menos que el sistema opere contrato por plan"
        // "Si se define que al migrar se adopta el nuevo plan ... actualizar snapshot SOLO SI el admin lo decide"
        $updateSnapshots = isset($_POST['update_snapshots']); 

        if (!$sourceId || !$targetId || $sourceId == $targetId) {
            $_SESSION['error'] = 'Invalid Source or Target Plan.';
            $this->redirect('/admin/plans');
        }

        $db = Database::getInstance()->getConnection();

        try {
            $db->beginTransaction();

            // 1. Validate Plans
            $stmt = $db->prepare("SELECT * FROM saas_plans WHERE id = ?");
            $stmt->execute([$sourceId]);
            $source = $stmt->fetch();
            
            $stmt->execute([$targetId]);
            $target = $stmt->fetch();

            if (!$source || !$target) {
                throw new Exception("One of the plans does not exist.");
            }

            // 2. Archive Source Plan
            $stmt = $db->prepare("UPDATE saas_plans SET is_active = 0, is_archived = 1, merged_into_plan_id = ? WHERE id = ?");
            $stmt->execute([$targetId, $sourceId]);

            // 3. Migrate Gyms
            // If updateSnapshots is true, we update the price snapshot to the TARGET plan's price immediately?
            // Or just change the plan ID?
            // "NO tocar pagos históricos" - handled by separate saas_payments table.
            // "NO tocar license_start/license_end automatically".
            
            if ($updateSnapshots) {
                // Update plan ID AND snapshots (price/period) to match new plan
                $stmt = $db->prepare("UPDATE gyms SET saas_plan_id = ?, subscription_price_snapshot = ?, subscription_period_months_snapshot = ? WHERE saas_plan_id = ?");
                $stmt->execute([$targetId, $target['current_price'], $target['period_months'], $sourceId]);
            } else {
                // Only update the Plan ID reference. Keep price/period snapshots as is (grandfathered).
                $stmt = $db->prepare("UPDATE gyms SET saas_plan_id = ? WHERE saas_plan_id = ?");
                $stmt->execute([$targetId, $sourceId]);
            }
            
            // 4. Audit Log (using Notifications table for simplicity or just basic logs)
            $msg = "Plan Merged: '{$source['name']}' -> '{$target['name']}'. Migrated gyms from Source.";
            $stmt = $db->prepare("INSERT INTO notifications (title, message, target_role, type) VALUES ('Plan Merge', ?, 'SUPER_ADMIN', 'INFO')");
            $stmt->execute([$msg]);

            $db->commit();
            $_SESSION['success'] = "Plan merged successfully. Gyms migrated.";

        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = "Error merging plans: " . $e->getMessage();
        }

        $this->redirect('/admin/plans');
    }

    public function toggleActive() {
        $this->checkRole(['SUPER_ADMIN']);
        $this->verifyCsrf();

        $id = $_POST['id'] ?? null;
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("UPDATE saas_plans SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['success'] = 'Plan status toggled.';
        $this->redirect('/admin/plans');
    }
}
