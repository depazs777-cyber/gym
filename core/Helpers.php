<?php

class Helpers {
    /**
     * Redirect to page
     */
    public static function redirect($page) {
        header('Location: ' . URL_ROOT . '/' . ltrim($page, '/'));
        exit();
    }

    /**
     * Sanitize input strings for XSS protection
     */
    public static function sanitize($input) {
        if (is_array($input)) {
            $sanitized = [];
            foreach ($input as $key => $value) {
                $sanitized[$key] = self::sanitize($value);
            }
            return $sanitized;
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Generate flash messages
     */
    public static function flash($name = '', $message = '', $class = 'alert alert-success') {
        if (!empty($name)) {
            if (!empty($message) && empty($_SESSION[$name])) {
                if (!empty($_SESSION[$name])) {
                    unset($_SESSION[$name]);
                }
                if (!empty($_SESSION[$name . '_class'])) {
                    unset($_SESSION[$name . '_class']);
                }
                $_SESSION[$name] = $message;
                $_SESSION[$name . '_class'] = $class;
            } elseif (empty($message) && !empty($_SESSION[$name])) {
                $class = !empty($_SESSION[$name . '_class']) ? $_SESSION[$name . '_class'] : '';
                echo '<div class="' . $class . '" id="msg-flash">' . $_SESSION[$name] . '</div>';
                unset($_SESSION[$name]);
                unset($_SESSION[$name . '_class']);
            }
        }
    }
}
