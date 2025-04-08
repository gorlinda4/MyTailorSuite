<?php
require_once 'db.php';

class OrderSystem {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function createOrder($customer_id, $garment_type_id, $data) {
        $conn = $this->db->connect();
        
        try {
            $conn->beginTransaction();
            
            // Create order
            $stmt = $conn->prepare("INSERT INTO orders 
                (customer_id, garment_type_id, measurements, design_notes, design_file_path, expected_delivery_date) 
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $customer_id,
                $garment_type_id,
                $data['measurements'] ?? null,
                $data['design_notes'] ?? null,
                $data['design_file_path'] ?? null,
                $data['expected_delivery_date'] ?? null
            ]);
            
            $order_id = $conn->lastInsertId();
            
            // Create initial work progress
            $stmt = $conn->prepare("INSERT INTO work_progress 
                (order_id, tailor_id, status, notes) 
                VALUES (?, ?, 'pending', 'Order created')");
            $stmt->execute([$order_id, null]);
            
            // Create notification for managers
            $this->notifyManagers($order_id);
            
            $conn->commit();
            
            return $order_id;
            
        } catch(PDOException $e) {
            $conn->rollBack();
            error_log("Order creation error: " . $e->getMessage());
            throw new Exception("Order creation failed");
        }
    }
    
    public function getOrder($order_id) {
        $conn = $this->db->connect();
        
        $stmt = $conn->prepare("SELECT o.*, gt.name as garment_type, gt.base_price,
                               u.first_name, u.last_name, u.email, u.phone
                               FROM orders o
                               JOIN garment_types gt ON o.garment_type_id = gt.garment_type_id
                               JOIN users u ON o.customer_id = u.user_id
                               WHERE o.order_id = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$order) {
            throw new Exception("Order not found");
        }
        
        // Get work progress
        $stmt = $conn->prepare("SELECT wp.*, u.first_name, u.last_name 
                               FROM work_progress wp
                               LEFT JOIN users u ON wp.tailor_id = u.user_id
                               WHERE wp.order_id = ?
                               ORDER BY wp.progress_date DESC");
        $stmt->execute([$order_id]);
        $order['progress'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get payments
        $stmt = $conn->prepare("SELECT p.* FROM payments p WHERE p.order_id = ?");
        $stmt->execute([$order_id]);
        $order['payments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $order;
    }
    
    public function updateOrderStatus($order_id, $status, $tailor_id = null, $notes = null) {
        $conn = $this->db->connect();
        
        try {
            $conn->beginTransaction();
            
            // Update order status
            $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
            $stmt->execute([$status, $order_id]);
            
            // Add work progress
            $stmt = $conn->prepare("INSERT INTO work_progress 
                (order_id, tailor_id, status, notes) 
                VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $order_id,
                $tailor_id,
                $status,
                $notes ?? "Status changed to $status"
            ]);
            
            // Get customer ID for notification
            $stmt = $conn->prepare("SELECT customer_id FROM orders WHERE order_id = ?");
            $stmt->execute([$order_id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($order) {
                // Create notification for customer
                $this->createNotification(
                    $order['customer_id'],
                    'Order Status Update',
                    "Your order #{$order_id} status has been updated to {$status}",
                    'order',
                    $order_id
                );
            }
            
            $conn->commit();
            
        } catch(PDOException $e) {
            $conn->rollBack();
            error_log("Order status update error: " . $e->getMessage());
            throw new Exception("Order status update failed");
        }
    }
    
    public function assignOrderToTailor($order_id, $tailor_id, $due_date, $priority = 'medium', $notes = null) {
        $conn = $this->db->connect();
        
        try {
            $conn->beginTransaction();
            
            // Assign order to tailor
            $stmt = $conn->prepare("UPDATE orders 
                                  SET assigned_tailor_id = ?, 
                                      expected_delivery_date = ?,
                                      priority = ?,
                                      status = 'in_progress'
                                  WHERE order_id = ?");
            $stmt->execute([
                $tailor_id,
                $due_date,
                $priority,
                $order_id
            ]);
            
            // Add work progress
            $stmt = $conn->prepare("INSERT INTO work_progress 
                (order_id, tailor_id, status, notes) 
                VALUES (?, ?, 'assigned', ?)");
            $stmt->execute([
                $order_id,
                $tailor_id,
                $notes ?? "Order assigned to tailor with due date $due_date"
            ]);
            
            // Update tailor status
            $stmt = $conn->prepare("UPDATE tailor_details SET status = 'busy' WHERE tailor_id = ?");
            $stmt->execute([$tailor_id]);
            
            // Create notification for tailor
            $this->createNotification(
                $tailor_id,
                'New Order Assigned',
                "You have been assigned order #{$order_id} with due date {$due_date}",
                'order',
                $order_id
            );
            
            $conn->commit();
            
        } catch(PDOException $e) {
            $conn->rollBack();
            error_log("Order assignment error: " . $e->getMessage());
            throw new Exception("Order assignment failed");
        }
    }
    
    public function getCustomerOrders($customer_id) {
        $conn = $this->db->connect();
        
        $stmt = $conn->prepare("SELECT o.*, gt.name as garment_type, gt.base_price
                               FROM orders o
                               JOIN garment_types gt ON o.garment_type_id = gt.garment_type_id
                               WHERE o.customer_id = ?
                               ORDER BY o.order_date DESC");
        $stmt->execute([$customer_id]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($orders as &$order) {
            // Get latest work progress
            $stmt = $conn->prepare("SELECT status FROM work_progress 
                                  WHERE order_id = ? 
                                  ORDER BY progress_date DESC LIMIT 1");
            $stmt->execute([$order['order_id']]);
            $progress = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $order['latest_status'] = $progress['status'] ?? 'pending';
            
            // Check if payment is complete
            $stmt = $conn->prepare("SELECT COUNT(*) as payment_count FROM payments 
                                  WHERE order_id = ? AND status = 'completed'");
            $stmt->execute([$order['order_id']]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $order['is_paid'] = ($payment['payment_count'] > 0);
        }
        
        return $orders;
    }
    
    public function getTailorOrders($tailor_id) {
        $conn = $this->db->connect();
        
        $stmt = $conn->prepare("SELECT o.*, gt.name as garment_type, gt.base_price,
                               u.first_name, u.last_name
                               FROM orders o
                               JOIN garment_types gt ON o.garment_type_id = gt.garment_type_id
                               JOIN users u ON o.customer_id = u.user_id
                               WHERE o.assigned_tailor_id = ?
                               ORDER BY o.priority DESC, o.expected_delivery_date ASC");
        $stmt->execute([$tailor_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAllOrders($filters = []) {
        $conn = $this->db->connect();
        
        $where = [];
        $params = [];
        
        if(!empty($filters['status'])) {
            $where[] = "o.status = ?";
            $params[] = $filters['status'];
        }
        
        if(!empty($filters['customer_id'])) {
            $where[] = "o.customer_id = ?";
            $params[] = $filters['customer_id'];
        }
        
        if(!empty($filters['tailor_id'])) {
            $where[] = "o.assigned_tailor_id = ?";
            $params[] = $filters['tailor_id'];
        }
        
        if(!empty($filters['date_from'])) {
            $where[] = "o.order_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if(!empty($filters['date_to'])) {
            $where[] = "o.order_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        $where_clause = $where ? "WHERE " . implode(" AND ", $where) : "";
        
        $query = "SELECT o.*, gt.name as garment_type, gt.base_price,
                 u.first_name, u.last_name, u.email,
                 t.first_name as tailor_first_name, t.last_name as tailor_last_name
                 FROM orders o
                 JOIN garment_types gt ON o.garment_type_id = gt.garment_type_id
                 JOIN users u ON o.customer_id = u.user_id
                 LEFT JOIN users t ON o.assigned_tailor_id = t.user_id
                 $where_clause
                 ORDER BY o.order_date DESC";
        
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($orders as &$order) {
            // Check if payment is complete
            $stmt = $conn->prepare("SELECT COUNT(*) as payment_count FROM payments 
                                  WHERE order_id = ? AND status = 'completed'");
            $stmt->execute([$order['order_id']]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $order['is_paid'] = ($payment['payment_count'] > 0);
        }
        
        return $orders;
    }
    
    private function notifyManagers($order_id) {
        $conn = $this->db->connect();
        
        // Get managers
        $stmt = $conn->prepare("SELECT u.user_id FROM users u WHERE u.role = 'manager'");
        $stmt->execute();
        $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($managers as $manager) {
            $this->createNotification(
                $manager['user_id'],
                'New Order Requires Approval',
                "A new order #{$order_id} has been created and requires approval",
                'order',
                $order_id
            );
        }
    }
    
    private function createNotification($user_id, $title, $message, $entity_type, $entity_id) {
        $conn = $this->db->connect();
        
        $stmt = $conn->prepare("INSERT INTO notifications 
            (user_id, title, message, related_entity_type, related_entity_id) 
            VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $user_id,
            $title,
            $message,
            $entity_type,
            $entity_id
        ]);
    }
}
?>