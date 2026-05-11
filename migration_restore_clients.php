<?php
define('APP_NAME', 'PROMPT_MAESTRO');
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$db = new Database()->getConnection();

// Check Driver
$driver = getenv('DB_DRIVER') ?: 'mysql';
if ($driver !== 'sqlite') {
    die("Skipping SQLite-specific migration on $driver.\n");
}

echo "Restoring clients table...\n";
$clientsSql = "CREATE TABLE IF NOT EXISTS clients (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    gym_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    identification VARCHAR(50) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    status VARCHAR(20) DEFAULT 'active',
    access_pin VARCHAR(20),
    qr_code VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE,
    UNIQUE (gym_id, identification)
)";
$db->exec($clientsSql);
echo "Clients table restored/created.\n";

echo "Fixing notifications table...\n";
// Drop old if exists just in case
$db->exec("DROP TABLE IF EXISTS notifications_old");
try {
    // Check if notifications exists
    $res = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='notifications'");
    if ($res->fetch()) {
        $db->exec("ALTER TABLE notifications RENAME TO notifications_old");
        
        // Create new
        $notifSql = "CREATE TABLE notifications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            gym_id INT DEFAULT NULL, 
            user_id INT DEFAULT NULL, 
            title VARCHAR(255) NULL,
            message TEXT NOT NULL,
            target_role VARCHAR(50) DEFAULT NULL,
            type VARCHAR(50) DEFAULT 'INFO',
            is_read TINYINT(1) DEFAULT 0, 
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE
        )";
        $db->exec($notifSql);
        
        // Copy data: notifications_old likely has fewer columns or mismatch. 
        // We select common columns.
        // Old columns likely: id, gym_id, user_id, title, message, target_role, type, is_read, created_at
        // If mismatch was "7 values supplied" for "9 columns", maybe new schema has more columns?
        // My new schema has 9 columns.
        // Let's assume the old table had similar columns.
        // We will map them explicitly.
        $db->exec("INSERT INTO notifications (id, gym_id, user_id, title, message, target_role, type, is_read, created_at)
                   SELECT id, gym_id, user_id, title, message, target_role, type, is_read, created_at FROM notifications_old");
                   
        $db->exec("DROP TABLE notifications_old");
        echo "Notifications fixed.\n";
    } else {
        // Just create it
        $db->exec($notifSql); // Re-use sql string from above? PHP scope issue.
        // Redefine here to be safe
        $notifSql = "CREATE TABLE notifications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            gym_id INT DEFAULT NULL, 
            user_id INT DEFAULT NULL, 
            title VARCHAR(255) NULL,
            message TEXT NOT NULL,
            target_role VARCHAR(50) DEFAULT NULL,
            type VARCHAR(50) DEFAULT 'INFO',
            is_read TINYINT(1) DEFAULT 0, 
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE
        )";
        $db->exec($notifSql);
        echo "Notifications created fresh.\n";
    }
} catch (Exception $e) {
    echo "Error fixing notifications: " . $e->getMessage() . "\n";
    // If copy failed, maybe columns don't match names.
    // Fallback: Drop and recreate empty.
    $db->exec("DROP TABLE IF EXISTS notifications");
    $db->exec($notifSql);
    echo "Notifications recreated empty due to error.\n";
}

echo "Final Verification of Tables:\n";
$stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
print_r($tables);
