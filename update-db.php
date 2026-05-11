<?php
/**
 * Script de actualización de Base de Datos
 * Ejecutar este archivo desde el navegador para aplicar cambios recientes en el esquema.
 */
require_once 'config/constants.php';
require_once 'config/session.php';

Session::start();
require_once 'core/Auth.php';

// Verificación de seguridad básica (solo accesible si tienes sesión abierta como superadmin,
// o si estás ejecutando en CLI para que sea más seguro).
if (php_sapi_name() !== 'cli') {
    if (!Auth::check() || Auth::user()->role_id != 1) {
        die("Acceso denegado. Debes estar logueado como Super Admin o ejecutar el script desde la terminal (CLI).");
    }
}

if (!file_exists('config/database.php')) {
    die("La base de datos no está configurada.");
}
require_once 'config/database.php';

try {
    $db = new Database();

    // 1. Eliminar columna subdominio si existe
    echo "Comprobando columna 'subdominio'...<br>";
    $db->query("SHOW COLUMNS FROM tenants LIKE 'subdominio'");
    $db->execute();
    if ($db->rowCount() > 0) {
        $db->query("ALTER TABLE tenants DROP INDEX subdominio");
        $db->execute();
        $db->query("ALTER TABLE tenants DROP COLUMN subdominio");
        $db->execute();
        echo "- Columna 'subdominio' e índice eliminados exitosamente.<br>";
    } else {
        echo "- La columna 'subdominio' ya fue eliminada.<br>";
    }

    // 2. Añadir columna precio_personalizado si no existe
    echo "Comprobando columna 'precio_personalizado'...<br>";
    $db->query("SHOW COLUMNS FROM tenants LIKE 'precio_personalizado'");
    $db->execute();
    if ($db->rowCount() == 0) {
        $db->query("ALTER TABLE tenants ADD COLUMN precio_personalizado decimal(10,2) DEFAULT NULL AFTER plan_id");
        $db->execute();
        echo "- Columna 'precio_personalizado' añadida exitosamente.<br>";
    } else {
        echo "- La columna 'precio_personalizado' ya existe.<br>";
    }

    echo "<br><strong>¡Actualización de base de datos completada con éxito!</strong><br>";
    echo "Por seguridad, elimina o bloquea el archivo <code>update-db.php</code>.";

} catch (Exception $e) {
    echo "Error durante la actualización: " . $e->getMessage();
}
