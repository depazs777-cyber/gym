<?php defined('APP_NAME') or exit('No direct script access allowed');

class BillingController extends BaseController {
    
    public function __construct() {
        // SUPER_ADMIN and FINANZAS can access billing
        $this->checkRole(['SUPER_ADMIN', 'FINANZAS']);
    }

    public function index() {
        $this->autoApplyPricing();

        $db = new Database()->getConnection();
        
        // Fetch All Gyms with License Details
        // Calculate days remaining
        $driver = getenv('DB_DRIVER') ?: 'mysql';
        if ($driver === 'sqlite') {
            $sql = "
                SELECT id, name, status, license_start, license_end,
                CAST(julianday(license_end) - julianday('now') AS INTEGER) as days_remaining
                FROM gyms 
                ORDER BY license_end ASC
            ";
        } else {
            $sql = "
                SELECT id, name, status, license_start, license_end,
                DATEDIFF(license_end, CURDATE()) as days_remaining
                FROM gyms 
                ORDER BY license_end ASC
            ";
        }
        
        $stmt = $db->query($sql);
        $gyms = $stmt->fetchAll();

        // Stats
        $active = 0;
        $expired = 0;
        $expiring = 0;

        foreach ($gyms as $gym) {
            if ($gym['days_remaining'] < 0) {
                $expired++;
            } elseif ($gym['days_remaining'] <= 15) {
                $expiring++;
            } else {
                $active++;
            }
        }

        // Fetch Pricing Info
        $stmt = $db->query("SELECT * FROM saas_plans");
        $plans = $stmt->fetchAll();
        
        $annualPlan = null;
        foreach($plans as $p) if($p['name'] === 'Anual') $annualPlan = $p;

        // Fetch Scheduled Change for Annual
        $scheduledChange = null;
        if ($annualPlan) {
            $stmt = $db->prepare("SELECT * FROM saas_plan_price_changes WHERE saas_plan_id = ? AND status = 'SCHEDULED' ORDER BY effective_date ASC LIMIT 1");
            $stmt->execute([$annualPlan['id']]);
            $scheduledChange = $stmt->fetch();
        }

        $this->view('layouts/main', [
            'childView' => 'admin/billing',
            'gyms' => $gyms,
            'stats' => [
                'active' => $active,
                'expired' => $expired,
                'expiring' => $expiring
            ],
            'plans' => $plans,
            'annualPlan' => $annualPlan,
            'scheduledChange' => $scheduledChange
        ]);
    }

    private function autoApplyPricing() {
        $db = new Database()->getConnection();
        $today = date('Y-m-d');
        $now = date('Y-m-d H:i:s');

        // 1. Apply SCHEDULED changes
        $stmt = $db->prepare("SELECT * FROM saas_plan_price_changes WHERE status = 'SCHEDULED' AND effective_date <= ?");
        $stmt->execute([$today]);
        $changes = $stmt->fetchAll();

        foreach ($changes as $change) {
            // Update Plan Price
            $updatePlan = $db->prepare("UPDATE saas_plans SET current_price = ?, updated_at = ? WHERE id = ?");
            $updatePlan->execute([$change['new_price'], $now, $change['saas_plan_id']]);

            // Mark Change as APPLIED
            $updateChange = $db->prepare("UPDATE saas_plan_price_changes SET status = 'APPLIED' WHERE id = ?");
            $updateChange->execute([$change['id']]);
        }

        // 2. Notifications
        $stmt = $db->prepare("SELECT * FROM saas_plan_price_changes WHERE status = 'SCHEDULED' AND notify_date <= ? AND effective_date > ?");
        $stmt->execute([$today, $today]);
        $notifyChanges = $stmt->fetchAll();

        if (count($notifyChanges) > 0) {
            $gyms = $db->query("SELECT id, name FROM gyms WHERE status = 'active'")->fetchAll();
            
            foreach ($notifyChanges as $change) {
                $title = "Actualización de tarifa — Plan Anual";
                foreach ($gyms as $gym) {
                    // Check if notification exists
                    $check = $db->prepare("SELECT id FROM notifications WHERE gym_id = ? AND title = ? AND created_at >= ?");
                    $check->execute([$gym['id'], $title, $change['notify_date'] . ' 00:00:00']);
                    
                    if (!$check->fetch()) {
                        $msg = "Queremos contarte con anticipación que a partir de {$change['effective_date']} se aplicará una actualización en la tarifa del Plan Anual. Nueva tarifa: $" . number_format($change['new_price'], 0) . " COP. Tu servicio seguirá funcionando con normalidad.";
                        
                        $ins = $db->prepare("INSERT INTO notifications (gym_id, title, message, target_role, type) VALUES (?, ?, ?, 'ADMIN_GYM', 'PRICE_INCREASE')");
                        $ins->execute([$gym['id'], $title, $msg]);
                    }
                }
            }
        }
    }

    public function scheduleIncrease() {
        $this->checkRole(['SUPER_ADMIN']);
        $this->verifyCsrf();
        
        $newPrice = $_POST['new_price'];
        $effectiveDate = $_POST['effective_date'];
        
        $eff = new DateTime($effectiveDate);
        $not = clone $eff;
        $not->modify('-1 month');
        $notifyDate = $not->format('Y-m-d');
        
        $db = new Database()->getConnection();
        
        // Get Annual Plan
        $stmt = $db->prepare("SELECT id, current_price FROM saas_plans WHERE name = 'Anual'");
        $stmt->execute();
        $plan = $stmt->fetch();
        
        // Insert
        $stmt = $db->prepare("INSERT INTO saas_plan_price_changes (saas_plan_id, old_price, new_price, effective_date, notify_date, created_by_user_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$plan['id'], $plan['current_price'], $newPrice, $effectiveDate, $notifyDate, $_SESSION['user_id']]);
        
        $_SESSION['success'] = "Price increase scheduled for $effectiveDate. Notification scheduled for $notifyDate.";
        $this->redirect('/admin/billing');
    }

    public function cancelIncrease() {
        $this->checkRole(['SUPER_ADMIN']);
        $this->verifyCsrf();
        $id = $_POST['change_id'];
        
        $db = new Database()->getConnection();
        $stmt = $db->prepare("UPDATE saas_plan_price_changes SET status = 'CANCELLED' WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['success'] = "Price increase cancelled.";
        $this->redirect('/admin/billing');
    }

    public function renew() {
        $this->verifyCsrf();
        $gymId = $_POST['gym_id'];
        $periodType = $_POST['period_type']; // MONTHLY, ANNUAL, CUSTOM
        $months = (int)$_POST['period_months']; 
        $amount = $_POST['amount'];
        $method = $_POST['method'];
        $reference = $_POST['reference'];
        $notes = $_POST['notes'];

        $db = new Database()->getConnection();
        
        // Get Gym
        $stmt = $db->prepare("SELECT * FROM gyms WHERE id = ?");
        $stmt->execute([$gymId]);
        $gym = $stmt->fetch();
        if (!$gym) die("Gym not found");

        // Calculate New End Date
        $currentEnd = new DateTime($gym['license_end']);
        $today = new DateTime();
        
        if ($currentEnd < $today) {
            $startBase = $today; // Expired, start from today
        } else {
            $startBase = $currentEnd; // Active, add to end
        }
        
        $newEnd = clone $startBase;
        $newEnd->modify("+$months months");
        $newEndDateStr = $newEnd->format('Y-m-d');

        try {
            $db->beginTransaction();

            // 1. Record Payment
            $stmt = $db->prepare("INSERT INTO saas_payments (gym_id, period_type, period_months, amount, method, reference, notes, created_by_user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$gymId, $periodType, $months, $amount, $method, $reference, $notes, $_SESSION['user_id']]);
            $paymentId = $db->lastInsertId();

            // 2. Record Renewal Log
            $stmt = $db->prepare("INSERT INTO saas_license_renewals (gym_id, old_end_date, new_end_date, renewed_by_user_id, payment_id, notes) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$gymId, $gym['license_end'], $newEndDateStr, $_SESSION['user_id'], $paymentId, $notes]);

            // 3. Update Gym
            $status = ($newEnd >= $today) ? 'active' : 'expired';
            $stmt = $db->prepare("UPDATE gyms SET license_end = ?, status = ? WHERE id = ?");
            $stmt->execute([$newEndDateStr, $status, $gymId]);

            $db->commit();
            $_SESSION['success'] = "License renewed successfully until $newEndDateStr";

        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = "Renewal failed: " . $e->getMessage();
        }

        $this->redirect('/admin/billing');
    }

    public function history() {
        $gymId = $_GET['gym_id'];
        $db = new Database()->getConnection();
        
        // Payments
        $stmt = $db->prepare("SELECT * FROM saas_payments WHERE gym_id = ? ORDER BY payment_date DESC");
        $stmt->execute([$gymId]);
        $payments = $stmt->fetchAll();

        // Renewals
        $stmt = $db->prepare("SELECT * FROM saas_license_renewals WHERE gym_id = ? ORDER BY renewed_at DESC");
        $stmt->execute([$gymId]);
        $renewals = $stmt->fetchAll();

        echo json_encode(['payments' => $payments, 'renewals' => $renewals]);
        exit;
    }
}
