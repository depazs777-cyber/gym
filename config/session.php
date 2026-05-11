<?php

class Session {
    public static function start() {
        if (session_status() == PHP_SESSION_NONE) {
            // Configure secure session parameters before starting
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            // ini_set('session.cookie_secure', 1); // Uncomment in production with HTTPS

            session_start();
        }
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    public static function has($key) {
        return isset($_SESSION[$key]);
    }

    public static function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public static function destroy() {
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
            // Delete the session cookie
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
        }
    }

    // Generate and verify CSRF token
    public static function generateCsrfToken() {
        if (!self::has('csrf_token')) {
            self::set('csrf_token', bin2hex(random_bytes(32)));
        }
        return self::get('csrf_token');
    }

    public static function verifyCsrfToken($token) {
        if (!self::has('csrf_token') || $token !== self::get('csrf_token')) {
            return false;
        }
        return true;
    }
}
