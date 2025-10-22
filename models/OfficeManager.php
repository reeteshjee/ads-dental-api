<?php
class OfficeManager extends User {
    private $first_name;
    private $last_name;
    private $contact_phone;

    public function __construct() {
        $this->role = 'office_manager';
    }

    public function getFirstName() { return $this->first_name; }
    public function getLastName() { return $this->last_name; }
    public function getContactPhone() { return $this->contact_phone; }
    
    public function setFirstName($name) { $this->first_name = $name; }
    public function setLastName($name) { $this->last_name = $name; }
    public function setContactPhone($phone) { $this->contact_phone = $phone; }
}