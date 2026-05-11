<?php defined('APP_NAME') or exit('No direct script access allowed');

class ScriptsController extends BaseController {
    
    public function __construct() {
        // CALL_CENTER can view, others manage
        $this->checkRole(['SUPER_ADMIN', 'MARKETING', 'CALL_CENTER']);
    }

    public function index() {
        $db = new Database()->getConnection();
        $stmt = $db->query("SELECT * FROM call_scripts ORDER BY title ASC");
        $scripts = $stmt->fetchAll();

        $this->view('layouts/main', [
            'childView' => 'admin/scripts_list',
            'scripts' => $scripts
        ]);
    }

    public function store() {
        $this->checkRole(['SUPER_ADMIN', 'MARKETING']);
        $this->verifyCsrf();
        $title = $_POST['title'];
        $type = $_POST['customer_type'];
        $obj = $_POST['objective'];
        $body = $_POST['script_body'];

        $db = new Database()->getConnection();
        $stmt = $db->prepare("INSERT INTO call_scripts (title, customer_type, objective, script_body, created_by_user_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $type, $obj, $body, $_SESSION['user_id']]);

        $this->redirect('/admin/scripts');
    }
}
