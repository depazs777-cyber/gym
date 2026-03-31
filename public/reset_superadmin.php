<?php
// reset_superadmin.php
// Ejecuta este archivo en tu navegador (ej: http://localhost/gym/public/reset_superadmin.php o http://localhost/gym/reset_superadmin.php)

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/config.php';

echo "<h2>Restableciendo Súper Administrador (Dueño del SaaS)</h2>";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $email = 'maestro@saas.com';
    $password = 'password123';
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Insertar o actualizar el usuario Super Admin (gym_id es NULL)
    // Buscamos si ya existe el usuario por email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user) {
        $update = $pdo->prepare("UPDATE users SET password_hash = :hash, role = 'super_admin', gym_id = NULL, status = 'active' WHERE id = :id");
        $update->execute([':hash' => $hash, ':id' => $user['id']]);
    } else {
        $insert = $pdo->prepare("
            INSERT INTO users (gym_id, name, email, password_hash, role, status)
            VALUES (NULL, 'Creador Maestro (SaaS)', :email, :hash, 'super_admin', 'active')
        ");
        $insert->execute([
            ':email' => $email,
            ':hash' => $hash
        ]);
    }

    echo "<div style='background: #e3f2fd; color: #1565c0; padding: 15px; border-radius: 5px; font-family: sans-serif; max-width: 450px; border: 1px solid #bbdefb;'>";
    echo "<h3>¡Súper Administrador Creado / Restablecido con Éxito!</h3>";
    echo "<p>Con este usuario tienes acceso total a todos los gimnasios (Tenants) y al CRM.</p>";
    echo "<ul>";
    echo "<li><b>Usuario/Email:</b> " . htmlspecialchars($email) . "</li>";
    echo "<li><b>Contraseña:</b> " . htmlspecialchars($password) . "</li>";
    echo "<li><b>Rol:</b> Súper Administrador Maestro</li>";
    echo "</ul>";
    echo "<a href='" . BASE_URL . "/login' style='display: inline-block; padding: 10px 15px; background: #2196f3; color: white; text-decoration: none; border-radius: 5px;'>Ir al Login</a>";
    echo "</div>";

    echo "<p style='color: red; font-family: sans-serif;'>⚠️ Por seguridad, <b>elimina este archivo (public/reset_superadmin.php)</b> después de ingresar al sistema.</p>";

} catch (PDOException $e) {
    echo "<div style='color: red; font-family: sans-serif;'>";
    echo "<h3>Error de Base de Datos</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Asegúrate de haber ejecutado <b>install.php</b> primero para crear las tablas.</p>";
    echo "</div>";
}
