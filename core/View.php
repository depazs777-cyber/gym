<?php
// Simple view renderer helper class
class View {
    public static function render($view, $data = [], $layout = null) {
        extract($data);

        ob_start();
        require_once APP_ROOT . '/views/' . $view . '.php';
        $content = ob_get_clean();

        if ($layout) {
            require_once APP_ROOT . '/views/layouts/' . $layout . '.php';
        } else {
            echo $content;
        }
    }
}
