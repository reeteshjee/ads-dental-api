<?php

class PatientController {
    private $db;
    private $patientRepo;
    private $userRepo;
    private $appointmentRepo;
    private $billRepo;
    
    public function __construct($db) {
        $this->db = $db;
        $this->patientRepo = new PatientRepository($db);
        $this->userRepo = new UserRepository($db);
        $this->appointmentRepo = new AppointmentRepository($db);
        $this->billRepo = new BillRepository($db);
    }
    
    public function enroll() {
        $user = AuthMiddleware::requireRole(['office_manager']);
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        $required = ['first_name', 'last_name', 'contact_phone', 'email', 
                     'mailing_address', 'date_of_birth', 'password'];
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
            $userId = $this->userRepo->create($data['email'], $data['password'], 'patient');
            
            if(!$userId) {
                throw new Exception('Failed to create user account.');
            }
            
            // Create patient profile
            $patient = new Patient();
            $patient->setFirstName($data['first_name']);
            $patient->setLastName($data['last_name']);
            $patient->setContactPhone($data['contact_phone']);
            $patient->setPatientEmail($data['email']);
            $patient->setMailingAddress($data['mailing_address']);
            $patient->setDateOfBirth($data['date_of_birth']);
            $patient->setHasOutstandingBill(false);
            
            $patientId = $this->patientRepo->create($patient, $userId);
            
            if(!$patientId) {
                throw new Exception('Failed to create patient profile.');
            }
            
            $this->db->commit();
            
            http_response_code(201);
            echo json_encode([
                'message' => 'Patient enrolled successfully.',
                'patient_id' => $patientId
            ]);
            
        } catch(Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['message' => 'Enrollment failed: ' . $e->getMessage()]);
        }
    }
    
    public function getAll() {
        $user = AuthMiddleware::requireRole(['office_manager']);
        
        $patients = $this->patientRepo->getAll();
        
        http_response_code(200);
        echo json_encode(['patients' => $patients]);
    }
    
    public function getAppointments() {
        $user = AuthMiddleware::requireRole(['patient']);
        
        $patient = $this->patientRepo->findByUserId($user['user_id']);
        
        if(!$patient) {
            http_response_code(404);
            echo json_encode(['message' => 'Patient profile not found.']);
            return;
        }
        
        $appointments = $this->appointmentRepo->findByPatientId($patient['id']);
        
        http_response_code(200);
        echo json_encode(['appointments' => $appointments]);
    }
    
    public function requestAppointment() {
        $user = AuthMiddleware::requireRole(['patient']);
        
        $patient = $this->patientRepo->findByUserId($user['user_id']);
        
        if(!$patient) {
            http_response_code(404);
            echo json_encode(['message' => 'Patient profile not found.']);
            return;
        }
        
        // Check for outstanding bills
        if($patient['has_outstanding_bill']) {
            http_response_code(403);
            echo json_encode([
                'message' => 'Cannot request appointment. You have an outstanding unpaid bill.'
            ]);
            return;
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        if(!isset($data['preferred_date']) || !isset($data['preferred_time'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Preferred date and time are required.']);
            return;
        }
        
        $appointment = new Appointment();
        $appointment->setPatientId($patient['id']);
        $appointment->setAppointmentDate($data['preferred_date']);
        $appointment->setAppointmentTime($data['preferred_time']);
        $appointment->setStatus('requested');
        $appointment->setRequestType('online');
        
        // These will be set by office manager when booking
        $appointment->setDentistId(null);
        $appointment->setSurgeryId(null);
        
        $appointmentId = $this->appointmentRepo->create($appointment);
        
        if($appointmentId) {
            http_response_code(201);
            echo json_encode([
                'message' => 'Appointment request submitted successfully.',
                'appointment_id' => $appointmentId
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to submit appointment request.']);
        }
    }
    
    public function cancelAppointment($appointmentId) {
        $user = AuthMiddleware::requireRole(['patient']);
        
        $patient = $this->patientRepo->findByUserId($user['user_id']);
        $appointment = $this->appointmentRepo->findById($appointmentId);
        
        if(!$appointment || $appointment['patient_id'] != $patient['id']) {
            http_response_code(404);
            echo json_encode(['message' => 'Appointment not found.']);
            return;
        }
        
        if($this->appointmentRepo->updateStatus($appointmentId, 'cancelled')) {
            http_response_code(200);
            echo json_encode(['message' => 'Appointment cancelled successfully.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to cancel appointment.']);
        }
    }
}