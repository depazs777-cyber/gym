<?php

// Paths
define('APP_ROOT', dirname(__DIR__));

// Dynamic URL_ROOT detection
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Script directory name
// Get the path of the script relative to document root
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$scriptDir = dirname($scriptName);

// Clean up slashes
$scriptDir = str_replace('\\', '/', $scriptDir);
if ($scriptDir === '/' || $scriptDir === '\\') {
    $scriptDir = '';
}

define('URL_ROOT', $protocol . '://' . $host . $scriptDir);

define('SITE_NAME', 'FitManager');
define('APP_VERSION', '1.0.0');

// Environment
define('ENVIRONMENT', 'development'); // development or production

if (ENVIRONMENT == 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
