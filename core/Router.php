<?php

class Router {
    protected $currentController = 'DashboardController';
    protected $currentMethod = 'index';
    protected $params = [];
    protected $routes = [];

    // Map subdomains to controller namespaces
    protected $namespace = '';

    public function __construct() {
        // Detect subdomain
        $host = $_SERVER['HTTP_HOST'];
        $parts = explode('.', $host);

        // Very basic subdomain detection logic
        // Assuming fitmanager.com or localhost
        // For localhost testing, we might pass ?tenant=name
        if (isset($_GET['tenant'])) {
            $subdomain = $_GET['tenant'];
            if ($subdomain === 'superadmin') {
                $this->namespace = 'superadmin';
            } else {
                $this->namespace = 'gym';
                // Tenant will be resolved in Tenant.php
            }
        } elseif (count($parts) >= 3 && $parts[0] !== 'www') {
            $subdomain = $parts[0];
            if ($subdomain === 'superadmin') {
                $this->namespace = 'superadmin';
            } else {
                $this->namespace = 'gym';
            }
        } else {
             // Default to superadmin or main landing page if no subdomain
             // For this project, let's say root goes to superadmin login
             $this->namespace = 'superadmin';
             $this->currentController = 'AuthController';
        }

        $url = $this->getUrl();

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
            // Future API routing implementation
            $this->namespace = 'api';
            $this->currentController = $controllerName;
            $controllerPath = APP_ROOT . '/controllers/api/' . $controllerName . '.php';
            unset($url[0]);
        } else {
            // Controller not found, redirect to 404 or default
            // In a real app, handle 404 properly
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
