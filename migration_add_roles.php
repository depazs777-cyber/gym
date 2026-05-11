<?php
// Load Config
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "Migrating Roles...\n";

$pdo = new Database()->getConnection();

try {
    // Add CALL_CENTER and DEV to the ENUM
    $sql = "ALTER TABLE users MODIFY COLUMN role ENUM(
        'SUPER_ADMIN', 'VENDEDOR', 'MARKETING', 'CALL_CENTER', 'FINANZAS', 'SOPORTE', 'DEV', 'SEGURIDAD', 
        'ADMIN_GYM', 'RECEPCION', 'ENTRENADOR', 'CONSULTA_REPORTES'
    ) NOT NULL";
    
    $pdo->exec($sql);
    echo "Roles updated successfully.\n";
} catch (PDOException $e) {
    echo "Error updating roles: " . $e->getMessage() . "\n";
}
