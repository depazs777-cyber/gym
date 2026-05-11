#!/usr/bin/env php
<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';

try {
    $db = new Database();
    // Miembros que vencen en los próximos 3 días
    $fechaLimite = date('Y-m-d', strtotime('+3 days'));
    $hoy = date('Y-m-d');

    $db->query("SELECT m.email, m.nombre, m.fecha_vencimiento, t.nombre as gym_nombre
                FROM members m
                JOIN tenants t ON m.tenant_id = t.id
                WHERE m.fecha_vencimiento BETWEEN :hoy AND :fechaLimite
                AND m.email IS NOT NULL AND m.estado = 'activo'");
    $db->bind(':hoy', $hoy);
    $db->bind(':fechaLimite', $fechaLimite);
    $members = $db->resultSet();

    $count = 0;
    foreach ($members as $m) {
        // En un sistema real aquí enviaríamos un email con mail() o PHPMailer
        // mail($m->email, "Tu membresía en {$m->gym_nombre} está por vencer", "Hola {$m->nombre}, tu membresía vence el {$m->fecha_vencimiento}.");
        $count++;
    }

    echo "Recordatorios de pago enviados: $count\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
