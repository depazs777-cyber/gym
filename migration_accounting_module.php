<?php
// Migration for Accounting Module: Third Parties, Withholding Rules, Purchases, Expenses
// Compatible with MySQL (XAMPP) and SQLite (Dev)

define('APP_NAME', 'PROMPT_MAESTRO');
define('ROOT_PATH', __DIR__);
require_once __DIR__ . '/config/database.php';

$db = (new Database())->getConnection();
$driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

echo "Starting Accounting Module Migration (Driver: $driver)...\n";

$autoIncrement = ($driver === 'sqlite') ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';

// 1. Third Parties Table
$sqlThirdParties = "CREATE TABLE third_parties (
    id $autoIncrement,
    gym_id INT NOT NULL,
    third_type VARCHAR(20) NOT NULL, -- PROVEEDOR, CLIENTE, MIXTO
    person_type VARCHAR(20) NOT NULL, -- NATURAL, JURIDICA
    has_economic_activity TINYINT(1) DEFAULT 1,
    document_type VARCHAR(20) DEFAULT 'NIT',
    document_number VARCHAR(50) NOT NULL,
    full_name_or_company VARCHAR(255) NOT NULL,
    trade_name VARCHAR(255) NULL,
    
    -- RUT Fields
    rut_required TINYINT(1) DEFAULT 1,
    rut_activity_code VARCHAR(20) NULL,
    rut_regime VARCHAR(50) NULL,
    vat_responsible VARCHAR(10) DEFAULT 'NO', -- YES, NO
    ica_responsible VARCHAR(10) DEFAULT 'NO', -- YES, NO
    reteiva_applicable VARCHAR(10) DEFAULT 'UNKNOWN',
    reteica_applicable VARCHAR(10) DEFAULT 'UNKNOWN',
    
    -- Contact
    email VARCHAR(255) NULL,
    phone VARCHAR(50) NULL,
    address TEXT NULL,
    city VARCHAR(100) NULL,
    department VARCHAR(100) NULL,
    
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE
)";

// 2. Withholding Rules (Global or Gym? Let's add gym_id nullable for overrides)
$sqlRules = "CREATE TABLE withholding_rules (
    id $autoIncrement,
    gym_id INT DEFAULT NULL, -- NULL = Global Rule
    tax_type VARCHAR(20) NOT NULL, -- RETEIVA, RETEICA
    applies_to VARCHAR(20) DEFAULT 'BOTH', -- FCI, DS, BOTH
    third_party_type VARCHAR(20) DEFAULT 'ANY', -- JURIDICA, NATURAL, NO_ECONOMIC, ANY
    responsible_vat VARCHAR(10) DEFAULT 'ANY', -- YES, NO, ANY
    economic_activity_required TINYINT(1) DEFAULT 0,
    min_base_amount DECIMAL(15, 2) DEFAULT 0.00,
    rate DECIMAL(10, 4) NOT NULL, -- 0.015 or 15.00
    rate_unit VARCHAR(20) DEFAULT 'PERCENT', -- PERCENT, PER_MILLE
    base_field VARCHAR(20) DEFAULT 'SUBTOTAL', -- SUBTOTAL, IVA, TOTAL
    is_active TINYINT(1) DEFAULT 1,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE
)";

// 3. Purchases (FCI / DS)
$sqlPurchases = "CREATE TABLE purchases (
    id $autoIncrement,
    gym_id INT NOT NULL,
    third_party_id INT NOT NULL,
    doc_type VARCHAR(20) NOT NULL, -- FCI (Factura Compra), DS (Doc Soporte)
    doc_number VARCHAR(50) NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NULL,
    
    -- Amounts
    subtotal DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    iva_rate DECIMAL(5, 2) DEFAULT 0.00,
    iva_value DECIMAL(15, 2) DEFAULT 0.00,
    other_taxes DECIMAL(15, 2) DEFAULT 0.00,
    total_gross DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    
    -- Withholdings (Calculated)
    reteiva_base DECIMAL(15, 2) DEFAULT 0.00,
    reteiva_rate DECIMAL(10, 4) DEFAULT 0.00,
    reteiva_value DECIMAL(15, 2) DEFAULT 0.00,
    
    reteica_base DECIMAL(15, 2) DEFAULT 0.00,
    reteica_rate DECIMAL(10, 4) DEFAULT 0.00,
    reteica_value DECIMAL(15, 2) DEFAULT 0.00,
    
    other_retentions DECIMAL(15, 2) DEFAULT 0.00,
    
    total_payable DECIMAL(15, 2) NOT NULL DEFAULT 0.00, -- Net to Pay
    
    snapshot_json TEXT NULL, -- Full tax calculation logic snapshot
    
    status VARCHAR(20) DEFAULT 'PENDING', -- PENDING, PAID, PARTIAL, CANCELLED
    created_by_user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE,
    FOREIGN KEY (third_party_id) REFERENCES third_parties(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE RESTRICT
)";

// 4. Expense Vouchers (Comprobante Egreso)
$sqlExpenses = "CREATE TABLE expense_vouchers (
    id $autoIncrement,
    gym_id INT NOT NULL,
    purchase_id INT DEFAULT NULL, -- Optional link to purchase
    third_party_id INT NULL, -- If direct expense without purchase (optional, but better linked)
    
    consecutive_number INT NOT NULL,
    payment_date DATE NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'CASH',
    notes TEXT NULL,
    
    created_by_user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE,
    FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE SET NULL,
    FOREIGN KEY (third_party_id) REFERENCES third_parties(id) ON DELETE SET NULL
)";

// Execution Helper
function execSql($db, $name, $sql) {
    try {
        // Drop if exists for clean state during dev (Optional, maybe specific cleanup script better?)
        // $db->exec("DROP TABLE IF EXISTS $name");
        
        $db->exec($sql);
        echo "Created table: $name\n";
    } catch (PDOException $e) {
        // Check if table already exists error
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "Table $name already exists.\n";
        } else {
            echo "Error creating $name: " . $e->getMessage() . "\n";
        }
    }
}

execSql($db, 'third_parties', $sqlThirdParties);
execSql($db, 'withholding_rules', $sqlRules);
execSql($db, 'purchases', $sqlPurchases);
execSql($db, 'expense_vouchers', $sqlExpenses);

// Seed Default Withholding Rules (Colombia Basics)
// ReteIVA 15% (of the IVA) usually applies to Simplify Regime? No, depends.
// Let's add basic rules as examples.
$check = $db->query("SELECT COUNT(*) FROM withholding_rules");
if ($check && $check->fetchColumn() == 0) {
    echo "Seeding default withholding rules...\n";
    // ReteIVA 15%
    $db->exec("INSERT INTO withholding_rules (tax_type, applies_to, base_field, rate, rate_unit, description) 
               VALUES ('RETEIVA', 'BOTH', 'IVA', 15.00, 'PERCENT', 'ReteIVA General 15%')");
    
    // ReteICA example (Bogota 9.66/1000 Service)
    $db->exec("INSERT INTO withholding_rules (tax_type, applies_to, base_field, rate, rate_unit, description) 
               VALUES ('RETEICA', 'BOTH', 'SUBTOTAL', 9.66, 'PER_MILLE', 'ReteICA Bogota Servicios 9.66')");
    
    // ReteFuente (Honorarios 10%) - Wait, prompt asked for ReteIVA / ReteICA specifically but "Dejar estructura lista".
    // I'll add ReteFuente as generic tax_type if I had column, but prompt defined enum RETEIVA, RETEICA.
    // I should stick to prompt enums but table definition `VARCHAR(20)` allows expansion.
}

echo "Migration Complete.\n";
