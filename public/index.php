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

// Despachar
$router->dispatch();
