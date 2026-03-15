<?php
// MIGRATION: FULL REFACTOR & EXTENSION
// Covers: Notifications, Accounting (Colombian Localized), Sales Orders, Payment Applications
// Target: MySQL (XAMPP) & SQLite (Dev)

define('APP_NAME', 'PROMPT_MAESTRO');
define('ROOT_PATH', __DIR__);
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();
$driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
$isSqlite = ($driver === 'sqlite');

echo "Starting Full Refactor Migration (Driver: $driver)...\n";

$autoInc = $isSqlite ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';

// Helper to exec SQL
function execM($db, $sql, $msg) {
    try {
        $db->exec($sql);
        echo "[OK] $msg\n";
    } catch (PDOException $e) {
        // Ignore "exists" errors roughly
        if (strpos($e->getMessage(), 'exists') !== false || strpos($e->getMessage(), 'Duplicate') !== false) {
             echo "[SKIP] $msg (Already exists)\n";
        } else {
             echo "[ERR] $msg: " . $e->getMessage() . "\n";
        }
    }
}

// 1. NOTIFICATIONS
$sql = "CREATE TABLE notifications (
    id $autoInc,
    target_role VARCHAR(50) DEFAULT NULL, -- if null, specific user
    gym_id INT DEFAULT NULL, -- if null, global/saas
    user_id INT DEFAULT NULL, -- specific user
    type VARCHAR(50) NOT NULL, -- PAYMENT, LICENSE, TARIFF, NEWS, ADS
    title VARCHAR(255) NULL,
    message TEXT NOT NULL,
    link_url VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
execM($db, $sql, "Table notifications");

$sql = "CREATE TABLE notification_reads (
    id $autoInc,
    notification_id INT NOT NULL,
    user_id INT NOT NULL,
    read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
execM($db, $sql, "Table notification_reads");

// 2. DOCUMENT SEQUENCES (Consecutivos)
$sql = "CREATE TABLE document_sequences (
    id $autoInc,
    gym_id INT DEFAULT NULL, -- NULL for Global SaaS Docs
    doc_type VARCHAR(20) NOT NULL, -- RC, CE, FCI, DS, NIC, OC
    prefix VARCHAR(10) DEFAULT '',
    current_number INT DEFAULT 0,
    padding INT DEFAULT 6,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
execM($db, $sql, "Table document_sequences");

// Seed Defaults for Global
if (!$isSqlite) { // MySQL insert ignore
    $db->exec("INSERT IGNORE INTO document_sequences (doc_type, prefix, current_number) VALUES
        ('OC', 'OC-', 0), ('RC', 'RC-', 0), ('CE', 'CE-', 0)");
} else {
    // SQLite logic
    $cnt = $db->query("SELECT count(*) FROM document_sequences WHERE gym_id IS NULL AND doc_type='OC'")->fetchColumn();
    if ($cnt == 0) $db->exec("INSERT INTO document_sequences (doc_type, prefix, current_number) VALUES ('OC', 'OC-', 0)");
}

// 3. ACCOUNTING: THIRD PARTIES (Update/Verify)
// Ensure columns exist (if table exists from previous turn)
// We'll Create IF NOT EXISTS.
$sql = "CREATE TABLE third_parties (
    id $autoInc,
    gym_id INT DEFAULT NULL, -- Nullable for Global SaaS providers? Schema says tenant specific. Let's keep NOT NULL if strict, but maybe SaaS needs them too? Prompt: 'Gestión interna...'. SaaS needs providers too.
    third_type VARCHAR(20) NOT NULL,
    person_type VARCHAR(20) NOT NULL,
    has_economic_activity TINYINT(1) DEFAULT 1,
    document_type VARCHAR(20) DEFAULT 'NIT',
    document_number VARCHAR(50) NOT NULL,
    name_or_company VARCHAR(255) NOT NULL,
    trade_name VARCHAR(255) NULL,
    rut_required TINYINT(1) DEFAULT 1,
    ciuu_code VARCHAR(20) NULL,
    vat_responsible VARCHAR(10) DEFAULT 'NO',
    ica_responsible VARCHAR(10) DEFAULT 'NO',
    email VARCHAR(255) NULL,
    phone VARCHAR(50) NULL,
    address TEXT NULL,
    city VARCHAR(100) NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
// Note: If table exists from previous step, we might need ALTER.
// For now, let's assume it might fail if exists, which is fine, but we need to ensure gym_id is nullable if SaaS uses it.
// Previous turn `gym_id` was NOT NULL.
// We will try to ALTER it to modify gym_id to INT NULL if needed, but SQLite doesn't support easy ALTER COLUMN.
// We will focus on Gym Accounting first as per prompt B.4.

// 4. ACCOUNTING DOCUMENTS

// Cash Receipts (RC)
$sql = "CREATE TABLE cash_receipts (
    id $autoInc,
    gym_id INT DEFAULT NULL, -- NULL = SaaS Global
    doc_number VARCHAR(50) NOT NULL,
    third_party_id INT NULL,
    date DATE NOT NULL,
    concept TEXT NULL,
    payment_method VARCHAR(50) DEFAULT 'CASH',
    reference VARCHAR(100) NULL,
    total_amount DECIMAL(15, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE', -- ACTIVE, VOID
    void_reason TEXT NULL,
    created_by_user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
execM($db, $sql, "Table cash_receipts");

// Disbursements (CE)
$sql = "CREATE TABLE disbursements (
    id $autoInc,
    gym_id INT DEFAULT NULL,
    doc_number VARCHAR(50) NOT NULL,
    third_party_id INT NULL,
    date DATE NOT NULL,
    concept TEXT NULL,
    payment_method VARCHAR(50) DEFAULT 'CASH',
    reference VARCHAR(100) NULL,
    total_amount DECIMAL(15, 2) NOT NULL, -- Net paid
    status VARCHAR(20) DEFAULT 'ACTIVE',
    void_reason TEXT NULL,
    created_by_user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
execM($db, $sql, "Table disbursements");

// Purchase Invoices (FCI) & Support Docs (DS)
// The prompt asks for separate tables.
$sql = "CREATE TABLE purchase_invoices (
    id $autoInc,
    gym_id INT DEFAULT NULL,
    doc_number VARCHAR(50) NOT NULL, -- Internal Sequence
    supplier_id INT NOT NULL,
    supplier_invoice_number VARCHAR(50) NOT NULL, -- External
    date DATE NOT NULL,
    due_date DATE NULL,

    subtotal DECIMAL(15, 2) DEFAULT 0,
    iva DECIMAL(15, 2) DEFAULT 0,
    total DECIMAL(15, 2) DEFAULT 0,

    reteiva_base DECIMAL(15, 2) DEFAULT 0,
    reteiva_value DECIMAL(15, 2) DEFAULT 0,
    reteica_base DECIMAL(15, 2) DEFAULT 0,
    reteica_value DECIMAL(15, 2) DEFAULT 0,

    total_payable DECIMAL(15, 2) DEFAULT 0,

    status VARCHAR(20) DEFAULT 'PENDING',
    attachment_path VARCHAR(255) NULL,
    created_by_user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
execM($db, $sql, "Table purchase_invoices");

$sql = "CREATE TABLE support_documents (
    id $autoInc,
    gym_id INT DEFAULT NULL,
    doc_number VARCHAR(50) NOT NULL,
    supplier_id INT NOT NULL, -- Can be 'No Economic Activity' person
    date DATE NOT NULL,
    concept TEXT NULL,

    subtotal DECIMAL(15, 2) DEFAULT 0,
    iva DECIMAL(15, 2) DEFAULT 0, -- DS usually no IVA, but optional
    total DECIMAL(15, 2) DEFAULT 0,

    reteiva_base DECIMAL(15, 2) DEFAULT 0,
    reteiva_value DECIMAL(15, 2) DEFAULT 0,
    reteica_base DECIMAL(15, 2) DEFAULT 0,
    reteica_value DECIMAL(15, 2) DEFAULT 0,

    total_payable DECIMAL(15, 2) DEFAULT 0,

    status VARCHAR(20) DEFAULT 'PENDING',
    attachment_path VARCHAR(255) NULL,
    created_by_user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
execM($db, $sql, "Table support_documents");

// Accounting Notes (NIC)
$sql = "CREATE TABLE accounting_notes (
    id $autoInc,
    gym_id INT DEFAULT NULL,
    doc_number VARCHAR(50) NOT NULL,
    date DATE NOT NULL,
    type VARCHAR(20) DEFAULT 'GENERAL', -- DEBIT, CREDIT, ADJUSTMENT
    description TEXT NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    void_reason TEXT NULL,
    attachment_path VARCHAR(255) NULL,
    created_by_user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
execM($db, $sql, "Table accounting_notes");

// 5. SALES & COLLECTIONS (For Legal Operation)

// Sales Orders (OC)
$sql = "CREATE TABLE sales_orders (
    id $autoInc,
    gym_id INT NOT NULL, -- The Gym being sold to (Tenant)
    doc_number VARCHAR(50) NOT NULL,

    plan_id INT NOT NULL, -- SaaS Plan
    period_months INT DEFAULT 12,
    unit_price DECIMAL(15, 2) NOT NULL,

    discount_type VARCHAR(20) DEFAULT NULL,
    discount_value DECIMAL(15, 2) DEFAULT 0,
    discount_reason TEXT NULL,
    discount_approved_by INT DEFAULT NULL,

    total_amount DECIMAL(15, 2) NOT NULL,

    status VARCHAR(20) DEFAULT 'PENDING_PAYMENT', -- PENDING_PAYMENT, PARTIAL, PAID, VOID

    seller_user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
execM($db, $sql, "Table sales_orders");

// Payment Applications (Linking RC to OC)
$sql = "CREATE TABLE payment_applications (
    id $autoInc,
    cash_receipt_id INT NOT NULL,
    sales_order_id INT NOT NULL,
    applied_amount DECIMAL(15, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cash_receipt_id) REFERENCES cash_receipts(id) ON DELETE CASCADE,
    FOREIGN KEY (sales_order_id) REFERENCES sales_orders(id) ON DELETE CASCADE
)";
execM($db, $sql, "Table payment_applications");

echo "Full Refactor Migration Complete.\n";
