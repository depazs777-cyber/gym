<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "Starting SaaS Pricing Migration...\n";

$pdo = Database::getInstance()->getConnection();
$driver = getenv('DB_DRIVER') ?: 'mysql';
$pk = ($driver === 'sqlite') ? "INTEGER PRIMARY KEY" : "INT AUTO_INCREMENT PRIMARY KEY";

function execSql($pdo, $sql) {
    try {
        $pdo->exec($sql);
        echo "Executed SQL successfully.\n";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

// 1. SaaS Plans
// Predefined plans (Monthly/Annual)
$sql = "CREATE TABLE IF NOT EXISTS saas_plans (
    id $pk,
    name VARCHAR(50) NOT NULL,
    period_months INTEGER NOT NULL DEFAULT 1,
    current_price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    currency VARCHAR(10) DEFAULT 'COP',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
execSql($pdo, $sql);

// Seed Plans if empty
$stmt = $pdo->query("SELECT COUNT(*) FROM saas_plans");
if ($stmt->fetchColumn() == 0) {
    $sql = "INSERT INTO saas_plans (name, period_months, current_price, currency) VALUES 
    ('Mensual', 1, 50000.00, 'COP'),
    ('Anual', 12, 500000.00, 'COP')";
    execSql($pdo, $sql);
    echo "Seeded SaaS Plans.\n";
}

// 2. SaaS Plan Price Changes
// For scheduling price increases
$sql = "CREATE TABLE IF NOT EXISTS saas_plan_price_changes (
    id $pk,
    saas_plan_id INTEGER NOT NULL,
    old_price DECIMAL(10, 2) NOT NULL,
    new_price DECIMAL(10, 2) NOT NULL,
    effective_date DATE NOT NULL,
    notify_date DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'SCHEDULED', -- SCHEDULED, APPLIED, CANCELLED
    created_by_user_id INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (saas_plan_id) REFERENCES saas_plans(id) ON DELETE CASCADE
)";
execSql($pdo, $sql);

// 3. Notifications System (if not exists)
// Generic notifications table
$sql = "CREATE TABLE IF NOT EXISTS notifications (
    id $pk,
    gym_id INTEGER NULL, -- NULL for global or system-wide (filtered by target_role)
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    target_role VARCHAR(50) DEFAULT 'ADMIN_GYM', -- Target audience
    type VARCHAR(50) DEFAULT 'INFO', -- INFO, WARNING, PRICE_INCREASE
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
execSql($pdo, $sql);

// Notification Reads
$sql = "CREATE TABLE IF NOT EXISTS notification_reads (
    id $pk,
    notification_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
execSql($pdo, $sql);

echo "SaaS Pricing Migration Complete.\n";
