<?php defined('APP_NAME') or exit('No direct script access allowed');

class PaymentController extends BaseController {
    
    public function __construct() {
        $this->checkRole(['ADMIN_GYM', 'RECEPCION']);
        if (!isset($_SESSION['gym_id'])) {
            die("No Gym ID associated with this session.");
        }
    }

    public function index() {
        $gymId = $_SESSION['gym_id'];
        $db = Database::getInstance()->getConnection();

        // Filters
        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-t');

        $sql = "
            SELECT p.*, c.name as client_name, u.name as cashier_name 
            FROM payments p
            JOIN clients c ON p.client_id = c.id
            JOIN users u ON p.created_by_user_id = u.id
            WHERE p.gym_id = ? 
            AND DATE(p.payment_date) BETWEEN ? AND ?
            ORDER BY p.payment_date DESC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([$gymId, $from, $to]);
        $payments = $stmt->fetchAll();

        $this->view('layouts/main', [
            'childView' => 'gym/payments_list',
            'payments' => $payments
        ]);
    }

    public function receipt() {
        $gymId = $_SESSION['gym_id'];
        $paymentId = $_GET['id'] ?? null;

        if (!$paymentId) {
            die("Payment ID required.");
        }

        $db = Database::getInstance()->getConnection();

        // Try to fetch from 'receipts' table first (Snapshot)
        $stmt = $db->prepare("SELECT * FROM receipts WHERE payment_id = ? AND gym_id = ?");
        $stmt->execute([$paymentId, $gymId]);
        $snapshot = $stmt->fetch();

        if ($snapshot) {
            // Use snapshot data
            $data = json_decode($snapshot['snapshot_json'], true);
            // Reconstruct receipt array structure for view compatibility
            $receipt = [
                'consecutive_number' => $snapshot['receipt_number'],
                'payment_date' => $snapshot['created_at'],
                'gym_name' => $data['gym_name'],
                'branding_logo' => $data['gym_logo'],
                'contact_details' => $data['gym_contact'],
                'client_name' => $data['client_name'],
                'client_id_num' => $data['client_id'],
                'plan_name' => $data['plan_name'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'amount' => $data['amount_total'],
                'payment_method' => 'N/A', // Snapshot might miss this if not saved, lets check logic
                'cashier_name' => $data['cashier'],
                'notes' => $data['notes'] ?? ''
            ];
            
            // Fetch payment method from real payment table if needed
            $stmt = $db->prepare("SELECT payment_method FROM payments WHERE id = ?");
            $stmt->execute([$paymentId]);
            $pm = $stmt->fetch();
            $receipt['payment_method'] = $pm['payment_method'] ?? '-';
            
        } else {
            // Fallback to Live Data (Old way)
            $sql = "
                SELECT p.*, 
                       c.name as client_name, c.identification as client_id_num,
                       m.start_date, m.end_date,
                       pl.name as plan_name,
                       u.name as cashier_name,
                       g.name as gym_name, g.branding_logo, g.contact_info
                FROM payments p
                JOIN clients c ON p.client_id = c.id
                LEFT JOIN memberships m ON p.membership_id = m.id
                LEFT JOIN plans pl ON m.plan_id = pl.id
                JOIN users u ON p.created_by_user_id = u.id
                JOIN gyms g ON p.gym_id = g.id
                WHERE p.id = ? AND p.gym_id = ?
            ";

            $stmt = $db->prepare($sql);
            $stmt->execute([$paymentId, $gymId]);
            $receipt = $stmt->fetch();
            
            if (!$receipt) die("Receipt not found.");

            $receipt['contact_details'] = json_decode($receipt['contact_info'] ?? '', true) ?? [];
        }

        $this->view('layouts/main', [
            'childView' => 'gym/receipt',
            'receipt' => $receipt
        ]);
    }
}
