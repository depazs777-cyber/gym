<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "Starting Call Center Migration (SQLite Compatible)...\n";

$pdo = (new Database())->getConnection();

function createTable($pdo, $sql) {
    try {
        $pdo->exec($sql);
        echo "Table created/verified.\n";
    } catch (Exception $e) {
        echo "Error creating table: " . $e->getMessage() . "\n";
    }
}

try {
    // 1. Leads Table
    $sql = "CREATE TABLE IF NOT EXISTS leads (
        id INTEGER PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        phone VARCHAR(50) NOT NULL,
        email VARCHAR(255),
        customer_type VARCHAR(50) DEFAULT 'SMALL_GYM', -- SMALL_GYM, PREMIUM, etc.
        status VARCHAR(50) DEFAULT 'NEW', -- NEW, CONTACTED, INTERESTED, WON, LOST, DNC
        next_followup DATETIME NULL,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    createTable($pdo, $sql);

    // 2. Call Scripts
    $sql = "CREATE TABLE IF NOT EXISTS call_scripts (
        id INTEGER PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        customer_type VARCHAR(50) NOT NULL,
        objective VARCHAR(100) NOT NULL, -- Qualify, Close, Demo
        script_body TEXT NOT NULL,
        objections_json TEXT NULL, -- JSON string
        is_active INTEGER DEFAULT 1,
        created_by_user_id INTEGER,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    createTable($pdo, $sql);

    // 3. Call Logs
    $sql = "CREATE TABLE IF NOT EXISTS call_logs (
        id INTEGER PRIMARY KEY,
        lead_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        call_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        call_end TIMESTAMP NULL,
        duration_seconds INTEGER DEFAULT 0,
        outcome VARCHAR(50), -- ANSWERED, NO_ANSWER, BUSY, WRONG_NUMBER
        notes TEXT,
        script_id INTEGER NULL,
        FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (script_id) REFERENCES call_scripts(id) ON DELETE SET NULL
    )";
    createTable($pdo, $sql);

    // 4. Motivation Posts
    $sql = "CREATE TABLE IF NOT EXISTS motivation_posts (
        id INTEGER PRIMARY KEY,
        title VARCHAR(255),
        image_url VARCHAR(255),
        quote_text TEXT,
        show_date DATE NOT NULL,
        is_active INTEGER DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    createTable($pdo, $sql);

    echo "Call Center Migration Complete.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
