<?php

class DentistRepository {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create(Dentist $dentist, $userId) {
        $query = "INSERT INTO dentists (user_id, unique_id, first_name, last_name, 
                  contact_phone, email, specialization) 
                  VALUES (:user_id, :unique_id, :first_name, :last_name, 
                  :contact_phone, :email, :specialization)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $uniqueId = $this->generateUniqueId();
        $stmt->bindValue(':unique_id', $uniqueId);
        $stmt->bindValue(':first_name', $dentist->getFirstName());
        $stmt->bindValue(':last_name', $dentist->getLastName());
        $stmt->bindValue(':contact_phone', $dentist->getContactPhone());
        $stmt->bindValue(':email', $dentist->getDentistEmail());
        $stmt->bindValue(':specialization', $dentist->getSpecialization());
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    public function findById($id) {
        $query = "SELECT d.*, u.email as user_email, u.role 
                  FROM dentists d 
                  JOIN users u ON d.user_id = u.id 
                  WHERE d.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function findByUserId($userId) {
        $query = "SELECT * FROM dentists WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function getAll() {
        $query = "SELECT d.*, u.email as user_email FROM dentists d 
                  JOIN users u ON d.user_id = u.id ORDER BY d.last_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    private function generateUniqueId() {
        return 'DEN' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
    
    public function getWeeklyAppointmentCount($dentistId, $year, $week) {
        $query = "SELECT COUNT(*) as count FROM appointments 
                  WHERE dentist_id = :dentist_id 
                  AND YEAR(appointment_date) = :year 
                  AND WEEK(appointment_date, 1) = :week 
                  AND status != 'cancelled'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':dentist_id', $dentistId);
        $stmt->bindParam(':year', $year);
        $stmt->bindParam(':week', $week);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }
}