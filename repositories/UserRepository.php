<?php

class UserRepository {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function findByEmail($email) {
        $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function create($email, $password, $role) {
        $query = "INSERT INTO users (email, password_hash, role) VALUES (:email, :password, :role)";
        $stmt = $this->conn->prepare($query);
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password_hash);
        $stmt->bindParam(':role', $role);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    public function verifyPassword($email, $password) {
        $user = $this->findByEmail($email);
        if($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        return false;
    }
}