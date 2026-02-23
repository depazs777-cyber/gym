<?php

namespace App\Core;

class Router {
    protected $routes = [];
    protected $middlewares = [];
    protected $groupStack = [];

    public function get($uri, $action) {
        $this->addRoute('GET', $uri, $action);
    }

    public function post($uri, $action) {
        $this->addRoute('POST', $uri, $action);
    }

    // Soporte para Middleware Groups
    public function group($attributes, $callback) {
        $this->groupStack[] = $attributes;
        call_user_func($callback, $this);
        array_pop($this->groupStack);
    }

    protected function addRoute($method, $uri, $action) {
        // Combinar prefijo si existe en el grupo
        if (!empty($this->groupStack)) {
            $lastGroup = end($this->groupStack);
            if (isset($lastGroup['prefix'])) {
                $uri = rtrim($lastGroup['prefix'], '/') . '/' . ltrim($uri, '/');
            }
        }

        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'action' => $action,
            'middleware' => $this->getGroupMiddleware()
        ];
    }

    protected function getGroupMiddleware() {
        $middlewares = [];
        foreach ($this->groupStack as $group) {
            if (isset($group['middleware'])) {
                $middlewares = array_merge($middlewares, (array) $group['middleware']);
            }
        }
        return $middlewares;
    }

    public function dispatch() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        // Manejo básico de subdirectorios si el script no está en la raíz
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        // Normalize slashes
        $scriptName = str_replace('\\', '/', $scriptName);

        if ($scriptName !== '/' && strpos($uri, $scriptName) === 0) {
            $uri = substr($uri, strlen($scriptName));
        }
        $uri = '/' . trim($uri, '/'); // Normalizar

        foreach ($this->routes as $route) {
            // Soporte básico para parámetros {id}
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route['uri']);
            $pattern = "#^" . $pattern . "$#";

            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {

                // Ejecutar Middleware
                foreach ($route['middleware'] as $middleware) {
                    $instance = new $middleware();
                    if (!$instance->handle()) {
                        return; // Middleware detuvo la ejecución (e.g. redirect)
                    }
                }

                // Limpiar matches numéricos
                foreach ($matches as $key => $value) {
                    if (is_int($key)) unset($matches[$key]);
                }

                $action = $route['action'];

                if (is_callable($action)) {
                    call_user_func_array($action, $matches);
                } elseif (is_string($action)) {
                    [$controller, $methodName] = explode('@', $action);
                    $controller = "App\\Controllers\\{$controller}";
                    if (class_exists($controller)) {
                        $controllerInstance = new $controller();
                        if (method_exists($controllerInstance, $methodName)) {
                            call_user_func_array([$controllerInstance, $methodName], $matches);
                        } else {
                            echo "Método {$methodName} no encontrado en {$controller}";
                        }
                    } else {
                        echo "Controlador {$controller} no encontrado";
                    }
                }
                return;
            }
        }

        http_response_code(404);
        require VIEW_PATH . '/404.php';
    }
}
