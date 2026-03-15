#!/usr/bin/env php
<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';

try {
    $db = new Database();
    $hoy = date('Y-m-d H:i:s');

    $db->query("UPDATE access_tokens SET estado = 'expirado' WHERE expira_en < :hoy AND estado = 'activo'");
    $db->bind(':hoy', $hoy);
    $db->execute();

    $afectados = $db->rowCount();
    echo "Tokens expirados actualizados: $afectados\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
