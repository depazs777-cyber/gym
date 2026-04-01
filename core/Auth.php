<?php

class Auth {
    public static function check() {
        return Session::has('user_id');
    }

    public static function user() {
        if (self::check()) {
            return (object) [
                'id' => Session::get('user_id'),
                'username' => Session::get('username'),
                'email' => Session::get('email'),
                'role_id' => Session::get('role_id'),
                'tenant_id' => Session::get('tenant_id')
            ];
        }
        return null;
    }

    public static function tenant() {
        if (self::check() && Session::has('tenant_id')) {
            // Can be enriched with a DB query if needed
            return Session::get('tenant_id');
        }
        return null;
    }

    public static function login($user) {
        Session::start(); // Ensure session is started
        Session::set('user_id', $user->id);
        Session::set('username', $user->username);
        Session::set('email', $user->email);
        Session::set('role_id', isset($user->rol_id) ? $user->rol_id : (isset($user->role_id) ? $user->role_id : null));
        Session::set('tenant_id', $user->tenant_id);

        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
    }

    public static function logout() {
        Session::destroy();
    }

    public static function requireLogin($namespace = 'gym') {
        if (!self::check()) {
            if ($namespace === 'superadmin') {
                Helpers::redirect('auth/login');
            } else {
                Helpers::redirect('auth/login');
            }
        }
    }
}
