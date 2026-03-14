<?php

// Configuración de Sesiones Seguras
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

session_start();

// Cargar Configuración
require_once __DIR__ . '/../config/config.php';

// Autoloader Simple (PSR-4 style)
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

use App\Core\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\TenantMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\SuperAdminMiddleware;

// Inicializar Router
$router = new Router();

// --- RUTAS PÚBLICAS ---
$router->get('/', 'AuthController@login');
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@authenticate');
$router->get('/logout', 'AuthController@logout');

// --- RUTAS SUPER ADMIN (SaaS Management) ---
$router->group(['middleware' => [AuthMiddleware::class, SuperAdminMiddleware::class]], function($r) {

    // Panel Maestro
    $r->get('/admin/dashboard', 'Admin\DashboardController@index');

    // Gestión de Clientes (Gimnasios)
    $r->get('/admin/gyms', 'Admin\GymsController@index');
    $r->get('/admin/gyms/create', 'Admin\GymsController@create');
    $r->group(['middleware' => [CsrfMiddleware::class]], function($r2) {
        $r2->post('/admin/gyms/store', 'Admin\GymsController@store');
        $r2->post('/admin/gyms/toggle', 'Admin\GymsController@toggleStatus');
    });

    // CRM Leads
    $r->get('/admin/leads', 'Admin\LeadsController@index');
    $r->get('/admin/leads/create', 'Admin\LeadsController@create');
    $r->group(['middleware' => [CsrfMiddleware::class]], function($r2) {
        $r2->post('/admin/leads/store', 'Admin\LeadsController@store');
        $r2->post('/admin/leads/update-status', 'Admin\LeadsController@updateStatus');
    });

    // Planes SaaS
    $r->get('/admin/plans', 'Admin\PlansController@index');
    $r->get('/admin/plans/create', 'Admin\PlansController@create');
    $r->group(['middleware' => [CsrfMiddleware::class]], function($r2) {
        $r2->post('/admin/plans/store', 'Admin\PlansController@store');
        $r2->post('/admin/plans/toggle', 'Admin\PlansController@toggleStatus');
    });

    // Placeholder para Facturación (mencionada en UI pero backend aún no detallado en prompts)
    $r->get('/admin/billing', 'Admin\GymsController@index');
});

// --- RUTAS TENANT (Gimnasios Individuales) ---
$router->group(['middleware' => [AuthMiddleware::class, TenantMiddleware::class]], function($r) {

    // Dashboard Gym
    $r->get('/dashboard', 'DashboardController@index');

    // Contabilidad Gym
    $r->get('/accounting', 'AccountingController@index');
    $r->group(['middleware' => [CsrfMiddleware::class]], function($r2) {
        $r2->post('/accounting/receipts/store', 'AccountingController@storeReceipt');
    });
    $r->get('/accounting/view', 'AccountingController@viewJournal');

    // Reportes Contables Gym
    $r->get('/accounting/reports', 'ReportsController@index');
    $r->get('/accounting/reports/balance', 'ReportsController@balanceSheet');
    $r->get('/accounting/reports/income', 'ReportsController@incomeStatement');

    // Miembros Gym
    $r->get('/members', 'MembersController@index');
    $r->get('/members/create', 'MembersController@create');
    $r->group(['middleware' => [CsrfMiddleware::class]], function($r2) {
        $r2->post('/members/store', 'MembersController@store');
    });

    // Control de Acceso (API + UI) Gym
    $r->get('/access', 'AccessController@index');
    $r->group(['middleware' => [CsrfMiddleware::class]], function($r2) {
        $r2->post('/access/scan', 'AccessController@scan');
    });
});

// Despachar y Manejar Errores de Base de Datos que requieran instalación
try {
    $router->dispatch();
} catch (\PDOException $e) {
    // 42S02 = Table not found
    // 42S22 o 1054 = Column not found
    if ($e->getCode() == '42S02' || $e->getCode() == '42S22' || strpos($e->getMessage(), '1054') !== false) {
        http_response_code(500);
        echo "<!DOCTYPE html><html lang='es'><head><meta charset='UTF-8'><title>Actualización Requerida</title><style>
            body { font-family: sans-serif; background: #121212; color: #e0e0e0; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
            .card { background: rgba(255,255,255,0.05); padding: 40px; border-radius: 12px; text-align: center; border: 1px solid rgba(255,255,255,0.1); max-width: 500px; box-shadow: 0 4px 6px rgba(0,0,0,0.3); }
            a.btn { display: inline-block; background: #2196f3; color: white; padding: 15px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; transition: background 0.3s;}
            a.btn:hover { background: #1976d2; }
            h1 { color: #f44336; }
        </style></head><body>";
        echo "<div class='card'>";
        echo "<h1>⚠️ Base de Datos Desactualizada</h1>";
        echo "<p>El sistema detectó que faltan tablas o columnas en la base de datos (probablemente se agregaron nuevas características). <b>Para solucionar este error, debes actualizar la base de datos.</b></p>";
        echo "<p><small style='color:#a0a0a0;'>Error Técnico: " . htmlspecialchars($e->getMessage()) . "</small></p>";
        echo "<a href='" . BASE_URL . "/install.php' class='btn'>🚀 Ejecutar Actualización (install.php) ahora</a>";
        echo "</div></body></html>";
        exit;
    }
    // Si es otro error de PDO, lanzarlo
    throw $e;
}
