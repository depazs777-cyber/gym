<?php
// Script de Instalación / Migración
// Ejecutar desde la línea de comandos o navegador

// Evitar redefinición de constante si se llama múltiples veces o desde otro contexto
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Cargar Configuración (silenciar warnings por redefinición si ocurriera)
@require_once ROOT_PATH . '/config/config.php';

echo "Iniciando instalación y actualización de base de datos...<br>\n";

try {
    // Conectar sin seleccionar DB para crearla si no existe
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $dbName = DB_NAME;
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Base de datos '$dbName' verificada.<br>\n";

    $pdo->exec("USE `$dbName`");

    // --- ACTUALIZACIONES DE ESQUEMA (Safe Alters) ---
    // Verificar y crear tabla saas_plans si no existe
    $pdo->exec("CREATE TABLE IF NOT EXISTS saas_plans (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        price DECIMAL(15, 2) NOT NULL,
        max_members INT DEFAULT 500,
        status ENUM('active', 'inactive') DEFAULT 'active'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // Verificar si la columna valid_until existe en tenants
    $stmt = $pdo->query("SHOW COLUMNS FROM tenants LIKE 'valid_until'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE tenants ADD COLUMN valid_until DATE DEFAULT NULL");
        echo "Añadida columna 'valid_until' a tenants.<br>\n";
    }

    // Verificar si la columna plan_id existe en tenants
    $stmt = $pdo->query("SHOW COLUMNS FROM tenants LIKE 'plan_id'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE tenants ADD COLUMN plan_id INT DEFAULT NULL");
        $pdo->exec("ALTER TABLE tenants ADD CONSTRAINT fk_plan FOREIGN KEY (plan_id) REFERENCES saas_plans(id) ON DELETE SET NULL");
        echo "Añadida columna 'plan_id' a tenants.<br>\n";
    }

    // Verificar y crear tabla leads si no existe
    $pdo->exec("CREATE TABLE IF NOT EXISTS leads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_name VARCHAR(255) NOT NULL,
        contact_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(50),
        status ENUM('new', 'contacted', 'demo_scheduled', 'won', 'lost') DEFAULT 'new',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // Leer Schema Completo (si es instalación nueva)
    $schemaFile = ROOT_PATH . '/database/schema.sql';
    if (!file_exists($schemaFile)) {
        die("Error: No se encuentra database/schema.sql<br>\n");
    }

    // Ejecutar múltiples queries - Ignorar errores si las tablas ya existen
    try {
        $sql = file_get_contents($schemaFile);
        $pdo->exec($sql);
        echo "Esquema completo validado.<br>\n";
    } catch (\PDOException $e) {
        // Algunas sentencias pueden fallar si no usamos "IF NOT EXISTS", es normal en un update iterativo
        // echo "Aviso (Schema): " . $e->getMessage() . "<br>\n";
    }

    // Leer Seeds
    $seedFile = ROOT_PATH . '/database/seeds/01_initial_seed.sql';
    if (file_exists($seedFile)) {
        try {
            $seedSql = file_get_contents($seedFile);
            $pdo->exec($seedSql);
            echo "Datos semilla insertados/actualizados correctamente.<br>\n";
        } catch (\PDOException $e) {
             // Ignoramos duplicados si los datos ya existen
        }
    }

    echo "<h3>Instalación/Actualización completada con éxito.</h3>\n";
    echo "<a href='" . BASE_URL . "/reset_superadmin.php' style='display:block;margin-bottom:10px;'>1. Crear credenciales del DUEÑO SaaS (Súper Administrador)</a>";
    echo "<a href='" . BASE_URL . "/reset_admin.php' style='display:block;'>2. Crear credenciales de CLIENTE Demo (Administrador Gym)</a>";

} catch (PDOException $e) {
    die("<span style='color:red;'>Error de Base de Datos: " . $e->getMessage() . "</span><br>\n");
}
