<?php

namespace App\Controllers;

use App\Core\Controller;

class DashboardController extends Controller {
    public function index() {
        // Ejemplo de datos para el dashboard
        $stats = [
            'active_members' => 120,
            'monthly_revenue' => 15000000,
            'daily_access' => 45
        ];

        $this->view('dashboard/index', ['stats' => $stats]);
    }
}
