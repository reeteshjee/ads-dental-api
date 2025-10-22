<?php
class Dentist extends User {
    private $unique_id;
    private $first_name;
    private $last_name;
    private $contact_phone;
    private $dentist_email;
    private $specialization;

    public function __construct() {
        $this->role = 'dentist';
    }

    public function getUniqueId() { return $this->unique_id; }
    public function getFirstName() { return $this->first_name; }
    public function getLastName() { return $this->last_name; }
    public function getContactPhone() { return $this->contact_phone; }
    public function getDentistEmail() { return $this->dentist_email; }
    public function getSpecialization() { return $this->specialization; }
    
    public function setUniqueId($id) { $this->unique_id = $id; }
    public function setFirstName($name) { $this->first_name = $name; }
    public function setLastName($name) { $this->last_name = $name; }
    public function setContactPhone($phone) { $this->contact_phone = $phone; }
    public function setDentistEmail($email) { $this->dentist_email = $email; }
    public function setSpecialization($spec) { $this->specialization = $spec; }
}
