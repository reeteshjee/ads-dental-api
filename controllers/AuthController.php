<?php 
class AuthController {
    private $db;
    private $userRepo;
    
    public function __construct($db) {
        $this->db = $db;
        $this->userRepo = new UserRepository($db);
    }
    
    public function login() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if(!isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Email and password are required.']);
            return;
        }
        
        $user = $this->userRepo->verifyPassword($data['email'], $data['password']);
        
        if(!$user) {
            http_response_code(401);
            echo json_encode(['message' => 'Invalid credentials.']);
            return;
        }
        $payload = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60) // 24 hours
        ];
        
        $token = JWT::encode($payload);
        
        http_response_code(200);
        echo json_encode([
            'message' => 'Login successful.',
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ]);
    }
}