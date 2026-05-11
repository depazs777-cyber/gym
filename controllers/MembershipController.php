<?php defined('APP_NAME') or exit('No direct script access allowed');

class MembershipController extends BaseController {
    
    public function __construct() {
        $this->checkRole(['ADMIN_GYM', 'RECEPCION']);
        if (!isset($_SESSION['gym_id'])) {
            die("No Gym ID associated with this session.");
        }
    }

    public function index() {
        // List Memberships with Tabs (Active, Expiring, Expired)
        $gymId = $_SESSION['gym_id'];
        $db = new Database()->getConnection();
        
        $status = $_GET['status'] ?? 'active'; // active, expiring, expired
        
        $sql = "SELECT m.*, c.name as client_name, p.name as plan_name, p.type 
                FROM memberships m 
                JOIN clients c ON m.client_id = c.id 
                JOIN plans p ON m.plan_id = p.id 
                WHERE m.gym_id = ?";

        if ($status === 'active') {
            $sql .= " AND m.status = 'active'";
        } elseif ($status === 'expiring') {
            // Expiring in next 7 days
            $sql .= " AND m.status = 'active' AND m.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
        } elseif ($status === 'expired') {
            $sql .= " AND m.status = 'expired'";
        }

        $sql .= " ORDER BY m.end_date ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute([$gymId]);
        $memberships = $stmt->fetchAll();

        $this->view('layouts/main', [
            'childView' => 'gym/memberships_list',
            'memberships' => $memberships,
            'currentTab' => $status
        ]);
    }

    // This method is for the "Sell Plan" UI
    public function create() {
        $gymId = $_SESSION['gym_id'];
        $clientId = $_GET['client_id'] ?? null;
        
        if (!$clientId) {
            $_SESSION['error'] = 'Client ID required.';
            $this->redirect('/gym/clients');
        }

        $db = new Database()->getConnection();

        // Get Client
        $stmt = $db->prepare("SELECT * FROM clients WHERE id = ? AND gym_id = ?");
        $stmt->execute([$clientId, $gymId]);
        $client = $stmt->fetch();

        if (!$client) {
            $_SESSION['error'] = 'Client not found.';
            $this->redirect('/gym/clients');
        }

        // Get Gym Config
        $stmt = $db->prepare("SELECT config_annual_days, config_renewal_mode FROM gyms WHERE id = ?");
        $stmt->execute([$gymId]);
        $gymConfig = $stmt->fetch();

        // Get Plans
        $stmt = $db->prepare("SELECT * FROM plans WHERE gym_id = ? AND status = 'active'");
        $stmt->execute([$gymId]);
        $plans = $stmt->fetchAll();

        // Get Last Membership (for Renewal Logic)
        $stmt = $db->prepare("SELECT * FROM memberships WHERE client_id = ? ORDER BY end_date DESC LIMIT 1");
        $stmt->execute([$clientId]);
        $lastMembership = $stmt->fetch();

        $this->view('layouts/main', [
            'childView' => 'gym/membership_create',
            'client' => $client,
            'plans' => $plans,
            'gymConfig' => $gymConfig,
            'lastMembership' => $lastMembership
        ]);
    }

    // Process the Sale
    public function store() {
        $this->verifyCsrf();
        $gymId = $_SESSION['gym_id'];
        $userId = $_SESSION['user_id'];
        
        $clientId = $_POST['client_id'] ?? null;
        $planId = $_POST['plan_id'] ?? null;
        $purchaseMode = $_POST['purchase_mode'] ?? 'PERIODIC'; // PERIODIC, ANNUAL
        $multiplier = (int)($_POST['multiplier'] ?? 1);
        $startDateInput = $_POST['start_date'] ?? date('Y-m-d'); // User selected or calculated by JS
        $paymentMethod = $_POST['payment_method'] ?? 'cash';
        $discount = (float)($_POST['discount'] ?? 0);
        $notes = $_POST['notes'] ?? '';

        if (!$clientId || !$planId || $multiplier < 1) {
            $_SESSION['error'] = 'Invalid data.';
            $this->redirect('/gym/clients');
        }

        $db = new Database()->getConnection();

        // Verify Client belongs to Gym
        $stmt = $db->prepare("SELECT id, name, identification, email FROM clients WHERE id = ? AND gym_id = ?");
        $stmt->execute([$clientId, $gymId]);
        $client = $stmt->fetch();
        if (!$client) {
             $_SESSION['error'] = 'Security Violation: Client does not belong to this gym.';
             $this->redirect('/gym/clients');
        }

        // Get Gym Config
        $stmt = $db->prepare("SELECT config_annual_days, branding_logo, name, contact_info FROM gyms WHERE id = ?");
        $stmt->execute([$gymId]);
        $gym = $stmt->fetch();
        $annualDays = $gym['config_annual_days'] ?? 360;

        try {
            $db->beginTransaction();

            // Get Plan Details
            $stmt = $db->prepare("SELECT * FROM plans WHERE id = ? AND gym_id = ?");
            $stmt->execute([$planId, $gymId]);
            $plan = $stmt->fetch();

            if (!$plan) {
                throw new Exception("Plan not found.");
            }

            // --- CALCULATION ENGINE ---
            $price = 0;
            $endDate = null;
            $sessionsTotal = 0;
            $sessionsUsed = 0; // Starts at 0
            
            // 1. Calculate Price
            // For MVP: Simple multiplier. If Plan has 'annual_price' could use that, but schema doesn't have it yet.
            // Assumption: Annual = 12 * Price (or user input logic if we had fixed annual).
            // Let's stick to simple math for now: Base Price * Multiplier.
            $basePrice = $plan['price'];
            $price = $basePrice * $multiplier;
            
            // Apply Discount
            if ($discount > 0) {
                // If discount is percentage or fixed? Usually fixed value from UI logic or backend.
                // Let's assume input is fixed value amount to subtract.
                $price = max(0, $price - $discount);
            }

            // 2. Calculate Dates / Sessions
            $startDate = $startDateInput; // Trusted from UI (which logic should be robust) or re-calc here?
            // Re-calc is safer but UI allows overrides. Let's trust UI start date but sanitize format.
            $startDate = date('Y-m-d', strtotime($startDate));

            if ($plan['type'] === 'TIME') {
                $daysToAdd = 0;
                if ($purchaseMode === 'ANNUAL') {
                    // Annual Logic
                    // Multiplier here acts as "Number of Years" usually 1.
                    // But prompt says "Multiplier = 12 (if periodic)".
                    // If Purchase Mode is ANNUAL, UI usually sends multiplier=1 (1 year) or logic handled.
                    // Let's assume Multiplier represents "Periods" (Months) OR "Years" depending on UI.
                    // Prompt B1.4: "Default multiplier = 12...". 
                    // Let's standardize: Multiplier is ALWAYS number of periods (Months).
                    // If Annual selected, JS sets multiplier 12.
                    // So we treat it as:
                    
                    if ($purchaseMode === 'ANNUAL') {
                         // Use configured annual days per "year" (12 months)
                         // If multiplier is 12, we add 1 year (config days).
                         // What if multiplier is 24? 2 years.
                         // Ratio: TotalDays = (Multiplier / 12) * AnnualDays.
                         $years = $multiplier / 12;
                         $daysToAdd = round($years * $annualDays);
                    } else {
                        // Periodic
                        $daysToAdd = $plan['duration_days'] * $multiplier;
                    }

                } else {
                    // Periodic
                    $daysToAdd = $plan['duration_days'] * $multiplier;
                }
                
                $endDate = date('Y-m-d', strtotime($startDate . " + $daysToAdd days"));
                
                // For TIME plans, sessions usually 0 or unlimited logic?
                // Schema has sessions_count. If 0 usually means unlimited/ignored.
                $sessionsTotal = 0;

            } else { // SESSIONS
                // Sessions Logic
                // Start Date matters for validity period? Usually yes or infinite.
                // Let's assume 1 year validity or config? For MVP: 1 year.
                $endDate = date('Y-m-d', strtotime($startDate . " + 365 days"));
                
                // Calculate total sessions
                $sessionsTotal = $plan['sessions_count'] * $multiplier;
                
                // Check if adding to existing active session membership?
                // Prompt B1.5: "Si tiene membresía activa... sessions_new = sessions_remaining + new".
                // This means we UPDATE the existing membership or create a new one that overlaps?
                // Creating a NEW membership record is cleaner for history/finance. 
                // But Access Control needs to sum them up.
                // Let's create a NEW record. The Attendance Logic will sum up "Active" memberships.
                // Or we can "Carry Over" logic. 
                // Decision: Create NEW membership. 
                // But wait, "Continue" logic for TIME extends date.
                // For SESSIONS, we just add a new pile of sessions valid from Today.
            }

            // --- DATABASE INSERTION ---

            // 1. Create Membership
            $stmt = $db->prepare("
                INSERT INTO memberships (
                    gym_id, client_id, plan_id, start_date, end_date, 
                    sessions_total, sessions_used, status, price_at_purchase, 
                    purchase_mode, multiplier
                ) VALUES (?, ?, ?, ?, ?, ?, 0, 'active', ?, ?, ?)
            ");
            $stmt->execute([
                $gymId, $clientId, $planId, $startDate, $endDate, 
                $sessionsTotal, $price, $purchaseMode, $multiplier
            ]);
            $membershipId = $db->lastInsertId();

            // 2. Create Payment
            // Generate Consecutive
            $stmt = $db->prepare("SELECT MAX(consecutive_number) as max_cons FROM payments WHERE gym_id = ?");
            $stmt->execute([$gymId]);
            $max = $stmt->fetch()['max_cons'];
            $consecutive = ($max) ? $max + 1 : 1;
            
            $stmt = $db->prepare("
                INSERT INTO payments (
                    gym_id, client_id, membership_id, amount, payment_method, 
                    created_by_user_id, consecutive_number, discount, notes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $gymId, $clientId, $membershipId, $price, $paymentMethod, 
                $userId, $consecutive, $discount, $notes
            ]);
            $paymentId = $db->lastInsertId();

            // 3. Create Receipt Snapshot
            $snapshot = [
                'gym_name' => $gym['name'],
                'gym_logo' => $gym['branding_logo'],
                'gym_contact' => json_decode($gym['contact_info'] ?? '', true),
                'client_name' => $client['name'],
                'client_id' => $client['identification'],
                'plan_name' => $plan['name'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'sessions' => $sessionsTotal,
                'amount_total' => $price,
                'discount' => $discount,
                'cashier' => $_SESSION['user_name'],
                'notes' => $notes
            ];
            
            $stmt = $db->prepare("
                INSERT INTO receipts (gym_id, payment_id, receipt_number, snapshot_json)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$gymId, $paymentId, $consecutive, json_encode($snapshot)]);

            // 4. Create Accounting Document (Receipt)
            require_once __DIR__ . '/../models/CashReceipt.php';
            $receiptModel = new CashReceipt();
            $concept = "Payment for Membership #$membershipId ({$plan['name']})";
            $receiptModel->create($gymId, $paymentId, $price, $paymentMethod, $consecutive, $concept, $notes, $userId, $clientId);

            // 5. Create Notification
            $msg = "New sale: {$plan['name']} to {$client['name']} ($" . number_format($price, 2) . ")";
            $stmt = $db->prepare("INSERT INTO notifications (gym_id, type, message) VALUES (?, 'SALE', ?)");
            $stmt->execute([$gymId, $msg]);

            $db->commit();

            $_SESSION['success'] = 'Membership sold successfully.';
            $this->redirect("/gym/payments/receipt?id=$paymentId"); // Redirect to receipt

        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = 'Error processing sale: ' . $e->getMessage();
            $this->redirect("/gym/memberships/create?client_id=$clientId");
        }
    }
}
