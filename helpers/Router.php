<?php

class Router {
    protected $routes = [];

    public function get($uri, $controller) {
        $this->routes['GET'][$uri] = $controller;
    }

    public function post($uri, $controller) {
        $this->routes['POST'][$uri] = $controller;
    }

    public function dispatch($uri, $method) {
        // Parse the URI path
        $path = parse_url($uri, PHP_URL_PATH);

        // Normalize the path by stripping the script execution path
        // This handles cases where the app is in a subdirectory or accessed via index.php
        $scriptName = $_SERVER['SCRIPT_NAME']; // e.g., /gym/index.php
        $scriptDir = dirname($scriptName);     // e.g., /gym

        // Normalize slashes for consistency (Windows supports backslashes)
        $scriptDir = str_replace('\\', '/', $scriptDir);

        // 1. If URI starts with script name (e.g. /gym/index.php/login), strip it
        if (strpos($path, $scriptName) === 0) {
            $path = substr($path, strlen($scriptName));
        }
        // 2. Else if URI starts with script directory (e.g. /gym/login), strip it
        // We ensure we don't strip "/" if scriptDir is just "/"
        elseif ($scriptDir !== '/' && strpos($path, $scriptDir) === 0) {
             $path = substr($path, strlen($scriptDir));
        }

        // Ensure path starts with /
        if ($path === '' || $path === false) {
            $path = '/';
        }
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

        if (array_key_exists($method, $this->routes) && array_key_exists($path, $this->routes[$method])) {
            $controllerAction = $this->routes[$method][$path];
            list($controller, $action) = explode('@', $controllerAction);
            
            // Autoloading handled in index.php, but we need to instantiate
            $controllerInstance = new $controller();
            $controllerInstance->$action();
        } else {
            http_response_code(404);
            require VIEW_PATH . '/404.php';
        }
    }
}
