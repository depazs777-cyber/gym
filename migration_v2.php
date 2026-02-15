<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "Starting Migration V2...\n";

$pdo = Database::getInstance()->getConnection();

try {
    // 1. Update Gyms Table (Config)
    echo "Updating Gyms table...\n";
    $sql = "ALTER TABLE gyms 
            ADD COLUMN config_annual_days INT DEFAULT 360,
            ADD COLUMN config_deduct_session TINYINT(1) DEFAULT 1,
            ADD COLUMN config_renewal_mode ENUM('CONTINUE', 'TODAY') DEFAULT 'CONTINUE'";
    try { $pdo->exec($sql); } catch(Exception $e) { echo "Gyms update skipped (maybe exists)\n"; }

    // 2. Update Memberships Table
    echo "Updating Memberships table...\n";
    $sql = "ALTER TABLE memberships 
            ADD COLUMN purchase_mode ENUM('PERIODIC', 'ANNUAL') DEFAULT 'PERIODIC',
            ADD COLUMN multiplier INT DEFAULT 1";
    try { $pdo->exec($sql); } catch(Exception $e) { echo "Memberships update skipped\n"; }

    // 3. Update Payments Table
    echo "Updating Payments table...\n";
    $sql = "ALTER TABLE payments 
            ADD COLUMN discount DECIMAL(10, 2) DEFAULT 0.00,
            ADD COLUMN notes TEXT DEFAULT NULL";
    try { $pdo->exec($sql); } catch(Exception $e) { echo "Payments update skipped\n"; }

    // 4. Create Receipts Table
    echo "Creating Receipts table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS receipts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        gym_id INT NOT NULL,
        payment_id INT NOT NULL,
        receipt_number VARCHAR(50) NOT NULL,
        snapshot_json JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE,
        FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE
    ) ENGINE=InnoDB";
    $pdo->exec($sql);

    // 5. Create Client Tokens (QR) Table
    echo "Creating Client Tokens table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS client_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        gym_id INT NOT NULL,
        client_id INT NOT NULL,
        token VARCHAR(255) NOT NULL,
        status ENUM('active', 'revoked') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE,
        FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
        UNIQUE KEY unique_token (token)
    ) ENGINE=InnoDB";
    $pdo->exec($sql);

    // 6. Create Notifications Table
    echo "Creating Notifications table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        gym_id INT NOT NULL,
        user_id INT DEFAULT NULL COMMENT 'NULL for all gym staff or specific user',
        type VARCHAR(50) NOT NULL,
        message TEXT NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE
    ) ENGINE=InnoDB";
    $pdo->exec($sql);

    echo "Migration V2 Complete.\n";

} catch (PDOException $e) {
    echo "Error during migration: " . $e->getMessage() . "\n";
}
