<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "Starting Gym Registration Migration...\n";

$pdo = (new Database())->getConnection();
$driver = getenv('DB_DRIVER') ?: 'mysql';

function addColumn($pdo, $table, $column, $definition) {
    try {
        $sql = "ALTER TABLE $table ADD COLUMN $column $definition";
        $pdo->exec($sql);
        echo "Added column $column to $table.\n";
    } catch (Exception $e) {
        // Simple check for SQLite/MySQL if column exists
        // MySQL: "Duplicate column name"
        // SQLite: "duplicate column name"
        if (strpos($e->getMessage(), 'duplicate column name') !== false || strpos($e->getMessage(), 'Duplicate column name') !== false) {
             echo "Column $column already exists in $table.\n";
        } else {
             echo "Error adding $column to $table: " . $e->getMessage() . "\n";
        }
    }
}

// Update gyms table
// registered_at: When the gym was created in the system
addColumn($pdo, 'gyms', 'registered_at', 'DATETIME DEFAULT NULL');

// subscription_status: ACTIVE, SUSPENDED, EXPIRED, TRIAL
addColumn($pdo, 'gyms', 'subscription_status', "VARCHAR(50) DEFAULT 'ACTIVE'");

// activated_at: When the license started
addColumn($pdo, 'gyms', 'activated_at', 'DATETIME DEFAULT NULL');

echo "Gym Registration Migration Complete.\n";
