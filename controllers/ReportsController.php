<?php defined('APP_NAME') or exit('No direct script access allowed');

class ReportsController extends BaseController {
    
    public function __construct() {
        $this->checkRole(['ADMIN_GYM', 'CONSULTA_REPORTES']);
        if (!isset($_SESSION['gym_id'])) {
            die("No Gym ID.");
        }
    }

    public function index() {
        $gymId = $_SESSION['gym_id'];
        $db = (new Database())->getConnection();
        
        $filter = $_GET['filter'] ?? 'month';
        
        // Income Stats
        $incomeSQL = "SELECT SUM(amount) as total FROM payments WHERE gym_id = ?";
        if ($filter === 'today') $incomeSQL .= " AND DATE(payment_date) = CURDATE()";
        elseif ($filter === 'month') $incomeSQL .= " AND MONTH(payment_date) = MONTH(CURDATE()) AND YEAR(payment_date) = YEAR(CURDATE())";
        
        $stmt = $db->prepare($incomeSQL);
        $stmt->execute([$gymId]);
        $income = $stmt->fetch()['total'] ?? 0;

        // Active Clients
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM clients WHERE gym_id = ? AND status = 'active'");
        $stmt->execute([$gymId]);
        $activeClients = $stmt->fetch()['total'];

        // Expiring Soon (7 days)
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM memberships WHERE gym_id = ? AND status = 'active' AND end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
        $stmt->execute([$gymId]);
        $expiring = $stmt->fetch()['total'];

        $this->view('layouts/main', [
            'childView' => 'gym/reports',
            'income' => $income,
            'activeClients' => $activeClients,
            'expiring' => $expiring,
            'filter' => $filter
        ]);
    }
}
