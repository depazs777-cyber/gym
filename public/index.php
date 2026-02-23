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

// Inicializar Router
$router = new Router();

// --- RUTAS ---

// Auth (Público)
$router->get('/', 'AuthController@login');
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@authenticate');
$router->get('/logout', 'AuthController@logout');

// Rutas Protegidas (Requieren Login + Gym Seleccionado)
$router->group(['middleware' => [AuthMiddleware::class, TenantMiddleware::class]], function($r) {

    // Dashboard
    $r->get('/dashboard', 'DashboardController@index');

    // Contabilidad
    $r->get('/accounting', 'AccountingController@index');
    $r->group(['middleware' => [CsrfMiddleware::class]], function($r2) {
        $r2->post('/accounting/receipts/store', 'AccountingController@storeReceipt');
    });
    $r->get('/accounting/view', 'AccountingController@viewJournal');

    // Reportes Contables
    $r->get('/accounting/reports', 'ReportsController@index');
    $r->get('/accounting/reports/balance', 'ReportsController@balanceSheet');
    $r->get('/accounting/reports/income', 'ReportsController@incomeStatement');

    // Miembros
    $r->get('/members', 'MembersController@index');
    $r->get('/members/create', 'MembersController@create');
    $r->group(['middleware' => [CsrfMiddleware::class]], function($r2) {
        $r2->post('/members/store', 'MembersController@store');
    });

    // Control de Acceso (API + UI)
    $r->get('/access', 'AccessController@index');
    $r->group(['middleware' => [CsrfMiddleware::class]], function($r2) {
        $r2->post('/access/scan', 'AccessController@scan');
    });
});

// Despachar
$router->dispatch();
