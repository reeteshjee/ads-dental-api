<?php
class Patient extends User {
    private $first_name;
    private $last_name;
    private $contact_phone;
    private $patient_email;
    private $mailing_address;
    private $date_of_birth;
    private $has_outstanding_bill;

    public function __construct() {
        $this->role = 'patient';
        $this->has_outstanding_bill = false;
    }

    public function getFirstName() { return $this->first_name; }
    public function getLastName() { return $this->last_name; }
    public function getContactPhone() { return $this->contact_phone; }
    public function getPatientEmail() { return $this->patient_email; }
    public function getMailingAddress() { return $this->mailing_address; }
    public function getDateOfBirth() { return $this->date_of_birth; }
    public function hasOutstandingBill() { return $this->has_outstanding_bill; }
    
    public function setFirstName($name) { $this->first_name = $name; }
    public function setLastName($name) { $this->last_name = $name; }
    public function setContactPhone($phone) { $this->contact_phone = $phone; }
    public function setPatientEmail($email) { $this->patient_email = $email; }
    public function setMailingAddress($address) { $this->mailing_address = $address; }
    public function setDateOfBirth($dob) { $this->date_of_birth = $dob; }
    public function setHasOutstandingBill($status) { $this->has_outstanding_bill = $status; }
}