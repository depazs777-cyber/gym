<?php

defined('APP_NAME') or exit('No direct script access allowed');

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $driver = getenv('DB_DRIVER') ?: 'mysql';

        if ($driver === 'sqlite') {
            try {
                $this->pdo = new PDO("sqlite:" . ROOT_PATH . "/database.sqlite");
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $this->pdo->exec("PRAGMA foreign_keys = ON;");
            } catch (\PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        } else {
            $host = DB_HOST;
            $db   = DB_NAME;
            $user = DB_USER;
            $pass = DB_PASS;
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                $this->pdo = new PDO($dsn, $user, $pass, $options);
            } catch (\PDOException $e) {
                // In a real production environment, log the error and show a generic message.
                // For this sandbox, we might fallback to sqlite if mysql fails?
                // But let's try to stick to what ENV says.
                 error_log($e->getMessage());
                 die("Database connection failed. " . $e->getMessage());
            }
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }
}
