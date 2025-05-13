<?php
require_once 'db.php';

class Order {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function createOrder($data) {
        // Validate required fields
        $required = ['customer_id', 'garment_type_id', 'measurements'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['status' => 'error', 'message' => "$field is required"];
            }
        }

        try {
            // Calculate expected delivery date (7 days from now by default)
            $deliveryDate = date('Y-m-d', strtotime('+7 days'));
            if (!empty($data['delivery_date'])) {
                $deliveryDate = date('Y-m-d', strtotime($data['delivery_date']));
            }

            // Insert order
            $stmt = $this->db->prepare("
                INSERT INTO orders 
                (customer_id, garment_type_id, order_date, status, assigned_tailor_id, 
                 measurements, design_notes, design_file_path, expected_delivery_date, priority) 
                VALUES (?, ?, NOW(), 'pending', ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $data['customer_id'],
                $data['garment_type_id'],
                $data['assigned_tailor_id'] ?? null,
                json_encode($data['measurements']),
                $data['design_notes'] ?? '',
                $data['design_file_path'] ?? null,
                $deliveryDate,
                $data['priority'] ?? 'medium'
            ]);

            $orderId = $this->db->lastInsertId();

            // Create notification
            $this->createNotification(
                $data['customer_id'],
                'Order Created',
                "Your order #{$orderId} has been created successfully",
                'order',
                $orderId
            );

            return [
                'status' => 'success',
                'message' => 'Order created successfully',
                'order_id' => $orderId
            ];

        } catch (PDOException $e) {
            error_log("Order Creation Error: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Order creation failed'];
        }
    }

    public function updateOrder($orderId, $data) {
        try {
            $fields = [];
            $values = [];
            
            // Build dynamic update query
            foreach ($data as $key => $value) {
                if ($key === 'measurements') {
                    $fields[] = "measurements = ?";
                    $values[] = json_encode($value);
                } else {
                    $fields[] = "{$key} = ?";
                    $values[] = $value;
                }
            }
            
            $values[] = $orderId;
            
            $stmt = $this->db->prepare("
                UPDATE orders 
                SET " . implode(', ', $fields) . ", updated_at = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute($values);

            // Create notification
            if (isset($data['status'])) {
                $this->createNotification(
                    $this->getOrderCustomerId($orderId),
                    'Order Status Updated',
                    "Your order #{$orderId} status has been updated to {$data['status']}",
                    'order',
                    $orderId
                );
            }

            return [
                'status' => 'success',
                'message' => 'Order updated successfully'
            ];

        } catch (PDOException $e) {
            error_log("Order Update Error: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Order update failed'];
        }
    }

    private function getOrderCustomerId($orderId) {
        $stmt = $this->db->prepare("SELECT customer_id FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $result = $stmt->fetch();
        return $result['customer_id'] ?? null;
    }

    public function cancelOrder($orderId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE orders 
                SET status = 'cancelled', updated_at = NOW()
                WHERE id = ? AND status NOT IN ('completed', 'delivered', 'cancelled')
            ");
            
            $stmt->execute([$orderId]);
            
            if ($stmt->rowCount() === 0) {
                return ['status' => 'error', 'message' => 'Order cannot be cancelled'];
            }

            // Create notification
            $customerId = $this->getOrderCustomerId($orderId);
            if ($customerId) {
                $this->createNotification(
                    $customerId,
                    'Order Cancelled',
                    "Your order #{$orderId} has been cancelled",
                    'order',
                    $orderId
                );
            }

            return [
                'status' => 'success',
                'message' => 'Order cancelled successfully'
            ];

        } catch (PDOException $e) {
            error_log("Order Cancellation Error: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Order cancellation failed'];
        }
    }

    public function getOrderDetails($orderId) {
        try {
            $stmt = $this->db->prepare("
                SELECT o.*, 
                    gt.name as garment_type, gt.base_price,
                    u.first_name as customer_first_name, u.last_name as customer_last_name,
                    t.first_name as tailor_first_name, t.last_name as tailor_last_name
                FROM orders o
                JOIN garment_types gt ON o.garment_type_id = gt.id
                JOIN users u ON o.customer_id = u.id
                LEFT JOIN users t ON o.assigned_tailor_id = t.id
                WHERE o.id = ?
            ");
            
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();
            
            if (!$order) {
                return ['status' => 'error', 'message' => 'Order not found'];
            }

            // Decode measurements
            $order['measurements'] = json_decode($order['measurements'], true);

            // Get payment status
            $payment = $this->getOrderPaymentStatus($orderId);
            $order['payment_status'] = $payment['status'] ?? 'pending';
            $order['payment_method'] = $payment['payment_method'] ?? null;

            // Get work progress
            $order['progress'] = $this->getOrderProgress($orderId);

            return [
                'status' => 'success',
                'order' => $order
            ];

        } catch (PDOException $e) {
            error_log("Order Details Error: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Failed to fetch order details'];
        }
    }

    private function getOrderPaymentStatus($orderId) {
        $stmt = $this->db->prepare("
            SELECT status, payment_method 
            FROM payments 
            WHERE order_id = ? 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetch();
    }

    private function getOrderProgress($orderId) {
        $stmt = $this->db->prepare("
            SELECT * FROM work_progress 
            WHERE order_id = ? 
            ORDER BY progress_date DESC
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public function getCustomerOrders($customerId, $status = null) {
        try {
            $query = "
                SELECT o.*, gt.name as garment_type, 
                    p.status as payment_status, p.payment_method
                FROM orders o
                JOIN garment_types gt ON o.garment_type_id = gt.id
                LEFT JOIN (
                    SELECT order_id, status, payment_method 
                    FROM payments 
                    WHERE id IN (
                        SELECT MAX(id) FROM payments GROUP BY order_id
                    )
                ) p ON o.id = p.order_id
                WHERE o.customer_id = ?
            ";
            
            $params = [$customerId];
            
            if ($status) {
                $query .= " AND o.status = ?";
                $params[] = $status;
            }
            
            $query .= " ORDER BY o.order_date DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            $orders = $stmt->fetchAll();
            
            // Decode measurements for each order
            foreach ($orders as &$order) {
                $order['measurements'] = json_decode($order['measurements'], true);
            }
            
            return [
                'status' => 'success',
                'orders' => $orders
            ];

        } catch (PDOException $e) {
            error_log("Customer Orders Error: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Failed to fetch customer orders'];
        }
    }

    public function getTailorOrders($tailorId, $status = null) {
        try {
            $query = "
                SELECT o.*, gt.name as garment_type, 
                    u.first_name as customer_first_name, u.last_name as customer_last_name,
                    p.status as payment_status
                FROM orders o
                JOIN garment_types gt ON o.garment_type_id = gt.id
                JOIN users u ON o.customer_id = u.id
                LEFT JOIN (
                    SELECT order_id, status 
                    FROM payments 
                    WHERE id IN (
                        SELECT MAX(id) FROM payments GROUP BY order_id
                    )
                ) p ON o.id = p.order_id
                WHERE o.assigned_tailor_id = ?
            ";
            
            $params = [$tailorId];
            
            if ($status) {
                $query .= " AND o.status = ?";
                $params[] = $status;
            }
            
            $query .= " ORDER BY o.priority DESC, o.expected_delivery_date ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            return [
                'status' => 'success',
                'orders' => $stmt->fetchAll()
            ];

        } catch (PDOException $e) {
            error_log("Tailor Orders Error: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Failed to fetch tailor orders'];
        }
    }

    public function updateOrderProgress($orderId, $tailorId, $status, $notes = '') {
        try {
            // Insert progress record
            $stmt = $this->db->prepare("
                INSERT INTO work_progress 
                (order_id, tailor_id, status, notes) 
                VALUES (?, ?, ?, ?)
            ");
            
            $stmt->execute([$orderId, $tailorId, $status, $notes]);
            
            // Update order status if needed
            if ($status === 'completed') {
                $this->updateOrderStatus($orderId, 'completed');
            }
            
            // Create notification
            $customerId = $this->getOrderCustomerId($orderId);
            if ($customerId) {
                $this->createNotification(
                    $customerId,
                    'Order Progress Update',
                    "Your order #{$orderId} has moved to {$status} stage",
                    'order',
                    $orderId
                );
            }

            return [
                'status' => 'success',
                'message' => 'Order progress updated successfully'
            ];

        } catch (PDOException $e) {
            error_log("Order Progress Error: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Failed to update order progress'];
        }
    }

    private function updateOrderStatus($orderId, $status) {
        $stmt = $this->db->prepare("
            UPDATE orders 
            SET status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$status, $orderId]);
    }

    private function createNotification($userId, $title, $message, $entityType = null, $entityId = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO notifications 
                (user_id, title, message, related_entity_type, related_entity_id) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([$userId, $title, $message, $entityType, $entityId]);
            
        } catch (PDOException $e) {
            error_log("Notification Creation Error: " . $e->getMessage());
        }
    }

    public function getDashboardStats($userId, $role) {
        try {
            $stats = [];
            
            if ($role === 'customer') {
                $query = "
                    SELECT 
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_orders,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
                        SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders
                    FROM orders
                    WHERE customer_id = ?
                ";
                $stmt = $this->db->prepare($query);
                $stmt->execute([$userId]);
                $stats = $stmt->fetch();
                
                // Add payment stats
                $query = "
                    SELECT 
                        SUM(CASE WHEN p.status = 'pending' THEN 1 ELSE 0 END) as pending_payments,
                        SUM(CASE WHEN p.status = 'completed' THEN 1 ELSE 0 END) as completed_payments
                    FROM payments p
                    JOIN orders o ON p.order_id = o.id
                    WHERE o.customer_id = ?
                ";
                $stmt = $this->db->prepare($query);
                $stmt->execute([$userId]);
                $paymentStats = $stmt->fetch();
                
                $stats = array_merge($stats, $paymentStats);
                
            } elseif ($role === 'tailor') {
                $query = "
                    SELECT 
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_orders,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
                        COUNT(DISTINCT customer_id) as total_customers
                    FROM orders
                    WHERE assigned_tailor_id = ?
                ";
                $stmt = $this->db->prepare($query);
                $stmt->execute([$userId]);
                $stats = $stmt->fetch();
                
            } elseif ($role === 'manager') {
                $query = "
                    SELECT 
                        COUNT(*) as total_orders,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_orders,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
                        SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
                        COUNT(DISTINCT customer_id) as total_customers,
                        COUNT(DISTINCT assigned_tailor_id) as active_tailors
                    FROM orders
                ";
                $stmt = $this->db->prepare($query);
                $stmt->execute();
                $stats = $stmt->fetch();
                
                // Add revenue stats
                $query = "
                    SELECT 
                        SUM(amount) as total_revenue,
                        SUM(CASE WHEN payment_method = 'mpesa' THEN amount ELSE 0 END) as mpesa_revenue,
                        SUM(CASE WHEN payment_method = 'paypal' THEN amount ELSE 0 END) as paypal_revenue,
                        SUM(CASE WHEN payment_method = 'credit_card' THEN amount ELSE 0 END) as credit_card_revenue,
                        SUM(CASE WHEN payment_method = 'bank_transfer' THEN amount ELSE 0 END) as bank_revenue,
                        SUM(CASE WHEN payment_method = 'cash' THEN amount ELSE 0 END) as cash_revenue
                    FROM payments
                    WHERE status = 'completed'
                ";
                $stmt = $this->db->prepare($query);
                $stmt->execute();
                $revenueStats = $stmt->fetch();
                
                $stats = array_merge($stats, $revenueStats);
            }
            
            return [
                'status' => 'success',
                'stats' => $stats
            ];

        } catch (PDOException $e) {
            error_log("Dashboard Stats Error: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Failed to fetch dashboard stats'];
        }
    }

    public function getOrderHistoryChartData($userId, $role, $months = 6) {
        try {
            $endDate = date('Y-m-d');
            $startDate = date('Y-m-d', strtotime("-$months months"));
            
            if ($role === 'customer') {
                $query = "
                    SELECT 
                        DATE_FORMAT(order_date, '%Y-%m') as month,
                        COUNT(*) as order_count
                    FROM orders
                    WHERE customer_id = ?
                    AND order_date BETWEEN ? AND ?
                    GROUP BY DATE_FORMAT(order_date, '%Y-%m')
                    ORDER BY month
                ";
                $params = [$userId, $startDate, $endDate];
            } else {
                $query = "
                    SELECT 
                        DATE_FORMAT(order_date, '%Y-%m') as month,
                        COUNT(*) as order_count,
                        SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_count
                    FROM orders
                    WHERE order_date BETWEEN ? AND ?
                    GROUP BY DATE_FORMAT(order_date, '%Y-%m')
                    ORDER BY month
                ";
                $params = [$startDate, $endDate];
            }
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            return [
                'status' => 'success',
                'data' => $stmt->fetchAll()
            ];

        } catch (PDOException $e) {
            error_log("Order History Chart Error: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Failed to fetch order history data'];
        }
    }
}
?>