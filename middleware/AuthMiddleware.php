<?php

class AuthMiddleware {
    public static function authenticate() {
        $headers = getallheaders();
        $token = null;
        
        if(isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            $arr = explode(" ", $authHeader);
            if(count($arr) == 2 && $arr[0] == 'Bearer') {
                $token = $arr[1];
            }
        }
        
        if(!$token) {
            http_response_code(401);
            echo json_encode(['message' => 'Access denied. No token provided.']);
            exit();
        }
        
        $decoded = JWT::decode($token);
        
        if(!$decoded) {
            http_response_code(401);
            echo json_encode(['message' => 'Invalid token.']);
            exit();
        }
        
        // Check token expiration
        if(isset($decoded['exp']) && $decoded['exp'] < time()) {
            http_response_code(401);
            echo json_encode(['message' => 'Token expired.']);
            exit();
        }
        
        return $decoded;
    }
    
    public static function requireRole($allowedRoles) {
        $user = self::authenticate();
        
        if(!in_array($user['role'], $allowedRoles)) {
            http_response_code(403);
            echo json_encode(['message' => 'Access denied. Insufficient permissions.']);
            exit();
        }
        
        return $user;
    }
}