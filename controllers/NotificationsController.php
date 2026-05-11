<?php defined('APP_NAME') or exit('No direct script access allowed');

class NotificationsController extends BaseController {
    
    public function __construct() {
        // Allow all gym roles
        $this->checkAuth(); 
        if (!isset($_SESSION['gym_id'])) {
            die("No Gym ID.");
        }
    }

    public function fetch() {
        // AJAX Endpoint
        $gymId = $_SESSION['gym_id'];
        $userId = $_SESSION['user_id'];
        $db = new Database()->getConnection();
        
        // Fetch notifications for this gym that user hasn't read
        $sql = "SELECT n.* FROM notifications n
                WHERE n.gym_id = ?
                AND NOT EXISTS (SELECT 1 FROM notification_reads nr WHERE nr.notification_id = n.id AND nr.user_id = ?)
                ORDER BY n.created_at DESC LIMIT 10";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$gymId, $userId]);
        $notifs = $stmt->fetchAll();
        
        header('Content-Type: application/json');
        echo json_encode($notifs);
        exit;
    }

    public function markRead() {
        // AJAX Endpoint
        $gymId = $_SESSION['gym_id'];
        $userId = $_SESSION['user_id'];
        $id = $_POST['id'] ?? null; // ID of notification. If 'all', mark all.
        
        $db = new Database()->getConnection();
        $driver = getenv('DB_DRIVER') ?: 'mysql';
        $ignore = ($driver === 'sqlite') ? "OR IGNORE" : "IGNORE";

        if ($id === 'all') {
             // Find all unread IDs
             $sql = "SELECT id FROM notifications n WHERE n.gym_id = ? AND NOT EXISTS (SELECT 1 FROM notification_reads nr WHERE nr.notification_id = n.id AND nr.user_id = ?)";
             $stmt = $db->prepare($sql);
             $stmt->execute([$gymId, $userId]);
             $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
             
             $ins = $db->prepare("INSERT $ignore INTO notification_reads (notification_id, user_id) VALUES (?, ?)");
             foreach($ids as $nid) {
                 $ins->execute([$nid, $userId]);
             }
        } elseif ($id) {
             $stmt = $db->prepare("INSERT $ignore INTO notification_reads (notification_id, user_id) VALUES (?, ?)");
             $stmt->execute([$id, $userId]);
        }
        
        echo json_encode(['status' => 'ok']);
        exit;
    }
}
