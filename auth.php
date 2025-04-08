<?php
require_once 'db.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function register($data) {
        $conn = $this->db->connect();
        
        // Validate input
        $required = ['username', 'email', 'password', 'first_name', 'last_name', 'role'];
        foreach($required as $field) {
            if(empty($data[$field])) {
                throw new Exception("$field is required");
            }
        }
        
        if(!in_array($data['role'], ['customer', 'tailor', 'manager', 'admin', 'cashier'])) {
            throw new Exception("Invalid role");
        }
        
        // Hash password
        $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT);
        
        try {
            $conn->beginTransaction();
            
            // Insert user
            $stmt = $conn->prepare("INSERT INTO users 
                (username, email, password_hash, first_name, last_name, phone, address, role) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['username'],
                $data['email'],
                $hashed_password,
                $data['first_name'],
                $data['last_name'],
                $data['phone'] ?? null,
                $data['address'] ?? null,
                $data['role']
            ]);
            
            $user_id = $conn->lastInsertId();
            
            // Insert role-specific details
            if($data['role'] === 'customer') {
                $stmt = $conn->prepare("INSERT INTO customer_details 
                    (customer_id, measurements, preferred_payment_method) 
                    VALUES (?, ?, ?)");
                $stmt->execute([
                    $user_id,
                    $data['measurements'] ?? null,
                    $data['preferred_payment_method'] ?? null
                ]);
            } elseif($data['role'] === 'tailor') {
                $stmt = $conn->prepare("INSERT INTO tailor_details 
                    (tailor_id, specialty, experience_years, hourly_rate, status) 
                    VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $user_id,
                    $data['specialty'] ?? null,
                    $data['experience_years'] ?? 0,
                    $data['hourly_rate'] ?? 0,
                    $data['status'] ?? 'available'
                ]);
            } elseif($data['role'] === 'manager') {
                $stmt = $conn->prepare("INSERT INTO manager_details 
                    (manager_id, department, access_level) 
                    VALUES (?, ?, ?)");
                $stmt->execute([
                    $user_id,
                    $data['department'] ?? null,
                    $data['access_level'] ?? 1
                ]);
            }
            
            $conn->commit();
            
            // Log the registration
            $this->logAudit($user_id, 'register', 'user', null, json_encode($data));
            
            return $user_id;
        } catch(PDOException $e) {
            $conn->rollBack();
            error_log("Registration error: " . $e->getMessage());
            throw new Exception("Registration failed");
        }
    }
    
    public function login($username, $password) {
        $conn = $this->db->connect();
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND is_active = TRUE");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$user || !password_verify($password, $user['password_hash'])) {
            throw new Exception("Invalid username or password");
        }
        
        // Update last login
        $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        
        // Start session
        session_start();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        // Log the login
        $this->logAudit($user['user_id'], 'login', 'user', null, null);
        
        return $user;
    }
    
    public function logout() {
        session_start();
        
        if(isset($_SESSION['user_id'])) {
            // Log the logout
            $this->logAudit($_SESSION['user_id'], 'logout', 'user', null, null);
        }
        
        session_unset();
        session_destroy();
    }
    
    public function isLoggedIn() {
        session_start();
        return isset($_SESSION['user_id']);
    }
    
    public function getUserRole() {
        session_start();
        return $_SESSION['role'] ?? null;
    }
    
    public function getCurrentUserId() {
        session_start();
        return $_SESSION['user_id'] ?? null;
    }
    
    private function logAudit($user_id, $action, $entity_type, $entity_id, $details) {
        $conn = $this->db->connect();
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $stmt = $conn->prepare("INSERT INTO audit_log 
            (user_id, action, entity_type, entity_id, old_value, new_value, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $user_id,
            $action,
            $entity_type,
            $entity_id,
            null,
            $details,
            $ip,
            $user_agent
        ]);
    }
}
?>