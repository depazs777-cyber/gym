<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "Starting Migration V3 (SQLite Compatible)...\n";

$pdo = (new Database())->getConnection();

function addColumn($pdo, $table, $colDef) {
    try {
        $pdo->exec("ALTER TABLE $table ADD COLUMN $colDef");
        echo "Added column to $table: $colDef\n";
    } catch (Exception $e) {
        // echo "Column exists or error: " . $e->getMessage() . "\n";
    }
}

try {
    // Add config_warning_days to gyms
    addColumn($pdo, 'gyms', 'config_warning_days INTEGER DEFAULT 3');
    
    echo "Migration V3 Complete.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
