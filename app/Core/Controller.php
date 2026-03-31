<?php

namespace App\Core;

class Controller {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    protected function view($view, $data = []) {
        View::render($view, $data);
    }

    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function redirect($url) {
        header("Location: " . $url);
        exit;
    }

    protected function validate($data, $rules) {
        // Implementación simple de validación
        $errors = [];
        foreach ($rules as $field => $ruleString) {
            $rulesArray = explode('|', $ruleString);
            foreach ($rulesArray as $rule) {
                if ($rule === 'required' && empty($data[$field])) {
                    $errors[$field][] = "El campo {$field} es obligatorio.";
                }
                if ($rule === 'numeric' && !is_numeric($data[$field])) {
                    $errors[$field][] = "El campo {$field} debe ser numérico.";
                }
                // Más reglas...
            }
        }
        return $errors;
    }
}
