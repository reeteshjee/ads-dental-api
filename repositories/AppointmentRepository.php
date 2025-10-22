<?php

class AppointmentRepository {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create(Appointment $appointment) {
        $query = "INSERT INTO appointments (patient_id, dentist_id, surgery_id, 
                  appointment_date, appointment_time, status, request_type) 
                  VALUES (:patient_id, :dentist_id, :surgery_id, :appointment_date, 
                  :appointment_time, :status, :request_type)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':patient_id', $appointment->getPatientId());
        $stmt->bindValue(':dentist_id', $appointment->getDentistId());
        $stmt->bindValue(':surgery_id', $appointment->getSurgeryId());
        $stmt->bindValue(':appointment_date', $appointment->getAppointmentDate());
        $stmt->bindValue(':appointment_time', $appointment->getAppointmentTime());
        $stmt->bindValue(':status', $appointment->getStatus());
        $stmt->bindValue(':request_type', $appointment->getRequestType());
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    public function findById($id) {
        $query = "SELECT a.*, 
                  p.first_name as patient_first_name, p.last_name as patient_last_name,
                  p.contact_phone as patient_phone, p.email as patient_email,
                  d.first_name as dentist_first_name, d.last_name as dentist_last_name,
                  d.specialization as dentist_specialization, d.contact_phone as dentist_phone,
                  s.name as surgery_name, s.location_address as surgery_address,
                  s.telephone_number as surgery_phone
                  FROM appointments a
                  JOIN patients p ON a.patient_id = p.id
                  JOIN dentists d ON a.dentist_id = d.id
                  JOIN surgeries s ON a.surgery_id = s.id
                  WHERE a.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function findByDentistId($dentistId) {
        $query = "SELECT a.*, 
                  p.first_name as patient_first_name, p.last_name as patient_last_name,
                  p.contact_phone as patient_phone, p.email as patient_email,
                  p.date_of_birth as patient_dob, p.mailing_address as patient_address,
                  s.name as surgery_name, s.location_address as surgery_address,
                  s.telephone_number as surgery_phone
                  FROM appointments a
                  JOIN patients p ON a.patient_id = p.id
                  JOIN surgeries s ON a.surgery_id = s.id
                  WHERE a.dentist_id = :dentist_id
                  ORDER BY a.appointment_date DESC, a.appointment_time DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':dentist_id', $dentistId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function findByPatientId($patientId) {
        $query = "SELECT a.*, 
                  d.first_name as dentist_first_name, d.last_name as dentist_last_name,
                  d.specialization as dentist_specialization, d.contact_phone as dentist_phone,
                  d.email as dentist_email,
                  s.name as surgery_name, s.location_address as surgery_address,
                  s.telephone_number as surgery_phone
                  FROM appointments a
                  JOIN dentists d ON a.dentist_id = d.id
                  JOIN surgeries s ON a.surgery_id = s.id
                  WHERE a.patient_id = :patient_id
                  ORDER BY a.appointment_date DESC, a.appointment_time DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':patient_id', $patientId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function updateStatus($id, $status) {
        $query = "UPDATE appointments SET status = :status, updated_at = NOW() 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    
    public function confirmAppointment($id) {
        $query = "UPDATE appointments SET status = 'confirmed', 
                  confirmed_at = NOW(), updated_at = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    
    public function updateAppointment($id, $date, $time, $dentistId, $surgeryId) {
        $query = "UPDATE appointments SET appointment_date = :date, 
                  appointment_time = :time, dentist_id = :dentist_id, 
                  surgery_id = :surgery_id, updated_at = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':time', $time);
        $stmt->bindParam(':dentist_id', $dentistId);
        $stmt->bindParam(':surgery_id', $surgeryId);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    
    public function getAll() {
        $query = "SELECT a.*, 
                  p.first_name as patient_first_name, p.last_name as patient_last_name,
                  d.first_name as dentist_first_name, d.last_name as dentist_last_name,
                  s.name as surgery_name
                  FROM appointments a
                  JOIN patients p ON a.patient_id = p.id
                  JOIN dentists d ON a.dentist_id = d.id
                  JOIN surgeries s ON a.surgery_id = s.id
                  ORDER BY a.appointment_date DESC, a.appointment_time DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}