<?php

class UserModel extends Model {
    protected $table = 'users';

    public function findByEmail($email) {
        $this->db->query("SELECT * FROM {$this->table} WHERE email = :email");
        $this->db->bind(':email', $email);
        return $this->db->single();
    }

    public function findByUsername($username) {
        $this->db->query("SELECT * FROM {$this->table} WHERE username = :username");
        $this->db->bind(':username', $username);
        return $this->db->single();
    }

    public function login($username, $password) {
        $row = $this->findByUsername($username);

        if ($row) {
            $hashed_password = $row->password;
            if (password_verify($password, $hashed_password)) {
                // Update last login
                $this->db->query("UPDATE {$this->table} SET ultimo_login = NOW() WHERE id = :id");
                $this->db->bind(':id', $row->id);
                $this->db->execute();
                return $row;
            }
        }
        return false;
    }
}
