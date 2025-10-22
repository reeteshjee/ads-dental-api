# Advantis Dental Surgeries (ADS) Management System
## Complete Documentation

---

## 📋 Table of Contents
1. [Overview](#overview)
2. [Domain Model](#domain-model)
3. [System Architecture](#system-architecture)
4. [Technology Stack](#technology-stack)
5. [Database Schema](#database-schema)
6. [Installation Guide](#installation-guide)
7. [API Documentation](#api-documentation)
8. [Business Rules](#business-rules)
9. [Testing Guide](#testing-guide)
10. [Security Features](#security-features)

---

## 🎯 Overview

The Advantis Dental Surgeries Management System is a comprehensive web-based solution designed to manage a growing network of dental surgeries across the South West region.

### Key Features
- ✅ Dentist registration and management with unique IDs
- ✅ Patient enrollment with complete profile management
- ✅ Appointment booking (phone and online requests)
- ✅ Role-based access control (Office Manager, Dentist, Patient)
- ✅ Automated business rule enforcement (weekly limits, bill checks)
- ✅ JWT-based secure authentication
- ✅ RESTful API architecture with PDO
- ✅ Surgery location management

---

## 🏗️ Domain Model

### Core Entities

#### 1. User (Abstract Base Class)
**Purpose:** Foundation for all user types in the system

**Attributes:**
- `id` (INT) - Unique identifier
- `email` (VARCHAR) - User's email address (unique)
- `password_hash` (VARCHAR) - Encrypted password
- `role` (ENUM) - User role: office_manager, dentist, patient
- `created_at`, `updated_at` (TIMESTAMP) - Audit timestamps

**Relationships:**
- Parent class for OfficeManager, Dentist, and Patient

---

#### 2. OfficeManager (extends User)
**Purpose:** Manages overall system operations

**Attributes:**
- `id` (INT) - Primary key
- `user_id` (INT) - Foreign key to users table
- `first_name` (VARCHAR) - First name
- `last_name` (VARCHAR) - Last name
- `contact_phone` (VARCHAR) - Phone number

**Responsibilities:**
- Register new dentists
- Enroll new patients
- Book and confirm appointments
- Manage all system operations

---

#### 3. Dentist (extends User)
**Purpose:** Healthcare professionals providing dental services

**Attributes:**
- `id` (INT) - Primary key
- `user_id` (INT) - Foreign key to users table
- `unique_id` (VARCHAR) - Auto-generated unique ID (e.g., DEN20250001)
- `first_name` (VARCHAR) - First name
- `last_name` (VARCHAR) - Last name
- `contact_phone` (VARCHAR) - Phone number
- `email` (VARCHAR) - Professional email
- `specialization` (VARCHAR) - Area of expertise

**Responsibilities:**
- View assigned appointments
- Access patient information for scheduled appointments
- Provide dental services at surgery locations

**Business Rules:**
- Cannot have more than 5 appointments in any given week
- Receives unique ID upon registration

---

#### 4. Patient (extends User)
**Purpose:** Individuals seeking dental services

**Attributes:**
- `id` (INT) - Primary key
- `user_id` (INT) - Foreign key to users table
- `first_name` (VARCHAR) - First name
- `last_name` (VARCHAR) - Last name
- `contact_phone` (VARCHAR) - Phone number
- `email` (VARCHAR) - Personal email
- `mailing_address` (TEXT) - Physical address
- `date_of_birth` (DATE) - Date of birth
- `has_outstanding_bill` (BOOLEAN) - Unpaid bill flag

**Responsibilities:**
- Request appointments (online or phone)
- View their appointments
- Cancel appointments
- Request to change appointments

**Business Rules:**
- Cannot request new appointments if has_outstanding_bill = TRUE

---

#### 5. Appointment
**Purpose:** Represents scheduled dental visits

**Attributes:**
- `id` (INT) - Primary key
- `patient_id` (INT) - Foreign key to patients
- `dentist_id` (INT) - Foreign key to dentists
- `surgery_id` (INT) - Foreign key to surgeries
- `appointment_date` (DATE) - Scheduled date
- `appointment_time` (TIME) - Scheduled time
- `status` (ENUM) - requested, confirmed, cancelled, completed
- `request_type` (ENUM) - phone, online
- `created_at`, `updated_at`, `confirmed_at` (TIMESTAMP)

**States:**
- **requested**: Patient has submitted request
- **confirmed**: Office manager has booked and sent confirmation
- **cancelled**: Appointment cancelled by patient
- **completed**: Service provided

**Relationships:**
- Belongs to one Patient (1:N)
- Belongs to one Dentist (1:N)
- Belongs to one Surgery (1:N)
- May generate one Bill (1:1)

---

#### 6. Surgery
**Purpose:** Physical locations where services are provided

**Attributes:**
- `id` (INT) - Primary key
- `name` (VARCHAR) - Surgery name
- `location_address` (TEXT) - Full address
- `telephone_number` (VARCHAR) - Contact number

**Relationships:**
- Hosts many Appointments (1:N)

---

#### 7. Bill
**Purpose:** Financial records for services

**Attributes:**
- `id` (INT) - Primary key
- `patient_id` (INT) - Foreign key to patients
- `appointment_id` (INT) - Foreign key to appointments
- `amount` (DECIMAL) - Bill amount
- `status` (ENUM) - unpaid, paid
- `due_date` (DATE) - Payment deadline
- `paid_date` (TIMESTAMP) - Payment completion

**Impact:**
- Unpaid bills block new appointment requests

---

### Domain Relationships Diagram

```
                    ┌─────────────┐
                    │    User     │
                    │  (Abstract) │
                    └──────┬──────┘
                           │
            ┌──────────────┼──────────────┐
            │              │              │
            ▼              ▼              ▼
    ┌──────────────┐ ┌──────────┐ ┌──────────────┐
    │OfficeManager │ │ Dentist  │ │   Patient    │
    └──────┬───────┘ └────┬─────┘ └──────┬───────┘
           │              │               │
           │ manages      │ assigned to   │ requests
           │              │               │
           └──────────────┼───────────────┘
                          ▼
                  ┌──────────────┐
                  │ Appointment  │◄────┐
                  └──────┬───────┘     │
                         │ hosted at   │ generates
                         ▼             │
                  ┌──────────┐         │
                  │ Surgery  │         ▼
                  └──────────┘    ┌────────┐
                                  │  Bill  │
                                  └────────┘
```

---

## 🏛️ System Architecture

### Layered Architecture

The system implements a clean layered architecture with separation of concerns:

```
┌─────────────────────────────────────────────────┐
│         PRESENTATION LAYER                      │
│    Controllers: Handle HTTP & Routing           │
│    - AuthController                             │
│    - DentistController                          │
│    - PatientController                          │
│    - AppointmentController                      │
│    - SurgeryController                          │
├─────────────────────────────────────────────────┤
│         SECURITY LAYER                          │
│    Middleware: Authentication & Authorization   │
│    - AuthMiddleware (JWT validation)            │
│    - Role-based access control                  │
├─────────────────────────────────────────────────┤
│         DOMAIN LAYER                            │
│    Models: Business Entities                    │
│    - User, OfficeManager, Dentist, Patient     │
│    - Appointment, Surgery, Bill                │
├─────────────────────────────────────────────────┤
│         DATA ACCESS LAYER                       │
│    Repositories: Database Operations            │
│    - UserRepository                             │
│    - DentistRepository                          │
│    - PatientRepository                          │
│    - AppointmentRepository                      │
│    - SurgeryRepository                          │
│    - BillRepository                             │
├─────────────────────────────────────────────────┤
│         DATABASE LAYER                          │
│    MySQL Database with PDO                      │
└─────────────────────────────────────────────────┘
```

### Request Flow

```
Client Request
      ↓
[index.php - Router]
      ↓
[AuthMiddleware - Verify JWT & Role]
      ↓
[Controller - Parse & Validate]
      ↓
[Repository - Execute Query via PDO]
      ↓
[Database - MySQL]
      ↓
[Repository - Return Data]
      ↓
[Controller - Format Response]
      ↓
JSON Response to Client
```

### Design Patterns

#### 1. Repository Pattern
Abstracts data access from business logic
```php
class PatientRepository {
    public function findById($id) {
        // Database query
    }
    public function create(Patient $patient) {
        // Insert logic
    }
}
```

#### 2. Dependency Injection
Controllers receive dependencies via constructor
```php
public function __construct($db) {
    $this->patientRepo = new PatientRepository($db);
}
```

#### 3. Middleware Pattern
Cross-cutting concerns (authentication)
```php
AuthMiddleware::requireRole(['office_manager']);
```

#### 4. Factory Pattern
JWT token creation and validation
```php
$token = JWT::encode($payload);
$decoded = JWT::decode($token);
```

---

## 💻 Technology Stack

### Backend
- **Language:** PHP 7.4+ (Core PHP, no framework)
- **Database:** MySQL 8.0+
- **Authentication:** Custom JWT (HMAC SHA256)
- **Database Access:** PDO with prepared statements

### Architecture
- **API Style:** RESTful
- **Authentication:** Bearer Token (JWT)
- **Data Format:** JSON
- **HTTP Methods:** GET, POST, PUT, DELETE

### Security
- **Password Hashing:** bcrypt (PASSWORD_BCRYPT)
- **SQL Injection Prevention:** PDO Prepared Statements
- **Token Security:** HMAC SHA256 signature
- **Access Control:** Role-based (RBAC)

---

## 🗄️ Database Schema

### Complete SQL Schema

```sql
-- Create Database
CREATE DATABASE ads_dental;
USE ads_dental;

-- 1. Users Table (Base table for all user types)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('office_manager', 'dentist', 'patient') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
);

-- 2. Office Managers Table
CREATE TABLE office_managers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    contact_phone VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
);

-- 3. Dentists Table
CREATE TABLE dentists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    unique_id VARCHAR(50) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    contact_phone VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_unique_id (unique_id),
    INDEX idx_user_id (user_id)
);

-- 4. Patients Table
CREATE TABLE patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    contact_phone VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL,
    mailing_address TEXT NOT NULL,
    date_of_birth DATE NOT NULL,
    has_outstanding_bill BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_outstanding_bill (has_outstanding_bill)
);

-- 5. Surgeries Table
CREATE TABLE surgeries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    location_address TEXT NOT NULL,
    telephone_number VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
);

-- 6. Appointments Table
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    dentist_id INT,
    surgery_id INT,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('requested', 'confirmed', 'cancelled', 'completed') DEFAULT 'requested',
    request_type ENUM('phone', 'online') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    confirmed_at TIMESTAMP NULL,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (dentist_id) REFERENCES dentists(id) ON DELETE SET NULL,
    FOREIGN KEY (surgery_id) REFERENCES surgeries(id) ON DELETE SET NULL,
    INDEX idx_patient_id (patient_id),
    INDEX idx_dentist_id (dentist_id),
    INDEX idx_appointment_date (appointment_date),
    INDEX idx_status (status)
);

-- 7. Bills Table
CREATE TABLE bills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    appointment_id INT,
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM('unpaid', 'paid') DEFAULT 'unpaid',
    due_date DATE NOT NULL,
    paid_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL,
    INDEX idx_patient_id (patient_id),
    INDEX idx_status (status)
);

-- Sample Data

-- Insert default office manager (password: "password")
INSERT INTO users (email, password_hash, role) 
VALUES ('manager@ads.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'office_manager');

INSERT INTO office_managers (user_id, first_name, last_name, contact_phone)
SELECT id, 'John', 'Manager', '555-0100' FROM users WHERE email = 'manager@ads.com';

-- Insert sample surgeries
INSERT INTO surgeries (name, location_address, telephone_number) VALUES
('ADS Main Surgery', '123 Dental Street, Phoenix, AZ 85001', '555-1000'),
('ADS West Surgery', '456 Healthcare Ave, Tucson, AZ 85701', '555-2000'),
('ADS North Surgery', '789 Medical Blvd, Flagstaff, AZ 86001', '555-3000');
```

---

## 🚀 Installation Guide

### Prerequisites
- PHP 7.4 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- mod_rewrite enabled (Apache)

### Step-by-Step Installation

#### Step 1: Database Setup
```bash
# Login to MySQL
mysql -u root -p

# Run the schema
source database/schema.sql
```

#### Step 2: Configure Database Connection
Edit `config/database.php`:
```php
private $host = "localhost";
private $db_name = "ads_dental";
private $username = "your_mysql_username";
private $password = "your_mysql_password";
```

#### Step 3: Configure JWT Secret
Edit `config/jwt.php`:
```php
private static $secret_key = "your_secure_secret_key_here";
```

Generate a secure key:
```bash
php -r "echo bin2hex(random_bytes(32));"
```

#### Step 4: Apache Configuration
Create `.htaccess` in project root:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^api/(.*)$ index.php [QSA,L]

Header set Access-Control-Allow-Origin "*"
Header set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
Header set Access-Control-Allow-Headers "Content-Type, Authorization"
```

#### Step 5: Nginx Configuration (Alternative)
```nginx
location /api {
    try_files $uri $uri/ /index.php?$query_string;
}

add_header Access-Control-Allow-Origin *;
add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS";
add_header Access-Control-Allow-Headers "Content-Type, Authorization";
```

#### Step 6: Test Installation
```bash
curl http://localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"manager@ads.com","password":"password"}'
```

Expected response:
```json
{
    "message": "Login successful.",
    "token": "eyJ0eXAi...",
    "user": {
        "id": 1,
        "email": "manager@ads.com",
        "role": "office_manager"
    }
}
```

---

## 📚 API Documentation

### Base URL
```
http://localhost/api
```

### Authentication
All endpoints (except `/auth/login`) require JWT:
```
Authorization: Bearer {your_jwt_token}
```

### HTTP Status Codes
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `409` - Conflict
- `500` - Internal Server Error

### Complete Endpoint List

| # | Method | Endpoint | Role | Description |
|---|--------|----------|------|-------------|
| 1 | POST | `/auth/login` | All | User login |
| 2 | POST | `/dentists/register` | Office Manager | Register dentist |
| 3 | GET | `/dentists` | Office Manager, Dentist | List dentists |
| 4 | GET | `/dentists/appointments` | Dentist | View dentist appointments |
| 5 | POST | `/patients/enroll` | Office Manager | Enroll patient |
| 6 | GET | `/patients` | Office Manager | List patients |
| 7 | GET | `/patients/appointments` | Patient | View patient appointments |
| 8 | POST | `/patients/appointments/request` | Patient | Request appointment |
| 9 | PUT | `/patients/appointments/{id}/cancel` | Patient | Cancel appointment |
| 10 | POST | `/appointments` | Office Manager | Book appointment |
| 11 | GET | `/appointments` | Office Manager | List all appointments |
| 12 | GET | `/appointments/{id}` | All (role-based) | Get appointment details |
| 13 | PUT | `/appointments/{id}` | Office Manager, Patient | Update appointment |
| 14 | POST | `/surgeries` | Office Manager | Create surgery |
| 15 | GET | `/surgeries` | All authenticated | List surgeries |
| 16 | GET | `/surgeries/{id}` | All authenticated | Get surgery details |

### Example Requests

#### 1. Login
```bash
POST /api/auth/login
Content-Type: application/json

{
    "email": "manager@ads.com",
    "password": "password"
}

# Response
{
    "message": "Login successful.",
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
        "id": 1,
        "email": "manager@ads.com",
        "role": "office_manager"
    }
}
```

#### 2. Register Dentist (Office Manager only)
```bash
POST /api/dentists/register
Authorization: Bearer {token}
Content-Type: application/json

{
    "first_name": "John",
    "last_name": "Smith",
    "contact_phone": "555-1234",
    "email": "dentist@example.com",
    "specialization": "Orthodontics",
    "password": "password123"
}

# Response
{
    "message": "Dentist registered successfully.",
    "dentist_id": 1
}
```

#### 3. Enroll Patient (Office Manager only)
```bash
POST /api/patients/enroll
Authorization: Bearer {token}
Content-Type: application/json

{
    "first_name": "Jane",
    "last_name": "Doe",
    "contact_phone": "555-5678",
    "email": "patient@example.com",
    "mailing_address": "123 Main Street, Phoenix, AZ 85001",
    "date_of_birth": "1990-05-15",
    "password": "password123"
}

# Response
{
    "message": "Patient enrolled successfully.",
    "patient_id": 1
}
```

#### 4. Request Appointment (Patient only)
```bash
POST /api/patients/appointments/request
Authorization: Bearer {token}
Content-Type: application/json

{
    "preferred_date": "2025-10-25",
    "preferred_time": "10:00:00"
}

# Response (Success)
{
    "message": "Appointment request submitted successfully.",
    "appointment_id": 1
}

# Response (Error - Outstanding Bill)
{
    "message": "Cannot request appointment. You have an outstanding unpaid bill."
}
```

#### 5. Book Appointment (Office Manager only)
```bash
POST /api/appointments
Authorization: Bearer {token}
Content-Type: application/json

{
    "patient_id": 1,
    "dentist_id": 1,
    "surgery_id": 1,
    "appointment_date": "2025-10-25",
    "appointment_time": "10:00:00",
    "request_type": "phone"
}

# Response (Success)
{
    "message": "Appointment booked and confirmed successfully.",
    "appointment_id": 1
}

# Response (Error - Weekly Limit)
{
    "message": "Cannot book appointment. Dentist has reached weekly limit of 5 appointments."
}
```

#### 6. View Dentist Appointments (Dentist only)
```bash
GET /api/dentists/appointments
Authorization: Bearer {token}

# Response
{
    "appointments": [
        {
            "id": 1,
            "appointment_date": "2025-10-25",
            "appointment_time": "10:00:00",
            "status": "confirmed",
            "patient_first_name": "Jane",
            "patient_last_name": "Doe",
            "patient_phone": "555-5678",
            "patient_email": "patient@example.com",
            "patient_dob": "1990-05-15",
            "patient_address": "123 Main St",
            "surgery_name": "ADS Main Surgery",
            "surgery_address": "123 Dental Street",
            "surgery_phone": "555-1000"
        }
    ]
}
```

---

## 📋 Business Rules

### Rule 1: Dentist Weekly Appointment Limit
**Rule:** Dentist cannot have more than 5 appointments in any given week

**Implementation:**
- Validated in `AppointmentController::bookAppointment()`
- Validated in `AppointmentController::updateAppointment()`
- Uses MySQL `WEEK()` function
- Returns HTTP 403 if limit exceeded

**SQL Query:**
```sql
SELECT COUNT(*) as count 
FROM appointments 
WHERE dentist_id = ? 
AND YEAR(appointment_date) = ? 
AND WEEK(appointment_date, 1) = ? 
AND status != 'cancelled'
```

### Rule 2: Outstanding Bill Restriction
**Rule:** Patients with unpaid bills cannot request new appointments

**Implementation:**
- Validated in `PatientController::requestAppointment()`
- Validated in `AppointmentController::bookAppointment()`
- Checks `has_outstanding_bill` flag
- Returns HTTP 403 if outstanding bill exists

**Workflow:**
1. Appointment completed → Bill created (status: unpaid)
2. Patient `has_outstanding_bill` = TRUE
3. Patient tries to book → BLOCKED
4. Patient pays bill → Bill status = paid
5. Patient `has_outstanding_bill` = FALSE
6. Patient can book again

### Rule 3: Unique Dentist ID
**Rule:** Each dentist gets unique ID on registration

**Format:** `DEN{YEAR}{4-digit-number}`
**Example:** `DEN20250001`

**Implementation:**
```php
private function generateUniqueId() {
    return 'DEN' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}
```

### Rule 4: Appointment Confirmation
**Rule:** Appointments must be confirmed with email notification

**Implementation:**
- Status: requested → confirmed
- `confirmed_at` timestamp recorded
- Email sent to patient (SMTP placeholder)

### Rule 5: Role-Based Access Control
**Rules:**
- Office Manager: Full system access
- Dentist: View their appointments only
- Patient: View/manage their appointments only

**Implementation:**
```php
AuthMiddleware::requireRole(['office_manager', 'dentist']);
```

---

## 🧪 Testing Guide

### Using Postman

#### Import Collection
1. Open Postman
2. Import `ADS_Postman_Collection.json`
3. Set variables:
   - `base_url`: `http://localhost/api`
   - `token`: Auto-populated after login

#### Test Workflow 1: Office Manager
```
1. POST /auth/login (manager@ads.com)
2. POST /dentists/register
3. POST /patients/enroll
4. POST /appointments (book appointment)
5. GET /appointments (view all)
```

#### Test Workflow 2: Dentist
```
1. POST /auth/login (dentist credentials)
2. GET /dentists/appointments
3. Verify patient details visible
```

#### Test Workflow 3: Patient
```
1. POST /auth/login (patient credentials)
2. POST /patients/appointments/request
3. GET /patients/appointments
4. PUT /patients/appointments/1/cancel
```

#### Test Workflow 4: Business Rules
```
# Test weekly limit
1. Book 5 appointments for same dentist, same week
2. Try 6th → Should fail with 403

# Test outstanding bill
1. Set patient has_outstanding_bill = true
2. Try to request appointment → Should fail with 403
```

### Using cURL

#### Test Login
```bash
curl -X POST http://localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"manager@ads.com","password":"password"}'
```

#### Test Register Dentist
```bash
curl -X POST http://localhost/api/dentists/register \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "first_name": "John",
    "last_name": "Smith",
    "contact_phone": "555-1234",
    "email": "dentist@example.com",
    "specialization": "Orthodontics",
    "password": "password123"
  }'
```

---

## 🔒 Security Features

### 1. JWT Authentication
- **Algorithm:** HMAC SHA256
- **Expiration:** 24 hours
- **Structure:** header.payload.signature
- **Transmission:** Authorization: Bearer {token}

### 2. Password Security
- **Algorithm:** bcrypt (PASSWORD_BCRYPT)
- **Cost Factor:** 10
- **Salt:** Auto-generated per password
- **Storage:** Only hashed passwords stored

### 3. SQL Injection Prevention
- **Method:** PDO Prepared Statements
- **All queries use parameter binding**
- **No direct SQL concatenation**

Example:
```php
$query = "SELECT * FROM users WHERE email = :email";
$stmt = $this->conn->prepare($query);
$stmt->bindParam(':email', $email);
$stmt->execute();
```

### 4. Role-Based Access Control
- **Roles:** office_manager, dentist, patient
- **Enforcement:** Middleware on every request
- **Granularity:** Endpoint-level

### 5. CORS Configuration
```php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
```

### 6. Input Validation
- Required field validation
- Email format validation
- Data type checking
- Length constraints

---

## 📁 Project Structure

```
ads-dental-system/
│
├── config/
│   ├── database.php          # Database connection
│   └── jwt.php                # JWT implementation
│
├── models/
│   ├── User.php               # Abstract base
│   ├── OfficeManager.php
│   ├── Dentist.php
│   ├── Patient.php
│   ├── Appointment.php
│   ├── Surgery.php
│   └── Bill.php
│
├── repositories/
│   ├── UserRepository.php
│   ├── DentistRepository.php
│   ├── PatientRepository.php
│   ├── AppointmentRepository.php
│   ├── SurgeryRepository.php
│   └── BillRepository.php
│
├── controllers/
│   ├── AuthController.php
│   ├── DentistController.php
│   ├── PatientController.php
│   ├── AppointmentController.php
│   └── SurgeryController.php
│
├── middleware/
│   └── AuthMiddleware.php
│
├── database/
│   └── schema.sql
│
├── docs/
│   ├── README.md
│   └── Postman_Collection.json
│
├── .htaccess
├── index.php                  # Entry point
└── README.md
```

---

## 🎯 Future Enhancements

### Phase 2 Features
1. Email notifications (SMTP)
2. Advanced scheduling
3. Reporting dashboard
4. Payment gateway integration
5. Patient medical history

### Technical Improvements
1. Redis caching
2. Rate limiting
3. API versioning
4. Comprehensive logging
5. Unit testing
6. CI/CD pipeline

---

## 📞 Support

**Email:** support@ads-dental.com  
**Documentation:** See `/docs` folder  
**API Reference:** Complete endpoint documentation above

---

## 🔧 Troubleshooting

### Common Issues

#### Issue 1: "Access denied" on login
**Solution:**
- Verify database credentials in `config/database.php`
- Check if default user exists in database
- Verify password hash is correct

#### Issue 2: "Route not found" errors
**Solution:**
- Verify `.htaccess` file exists and mod_rewrite is enabled
- Check Apache/Nginx configuration
- Ensure all requests route through `index.php`

#### Issue 3: "Invalid token" errors
**Solution:**
- Check JWT secret key is set in `config/jwt.php`
- Verify token is passed in Authorization header
- Check token expiration (24 hours)

#### Issue 4: CORS errors in browser
**Solution:**
- Add CORS headers to `.htaccess` or server config
- For production, replace `*` with specific domain
- Ensure OPTIONS method is handled

#### Issue 5: Database connection fails
**Solution:**
```bash
# Test MySQL connection
mysql -u your_username -p

# Verify database exists
SHOW DATABASES;

# Check user permissions
SHOW GRANTS FOR 'your_username'@'localhost';
```

---

## 📊 Database Design Principles

### Normalization
- **3NF Compliance:** All tables follow Third Normal Form
- **No data redundancy:** Each fact stored once
- **Referential integrity:** Foreign keys enforce relationships

### Indexing Strategy
- **Primary Keys:** All tables have auto-increment PKs
- **Foreign Keys:** Indexed for join performance
- **Email Lookups:** Index on users.email
- **Date Queries:** Index on appointments.appointment_date
- **Status Filters:** Index on status columns

### Data Integrity
- **Cascading Deletes:** User deletion removes related records
- **SET NULL:** Deleted dentist/surgery keeps appointment history
- **ENUM Constraints:** Valid values enforced at DB level
- **NOT NULL:** Required fields enforced

### Performance Optimization
```sql
-- Composite indexes for common queries
CREATE INDEX idx_dentist_date ON appointments(dentist_id, appointment_date);
CREATE INDEX idx_patient_status ON appointments(patient_id, status);

-- Covering indexes for frequent selects
CREATE INDEX idx_user_auth ON users(email, password_hash, role);
```

---

## 🎨 API Design Principles

### RESTful Conventions
- **Resource-based URLs:** `/patients`, `/appointments`
- **HTTP methods:** GET (read), POST (create), PUT (update), DELETE (delete)
- **Plural nouns:** Use `/dentists` not `/dentist`
- **Nested resources:** `/patients/appointments` for relationships

### Response Standards
```json
// Success Response
{
    "message": "Operation successful",
    "data": {...},
    "meta": {
        "timestamp": "2025-10-21T10:00:00Z"
    }
}

// Error Response
{
    "message": "Error description",
    "error_code": "BUSINESS_RULE_VIOLATION",
    "details": "Dentist has reached weekly limit"
}
```

### Versioning Strategy
```
Current: /api/endpoint
Future: /api/v2/endpoint
```

### Rate Limiting (Recommended)
```php
// Future implementation
$rateLimit = 100; // requests per minute
$userRequests = getRateLimitCount($userId);
if ($userRequests > $rateLimit) {
    http_response_code(429);
    echo json_encode(['message' => 'Rate limit exceeded']);
    exit();
}
```

---

## 🔐 Security Best Practices

### Production Checklist

#### 1. Environment Configuration
```php
// .env file (DO NOT commit to version control)
DB_HOST=localhost
DB_NAME=ads_dental
DB_USER=ads_user
DB_PASS=secure_password_here
JWT_SECRET=your_64_char_secret_key
APP_ENV=production
```

#### 2. HTTPS Enforcement
```apache
# Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

#### 3. Security Headers
```php
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Strict-Transport-Security: max-age=31536000");
```

#### 4. Input Sanitization
```php
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
```

#### 5. Error Handling
```php
// Production: Hide detailed errors
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_log('Error message', 3, '/var/log/php_errors.log');
```

#### 6. Database Security
```sql
-- Create dedicated database user
CREATE USER 'ads_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON ads_dental.* TO 'ads_user'@'localhost';
FLUSH PRIVILEGES;

-- Revoke unnecessary privileges
REVOKE ALL PRIVILEGES ON *.* FROM 'ads_user'@'localhost';
```

---

## 📈 Performance Optimization

### Database Optimization

#### Query Optimization
```sql
-- Use EXPLAIN to analyze queries
EXPLAIN SELECT * FROM appointments 
WHERE dentist_id = 1 AND appointment_date > '2025-10-21';

-- Add indexes for slow queries
CREATE INDEX idx_appointment_search 
ON appointments(dentist_id, appointment_date, status);
```

#### Connection Pooling
```php
// Reuse database connections
class Database {
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
}
```

### Caching Strategy (Future)
```php
// Redis caching example
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

// Cache dentist list for 1 hour
$cacheKey = 'dentists:all';
$dentists = $redis->get($cacheKey);

if (!$dentists) {
    $dentists = $dentistRepo->getAll();
    $redis->setex($cacheKey, 3600, json_encode($dentists));
}
```

### Response Compression
```apache
# Enable gzip compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE application/json
    AddOutputFilterByType DEFLATE text/html
</IfModule>
```

---

## 🧪 Advanced Testing

### Unit Testing Structure (Recommended)
```php
// tests/UserRepositoryTest.php
class UserRepositoryTest extends PHPUnit\Framework\TestCase {
    
    public function testFindByEmail() {
        $db = $this->createMock(PDO::class);
        $repo = new UserRepository($db);
        
        $user = $repo->findByEmail('test@example.com');
        
        $this->assertNotNull($user);
        $this->assertEquals('test@example.com', $user['email']);
    }
    
    public function testCreateUser() {
        $repo = new UserRepository($this->getTestDatabase());
        
        $userId = $repo->create('new@example.com', 'password', 'patient');
        
        $this->assertGreaterThan(0, $userId);
    }
}
```

### Integration Testing
```php
// tests/AppointmentFlowTest.php
class AppointmentFlowTest extends PHPUnit\Framework\TestCase {
    
    public function testCompleteAppointmentBookingFlow() {
        // 1. Office manager logs in
        $loginResponse = $this->apiCall('POST', '/auth/login', [
            'email' => 'manager@ads.com',
            'password' => 'password'
        ]);
        
        $token = $loginResponse['token'];
        
        // 2. Register dentist
        $dentistResponse = $this->apiCall('POST', '/dentists/register', [
            'first_name' => 'Test',
            'last_name' => 'Dentist',
            // ... other fields
        ], $token);
        
        // 3. Enroll patient
        $patientResponse = $this->apiCall('POST', '/patients/enroll', [
            // ... patient data
        ], $token);
        
        // 4. Book appointment
        $appointmentResponse = $this->apiCall('POST', '/appointments', [
            'patient_id' => $patientResponse['patient_id'],
            'dentist_id' => $dentistResponse['dentist_id'],
            // ... other fields
        ], $token);
        
        $this->assertEquals(201, $appointmentResponse['status_code']);
    }
}
```

### Load Testing (Apache Bench)
```bash
# Test 1000 requests with 10 concurrent users
ab -n 1000 -c 10 -H "Authorization: Bearer TOKEN" \
   http://localhost/api/appointments

# Monitor results
# Requests per second
# Time per request
# Transfer rate
```

---

## 📚 Additional Resources

### Helpful Links
- **PHP PDO Documentation:** https://www.php.net/manual/en/book.pdo.php
- **JWT Standard:** https://jwt.io/introduction
- **MySQL Performance:** https://dev.mysql.com/doc/refman/8.0/en/optimization.html
- **REST API Best Practices:** https://restfulapi.net/

### Recommended Reading
1. "RESTful Web APIs" by Leonard Richardson
2. "High Performance MySQL" by Baron Schwartz
3. "PHP Objects, Patterns, and Practice" by Matt Zandstra

### Code Standards
- **PSR-1:** Basic Coding Standard
- **PSR-4:** Autoloading Standard
- **PSR-12:** Extended Coding Style

---

## 🤝 Contributing

### Development Workflow
1. Fork the repository
2. Create feature branch: `git checkout -b feature/new-feature`
3. Commit changes: `git commit -am 'Add new feature'`
4. Push to branch: `git push origin feature/new-feature`
5. Submit pull request

### Code Review Checklist
- [ ] Code follows PSR standards
- [ ] All tests pass
- [ ] Security vulnerabilities checked
- [ ] Documentation updated
- [ ] Database migrations included
- [ ] API endpoints documented

### Commit Message Format
```
feat: Add patient medical history feature
fix: Resolve weekly appointment limit bug
docs: Update API documentation
refactor: Improve repository pattern implementation
test: Add unit tests for AuthMiddleware
```

---

## 📝 Changelog

### Version 1.0.0 (2025-10-21)
**Initial Release**
- ✅ Complete domain model implementation
- ✅ JWT authentication system
- ✅ Role-based access control
- ✅ 16 RESTful API endpoints
- ✅ Business rule enforcement
- ✅ MySQL database schema
- ✅ Repository pattern with PDO
- ✅ Comprehensive documentation

### Upcoming Features
- Email notification system
- Advanced reporting dashboard
- Payment processing integration
- Mobile app API support
- Real-time appointment availability
- SMS appointment reminders

---

## 🏆 System Capabilities Summary

### ✅ Fully Implemented Features

#### User Management
- [x] Office Manager registration and authentication
- [x] Dentist registration with unique IDs
- [x] Patient enrollment with complete profiles
- [x] Role-based access control (3 roles)
- [x] Secure password hashing (bcrypt)
- [x] JWT token-based authentication

#### Appointment Management
- [x] Online appointment requests by patients
- [x] Phone-based appointment booking by office manager
- [x] Appointment confirmation workflow
- [x] Appointment cancellation by patients
- [x] Appointment rescheduling
- [x] Status tracking (requested, confirmed, cancelled, completed)

#### Business Rules
- [x] Dentist weekly limit enforcement (max 5 per week)
- [x] Outstanding bill validation
- [x] Unique dentist ID generation
- [x] Appointment confirmation with timestamps

#### Surgery Management
- [x] Surgery location creation
- [x] Surgery information display
- [x] Surgery assignment to appointments

#### Data Access
- [x] Dentists view their appointments with patient details
- [x] Patients view their appointments with dentist info
- [x] Office managers view all system data
- [x] Surgery location details in appointments

#### Security
- [x] JWT authentication (HMAC SHA256)
- [x] Password hashing (bcrypt)
- [x] SQL injection prevention (PDO)
- [x] Role-based authorization
- [x] CORS configuration

---

## 📞 Contact & Support

### Technical Support
- **Email:** support@ads-dental.com
- **Documentation:** Full documentation in `/docs`
- **Issue Tracker:** Report bugs via GitHub issues
- **Response Time:** 24-48 hours

### Development Team
- **Lead Developer:** System Architect
- **Database Administrator:** Available for query optimization
- **Security Consultant:** Available for security audits

### Office Hours
- **Monday-Friday:** 9:00 AM - 5:00 PM (MST)
- **Emergency Support:** Available 24/7 for critical issues

---

## 📜 License

**Copyright © 2025 Advantis Dental Surgeries, LLC**

All rights reserved. This software and associated documentation files (the "Software") are proprietary and confidential. Unauthorized copying, distribution, or modification of this Software is strictly prohibited.

### Terms of Use
- Internal use only by ADS employees
- No redistribution without written permission
- Regular security audits required
- Compliance with HIPAA regulations

---

## 🎓 Training & Onboarding

### For Office Managers
1. Review user management sections
2. Practice dentist registration
3. Learn patient enrollment process
4. Master appointment booking workflow

### For Dentists
1. Understand login process
2. Learn to view appointments
3. Review patient information access
4. Familiarize with surgery locations

### For Technical Staff
1. Complete installation guide
2. Review architecture documentation
3. Study security features
4. Practice API testing with Postman

---

## 🔍 Quick Reference

### Default Credentials
```
Office Manager:
Email: manager@ads.com
Password: password
```

### Common API Calls
```bash
# Login
POST /api/auth/login

# Register Dentist
POST /api/dentists/register

# Enroll Patient
POST /api/patients/enroll

# Book Appointment
POST /api/appointments

# View Appointments (Dentist)
GET /api/dentists/appointments

# View Appointments (Patient)
GET /api/patients/appointments
```

### Database Quick Commands
```sql
-- Check appointment count
SELECT COUNT(*) FROM appointments;

-- View dentist weekly appointments
SELECT dentist_id, WEEK(appointment_date) as week, COUNT(*) 
FROM appointments 
GROUP BY dentist_id, week;

-- Find patients with outstanding bills
SELECT * FROM patients WHERE has_outstanding_bill = TRUE;
```

---

## ✅ Final Checklist

Before deploying to production:

- [ ] Database credentials updated
- [ ] JWT secret key changed
- [ ] HTTPS enabled
- [ ] CORS configured for production domain
- [ ] Error logging enabled
- [ ] Backup strategy implemented
- [ ] Security headers added
- [ ] Rate limiting configured
- [ ] Email SMTP configured
- [ ] Database indexes optimized
- [ ] All endpoints tested
- [ ] Documentation reviewed
- [ ] User training completed

---

**Document Version:** 1.0.0  
**Last Updated:** October 21, 2025  
**Maintained By:** Development Team  
**Next Review:** November 21, 2025

---

**End of Documentation**

For the latest updates and additional resources, visit the project repository or contact the development team.