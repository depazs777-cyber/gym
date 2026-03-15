<?php defined('APP_NAME') or exit('No direct script access allowed');

class FinanceReportsController extends BaseController {

    public function __construct() {
        $this->checkRole(['SUPER_ADMIN', 'FINANZAS']);
    }

    public function index() {
        $db = Database::getInstance()->getConnection();

        // Filters
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');

        // KPIs
        // Income Today
        $driver = getenv('DB_DRIVER') ?: 'mysql';
        if ($driver === 'sqlite') {
            $sqlToday = "SELECT SUM(total_amount) FROM sales_orders WHERE status='PAID' AND date(created_at) = date('now')";
            $sqlMonth = "SELECT SUM(total_amount) FROM sales_orders WHERE status='PAID' AND strftime('%Y-%m', created_at) = strftime('%Y-%m', 'now')";
            $sqlYear = "SELECT SUM(total_amount) FROM sales_orders WHERE status='PAID' AND strftime('%Y', created_at) = strftime('%Y', 'now')";

            // Renewals Month (Gyms expiring this month)
            $sqlRenewals = "SELECT COUNT(*) FROM gyms WHERE status='active' AND strftime('%Y-%m', license_end) = strftime('%Y-%m', 'now')";
        } else {
            $sqlToday = "SELECT SUM(total_amount) FROM sales_orders WHERE status='PAID' AND DATE(created_at) = CURDATE()";
            $sqlMonth = "SELECT SUM(total_amount) FROM sales_orders WHERE status='PAID' AND YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())";
            $sqlYear = "SELECT SUM(total_amount) FROM sales_orders WHERE status='PAID' AND YEAR(created_at) = YEAR(CURDATE())";
             $sqlRenewals = "SELECT COUNT(*) FROM gyms WHERE status='active' AND YEAR(license_end) = YEAR(CURDATE()) AND MONTH(license_end) = MONTH(CURDATE())";
        }

        $incomeToday = 0;
        $incomeMonth = 0;
        $incomeYear = 0;
        $renewalsMonth = 0;

        try {
            $incomeToday = $db->query($sqlToday)->fetchColumn() ?: 0;
            $incomeMonth = $db->query($sqlMonth)->fetchColumn() ?: 0;
            $incomeYear = $db->query($sqlYear)->fetchColumn() ?: 0;
            $renewalsMonth = $db->query($sqlRenewals)->fetchColumn() ?: 0;
        } catch (Exception $e) {
            $_SESSION['error'] = "Warning: Database tables missing. Please run migration_foundation_fix.php";
        }

        // Expiring Licenses (Next 15 days)
        if ($driver === 'sqlite') {
            $sqlExpiring = "SELECT COUNT(*) FROM gyms WHERE status = 'active' AND date(license_end) BETWEEN date('now') AND date('now', '+15 days')";
            $sqlExpired = "SELECT COUNT(*) FROM gyms WHERE status IN ('active','expiring') AND date(license_end) < date('now')";
        } else {
            $sqlExpiring = "SELECT COUNT(*) FROM gyms WHERE status = 'active' AND license_end BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY)";
            $sqlExpired = "SELECT COUNT(*) FROM gyms WHERE status IN ('active','expiring') AND license_end < CURDATE()";
        }
        $expiringCount = $db->query($sqlExpiring)->fetchColumn() ?: 0;
        $expiredCount = $db->query($sqlExpired)->fetchColumn() ?: 0;

        // Payments Table (from sales_orders)
        if ($driver === 'sqlite') {
            $sql = "SELECT s.id, s.created_at as payment_date, s.total_amount as amount, s.period_months, s.doc_number as reference,
                           g.name as gym_name, u.name as user_name
                    FROM sales_orders s
                    LEFT JOIN gyms g ON s.gym_id = g.id
                    LEFT JOIN users u ON s.seller_user_id = u.id
                    WHERE s.status='PAID' AND date(s.created_at) BETWEEN ? AND ?
                    ORDER BY s.created_at DESC";
        } else {
            $sql = "SELECT s.id, s.created_at as payment_date, s.total_amount as amount, s.period_months, s.doc_number as reference,
                           g.name as gym_name, u.name as user_name
                    FROM sales_orders s
                    LEFT JOIN gyms g ON s.gym_id = g.id
                    LEFT JOIN users u ON s.seller_user_id = u.id
                    WHERE s.status='PAID' AND DATE(s.created_at) BETWEEN ? AND ?
                    ORDER BY s.created_at DESC";
        }
        $payments = [];
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            $payments = $stmt->fetchAll();
        } catch (Exception $e) {
            // Table missing
        }

        $this->view('layouts/main', [
            'childView' => 'admin/finance_reports',
            'stats' => [
                'income_today' => $incomeToday,
                'income_month' => $incomeMonth,
                'income_year' => $incomeYear,
                'renewals_month' => $renewalsMonth,
                'expiring' => $expiringCount,
                'expired' => $expiredCount
            ],
            'payments' => $payments,
            'filters' => ['start' => $startDate, 'end' => $endDate]
        ]);
    }

    public function export() {
        $db = Database::getInstance()->getConnection();
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');

        $driver = getenv('DB_DRIVER') ?: 'mysql';
        if ($driver === 'sqlite') {
            $sql = "SELECT s.id, s.created_at as payment_date, g.name as gym_name, s.total_amount as amount, s.period_months, 'N/A' as method, s.doc_number as reference, u.name as user_name
                    FROM sales_orders s
                    LEFT JOIN gyms g ON s.gym_id = g.id
                    LEFT JOIN users u ON s.seller_user_id = u.id
                    WHERE s.status='PAID' AND date(s.created_at) BETWEEN ? AND ?
                    ORDER BY s.created_at DESC";
        } else {
            $sql = "SELECT s.id, s.created_at as payment_date, g.name as gym_name, s.total_amount as amount, s.period_months, 'N/A' as method, s.doc_number as reference, u.name as user_name
                    FROM sales_orders s
                    LEFT JOIN gyms g ON s.gym_id = g.id
                    LEFT JOIN users u ON s.seller_user_id = u.id
                    WHERE s.status='PAID' AND DATE(s.created_at) BETWEEN ? AND ?
                    ORDER BY s.created_at DESC";
        }

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die("Error: Database setup incomplete. Please run migration_foundation_fix.php");
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="finance_report_' . $startDate . '_to_' . $endDate . '.csv"');

        $fp = fopen('php://output', 'w');
        fputcsv($fp, ['ID', 'Date', 'Gym', 'Amount', 'Months', 'Method', 'Reference', 'User']);
        foreach ($rows as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);
        exit;
    }
}
