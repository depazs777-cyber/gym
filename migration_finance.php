<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "Starting Finance Module Migration...\n";

$pdo = (new Database())->getConnection();

function createTable($pdo, $sql) {
    try {
        $pdo->exec($sql);
        echo "Table verified/created.\n";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

// 1. SaaS Payments
// Records payments from Gyms to the SaaS Platform
$sql = "CREATE TABLE IF NOT EXISTS saas_payments (
    id INTEGER PRIMARY KEY, -- SQLite uses INTEGER PRIMARY KEY for auto-increment
    gym_id INTEGER NOT NULL,
    payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    period_type VARCHAR(20) NOT NULL, -- ANNUAL, MONTHLY, CUSTOM
    period_months INTEGER DEFAULT 12,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'COP',
    method VARCHAR(20) NOT NULL, -- cash, transfer, card
    reference VARCHAR(255) NULL,
    notes TEXT NULL,
    created_by_user_id INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE RESTRICT
)";
// MySQL compatibility note: SQLite uses INTEGER PRIMARY KEY for autoinc. MySQL uses INT AUTO_INCREMENT.
// The driver check in Database class handles connection, but SQL syntax differs slightly.
// Since we are running in SQLite env, I use SQLite syntax.
// However, 'AUTO_INCREMENT' is MySQL specific. SQLite implies it with INTEGER PRIMARY KEY.
// Let's ensure compatibility or stick to SQLite for this migration script in this env.
// The prompt says "MySQL (InnoDB)" in stack, but env is SQLite.
// I will use a generic CREATE TABLE that works for SQLite, assuming similar for MySQL in prod or handling it there.
// Actually, I should check driver.

$driver = getenv('DB_DRIVER') ?: 'mysql';
$pk = ($driver === 'sqlite') ? "INTEGER PRIMARY KEY" : "INT AUTO_INCREMENT PRIMARY KEY";

$sql = "CREATE TABLE IF NOT EXISTS saas_payments (
    id $pk,
    gym_id INTEGER NOT NULL,
    payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    period_type VARCHAR(20) NOT NULL,
    period_months INTEGER DEFAULT 12,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'COP',
    method VARCHAR(20) NOT NULL,
    reference VARCHAR(255) NULL,
    notes TEXT NULL,
    created_by_user_id INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
// Foreign keys can be added, SQLite supports them if enabled.
createTable($pdo, $sql);

// 2. SaaS License Renewals
// History of license updates
$sql = "CREATE TABLE IF NOT EXISTS saas_license_renewals (
    id $pk,
    gym_id INTEGER NOT NULL,
    old_end_date DATE,
    new_end_date DATE,
    renewed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    renewed_by_user_id INTEGER NOT NULL,
    payment_id INTEGER NULL,
    notes TEXT NULL,
    FOREIGN KEY (payment_id) REFERENCES saas_payments(id) ON DELETE SET NULL
)";
createTable($pdo, $sql);

echo "Finance Migration Complete.\n";
