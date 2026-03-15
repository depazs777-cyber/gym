<?php
// SCRIPT DE CREACIÓN DE ADMIN MANUAL (SOLO CLI)

if (php_sapi_name() !== 'cli') {
    die("Error: Este script solo puede ejecutarse desde la linea de comandos (CLI).\n");
}

require_once 'config/constants.php';
if (!file_exists('config/database.php')) {
    die("Error: El sistema no está instalado. Ejecuta install.php primero.\n");
}
require_once 'config/database.php';

$options = getopt("", ["name:", "email:", "username:", "password:", "interactive"]);

if (isset($options['interactive'])) {
    echo "=== CREACIÓN DE SUPER ADMIN ===\n";

    echo "Nombre completo: ";
    $name = trim(fgets(STDIN));

    echo "Email: ";
    $email = trim(fgets(STDIN));

    echo "Username: ";
    $username = trim(fgets(STDIN));

    echo "Contraseña: ";
    $password = trim(fgets(STDIN));
} else {
    $name = $options['name'] ?? null;
    $email = $options['email'] ?? null;
    $username = $options['username'] ?? null;
    $password = $options['password'] ?? null;
}

if (!$name || !$email || !$username || !$password) {
    die("Error: Faltan parametros.\nUso: php create-admin.php --name=\"Nombre\" --email=\"email@ejemplo.com\" --username=\"admin\" --password=\"pass\"\nO usa el modo interactivo: php create-admin.php --interactive\n");
}

try {
    $db = new Database();

    // Verificar si ya existe
    $db->query("SELECT id FROM users WHERE email = :email OR username = :username");
    $db->bind(':email', $email);
    $db->bind(':username', $username);
    $db->execute();

    if ($db->rowCount() > 0) {
        die("Error: Ya existe un usuario con ese email o username.\n");
    }

    // Obtener ID del rol Super Admin
    $db->query("SELECT id FROM roles WHERE nombre = 'Super Admin' LIMIT 1");
    $rol = $db->single();
    if (!$rol) {
        die("Error: No se encontró el rol 'Super Admin' en la base de datos. Ejecutaste seeds.sql?\n");
    }

    // Insertar usuario
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $db->query("INSERT INTO users (rol_id, nombre_completo, email, username, password, estado) VALUES (:rol_id, :nombre, :email, :username, :password, 'activo')");
    $db->bind(':rol_id', $rol->id);
    $db->bind(':nombre', $name);
    $db->bind(':email', $email);
    $db->bind(':username', $username);
    $db->bind(':password', $hashed_password);

    if ($db->execute()) {
        echo "¡Super Admin creado exitosamente!\n";
    } else {
        echo "Error al crear el usuario en la base de datos.\n";
    }

} catch (Exception $e) {
    die("Error de base de datos: " . $e->getMessage() . "\n");
}
