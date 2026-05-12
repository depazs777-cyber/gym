<?php
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['REQUEST_URI'] = '/auth/login';
require_once 'config/constants.php';
require_once 'config/database.php';

require_once 'config/session.php';
require_once 'core/Auth.php';
require_once 'core/Helpers.php';
require_once 'core/Tenant.php';
require_once 'core/Controller.php';

Session::start();
Session::set('user_id', 1);
Session::set('role_id', 1);
Session::set('tenant_id', null);

class MockController {
    public function __construct() {}
    public function view($v) { echo "View $v called"; }
}

$c = new class extends Controller {
    public function login() {
        echo "Login called";
    }
};

$c->login();
