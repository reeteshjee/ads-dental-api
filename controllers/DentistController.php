<?php

class DentistController {
    private $db;
    private $dentistRepo;
    private $userRepo;
    private $appointmentRepo;
    
    public function __construct($db) {
        $this->db = $db;
        $this->dentistRepo = new DentistRepository($db);
        $this->userRepo = new UserRepository($db);
        $this->appointmentRepo = new AppointmentRepository($db);
    }
    
    public function register() {
        $user = AuthMiddleware::requireRole(['office_manager']);
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        $required = ['first_name', 'last_name', 'contact_phone', 'email', 
                     'specialization', 'password'];
        foreach($required as $field) {
            if(!isset($data[$field]) || empty($data[$field])) {
                http_response_code(400);
                echo json_encode(['message' => "Field '$field' is required."]);
                return;
            }
        }
        
        // Check if email already exists
        if($this->userRepo->findByEmail($data['email'])) {
            http_response_code(409);
            echo json_encode(['message' => 'Email already exists.']);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            // Create user account
            $userId = $this->userRepo->create($data['email'], $data['password'], 'dentist');
            
            if(!$userId) {
                throw new Exception('Failed to create user account.');
            }
            
            // Create dentist profile
            $dentist = new Dentist();
            $dentist->setFirstName($data['first_name']);
            $dentist->setLastName($data['last_name']);
            $dentist->setContactPhone($data['contact_phone']);
            $dentist->setDentistEmail($data['email']);
            $dentist->setSpecialization($data['specialization']);
            
            $dentistId = $this->dentistRepo->create($dentist, $userId);
            
            if(!$dentistId) {
                throw new Exception('Failed to create dentist profile.');
            }
            
            $this->db->commit();
            
            http_response_code(201);
            echo json_encode([
                'message' => 'Dentist registered successfully.',
                'dentist_id' => $dentistId
            ]);
            
        } catch(Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['message' => 'Registration failed: ' . $e->getMessage()]);
        }
    }
    
    public function getAll() {
        $user = AuthMiddleware::requireRole(['office_manager', 'dentist']);
        
        $dentists = $this->dentistRepo->getAll();
        
        http_response_code(200);
        echo json_encode(['dentists' => $dentists]);
    }
    
    public function getAppointments() {
        $user = AuthMiddleware::requireRole(['dentist']);
        
        $dentist = $this->dentistRepo->findByUserId($user['user_id']);
        
        if(!$dentist) {
            http_response_code(404);
            echo json_encode(['message' => 'Dentist profile not found.']);
            return;
        }
        
        $appointments = $this->appointmentRepo->findByDentistId($dentist['id']);
        
        http_response_code(200);
        echo json_encode(['appointments' => $appointments]);
    }
}