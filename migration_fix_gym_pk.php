<?php
define('APP_NAME', 'PROMPT_MAESTRO');
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$db = new Database()->getConnection();

// Tables to fix
$tables = [
    'memberships',
    'payments',
    'receipts',
    'attendance',
    'client_tokens',
    'plans',
    'clients',
    'notifications',
    'notification_reads'
];

echo "Starting Migration: Fixing PKs and Adding Columns...\n";

foreach ($tables as $table) {
    try {
        echo "Fixing $table PK...\n";
        // Modify ID to be AUTO_INCREMENT
        $sql = "ALTER TABLE $table MODIFY id INT AUTO_INCREMENT";
        $db->exec($sql);
        echo "Fixed $table.\n";
    } catch (PDOException $e) {
        echo "Error fixing $table: " . $e->getMessage() . "\n";
    }
}

// Add missing columns to memberships if needed
try {
    echo "Adding columns to memberships...\n";
    $sql = "ALTER TABLE memberships 
            ADD COLUMN purchase_mode VARCHAR(20) DEFAULT 'PERIODIC',
            ADD COLUMN multiplier INT DEFAULT 1";
    $db->exec($sql);
    echo "Added columns purchase_mode and multiplier to memberships.\n";
} catch (PDOException $e) {
    echo "Columns purchase_mode/multiplier might already exist or error: " . $e->getMessage() . "\n";
}

echo "Migration Complete.\n";
