<?php

namespace App\Core;

use PDO;

class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $useGymScope = true; // Por defecto true para SaaS

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Encuentra un registro por ID, asegurando el gym_id si aplica
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $params = [':id' => $id];

        if ($this->useGymScope && isset($_SESSION['gym_id'])) {
            $sql .= " AND gym_id = :gym_id";
            $params[':gym_id'] = $_SESSION['gym_id'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    // Obtiene todos los registros (con paginación opcional)
    public function all($limit = 100, $offset = 0) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if ($this->useGymScope && isset($_SESSION['gym_id'])) {
            $sql .= " WHERE gym_id = :gym_id";
            $params[':gym_id'] = $_SESSION['gym_id'];
        }

        $sql .= " LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Crea un nuevo registro
    public function create(array $data) {
        // Filtrar fillable
        $data = array_intersect_key($data, array_flip($this->fillable));

        // Inyectar gym_id automáticamente
        if ($this->useGymScope && isset($_SESSION['gym_id']) && !isset($data['gym_id'])) {
            $data['gym_id'] = $_SESSION['gym_id'];
        }

        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";

        $stmt = $this->db->prepare($sql);

        try {
            $stmt->execute($data);
            return $this->db->lastInsertId();
        } catch (\PDOException $e) {
            // Log error
            throw $e;
        }
    }

    // Actualiza un registro
    public function update($id, array $data) {
        $data = array_intersect_key($data, array_flip($this->fillable));

        $sets = [];
        foreach ($data as $key => $value) {
            $sets[] = "{$key} = :{$key}";
        }
        $setString = implode(', ', $sets);

        $sql = "UPDATE {$this->table} SET {$setString} WHERE {$this->primaryKey} = :pk";
        $data['pk'] = $id; // Bind ID

        if ($this->useGymScope && isset($_SESSION['gym_id'])) {
            $sql .= " AND gym_id = :gym_id";
            $data['gym_id'] = $_SESSION['gym_id'];
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }
}
