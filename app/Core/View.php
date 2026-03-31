<?php

namespace App\Core;

class View {
    public static function render($view, $data = []) {
        extract($data);

        $viewPath = VIEW_PATH . '/' . $view . '.php';

        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            // Manejo de error si la vista no existe
            echo "Vista no encontrada: {$view}";
        }
    }
}
