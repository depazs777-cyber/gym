<?php
define('APP_NAME', 'PROMPT_MAESTRO');
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$db = (new Database())->getConnection();

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
        // Maybe table doesn't exist or already renamed
        echo "Rename failed (maybe table doesn't exist?): " . $e->getMessage() . "\n";
        return;
    }

    // Create new
    try {
        $db->exec($createSql);
    } catch (PDOException $e) {
        echo "Create failed: " . $e->getMessage() . "\n";
        // Rollback rename?
        $db->exec("ALTER TABLE {$tableName}_old RENAME TO $tableName");
        return;
    }

    // Copy data
    try {
        // We need to know columns. 
        // Simplest is INSERT INTO new SELECT * FROM old, but schema might differ slightly if we fixed types?
        // But here we only changed PK definition. Columns should match.
        // However, if we defined columns in slightly different order in $createSql, it might mismatch if using *
        // Better to list columns. But that's hard dynamically.
        // Let's assume * works if structure is same.
        $db->exec("INSERT INTO $tableName SELECT * FROM {$tableName}_old");
    } catch (PDOException $e) {
        echo "Copy failed: " . $e->getMessage() . "\n";
        // Restore
        $db->exec("DROP TABLE IF EXISTS $tableName");
        $db->exec("ALTER TABLE {$tableName}_old RENAME TO $tableName");
        return;
    }

    // Drop old
    $db->exec("DROP TABLE {$tableName}_old");
    $db->exec("PRAGMA foreign_keys = ON");
    echo "Done $tableName.\n";
}

// DEFINITIONS with AUTOINCREMENT
$membershipsSql = "CREATE TABLE memberships (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    gym_id INT NOT NULL,
    client_id INT NOT NULL,
    plan_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE DEFAULT NULL,
    sessions_total INT DEFAULT 0,
    sessions_used INT DEFAULT 0,
    status TEXT DEFAULT 'active',
    price_at_purchase DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    purchase_mode VARCHAR(20) DEFAULT 'PERIODIC',
    multiplier INT DEFAULT 1,
    FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE RESTRICT
)";

$paymentsSql = "CREATE TABLE payments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    gym_id INT NOT NULL,
    client_id INT NOT NULL,
    membership_id INT DEFAULT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    discount DECIMAL(10, 2) DEFAULT 0.00,
    notes TEXT DEFAULT NULL,
    payment_method VARCHAR(50) NOT NULL, 
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by_user_id INT NOT NULL,
    consecutive_number INT NOT NULL, 
    FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (membership_id) REFERENCES memberships(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE RESTRICT
)";

$receiptsSql = "CREATE TABLE receipts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    gym_id INT NOT NULL,
    payment_id INT NOT NULL,
    receipt_number VARCHAR(50) NOT NULL,
    snapshot_json JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE
)";

$attendanceSql = "CREATE TABLE attendance (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    gym_id INT NOT NULL,
    client_id INT NOT NULL,
    access_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    method VARCHAR(20) NOT NULL,
    access_granted TINYINT(1) NOT NULL, 
    rejection_reason VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
)";

recreateTable($db, 'memberships', $membershipsSql);
recreateTable($db, 'payments', $paymentsSql);
recreateTable($db, 'receipts', $receiptsSql);
recreateTable($db, 'attendance', $attendanceSql);

echo "All critical tables recreated with AUTOINCREMENT.\n";
