<?php

class PatientRepository {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create(Patient $patient, $userId) {
        $query = "INSERT INTO patients (user_id, first_name, last_name, contact_phone, 
                  email, mailing_address, date_of_birth, has_outstanding_bill) 
                  VALUES (:user_id, :first_name, :last_name, :contact_phone, 
                  :email, :mailing_address, :date_of_birth, :has_outstanding_bill)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindValue(':first_name', $patient->getFirstName());
        $stmt->bindValue(':last_name', $patient->getLastName());
        $stmt->bindValue(':contact_phone', $patient->getContactPhone());
        $stmt->bindValue(':email', $patient->getPatientEmail());
        $stmt->bindValue(':mailing_address', $patient->getMailingAddress());
        $stmt->bindValue(':date_of_birth', $patient->getDateOfBirth());
        $outstandingBill = $patient->hasOutstandingBill() ? 1 : 0;
        $stmt->bindParam(':has_outstanding_bill', $outstandingBill);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    public function findById($id) {
        $query = "SELECT p.*, u.email as user_email, u.role 
                  FROM patients p 
                  JOIN users u ON p.user_id = u.id 
                  WHERE p.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function findByUserId($userId) {
        $query = "SELECT * FROM patients WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function updateOutstandingBillStatus($patientId, $hasOutstandingBill) {
        $query = "UPDATE patients SET has_outstanding_bill = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $status = $hasOutstandingBill ? 1 : 0;
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $patientId);
        return $stmt->execute();
    }
    
    public function getAll() {
        $query = "SELECT p.*, u.email as user_email FROM patients p 
                  JOIN users u ON p.user_id = u.id ORDER BY p.last_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}