<?php

class SurgeryController {
    private $db;
    private $surgeryRepo;
    
    public function __construct($db) {
        $this->db = $db;
        $this->surgeryRepo = new SurgeryRepository($db);
    }
    
    public function create() {
        $user = AuthMiddleware::requireRole(['office_manager']);
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        $required = ['name', 'location_address', 'telephone_number'];
        foreach($required as $field) {
            if(!isset($data[$field]) || empty($data[$field])) {
                http_response_code(400);
                echo json_encode(['message' => "Field '$field' is required."]);
                return;
            }
        }
        
        $surgery = new Surgery();
        $surgery->setName($data['name']);
        $surgery->setLocationAddress($data['location_address']);
        $surgery->setTelephoneNumber($data['telephone_number']);
        
        $surgeryId = $this->surgeryRepo->create($surgery);
        
        if($surgeryId) {
            http_response_code(201);
            echo json_encode([
                'message' => 'Surgery created successfully.',
                'surgery_id' => $surgeryId
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to create surgery.']);
        }
    }
    
    public function getAll() {
        $user = AuthMiddleware::authenticate();
        
        $surgeries = $this->surgeryRepo->getAll();
        
        http_response_code(200);
        echo json_encode(['surgeries' => $surgeries]);
    }
    
    public function getById($id) {
        $user = AuthMiddleware::authenticate();
        
        $surgery = $this->surgeryRepo->findById($id);
        
        if($surgery) {
            http_response_code(200);
            echo json_encode(['surgery' => $surgery]);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Surgery not found.']);
        }
    }
}