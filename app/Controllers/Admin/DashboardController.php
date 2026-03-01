<?php

namespace App\Controllers\Admin;

use App\Core\Controller;

class DashboardController extends Controller {

    public function index() {
        // Métricas Globales del SaaS (Super Admin)
        $stats = [
            'total_gyms' => $this->db->query("SELECT COUNT(*) FROM tenants WHERE status = 'active'")->fetchColumn(),
            'expired_gyms' => $this->db->query("SELECT COUNT(*) FROM tenants WHERE valid_until < CURDATE() AND status = 'active'")->fetchColumn(),
            'total_leads' => $this->db->query("SELECT COUNT(*) FROM leads WHERE status IN ('new', 'contacted', 'demo_scheduled')")->fetchColumn(),
            // Simulación MRR (Ingreso Recurrente Mensual de los planes activos)
            'mrr' => $this->db->query("SELECT SUM(p.price) FROM tenants t JOIN saas_plans p ON t.plan_id = p.id WHERE t.status = 'active'")->fetchColumn()
        ];

        // Últimos Gimnasios
        $latestGyms = $this->db->query("
            SELECT t.name, t.valid_until, p.name as plan_name
            FROM tenants t
            LEFT JOIN saas_plans p ON t.plan_id = p.id
            ORDER BY t.created_at DESC LIMIT 5
        ")->fetchAll();

        // Embudo
        $funnel = $this->db->query("
            SELECT status, COUNT(*) as count
            FROM leads
            GROUP BY status
        ")->fetchAll();

        $this->view('admin/dashboard/index', [
            'stats' => $stats,
            'latestGyms' => $latestGyms,
            'funnel' => $funnel
        ]);
    }
}
