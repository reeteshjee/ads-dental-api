<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once('Dotenv.php');
$dotenv = new Dotenv(__DIR__);
$dotenv->load();


//autoload all classes
spl_autoload_register(function ($class) {
    // Define base directories for your app structure
    $baseDirs = [
        __DIR__ . '/controllers/',
        __DIR__ . '/models/',
        __DIR__ . '/repositories/',
        __DIR__ . '/middleware/',
        __DIR__ . '/config/',
        __DIR__ . '/database/',
    ];

    foreach ($baseDirs as $baseDir) {
        $file = $baseDir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Database connection
$database = new Database();
$db = $database->getConnection();

// Get request method and URI
$method = $_SERVER['REQUEST_METHOD'];
$request = strtok($_SERVER['REQUEST_URI'], '?');

// Dynamically detect and remove the base folder (e.g., /apsd)
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$basePath = rtrim($scriptName, '/');
if (!empty($basePath) && str_starts_with($request, $basePath)) {
    $request = substr($request, strlen($basePath));
}

// Remove the /api prefix if present
$request = str_replace('/api', '', $request);

// Route the request
try {
    // Auth routes
    if ($method === 'POST' && $request === '/auth/login') {
        $controller = new AuthController($db);
        $controller->login();
    }
    
    // Dentist routes
    elseif ($method === 'POST' && $request === '/dentists/register') {
        $controller = new DentistController($db);
        $controller->register();
    }
    elseif ($method === 'GET' && $request === '/dentists') {
        $controller = new DentistController($db);
        $controller->getAll();
    }
    elseif ($method === 'GET' && $request === '/dentists/appointments') {
        $controller = new DentistController($db);
        $controller->getAppointments();
    }
    
    // Patient routes
    elseif ($method === 'POST' && $request === '/patients/enroll') {
        $controller = new PatientController($db);
        $controller->enroll();
    }
    elseif ($method === 'GET' && $request === '/patients') {
        $controller = new PatientController($db);
        $controller->getAll();
    }
    elseif ($method === 'GET' && $request === '/patients/appointments') {
        $controller = new PatientController($db);
        $controller->getAppointments();
    }
    elseif ($method === 'POST' && $request === '/patients/appointments/request') {
        $controller = new PatientController($db);
        $controller->requestAppointment();
    }
    elseif ($method === 'PUT' && preg_match('/^\/patients\/appointments\/(\d+)\/cancel$/', $request, $matches)) {
        $controller = new PatientController($db);
        $controller->cancelAppointment($matches[1]);
    }
    
    // Appointment routes
    elseif ($method === 'POST' && $request === '/appointments') {
        $controller = new AppointmentController($db);
        $controller->bookAppointment();
    }
    elseif ($method === 'GET' && $request === '/appointments') {
        $controller = new AppointmentController($db);
        $controller->getAll();
    }
    elseif ($method === 'GET' && preg_match('/^\/appointments\/(\d+)$/', $request, $matches)) {
        $controller = new AppointmentController($db);
        $controller->getById($matches[1]);
    }
    elseif ($method === 'PUT' && preg_match('/^\/appointments\/(\d+)$/', $request, $matches)) {
        $controller = new AppointmentController($db);
        $controller->updateAppointment($matches[1]);
    }
    
    // Surgery routes
    elseif ($method === 'POST' && $request === '/surgeries') {
        $controller = new SurgeryController($db);
        $controller->create();
    }
    elseif ($method === 'GET' && $request === '/surgeries') {
        $controller = new SurgeryController($db);
        $controller->getAll();
    }
    elseif ($method === 'GET' && preg_match('/^\/surgeries\/(\d+)$/', $request, $matches)) {
        $controller = new SurgeryController($db);
        $controller->getById($matches[1]);
    }
    
    // 404 - Route not found
    else {
        http_response_code(404);
        echo json_encode(['message' => 'Route not found.']);
    }
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'message' => 'Internal server error.',
        'error' => $e->getMessage()
    ]);
}