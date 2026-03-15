<?php
// migration_phase_2.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();
$driver = getenv('DB_DRIVER') ?: 'mysql';
$pk = ($driver === 'sqlite') ? "INTEGER PRIMARY KEY AUTOINCREMENT" : "INT AUTO_INCREMENT PRIMARY KEY";

echo "Starting Phase 2 Migration ($driver)...\n";

function execSql($db, $sql) {
    try {
        $db->exec($sql);
    } catch (PDOException $e) {
        // echo "SQL Info: " . $e->getMessage() . "\n";
    }
}

// 1. SaaS Plan Price Changes
$sql = "CREATE TABLE IF NOT EXISTS saas_plan_price_changes (
    id $pk,
    saas_plan_id INT NOT NULL,
    old_price DECIMAL(15,2),
    new_price DECIMAL(15,2) NOT NULL,
    effective_date DATE NOT NULL,
    notify_date DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'SCHEDULED', -- SCHEDULED, APPLIED, CANCELLED
    created_by_user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (saas_plan_id) REFERENCES saas_plans(id)
)";
execSql($db, $sql);

// 2. Accounting Documents (Unified)
// Replaces receipts, purchases tables eventually.
$sql = "CREATE TABLE IF NOT EXISTS accounting_documents (
    id $pk,
    gym_id INT NOT NULL DEFAULT 0, -- 0 for SaaS
    doc_type VARCHAR(10) NOT NULL, -- RC, CE, FC, DS, NC
    prefix VARCHAR(10),
    consecutive_number INT,
    doc_number_full VARCHAR(50), -- PREFIX-NUMBER
    issue_date DATE NOT NULL,
    third_party_id INT,
    description TEXT,
    status VARCHAR(20) DEFAULT 'DRAFT', -- DRAFT, POSTED, VOID
    total_gross DECIMAL(15,2) DEFAULT 0,
    total_taxes DECIMAL(15,2) DEFAULT 0,
    total_withholdings DECIMAL(15,2) DEFAULT 0,
    total_net DECIMAL(15,2) DEFAULT 0,
    created_by_user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (third_party_id) REFERENCES third_parties(id)
)";
execSql($db, $sql);

// 3. Document Lines
$sql = "CREATE TABLE IF NOT EXISTS accounting_document_lines (
    id $pk,
    document_id INT NOT NULL,
    concept VARCHAR(255),
    quantity DECIMAL(10,2) DEFAULT 1,
    unit_price DECIMAL(15,2) DEFAULT 0,
    line_total DECIMAL(15,2) DEFAULT 0,
    tax_type VARCHAR(20) DEFAULT 'NONE', -- IVA_19, etc.
    tax_amount DECIMAL(15,2) DEFAULT 0,
    withholding_type VARCHAR(20) DEFAULT 'NONE',
    withholding_amount DECIMAL(15,2) DEFAULT 0,
    gl_account_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (document_id) REFERENCES accounting_documents(id) ON DELETE CASCADE
)";
execSql($db, $sql);

// 4. GL Accounts (Plan de Cuentas)
$sql = "CREATE TABLE IF NOT EXISTS gl_accounts (
    id $pk,
    gym_id INT NOT NULL DEFAULT 0,
    code VARCHAR(20) NOT NULL,
    name VARCHAR(100) NOT NULL,
    nature VARCHAR(10) NOT NULL, -- DEBIT, CREDIT
    is_active TINYINT(1) DEFAULT 1,
    parent_id INT DEFAULT NULL
)";
execSql($db, $sql);

echo "Phase 2 Migration Complete.\n";
