<?php

class Bill {
    private $id;
    private $patient_id;
    private $appointment_id;
    private $amount;
    private $status; // 'unpaid', 'paid'
    private $due_date;
    private $paid_date;
    private $created_at;

    public function getId() { return $this->id; }
    public function getPatientId() { return $this->patient_id; }
    public function getAppointmentId() { return $this->appointment_id; }
    public function getAmount() { return $this->amount; }
    public function getStatus() { return $this->status; }
    
    public function setId($id) { $this->id = $id; }
    public function setPatientId($id) { $this->patient_id = $id; }
    public function setAppointmentId($id) { $this->appointment_id = $id; }
    public function setAmount($amount) { $this->amount = $amount; }
    public function setStatus($status) { $this->status = $status; }
    public function setDueDate($date) { $this->due_date = $date; }
    public function setPaidDate($date) { $this->paid_date = $date; }
}