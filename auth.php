<?php
require_once 'db.php';

class Auth {
    private $db;
    private $table = 'users';

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function register($data) {
        $required = ['username', 'email', 'password', 'first_name', 'last_name', 'role'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['status' => 'error', 'message' => "$field is required"];
            }
        }

        $validRoles = ['customer', 'tailor', 'manager', 'admin', 'cashier'];
        if (!in_array($data['role'], $validRoles)) {
            return ['status' => 'error', 'message' => 'Invalid role'];
        }

        $stmt = $this->db->prepare("SELECT email FROM {$this->table} WHERE email = ? OR username = ?");
        $stmt->execute([$data['email'], $data['username']]);

        if ($stmt->rowCount() > 0) {
            return ['status' => 'error', 'message' => 'User already exists'];
        }

        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} 
            (username, email, password_hash, first_name, last_name, phone, address, role, profile_pic) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        try {
            $stmt->execute([
                $data['username'],
                $data['email'],
                $hashedPassword,
                $data['first_name'],
                $data['last_name'],
                $data['phone'] ?? null,
                $data['address'] ?? null,
                $data['role'],
                $data['profile_pic'] ?? null
            ]);

            $userId = $this->db->lastInsertId();
            $this->insertRoleSpecificData($userId, $data['role'], $data);

            return [
                'status' => 'success', 
                'message' => 'User registered successfully',
                'user_id' => $userId
            ];
        } catch (PDOException $e) {
            error_log("Registration Error: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Registration failed'];
        }
    }

    private function insertRoleSpecificData($userId, $role, $data) {
        switch ($role) {
            case 'customer':
                $stmt = $this->db->prepare("
                    INSERT INTO customer_details 
                    (customer_id, measurements, preferred_payment_method) 
                    VALUES (?, ?, ?)
                ");
                $measurements = json_encode($data['measurements'] ?? []);
                $stmt->execute([
                    $userId,
                    $measurements,
                    $data['preferred_payment_method'] ?? 'mpesa'
                ]);
                break;

            case 'tailor':
                $stmt = $this->db->prepare("
                    INSERT INTO tailor_details 
                    (tailor_id, specialty, experience_years, hourly_rate, status, rating) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $userId,
                    $data['specialty'] ?? 'General',
                    $data['experience_years'] ?? 0,
                    $data['hourly_rate'] ?? 0,
                    $data['status'] ?? 'available',
                    $data['rating'] ?? 0
                ]);
                break;

            case 'manager':
                $stmt = $this->db->prepare("
                    INSERT INTO manager_details 
                    (manager_id, department, access_level) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([
                    $userId,
                    $data['department'] ?? 'General',
                    $data['access_level'] ?? 1
                ]);
                break;
        }
    }

    public function login($username, $password) {
        $stmt = $this->db->prepare("
            SELECT u.*, 
                CASE 
                    WHEN u.role = 'customer' THEN cd.preferred_payment_method
                    WHEN u.role = 'tailor' THEN t.specialty
                    WHEN u.role = 'manager' THEN m.department
                    ELSE NULL
                END as role_detail
            FROM {$this->table} u
            LEFT JOIN customer_details cd ON u.id = cd.customer_id AND u.role = 'customer'
            LEFT JOIN tailor_details t ON u.id = t.tailor_id AND u.role = 'tailor'
            LEFT JOIN manager_details m ON u.id = m.manager_id AND u.role = 'manager'
            WHERE u.username = ? OR u.email = ?
        ");

        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['status' => 'error', 'message' => 'Invalid credentials'];
        }

        $this->updateLastLogin($user['id']);
        $this->startUserSession($user);

        return [
            'status' => 'success',
            'message' => 'Login successful',
            'user' => $user
        ];
    }

    private function updateLastLogin($userId) {
        $stmt = $this->db->prepare("
            UPDATE {$this->table} 
            SET last_login = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
    }

    private function startUserSession($user) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'role' => $user['role'],
            'profile_pic' => $user['profile_pic'],
            'role_detail' => $user['role_detail']
        ];
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        return ['status' => 'success', 'message' => 'Logged out successfully'];
    }

    public function isLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user']);
    }

    public function getUserRole() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user']['role'] ?? null;
    }

    public function getCurrentUser() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user'] ?? null;
    }
}
?>
