<?php
// migration_foundation_fix.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();
$driver = getenv('DB_DRIVER') ?: 'mysql';
$pk = ($driver === 'sqlite') ? "INTEGER PRIMARY KEY AUTOINCREMENT" : "INT AUTO_INCREMENT PRIMARY KEY";

echo "Starting Foundation Fix Migration ($driver)...\n";

function execSql($db, $sql) {
    try {
        $db->exec($sql);
    } catch (PDOException $e) {
        // Echo only if strictly necessary, catching 'exists' errors is hard reliably across drivers without specific codes
        // But for migration fix, silent fail on exists is usually desired if IF NOT EXISTS is used.
        // echo "SQL Info: " . $e->getMessage() . "\n";
    }
}

function addColumn($db, $table, $colDef) {
    try {
        $db->exec("ALTER TABLE $table ADD COLUMN $colDef");
        echo "Added column to $table: $colDef\n";
    } catch (PDOException $e) {
        // Likely exists
    }
}

// 1. SaaS Plans
$sql = "CREATE TABLE IF NOT EXISTS saas_plans (
    id $pk,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50) DEFAULT NULL,
    period_months INT NOT NULL DEFAULT 1,
    current_price DECIMAL(15,2) NOT NULL DEFAULT 0,
    price_cop DECIMAL(15,2) DEFAULT NULL,
    duration_months INT DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    is_archived TINYINT(1) DEFAULT 0,
    merged_into_plan_id INT DEFAULT NULL,
    currency VARCHAR(10) DEFAULT 'COP',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
execSql($db, $sql);

// 2. Sales Orders
$sql = "CREATE TABLE IF NOT EXISTS sales_orders (
    id $pk,
    gym_id INT NOT NULL DEFAULT 0,
    plan_id INT DEFAULT NULL,
    total_amount DECIMAL(15,2) NOT NULL,
    unit_price DECIMAL(15,2) DEFAULT 0,
    period_months INT DEFAULT 1,
    discount_value DECIMAL(15,2) DEFAULT 0,
    status VARCHAR(20) DEFAULT 'PENDING',
    doc_number VARCHAR(50) DEFAULT NULL,
    seller_user_id INT DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
execSql($db, $sql);
addColumn($db, 'sales_orders', "notes TEXT DEFAULT NULL");

// 3. Notifications
$sql = "CREATE TABLE IF NOT EXISTS notifications (
    id $pk,
    gym_id INT DEFAULT NULL,
    user_id INT DEFAULT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(100),
    message TEXT NOT NULL,
    link VARCHAR(255) DEFAULT NULL,
    target_role VARCHAR(50) DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
execSql($db, $sql);

// 4. Gyms (Update Columns)
addColumn($db, 'gyms', "plan_id INT DEFAULT NULL");
addColumn($db, 'gyms', "saas_plan_id INT DEFAULT NULL");
addColumn($db, 'gyms', "registered_at TIMESTAMP DEFAULT NULL");
addColumn($db, 'gyms', "discount_value DECIMAL(15,2) DEFAULT 0");
addColumn($db, 'gyms', "license_start DATE DEFAULT NULL");
addColumn($db, 'gyms', "license_end DATE DEFAULT NULL");
addColumn($db, 'gyms', "status VARCHAR(20) DEFAULT 'active'");
addColumn($db, 'gyms', "subscription_status VARCHAR(20) DEFAULT 'ACTIVE'");
addColumn($db, 'gyms', "subscription_price_snapshot DECIMAL(15,2) DEFAULT 0");
addColumn($db, 'gyms', "subscription_period_months_snapshot INT DEFAULT 1");
addColumn($db, 'gyms', "subscription_plan_code VARCHAR(50) DEFAULT NULL");

// 5. Third Parties (Ensure exists for Accounting)
$sql = "CREATE TABLE IF NOT EXISTS third_parties (
    id $pk,
    gym_id INT NOT NULL DEFAULT 0,
    type_persona VARCHAR(20) DEFAULT 'JURIDICA',
    doc_type VARCHAR(10) DEFAULT 'NIT',
    doc_number VARCHAR(50) NOT NULL,
    dv CHAR(1) DEFAULT NULL,
    name VARCHAR(255) NOT NULL,
    full_name_or_company VARCHAR(255) DEFAULT NULL,
    trade_name VARCHAR(255) DEFAULT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    address VARCHAR(255),
    city VARCHAR(100),
    is_client TINYINT(1) DEFAULT 0,
    is_provider TINYINT(1) DEFAULT 0,
    reteiva_percent DECIMAL(5,2) DEFAULT 0,
    reteica_percent DECIMAL(5,2) DEFAULT 0,
    has_economic_activity TINYINT(1) DEFAULT 1,
    rut_required TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
execSql($db, $sql);

echo "Foundation Fix Migration Complete.\n";
