<?php

abstract class User {
    protected $id;
    protected $email;
    protected $password_hash;
    protected $role;
    protected $created_at;
    protected $updated_at;

    public function getId() { return $this->id; }
    public function getEmail() { return $this->email; }
    public function getRole() { return $this->role; }
    
    public function setId($id) { $this->id = $id; }
    public function setEmail($email) { $this->email = $email; }
    public function setPasswordHash($hash) { $this->password_hash = $hash; }
    public function setRole($role) { $this->role = $role; }
}