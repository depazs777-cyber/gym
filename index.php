<?php

session_start();

// Load Config
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Helper functions (Optional)
function dd($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    die();
}

// Autoloader
spl_autoload_register(function ($class) {
    $paths = [
        CONTROLLER_PATH . '/',
        MODEL_PATH . '/',
        ROOT_PATH . '/helpers/',
        ROOT_PATH . '/middlewares/'
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Init Router
$router = new Router();

// Routes Definition
// Auth
$router->get('/', 'AuthController@login'); // Login page is home if not auth
$router->post('/login', 'AuthController@authenticate');
$router->get('/logout', 'AuthController@logout');

// Super Admin
$router->get('/admin/dashboard', 'AdminController@dashboard');
$router->get('/admin/gyms', 'AdminController@listGyms');
$router->get('/admin/gyms/create', 'AdminController@createGymForm');
$router->post('/admin/gyms/store', 'AdminController@storeGym');
$router->get('/admin/gyms/edit', 'AdminController@editGymForm');
$router->post('/admin/gyms/update', 'AdminController@updateGym');
$router->post('/admin/gyms/create-admin', 'AdminController@createGymAdmin'); // New for Vendedor
$router->get('/admin/api/global-data', 'AdminController@getGlobalData');

// SaaS Users Management (Super Admin)
$router->get('/admin/users', 'SaaSUsersController@index');
$router->post('/admin/users/create', 'SaaSUsersController@store');
$router->post('/admin/users/update', 'SaaSUsersController@update');
$router->post('/admin/users/toggle', 'SaaSUsersController@toggleStatus');
$router->post('/admin/users/reset-pass', 'SaaSUsersController@resetPassword');

// Billing (Finance)
$router->get('/admin/billing', 'BillingController@index');
$router->post('/admin/billing/renew', 'BillingController@renew');
$router->get('/admin/billing/history', 'BillingController@history');
$router->post('/admin/billing/schedule-increase', 'BillingController@scheduleIncrease');
$router->post('/admin/billing/cancel-increase', 'BillingController@cancelIncrease');

// Finance Reports
$router->get('/admin/reports-finance', 'FinanceReportsController@index');
$router->get('/admin/reports-finance/export', 'FinanceReportsController@export');

// === NEW ADMIN ACCOUNTING ROUTES ===
$router->get('/admin/accounting', 'AdminAccountingController@index');

$router->get('/admin/accounting/third-parties', 'AdminAccountingController@thirdPartiesIndex');
$router->get('/admin/accounting/third-parties/create', 'AdminAccountingController@thirdPartiesCreate');
$router->post('/admin/accounting/third-parties/store', 'AdminAccountingController@thirdPartiesStore');

$router->get('/admin/accounting/orders', 'AdminAccountingController@ordersIndex');

$router->get('/admin/accounting/receipts/create', 'AdminAccountingController@receiptsCreate');
$router->post('/admin/accounting/receipts/store', 'AdminAccountingController@receiptsStore');

$router->get('/admin/accounting/purchases', 'AdminAccountingController@purchasesIndex');
$router->get('/admin/accounting/purchases/create', 'AdminAccountingController@purchasesCreate');
$router->post('/admin/accounting/purchases/store', 'AdminAccountingController@purchasesStore');
// ===================================

// SaaS Plans (Super Admin)
$router->get('/admin/plans', 'PlansController@index');
$router->post('/admin/plans/store', 'PlansController@store');
$router->post('/admin/plans/update', 'PlansController@update');
$router->post('/admin/plans/schedule-price', 'PlansController@schedulePrice');
$router->post('/admin/plans/merge', 'PlansController@merge');
$router->post('/admin/plans/toggle-active', 'PlansController@toggleActive');

// Call Center
$router->get('/admin/leads', 'LeadsController@index');
$router->get('/admin/leads/create', 'LeadsController@create');
$router->post('/admin/leads/store', 'LeadsController@store');
$router->post('/admin/leads/update', 'LeadsController@update');
$router->post('/admin/leads/assign', 'LeadsController@assign');
$router->get('/admin/leads/users', 'LeadsController@getCallCenterUsers'); // AJAX
$router->get('/admin/leads/call-data', 'LeadsController@callData'); // AJAX
$router->post('/admin/calls/log', 'LeadsController@logCall');

// Call Center Extended
$router->get('/admin/call-logs', 'LeadsController@callLogs');
$router->get('/admin/agenda', 'LeadsController@agenda');

$router->get('/admin/scripts', 'ScriptsController@index');
$router->post('/admin/scripts/store', 'ScriptsController@store');

$router->get('/admin/motivation', 'MotivationController@index');
$router->post('/admin/motivation/store', 'MotivationController@store');

// Gym Admin / Staff
$router->get('/gym/dashboard', 'GymController@dashboard');
$router->get('/gym/clients', 'GymController@listClients');
$router->get('/gym/clients/create', 'GymController@createClientForm');
$router->post('/gym/clients/store', 'GymController@storeClient');
$router->get('/gym/clients/card-data', 'GymController@getCardData');

// Plans
$router->get('/gym/plans', 'PlanController@index');
$router->get('/gym/plans/create', 'PlanController@create');
$router->post('/gym/plans/store', 'PlanController@store');
$router->get('/gym/plans/edit', 'PlanController@edit');
$router->post('/gym/plans/update', 'PlanController@update');

// Gym Settings
$router->get('/gym/settings', 'GymSettingsController@settings');
$router->post('/gym/settings/update', 'GymSettingsController@update');

// Memberships & Payments
$router->get('/gym/memberships', 'MembershipController@index');
$router->get('/gym/memberships/create', 'MembershipController@create');
$router->post('/gym/memberships/store', 'MembershipController@store');
$router->get('/gym/payments', 'PaymentController@index');
$router->get('/gym/payments/receipt', 'PaymentController@receipt');

// Staff
$router->get('/gym/staff', 'GymStaffController@index');
$router->post('/gym/staff/store', 'GymStaffController@store');
$router->post('/gym/staff/toggle', 'GymStaffController@toggle');

// Reports
$router->get('/gym/reports', 'ReportsController@index');

// Notifications
$router->get('/gym/notifications/fetch', 'NotificationsController@fetch');
$router->post('/gym/notifications/mark-read', 'NotificationsController@markRead');

// Attendance
$router->get('/gym/attendance', 'AttendanceController@index');
$router->get('/gym/attendance/checkin', 'AttendanceController@checkin');
$router->post('/gym/attendance/verify', 'AttendanceController@verify');

// === GYM THIRD PARTIES ROUTES ===
$router->get('/gym/third_parties', 'ThirdPartiesController@index');
$router->get('/gym/third_parties/create', 'ThirdPartiesController@create');
$router->post('/gym/third_parties/store', 'ThirdPartiesController@store');
$router->get('/gym/third_parties/edit', 'ThirdPartiesController@edit'); // Note: router needs wildcard support or use Query Param. Using Query Param as per controller implementation.
$router->post('/gym/third_parties/update', 'ThirdPartiesController@update');

// === GYM ACCOUNTING ROUTES ===
$router->get('/gym/accounting', 'AccountingController@index');
$router->get('/gym/accounting/view', 'AccountingController@viewDocument'); // Query Param id

// Dispatch
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
