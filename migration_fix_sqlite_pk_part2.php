<?php
define('APP_NAME', 'PROMPT_MAESTRO');
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

// Check Driver
$driver = getenv('DB_DRIVER') ?: 'mysql';
if ($driver !== 'sqlite') {
    die("Skipping SQLite-specific migration on $driver.\n");
}

function recreateTable($db, $tableName, $createSql) {
    echo "Recreating $tableName...\n";
    $db->exec("PRAGMA foreign_keys = OFF");

    // Rename old
    try {
        $db->exec("ALTER TABLE $tableName RENAME TO {$tableName}_old");
    } catch (PDOException $e) {
        echo "Rename failed (maybe table doesn't exist?): " . $e->getMessage() . "\n";
        return;
    }

    // Create new
    try {
        $db->exec($createSql);
    } catch (PDOException $e) {
        echo "Create failed: " . $e->getMessage() . "\n";
        $db->exec("ALTER TABLE {$tableName}_old RENAME TO $tableName");
        return;
    }

    // Copy data
    try {
        $db->exec("INSERT INTO $tableName SELECT * FROM {$tableName}_old");
    } catch (PDOException $e) {
        echo "Copy failed: " . $e->getMessage() . "\n";
        $db->exec("DROP TABLE IF EXISTS $tableName");
        $db->exec("ALTER TABLE {$tableName}_old RENAME TO $tableName");
        return;
    }

    // Drop old
    $db->exec("DROP TABLE {$tableName}_old");
    $db->exec("PRAGMA foreign_keys = ON");
    echo "Done $tableName.\n";
}

$clientsSql = "CREATE TABLE clients (
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

$plansSql = "CREATE TABLE plans (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    gym_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(20) NOT NULL,
    duration_days INT DEFAULT 0,
    sessions_count INT DEFAULT 0,
    price DECIMAL(10, 2) NOT NULL,
    allow_freeze TINYINT(1) DEFAULT 0,
    status VARCHAR(20) DEFAULT 'active',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE
)";

$tokensSql = "CREATE TABLE client_tokens (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    gym_id INT NOT NULL,
    client_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    UNIQUE (token)
)";

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

$notifReadsSql = "CREATE TABLE notification_reads (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    notification_id INT NOT NULL,
    user_id INT NOT NULL,
    read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

recreateTable($db, 'clients', $clientsSql);
recreateTable($db, 'plans', $plansSql);
recreateTable($db, 'client_tokens', $tokensSql);
recreateTable($db, 'notifications', $notifSql);
recreateTable($db, 'notification_reads', $notifReadsSql);

echo "Part 2 Complete.\n";
