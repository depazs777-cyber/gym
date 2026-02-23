<?php
// Script de Instalación / Migración
// Ejecutar desde la línea de comandos o navegador

// Evitar redefinición de constante si se llama múltiples veces o desde otro contexto
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

// Cargar Configuración (silenciar warnings por redefinición si ocurriera)
@require_once ROOT_PATH . '/config/config.php';

echo "Iniciando instalación de base de datos...\n";

try {
    // Conectar sin seleccionar DB para crearla si no existe
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $dbName = DB_NAME;
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Base de datos '$dbName' verificada.\n";

    $pdo->exec("USE `$dbName`");

    // Leer Schema
    $schemaFile = ROOT_PATH . '/database/schema.sql';
    if (!file_exists($schemaFile)) {
        die("Error: No se encuentra database/schema.sql\n");
    }

    $sql = file_get_contents($schemaFile);

    // Ejecutar múltiples queries
    $pdo->exec($sql);
    echo "Esquema importado correctamente.\n";

    // Leer Seeds
    $seedFile = ROOT_PATH . '/database/seeds/01_initial_seed.sql';
    if (file_exists($seedFile)) {
        $seedSql = file_get_contents($seedFile);
        $pdo->exec($seedSql);
        echo "Datos semilla importados correctamente.\n";
    }

    echo "Instalación completada con éxito.\n";

} catch (PDOException $e) {
    die("Error de Base de Datos: " . $e->getMessage() . "\n");
}
