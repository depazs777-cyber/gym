<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "Starting PK Fix Migration (MySQL Auto-Increment)...\n";

$pdo = Database::getInstance()->getConnection();
$driver = getenv('DB_DRIVER') ?: 'mysql';

// Helper to modify column
function fixAutoIncrement($pdo, $table) {
    global $driver;
    try {
        if ($driver === 'sqlite') {
            echo "Skipping $table fix for SQLite (INTEGER PRIMARY KEY is auto-increment).\n";
            return;
        }

        // MySQL Syntax
        $sql = "ALTER TABLE $table MODIFY COLUMN id INT AUTO_INCREMENT";
        $pdo->exec($sql);
        echo "Fixed $table: Added AUTO_INCREMENT to id.\n";
    } catch (Exception $e) {
        echo "Error fixing $table: " . $e->getMessage() . "\n";
    }
}

fixAutoIncrement($pdo, 'leads');
fixAutoIncrement($pdo, 'call_scripts');
fixAutoIncrement($pdo, 'call_logs');
fixAutoIncrement($pdo, 'motivation_posts');

echo "PK Fix Migration Complete.\n";
