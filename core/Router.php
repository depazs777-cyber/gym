<?php

class Router {
    protected $currentController = 'DashboardController';
    protected $currentMethod = 'index';
    protected $params = [];
    protected $routes = [];

    // Map subdomains to controller namespaces
    protected $namespace = '';

    public function __construct() {
        $url = $this->getUrl();

        // 1. Detect environment (subdomain vs localhost folders)
        $host = $_SERVER['HTTP_HOST'];
        $parts = explode('.', $host);

        // If testing locally, we can use the first URL segment as the namespace
        // e.g., localhost/fitmanager/gym/auth/login -> URL is gym/auth/login
        if (isset($url[0]) && ($url[0] === 'gym' || $url[0] === 'superadmin' || $url[0] === 'api')) {
            $this->namespace = $url[0];
            unset($url[0]);
            // Re-index array
            $url = array_values($url);
        } else {
            // Very basic subdomain detection logic
            if (isset($_GET['tenant'])) {
                $subdomain = $_GET['tenant'];
                if ($subdomain === 'superadmin') {
                    $this->namespace = 'superadmin';
                } else {
                    $this->namespace = 'gym';
                    // Tenant will be resolved in Tenant.php
                }
            } elseif (count($parts) >= 3 && $parts[0] !== 'www' && $parts[0] !== '127') {
                $subdomain = $parts[0];
                if ($subdomain === 'superadmin') {
                    $this->namespace = 'superadmin';
                } else {
                    $this->namespace = 'gym';
                }
            } else {
                 // Default to superadmin or main landing page if no subdomain
                 $this->namespace = 'superadmin';
                 if (empty($url)) {
                     $this->currentController = 'AuthController';
                 }
            }
        }

        // Check if controller exists in namespace
        $controllerName = 'DashboardController';
        if (isset($url[0])) {
             $controllerName = ucwords($url[0]) . 'Controller';
        }

        $controllerPath = APP_ROOT . '/controllers/' . $this->namespace . '/' . $controllerName . '.php';

        if (file_exists($controllerPath)) {
            $this->currentController = $controllerName;
            unset($url[0]);
        } elseif (file_exists(APP_ROOT . '/controllers/api/' . $controllerName . '.php') && $this->namespace === 'api') {
            $this->namespace = 'api';
            $this->currentController = $controllerName;
            $controllerPath = APP_ROOT . '/controllers/api/' . $controllerName . '.php';
            unset($url[0]);
        } else {
            // Default to AuthController if the root of a namespace is accessed and dashboard isn't found
            // or if the URL specifies an unknown controller
            if (!file_exists($controllerPath) && empty($url)) {
                $controllerName = 'AuthController';
                $controllerPath = APP_ROOT . '/controllers/' . $this->namespace . '/AuthController.php';
                if (file_exists($controllerPath)) {
                    $this->currentController = $controllerName;
                }
            }
        }

        // If it STILL doesn't exist, we fallback
        if (!file_exists($controllerPath)) {
             // Basic 404 handling
             die("Controller $controllerName no encontrado en el namespace $this->namespace. URL: " . implode('/', $url));
        }

        // Require the controller
        require_once $controllerPath;
        // Instantiate controller class
        $this->currentController = new $this->currentController;

        // Check for second part of url (method)
        if (isset($url[1])) {
            if (method_exists($this->currentController, $url[1])) {
                $this->currentMethod = $url[1];
                unset($url[1]);
            }
        }

        // Get parameters
        $this->params = $url ? array_values($url) : [];

        // Call a callback with array of params
        call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
    }

    public function getUrl() {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
        return [];
    }
}
