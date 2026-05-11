<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "Starting Finance Sales Migration...\n";

$pdo = new Database()->getConnection();
$driver = getenv('DB_DRIVER') ?: 'mysql';

function addColumn($pdo, $table, $column, $definition) {
    try {
        $sql = "ALTER TABLE $table ADD COLUMN $column $definition";
        $pdo->exec($sql);
        echo "Added column $column to $table.\n";
    } catch (Exception $e) {
        // Simple check, SQLite errors if column exists
        echo "Column $column might already exist or error: " . $e->getMessage() . "\n";
    }
}

// 1. Update saas_payments table
addColumn($pdo, 'saas_payments', 'plan_code', 'VARCHAR(20) DEFAULT NULL'); // ANNUAL, MONTHLY
addColumn($pdo, 'saas_payments', 'years_paid', 'INTEGER DEFAULT NULL');
addColumn($pdo, 'saas_payments', 'unit_price', 'DECIMAL(10, 2) DEFAULT 0.00');
addColumn($pdo, 'saas_payments', 'discount_type', 'VARCHAR(20) DEFAULT NULL'); // PERCENT, FIXED
addColumn($pdo, 'saas_payments', 'discount_value', 'DECIMAL(10, 2) DEFAULT 0.00');
addColumn($pdo, 'saas_payments', 'discount_reason', 'TEXT DEFAULT NULL');
addColumn($pdo, 'saas_payments', 'discount_approved_by', 'INTEGER DEFAULT NULL');
addColumn($pdo, 'saas_payments', 'amount_total', 'DECIMAL(10, 2) DEFAULT 0.00');

// 2. Update gyms table (Snapshot)
addColumn($pdo, 'gyms', 'subscription_plan_code', 'VARCHAR(20) DEFAULT NULL');
addColumn($pdo, 'gyms', 'subscription_price_snapshot', 'DECIMAL(10, 2) DEFAULT 0.00');

echo "Finance Sales Migration Complete.\n";
