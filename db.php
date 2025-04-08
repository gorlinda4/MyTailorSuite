<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'tailor_suite';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name}", 
                $this->username, 
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch(PDOException $e) {
            error_log("Connection error: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }

        return $this->conn;
    }
}

// Create database tables if they don't exist
function initializeDatabase() {
    try {
        $db = new Database();
        $conn = $db->connect();
        
        // Users Table
        $conn->exec("CREATE TABLE IF NOT EXISTS users (
            user_id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            phone VARCHAR(20),
            address TEXT,
            role ENUM('customer', 'tailor', 'manager', 'admin', 'cashier') NOT NULL,
            profile_pic VARCHAR(255),
            last_login DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            is_active BOOLEAN DEFAULT TRUE
        )");
        
        // Customer-specific details
        $conn->exec("CREATE TABLE IF NOT EXISTS customer_details (
            customer_id INT PRIMARY KEY,
            measurements TEXT,
            preferred_payment_method ENUM('mpesa', 'paypal', 'credit_card', 'bank_transfer', 'cash'),
            FOREIGN KEY (customer_id) REFERENCES users(user_id) ON DELETE CASCADE
        )");
        
        // Tailor-specific details
        $conn->exec("CREATE TABLE IF NOT EXISTS tailor_details (
            tailor_id INT PRIMARY KEY,
            specialty VARCHAR(100),
            experience_years INT,
            hourly_rate DECIMAL(10,2),
            status ENUM('available', 'busy', 'on_leave'),
            rating DECIMAL(3,2),
            FOREIGN KEY (tailor_id) REFERENCES users(user_id) ON DELETE CASCADE
        )");
        
        // Manager-specific details
        $conn->exec("CREATE TABLE IF NOT EXISTS manager_details (
            manager_id INT PRIMARY KEY,
            department VARCHAR(100),
            access_level INT,
            FOREIGN KEY (manager_id) REFERENCES users(user_id) ON DELETE CASCADE
        )");
        
        // Garment Types
        $conn->exec("CREATE TABLE IF NOT EXISTS garment_types (
            garment_type_id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            description TEXT,
            base_price DECIMAL(10,2),
            estimated_production_days INT
        )");
        
        // Materials/Inventory
        $conn->exec("CREATE TABLE IF NOT EXISTS materials (
            material_id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            type VARCHAR(50),
            quantity DECIMAL(10,2),
            unit VARCHAR(20),
            low_stock_threshold DECIMAL(10,2),
            cost_per_unit DECIMAL(10,2),
            supplier_id INT,
            last_restocked DATE
        )");
        
        // Suppliers
        $conn->exec("CREATE TABLE IF NOT EXISTS suppliers (
            supplier_id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            contact_person VARCHAR(100),
            phone VARCHAR(20),
            email VARCHAR(100),
            materials_provided TEXT,
            rating DECIMAL(3,2),
            last_order_date DATE
        )");
        
        // Orders
        $conn->exec("CREATE TABLE IF NOT EXISTS orders (
            order_id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT NOT NULL,
            garment_type_id INT NOT NULL,
            order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            status ENUM('pending', 'approved', 'in_progress', 'completed', 'delivered', 'cancelled') DEFAULT 'pending',
            assigned_tailor_id INT,
            measurements TEXT,
            design_notes TEXT,
            design_file_path VARCHAR(255),
            expected_delivery_date DATE,
            actual_delivery_date DATE,
            priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
            FOREIGN KEY (customer_id) REFERENCES users(user_id),
            FOREIGN KEY (garment_type_id) REFERENCES garment_types(garment_type_id),
            FOREIGN KEY (assigned_tailor_id) REFERENCES users(user_id)
        )");
        
        // Payments
        $conn->exec("CREATE TABLE IF NOT EXISTS payments (
            payment_id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            payment_method ENUM('mpesa', 'paypal', 'credit_card', 'bank_transfer', 'cash') NOT NULL,
            transaction_id VARCHAR(100),
            status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
            receipt_path VARCHAR(255),
            FOREIGN KEY (order_id) REFERENCES orders(order_id)
        )");
        
        // M-Pesa Transactions
        $conn->exec("CREATE TABLE IF NOT EXISTS mpesa_transactions (
            transaction_id INT AUTO_INCREMENT PRIMARY KEY,
            payment_id INT NOT NULL,
            phone_number VARCHAR(20) NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            mpesa_code VARCHAR(50),
            checkout_request_id VARCHAR(100),
            merchant_request_id VARCHAR(100),
            result_code VARCHAR(20),
            result_desc VARCHAR(255),
            transaction_date DATETIME,
            FOREIGN KEY (payment_id) REFERENCES payments(payment_id)
        )");
        
        // PayPal Transactions
        $conn->exec("CREATE TABLE IF NOT EXISTS paypal_transactions (
            transaction_id INT AUTO_INCREMENT PRIMARY KEY,
            payment_id INT NOT NULL,
            paypal_id VARCHAR(100),
            payer_email VARCHAR(100),
            payer_name VARCHAR(100),
            currency VARCHAR(10),
            status VARCHAR(50),
            transaction_date DATETIME,
            FOREIGN KEY (payment_id) REFERENCES payments(payment_id)
        )");
        
        // Credit Card Transactions
        $conn->exec("CREATE TABLE IF NOT EXISTS credit_card_transactions (
            transaction_id INT AUTO_INCREMENT PRIMARY KEY,
            payment_id INT NOT NULL,
            card_last_four VARCHAR(4),
            card_type VARCHAR(20),
            authorization_code VARCHAR(100),
            transaction_date DATETIME,
            FOREIGN KEY (payment_id) REFERENCES payments(payment_id)
        )");
        
        // Work Progress
        $conn->exec("CREATE TABLE IF NOT EXISTS work_progress (
            progress_id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            tailor_id INT NOT NULL,
            progress_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            status ENUM('started', 'cutting', 'sewing', 'finishing', 'completed') NOT NULL,
            notes TEXT,
            hours_spent DECIMAL(5,2),
            FOREIGN KEY (order_id) REFERENCES orders(order_id),
            FOREIGN KEY (tailor_id) REFERENCES users(user_id)
        )");
        
        // Notifications
        $conn->exec("CREATE TABLE IF NOT EXISTS notifications (
            notification_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(100) NOT NULL,
            message TEXT NOT NULL,
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            related_entity_type ENUM('order', 'payment', 'inventory', 'system'),
            related_entity_id INT,
            FOREIGN KEY (user_id) REFERENCES users(user_id)
        )");
        
        // System Settings
        $conn->exec("CREATE TABLE IF NOT EXISTS system_settings (
            setting_id INT AUTO_INCREMENT PRIMARY KEY,
            setting_name VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT,
            description TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        // Audit Log
        $conn->exec("CREATE TABLE IF NOT EXISTS audit_log (
            log_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            action VARCHAR(100) NOT NULL,
            entity_type VARCHAR(50),
            entity_id INT,
            old_value TEXT,
            new_value TEXT,
            ip_address VARCHAR(50),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(user_id)
        )");
        
        // Insert default system settings if they don't exist
        $stmt = $conn->prepare("INSERT IGNORE INTO system_settings (setting_name, setting_value, description) VALUES 
            ('shop_name', 'TailorSuite', 'The name of the tailoring business'),
            ('timezone', 'Africa/Nairobi', 'System timezone'),
            ('currency', 'KES', 'Default currency'),
            ('mpesa_paybill', '', 'M-Pesa paybill number'),
            ('mpesa_passkey', '', 'M-Pesa passkey'),
            ('paypal_client_id', '', 'PayPal client ID'),
            ('paypal_secret', '', 'PayPal secret key')
        ");
        $stmt->execute();
        
    } catch(Exception $e) {
        error_log("Database initialization error: " . $e->getMessage());
        throw new Exception("Database initialization failed");
    }
}

// Initialize the database when this file is included
initializeDatabase();
?>