<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "Starting Call Center V2 Migration...\n";

$pdo = Database::getInstance()->getConnection();

function addColumn($pdo, $table, $column, $type) {
    try {
        $sql = "ALTER TABLE $table ADD COLUMN $column $type";
        $pdo->exec($sql);
        echo "Added column $column to $table.\n";
    } catch (Exception $e) {
        // Ignore if exists (SQLite throws error)
        echo "Column $column might already exist or error: " . $e->getMessage() . "\n";
    }
}

// 1. Add Columns to Leads
addColumn($pdo, 'leads', 'city', 'VARCHAR(100)');
addColumn($pdo, 'leads', 'gym_name', 'VARCHAR(255)');
addColumn($pdo, 'leads', 'owner_name', 'VARCHAR(255)');
addColumn($pdo, 'leads', 'last_call_at', 'DATETIME');
addColumn($pdo, 'leads', 'assigned_to_user_id', 'INTEGER');

// 2. SaaS Settings Table
try {
    $sql = "CREATE TABLE IF NOT EXISTS saas_settings (
        id INTEGER PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Table saas_settings created/verified.\n";

    // Seed default settings
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO saas_settings (setting_key, setting_value) VALUES (?, ?)");
    $stmt->execute(['call_center_start_time', '08:00']);
    $stmt->execute(['call_center_end_time', '18:00']);
    echo "Seeded default settings.\n";

} catch (Exception $e) {
    echo "Error with settings: " . $e->getMessage() . "\n";
}

echo "Migration V2 Complete.\n";
