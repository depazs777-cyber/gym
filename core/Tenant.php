<?php

class Tenant {
    public static function current() {
        // Determine the tenant based on the subdomain
        $host = $_SERVER['HTTP_HOST'];
        $parts = explode('.', $host);

        $subdomain = null;
        if (isset($_GET['tenant'])) {
             $subdomain = $_GET['tenant'];
        } elseif (count($parts) >= 3 && $parts[0] !== 'www') {
             $subdomain = $parts[0];
        }

        if ($subdomain && $subdomain !== 'superadmin') {
            // Look up the tenant in the database by subdomain
            $db = new Database();
            $db->query("SELECT * FROM tenants WHERE subdominio = :subdominio AND estado = 'activo' LIMIT 1");
            $db->bind(':subdominio', $subdomain);
            $tenant = $db->single();

            return $tenant ? $tenant : null;
        }

        return null;
    }
}
