<?php
// Migration for SaaS Accounting & Integration (Sales/Collection)
// Adds Sales Orders, Cash Receipts, Document Sequences, Internal Notes

define('APP_NAME', 'PROMPT_MAESTRO');
define('ROOT_PATH', __DIR__); // Define ROOT_PATH
require_once __DIR__ . '/config/config.php'; // Load config constants
require_once __DIR__ . '/config/database.php';

$db = new Database()->getConnection();
$driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

echo "Starting SaaS Accounting Migration (Driver: $driver)...\n";

$autoIncrement = ($driver === 'sqlite') ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';

// 1. Sales Orders (Contratos de Suscripción)
$sqlOrders = "CREATE TABLE sales_orders (
    id $autoIncrement,
    gym_id INT NOT NULL, -- The Customer
    plan_id INT NULL,
    
    amount DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    discount DECIMAL(15, 2) DEFAULT 0.00,
    total DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    
    status VARCHAR(20) DEFAULT 'PENDING_PAYMENT', -- PENDING_PAYMENT, PARTIAL, PAID, CANCELLED
    
    notes TEXT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE
)";

// 2. Cash Receipts (Recibos de Caja - RC)
$sqlRC = "CREATE TABLE cash_receipts (
    id $autoIncrement,
    gym_id INT NOT NULL, -- Payer (Gym or 0 if generic?) usually Gym
    sales_order_id INT NULL, -- Link to order
    
    consecutive_number VARCHAR(20) NOT NULL, -- RC-00001
    issue_date DATE NOT NULL,
    
    amount DECIMAL(15, 2) NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'CASH', -- CASH, TRANSFER, CARD
    reference VARCHAR(100) NULL, -- Trans ID
    
    concept VARCHAR(255) NULL,
    notes TEXT NULL,
    
    status VARCHAR(20) DEFAULT 'ACTIVE', -- ACTIVE, ANNULLED
    annulment_reason TEXT NULL,
    
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE,
    FOREIGN KEY (sales_order_id) REFERENCES sales_orders(id) ON DELETE SET NULL
)";

// 3. Document Sequences (Consecutivos)
$sqlSeq = "CREATE TABLE document_sequences (
    id $autoIncrement,
    gym_id INT DEFAULT 0, -- 0 for SaaS Global, >0 for Gym Internal
    doc_type VARCHAR(20) NOT NULL, -- RC, CE, FCI, DS, NIC
    prefix VARCHAR(10) DEFAULT '',
    current_number INT NOT NULL DEFAULT 0,
    padding INT DEFAULT 6, -- 000001
    
    UNIQUE(gym_id, doc_type)
)";

// 4. Internal Accounting Notes (NIC)
$sqlNIC = "CREATE TABLE internal_notes (
    id $autoIncrement,
    gym_id INT DEFAULT 0, -- 0 for SaaS
    consecutive_number VARCHAR(20) NOT NULL,
    issue_date DATE NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    description TEXT NOT NULL,
    
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Execution Helper
function execSql($db, $name, $sql) {
    try {
        $db->exec($sql);
        echo "Created table: $name\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "Table $name already exists.\n";
        } else {
            echo "Error creating $name: " . $e->getMessage() . "\n";
        }
    }
}

execSql($db, 'sales_orders', $sqlOrders);
execSql($db, 'cash_receipts', $sqlRC);
execSql($db, 'document_sequences', $sqlSeq);
execSql($db, 'internal_notes', $sqlNIC);

// Seed Sequences for SaaS (Gym ID 0)
$sequences = ['RC' => 'RC-', 'CE' => 'CE-', 'FCI' => 'FCI-', 'DS' => 'DS-', 'NIC' => 'NIC-'];
foreach ($sequences as $type => $prefix) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM document_sequences WHERE gym_id = 0 AND doc_type = ?");
    $stmt->execute([$type]);
    if ($stmt->fetchColumn() == 0) {
        $stmt = $db->prepare("INSERT INTO document_sequences (gym_id, doc_type, prefix, current_number) VALUES (0, ?, ?, 0)");
        $stmt->execute([$type, $prefix]);
        echo "Seeded sequence for $type\n";
    }
}

echo "Migration Complete.\n";
