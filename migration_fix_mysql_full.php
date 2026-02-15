<?php
// Fix for MySQL (MariaDB) on XAMPP
// usage: php migration_fix_mysql_full.php

// Avoid redefining APP_NAME if config.php is already loaded or we load it now
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/config/config.php';
}
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

echo "Starting MySQL Fixes...\n";

// 1. Disable Foreign Keys
try {
    $db->exec("SET FOREIGN_KEY_CHECKS=0");
} catch (PDOException $e) {
    die("Error disabling FK checks (Are you on MySQL?): " . $e->getMessage());
}

// 2. List of tables to ensure AUTO_INCREMENT
$tables = [
    'gyms', 'users', 'plans', 'clients', 'memberships', 'payments', 
    'receipts', 'attendance', 'client_tokens', 'saas_plans', 
    'saas_plan_price_changes', 'saas_payments', 'saas_license_renewals', 
    'leads', 'call_scripts', 'call_logs', 'motivation_posts', 
    'saas_settings', 'notifications', 'notification_reads'
];

foreach ($tables as $table) {
    try {
        echo "Processing $table...\n";
        
        // Check if table exists
        $check = $db->query("SHOW TABLES LIKE '$table'");
        if ($check->rowCount() == 0) {
            echo "Table $table does not exist. ";
            if ($table === 'clients') {
                echo "Creating clients table...\n";
                createClientsTable($db);
                continue; // created with auto_increment
            } else {
                echo "Skipping.\n";
                continue;
            }
        }

        // Clean ID 0
        // We check if column 'id' exists first? Assuming all these have 'id'.
        $count = $db->query("SELECT COUNT(*) FROM $table WHERE id = 0")->fetchColumn();
        if ($count > 0) {
            echo "Found $count rows with id=0 in $table. Deleting to fix AUTO_INCREMENT...\n";
            $db->exec("DELETE FROM $table WHERE id = 0");
        }

        // Modify Column to add AUTO_INCREMENT
        // Note: In MySQL, the column must be a Key. It usually is PRIMARY KEY already.
        // If not, we might need to add PRIMARY KEY.
        // But the error "Duplicate entry '0' for key 'PRIMARY'" implies it IS a primary key.
        $sql = "ALTER TABLE $table MODIFY id INT AUTO_INCREMENT";
        $db->exec($sql);
        echo "Fixed AUTO_INCREMENT on $table.\n";

    } catch (PDOException $e) {
        echo "Error on $table: " . $e->getMessage() . "\n";
    }
}

// 3. Add columns to memberships
try {
    echo "Checking memberships columns...\n";
    
    // Add purchase_mode
    $cols = $db->query("SHOW COLUMNS FROM memberships LIKE 'purchase_mode'");
    if ($cols->rowCount() == 0) {
        $db->exec("ALTER TABLE memberships ADD COLUMN purchase_mode VARCHAR(20) DEFAULT 'PERIODIC'");
        echo "Added purchase_mode.\n";
    }
    
    // Add multiplier
    $cols = $db->query("SHOW COLUMNS FROM memberships LIKE 'multiplier'");
    if ($cols->rowCount() == 0) {
        $db->exec("ALTER TABLE memberships ADD COLUMN multiplier INT DEFAULT 1");
        echo "Added multiplier.\n";
    }
} catch (PDOException $e) {
    echo "Error modifying memberships: " . $e->getMessage() . "\n";
}

// 4. Re-enable Foreign Keys
$db->exec("SET FOREIGN_KEY_CHECKS=1");

echo "MySQL Fixes Complete.\n";

function createClientsTable($db) {
    $sql = "CREATE TABLE clients (
        id INT AUTO_INCREMENT PRIMARY KEY,
        gym_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        identification VARCHAR(50) NOT NULL,
        email VARCHAR(255),
        phone VARCHAR(50),
        status ENUM('active', 'inactive', 'blocked', 'debtor') DEFAULT 'active',
        access_pin VARCHAR(20),
        qr_code VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE,
        UNIQUE KEY unique_client_per_gym (gym_id, identification)
    ) ENGINE=InnoDB";
    $db->exec($sql);
    echo "Clients table created.\n";
}
