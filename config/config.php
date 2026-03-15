<?php

// Determine Base URL dynamically
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) : '';
// Normalize scriptDir to remove trailing slash unless it's just "/"
$scriptDir = rtrim(str_replace('\\', '/', $scriptDir), '/');

if (!defined('APP_NAME')) define('APP_NAME', 'PROMPT MAESTRO');
if (!defined('BASE_URL')) define('BASE_URL', $protocol . '://' . $host . $scriptDir);
if (!defined('BASE_PATH')) define('BASE_PATH', $scriptDir); // Relative path e.g. /gym

if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'gym_saas');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: '');

// Path constants
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__));
if (!defined('VIEW_PATH')) define('VIEW_PATH', ROOT_PATH . '/views');
if (!defined('CONTROLLER_PATH')) define('CONTROLLER_PATH', ROOT_PATH . '/controllers');
if (!defined('MODEL_PATH')) define('MODEL_PATH', ROOT_PATH . '/models');

// Security
if (!defined('CSRF_TOKEN_NAME')) define('CSRF_TOKEN_NAME', 'csrf_token');

// Helper function for URLs
function url($path = '') {
    if ($path === '' || $path === '/') {
        return BASE_PATH . '/';
    }
    if ($path[0] !== '/') {
        $path = '/' . $path;
    }
    return BASE_PATH . $path;
}
