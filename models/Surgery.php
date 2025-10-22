<?php

class Surgery {
    private $id;
    private $name;
    private $location_address;
    private $telephone_number;
    private $created_at;

    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getLocationAddress() { return $this->location_address; }
    public function getTelephoneNumber() { return $this->telephone_number; }
    
    public function setId($id) { $this->id = $id; }
    public function setName($name) { $this->name = $name; }
    public function setLocationAddress($address) { $this->location_address = $address; }
    public function setTelephoneNumber($phone) { $this->telephone_number = $phone; }
}