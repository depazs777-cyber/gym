#!/usr/bin/env php
<?php
require_once __DIR__ . '/../config/constants.php';

// Script básico para crear respaldo de BD
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$name = getenv('DB_NAME') ?: 'fitmanager';

$backupDir = __DIR__ . '/../storage/backups/';
$fileName = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
$filePath = $backupDir . $fileName;

// Usando mysqldump (requiere que esté en el PATH del sistema)
$command = "mysqldump --host={$host} --user={$user} --password={$pass} {$name} > {$filePath}";
exec($command, $output, $returnVar);

if ($returnVar === 0) {
    echo "Backup creado exitosamente: {$fileName}\n";
    // Eliminar backups antiguos (más de 30 días)
    $files = glob($backupDir . '*.sql');
    $now = time();
    foreach ($files as $file) {
        if (is_file($file) && ($now - filemtime($file) >= 30 * 24 * 60 * 60)) {
            unlink($file);
        }
    }
} else {
    echo "Error creando el backup.\n";
}
