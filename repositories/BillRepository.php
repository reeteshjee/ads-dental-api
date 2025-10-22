<?php
class BillRepository {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create(Bill $bill) {
        $query = "INSERT INTO bills (patient_id, appointment_id, amount, status, due_date) 
                  VALUES (:patient_id, :appointment_id, :amount, :status, :due_date)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':patient_id', $bill->getPatientId());
        $stmt->bindValue(':appointment_id', $bill->getAppointmentId());
        $stmt->bindValue(':amount', $bill->getAmount());
        $stmt->bindValue(':status', $bill->getStatus());
        $stmt->bindValue(':due_date', $bill->getDueDate());
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    public function hasOutstandingBill($patientId) {
        $query = "SELECT COUNT(*) as count FROM bills 
                  WHERE patient_id = :patient_id AND status = 'unpaid'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':patient_id', $patientId);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    public function getByPatientId($patientId) {
        $query = "SELECT * FROM bills WHERE patient_id = :patient_id 
                  ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':patient_id', $patientId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function markAsPaid($billId) {
        $query = "UPDATE bills SET status = 'paid', paid_date = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $billId);
        return $stmt->execute();
    }
}