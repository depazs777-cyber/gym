<?php

class Database {
    private static $shared_dbh = null;
    private $host;
    private $user;
    private $pass;
    private $dbname;
    private $dbh;
    private $error;
    private $stmt;

    public function __construct() {
        // Load credentials from environment or config file
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->user = getenv('DB_USER') ?: 'root';
        $this->pass = getenv('DB_PASS') ?: '';
        $this->dbname = getenv('DB_NAME') ?: 'fitmanager';

        // Set DSN
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=utf8mb4';
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        );

        // Create PDO instance
        try {
            if (self::$shared_dbh === null) {
                self::$shared_dbh = new PDO($dsn, $this->user, $this->pass, $options);
            }
            $this->dbh = self::$shared_dbh;
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
            // In a real app, handle connection errors gracefully
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                die("Database connection failed: " . $this->error);
            } else {
                die("A database error occurred.");
            }
        }
    }

    public function getConnection() {
        return $this->dbh;
    }

    // Prepare statement with query
    public function query($sql) {
        $this->stmt = $this->dbh->prepare($sql);
    }

    // Bind values
    public function bind($param, $value, $type = null) {
        if(is_null($type)) {
            switch(true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    // Execute the prepared statement
    public function execute() {
        return $this->stmt->execute();
    }

    // Get result set as array of objects
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll();
    }

    // Get single record as object
    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }

    // Get row count
    public function rowCount() {
        return $this->stmt->rowCount();
    }

    // Get last insert id
    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }
}
