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

        // Unified login - Check if it's the auth controller explicitly
        if (empty($url) || (isset($url[0]) && $url[0] === 'auth')) {
            $this->namespace = '';
            $this->currentController = 'AuthController';
            if (isset($url[0]) && $url[0] === 'auth') {
                unset($url[0]);
                $url = array_values($url);
            }
            $controllerPath = APP_ROOT . '/controllers/AuthController.php';
        } else {
            // Otherwise parse namespace from URL segment
            if (isset($url[0]) && ($url[0] === 'gym' || $url[0] === 'superadmin' || $url[0] === 'api')) {
                $this->namespace = $url[0];
                unset($url[0]);
                $url = array_values($url);
            } else {
                 // Default to login if no valid namespace is provided
                 Helpers::redirect('auth/login');
                 exit();
            }

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
        } else {
            // Fallback for PHP built-in server or missing htaccess
            $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $scriptName = dirname($_SERVER['SCRIPT_NAME']);

            // Remove the script directory from the request URI
            if ($scriptName !== '/' && $scriptName !== '\\') {
                if (strpos($requestUri, $scriptName) === 0) {
                    $requestUri = substr($requestUri, strlen($scriptName));
                }
            }

            $url = trim($requestUri, '/');
            if (!empty($url) && $url !== 'index.php') {
                $url = filter_var($url, FILTER_SANITIZE_URL);
                return explode('/', $url);
            }
        }
        return [];
    }
}
