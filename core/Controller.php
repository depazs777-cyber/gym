<?php

class Controller {
    // Load model
    public function model($model) {
        require_once APP_ROOT . '/models/' . $model . '.php';
        return new $model();
    }

    // Load view
    public function view($view, $data = []) {
        // Render view component
        if (file_exists(APP_ROOT . '/views/' . $view . '.php')) {
            // Extract data to make keys available as variables
            extract($data);
            require_once APP_ROOT . '/views/' . $view . '.php';
        } else {
            die('View does not exist');
        }
    }
}
