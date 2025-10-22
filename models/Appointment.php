<?php

class Appointment {
    private $id;
    private $patient_id;
    private $dentist_id;
    private $surgery_id;
    private $appointment_date;
    private $appointment_time;
    private $status; // 'requested', 'confirmed', 'cancelled', 'completed'
    private $request_type; // 'phone', 'online'
    private $created_at;
    private $updated_at;
    private $confirmed_at;

    public function getId() { return $this->id; }
    public function getPatientId() { return $this->patient_id; }
    public function getDentistId() { return $this->dentist_id; }
    public function getSurgeryId() { return $this->surgery_id; }
    public function getAppointmentDate() { return $this->appointment_date; }
    public function getAppointmentTime() { return $this->appointment_time; }
    public function getStatus() { return $this->status; }
    public function getRequestType() { return $this->request_type; }
    
    public function setId($id) { $this->id = $id; }
    public function setPatientId($id) { $this->patient_id = $id; }
    public function setDentistId($id) { $this->dentist_id = $id; }
    public function setSurgeryId($id) { $this->surgery_id = $id; }
    public function setAppointmentDate($date) { $this->appointment_date = $date; }
    public function setAppointmentTime($time) { $this->appointment_time = $time; }
    public function setStatus($status) { $this->status = $status; }
    public function setRequestType($type) { $this->request_type = $type; }
    public function setConfirmedAt($datetime) { $this->confirmed_at = $datetime; }
}