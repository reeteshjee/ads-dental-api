<?php
class AppointmentController {
    private $db;
    private $appointmentRepo;
    private $dentistRepo;
    private $patientRepo;
    
    public function __construct($db) {
        $this->db = $db;
        $this->appointmentRepo = new AppointmentRepository($db);
        $this->dentistRepo = new DentistRepository($db);
        $this->patientRepo = new PatientRepository($db);
    }
    
    public function bookAppointment() {
        $user = AuthMiddleware::requireRole(['office_manager']);
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        $required = ['patient_id', 'dentist_id', 'surgery_id', 
                     'appointment_date', 'appointment_time'];
        foreach($required as $field) {
            if(!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(['message' => "Field '$field' is required."]);
                return;
            }
        }
        
        // Check patient outstanding bills
        $patient = $this->patientRepo->findById($data['patient_id']);
        if($patient['has_outstanding_bill']) {
            http_response_code(403);
            echo json_encode([
                'message' => 'Cannot book appointment. Patient has outstanding bill.'
            ]);
            return;
        }
        
        // Check dentist weekly appointment limit
        $appointmentDate = $data['appointment_date'];
        $year = date('Y', strtotime($appointmentDate));
        $week = date('W', strtotime($appointmentDate));
        
        $weeklyCount = $this->dentistRepo->getWeeklyAppointmentCount(
            $data['dentist_id'], $year, $week
        );
        
        if($weeklyCount >= 5) {
            http_response_code(403);
            echo json_encode([
                'message' => 'Cannot book appointment. Dentist has reached weekly limit of 5 appointments.'
            ]);
            return;
        }
        
        $appointment = new Appointment();
        $appointment->setPatientId($data['patient_id']);
        $appointment->setDentistId($data['dentist_id']);
        $appointment->setSurgeryId($data['surgery_id']);
        $appointment->setAppointmentDate($data['appointment_date']);
        $appointment->setAppointmentTime($data['appointment_time']);
        $appointment->setStatus('confirmed');
        $appointment->setRequestType($data['request_type'] ?? 'phone');
        
        $appointmentId = $this->appointmentRepo->create($appointment);
        
        if($appointmentId) {
            // Confirm the appointment
            $this->appointmentRepo->confirmAppointment($appointmentId);
            
            // Here you would send confirmation email
            // $this->sendConfirmationEmail($appointmentId);
            
            http_response_code(201);
            echo json_encode([
                'message' => 'Appointment booked and confirmed successfully.',
                'appointment_id' => $appointmentId
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to book appointment.']);
        }
    }
    
    public function getAll() {
        $user = AuthMiddleware::requireRole(['office_manager']);
        
        $appointments = $this->appointmentRepo->getAll();
        
        http_response_code(200);
        echo json_encode(['appointments' => $appointments]);
    }
    
    public function getById($id) {
        $user = AuthMiddleware::authenticate();
        
        $appointment = $this->appointmentRepo->findById($id);
        
        if(!$appointment) {
            http_response_code(404);
            echo json_encode(['message' => 'Appointment not found.']);
            return;
        }
        
        // Authorization check based on role
        if($user['role'] == 'patient') {
            $patient = $this->patientRepo->findByUserId($user['user_id']);
            if($appointment['patient_id'] != $patient['id']) {
                http_response_code(403);
                echo json_encode(['message' => 'Access denied.']);
                return;
            }
        } elseif($user['role'] == 'dentist') {
            $dentist = $this->dentistRepo->findByUserId($user['user_id']);
            if($appointment['dentist_id'] != $dentist['id']) {
                http_response_code(403);
                echo json_encode(['message' => 'Access denied.']);
                return;
            }
        }
        
        http_response_code(200);
        echo json_encode(['appointment' => $appointment]);
    }
    
    public function updateAppointment($id) {
        $user = AuthMiddleware::requireRole(['office_manager', 'patient']);
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        $appointment = $this->appointmentRepo->findById($id);
        
        if(!$appointment) {
            http_response_code(404);
            echo json_encode(['message' => 'Appointment not found.']);
            return;
        }
        
        // Patient can only update their own appointments
        if($user['role'] == 'patient') {
            $patient = $this->patientRepo->findByUserId($user['user_id']);
            if($appointment['patient_id'] != $patient['id']) {
                http_response_code(403);
                echo json_encode(['message' => 'Access denied.']);
                return;
            }
        }
        
        $date = $data['appointment_date'] ?? $appointment['appointment_date'];
        $time = $data['appointment_time'] ?? $appointment['appointment_time'];
        $dentistId = $data['dentist_id'] ?? $appointment['dentist_id'];
        $surgeryId = $data['surgery_id'] ?? $appointment['surgery_id'];
        
        // Check dentist weekly limit if changing date or dentist
        if($date != $appointment['appointment_date'] || 
           $dentistId != $appointment['dentist_id']) {
            $year = date('Y', strtotime($date));
            $week = date('W', strtotime($date));
            
            $weeklyCount = $this->dentistRepo->getWeeklyAppointmentCount(
                $dentistId, $year, $week
            );
            
            if($weeklyCount >= 5) {
                http_response_code(403);
                echo json_encode([
                    'message' => 'Cannot update appointment. Dentist has reached weekly limit.'
                ]);
                return;
            }
        }
        
        if($this->appointmentRepo->updateAppointment($id, $date, $time, $dentistId, $surgeryId)) {
            http_response_code(200);
            echo json_encode(['message' => 'Appointment updated successfully.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to update appointment.']);
        }
    }
}