<?php

// Paths
define('APP_ROOT', dirname(__DIR__));
define('URL_ROOT', 'http://localhost'); // Adjust in production
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
