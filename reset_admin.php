<?php
// reset_admin.php
// Ejecuta este archivo en tu navegador (ej: http://localhost/gym/reset_admin.php)

define('ROOT_PATH', __DIR__);
require_once ROOT_PATH . '/config/config.php';

echo "<h2>Restableciendo Usuario Administrador</h2>";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $email = 'admin@demo.com';
    $password = 'password123';
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Asegurarnos de que el Tenant 1 exista
    $pdo->exec("INSERT IGNORE INTO tenants (id, name, domain, status) VALUES (1, 'Gimnasio Demo', 'demo.gym.com', 'active')");

    // Insertar o actualizar el usuario
    $stmt = $pdo->prepare("
        INSERT INTO users (id, gym_id, name, email, password_hash, role, status)
        VALUES (1, 1, 'Admin Demo', :email, :hash, 'gym_admin', 'active')
        ON DUPLICATE KEY UPDATE password_hash = :hash_update, status = 'active'
    ");

    $stmt->execute([
        ':email' => $email,
        ':hash' => $hash,
        ':hash_update' => $hash
    ]);

    echo "<div style='background: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 5px; font-family: sans-serif; max-width: 400px; border: 1px solid #c8e6c9;'>";
    echo "<h3>¡Usuario Creado / Restablecido con Éxito!</h3>";
    echo "<p>Usa las siguientes credenciales para ingresar:</p>";
    echo "<ul>";
    echo "<li><b>Usuario/Email:</b> " . htmlspecialchars($email) . "</li>";
    echo "<li><b>Contraseña:</b> " . htmlspecialchars($password) . "</li>";
    echo "</ul>";
    echo "<a href='" . BASE_URL . "/login' style='display: inline-block; padding: 10px 15px; background: #4caf50; color: white; text-decoration: none; border-radius: 5px;'>Ir al Login</a>";
    echo "</div>";

    echo "<p style='color: red; font-family: sans-serif;'>⚠️ Por seguridad, <b>elimina este archivo (reset_admin.php)</b> después de ingresar al sistema.</p>";

} catch (PDOException $e) {
    echo "<div style='color: red; font-family: sans-serif;'>";
    echo "<h3>Error de Base de Datos</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Asegúrate de haber ejecutado <b>install.php</b> primero para crear las tablas.</p>";
    echo "</div>";
}
