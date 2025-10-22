<?php

class SurgeryRepository {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create(Surgery $surgery) {
        $query = "INSERT INTO surgeries (name, location_address, telephone_number) 
                  VALUES (:name, :location_address, :telephone_number)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':name', $surgery->getName());
        $stmt->bindValue(':location_address', $surgery->getLocationAddress());
        $stmt->bindValue(':telephone_number', $surgery->getTelephoneNumber());
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    public function findById($id) {
        $query = "SELECT * FROM surgeries WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function getAll() {
        $query = "SELECT * FROM surgeries ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}