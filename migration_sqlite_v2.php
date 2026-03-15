<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "Starting Migration V2 (SQLite Compatible)...\n";

$pdo = Database::getInstance()->getConnection();

// Check Driver
$driver = getenv('DB_DRIVER') ?: 'mysql';
if ($driver !== 'sqlite') {
    die("Skipping SQLite-specific migration on $driver.\n");
}

// Helpers
function addColumn($pdo, $table, $colDef) {
    try {
        $pdo->exec("ALTER TABLE $table ADD COLUMN $colDef");
        echo "Added column to $table: $colDef\n";
    } catch (Exception $e) {
        // Likely exists
        // echo "Column exists or error: " . $e->getMessage() . "\n";
    }
}

try {
    // 1. Gyms
    addColumn($pdo, 'gyms', 'config_annual_days INTEGER DEFAULT 360');
    addColumn($pdo, 'gyms', 'config_deduct_session INTEGER DEFAULT 1');
    addColumn($pdo, 'gyms', 'config_renewal_mode TEXT DEFAULT \'CONTINUE\'');

    // 2. Memberships
    addColumn($pdo, 'memberships', 'purchase_mode TEXT DEFAULT \'PERIODIC\'');
    addColumn($pdo, 'memberships', 'multiplier INTEGER DEFAULT 1');

    // 3. Payments
    addColumn($pdo, 'payments', 'discount DECIMAL(10, 2) DEFAULT 0.00');
    addColumn($pdo, 'payments', 'notes TEXT DEFAULT NULL');

    // 4. Create Receipts
    $sql = "CREATE TABLE IF NOT EXISTS receipts (
        id INTEGER PRIMARY KEY,
        gym_id INTEGER NOT NULL,
        payment_id INTEGER NOT NULL,
        receipt_number VARCHAR(50) NOT NULL,
        snapshot_json TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE,
        FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Receipts table created.\n";

    // 5. Create Client Tokens
    $sql = "CREATE TABLE IF NOT EXISTS client_tokens (
        id INTEGER PRIMARY KEY,
        gym_id INTEGER NOT NULL,
        client_id INTEGER NOT NULL,
        token VARCHAR(255) NOT NULL,
        status TEXT DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE,
        FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    // Unique index separately for safety
    try { $pdo->exec("CREATE UNIQUE INDEX idx_token ON client_tokens(token)"); } catch(Exception $e) {}
    echo "Client Tokens table created.\n";

    // 6. Notifications
    $sql = "CREATE TABLE IF NOT EXISTS notifications (
        id INTEGER PRIMARY KEY,
        gym_id INTEGER NOT NULL,
        user_id INTEGER DEFAULT NULL,
        type VARCHAR(50) NOT NULL,
        message TEXT NOT NULL,
        is_read INTEGER DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Notifications table created.\n";

    echo "Migration V2 Complete.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
