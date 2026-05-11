<?php

// Load Config
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "Resetting Super Admin password...\n";

$email = 'admin@promptmaestro.com';
$password = 'admin';
$hash = password_hash($password, PASSWORD_DEFAULT);

$pdo = new Database()->getConnection();

// Check if user exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    // Update
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
    $stmt->execute([$hash, $email]);
    echo "Password updated for $email. New password is: $password\n";
} else {
    // Insert if somehow missing
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, status) VALUES (?, ?, ?, 'SUPER_ADMIN', 'active')");
    $stmt->execute(['Super Admin', $email, $hash]);
    echo "User created: $email / $password\n";
}
