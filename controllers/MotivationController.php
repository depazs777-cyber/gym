<?php defined('APP_NAME') or exit('No direct script access allowed');

class MotivationController extends BaseController {
    
    public function __construct() {
        $this->checkRole(['SUPER_ADMIN', 'MARKETING', 'CALL_CENTER']);
    }

    public function index() {
        $db = new Database()->getConnection();
        $stmt = $db->query("SELECT * FROM motivation_posts ORDER BY show_date DESC");
        $posts = $stmt->fetchAll();

        $this->view('layouts/main', [
            'childView' => 'admin/motivation_list',
            'posts' => $posts
        ]);
    }

    public function store() {
        $this->checkRole(['SUPER_ADMIN', 'MARKETING']);
        $this->verifyCsrf();
        $title = $_POST['title'];
        $quote = $_POST['quote_text'];
        $date = $_POST['show_date'];
        
        // Image upload logic here (omitted for brevity, assume URL input or file)
        $img = $_POST['image_url'] ?? '';

        $db = new Database()->getConnection();
        $stmt = $db->prepare("INSERT INTO motivation_posts (title, quote_text, show_date, image_url) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $quote, $date, $img]);

        $this->redirect('/admin/motivation');
    }
}
