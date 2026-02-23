<?php

// Configuración Global

// Base de Datos
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'gym_saas');
define('DB_USER', 'root');
define('DB_PASS', '');

// Rutas
define('ROOT_PATH', dirname(__DIR__)); // Raíz del proyecto
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('VIEW_PATH', APP_PATH . '/Views');

// URL Base (Ajustar según entorno)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('BASE_URL', $protocol . '://' . $host);

// Seguridad
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_LIFETIME', 3600 * 2); // 2 horas

// Configuración Regional
date_default_timezone_set('America/Bogota');
setlocale(LC_MONETARY, 'es_CO.UTF-8');
