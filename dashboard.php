<?php
require_once 'db.php';

class Dashboard {
    private $db;
    
    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function getCustomerStats($customer_id) {
        try {
            $stats = [];
            
            // Active orders
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM orders 
                                      WHERE customer_id = ? AND status IN ('pending', 'approved', 'in_progress')");
            $stmt->execute([$customer_id]);
            $stats['active_orders'] = $stmt->fetchColumn();
            
            // Pending payments
            $stmt = $this->db->prepare("SELECT COUNT(DISTINCT o.order_id) 
                                      FROM orders o
                                      LEFT JOIN payments p ON o.order_id = p.order_id
                                      WHERE o.customer_id = ? 
                                      AND (p.payment_id IS NULL OR p.status != 'completed')");
            $stmt->execute([$customer_id]);
            $stats['pending_payments'] = $stmt->fetchColumn();
            
            // Delivered orders
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM orders 
                                      WHERE customer_id = ? AND status = 'delivered'");
            $stmt->execute([$customer_id]);
            $stats['delivered_orders'] = $stmt->fetchColumn();
            
            return $stats;
        } catch(PDOException $e) {
            error_log("Get customer stats error: " . $e->getMessage());
            throw new Exception("Failed to retrieve dashboard stats");
        }
    }

    public function getTailorStats($tailor_id) {
        try {
            $stats = [];
            
            // Assigned orders
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM orders 
                                      WHERE assigned_tailor_id = ? AND status IN ('approved', 'in_progress')");
            $stmt->execute([$tailor_id]);
            $stats['assigned_orders'] = $stmt->fetchColumn();
            
            // Completed orders (this month)
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM orders 
                                      WHERE assigned_tailor_id = ? AND status = 'completed'
                                      AND MONTH(actual_delivery_date) = MONTH(CURRENT_DATE())
                                      AND YEAR(actual_delivery_date) = YEAR(CURRENT_DATE())");
            $stmt->execute([$tailor_id]);
            $stats['completed_orders'] = $stmt->fetchColumn();
            
            // Average completion time
            $stmt = $this->db->prepare("SELECT AVG(DATEDIFF(actual_delivery_date, order_date)) 
                                      FROM orders 
                                      WHERE assigned_tailor_id = ? AND status = 'completed'");
            $stmt->execute([$tailor_id]);
            $stats['avg_completion_days'] = round($stmt->fetchColumn(), 1);
            
            return $stats;
        } catch(PDOException $e) {
            error_log("Get tailor stats error: " . $e->getMessage());
            throw new Exception("Failed to retrieve dashboard stats");
        }
    }

    public function getManagerStats() {
        try {
            $stats = [];
            
            // Pending approvals
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
            $stmt->execute();
            $stats['pending_approvals'] = $stmt->fetchColumn();
            
            // Active orders
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM orders WHERE status IN ('approved', 'in_progress')");
            $stmt->execute();
            $stats['active_orders'] = $stmt->fetchColumn();
            
            // Revenue (this month)
            $stmt = $this->db->prepare("SELECT SUM(p.amount) 
                                      FROM payments p
                                      JOIN orders o ON p.order_id = o.order_id
                                      WHERE p.status = 'completed'
                                      AND MONTH(p.payment_date) = MONTH(CURRENT_DATE())
                                      AND YEAR(p.payment_date) = YEAR(CURRENT_DATE())");
            $stmt->execute();
            $stats['monthly_revenue'] = $stmt->fetchColumn();
            
            return $stats;
        } catch(PDOException $e) {
            error_log("Get manager stats error: " . $e->getMessage());
            throw new Exception("Failed to retrieve dashboard stats");
        }
    }

    public function getAdminStats() {
        try {
            $stats = [];
            
            // Total users
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users");
            $stmt->execute();
            $stats['total_users'] = $stmt->fetchColumn();
            
            // Active orders
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM orders WHERE status IN ('pending', 'approved', 'in_progress')");
            $stmt->execute();
            $stats['active_orders'] = $stmt->fetchColumn();
            
            // Monthly revenue
            $stmt = $this->db->prepare("SELECT SUM(p.amount) 
                                      FROM payments p
                                      WHERE p.status = 'completed'
                                      AND MONTH(p.payment_date) = MONTH(CURRENT_DATE())
                                      AND YEAR(p.payment_date) = YEAR(CURRENT_DATE())");
            $stmt->execute();
            $stats['monthly_revenue'] = $stmt->fetchColumn();
            
            // System health
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE last_login < DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $stmt->execute();
            $stats['inactive_users'] = $stmt->fetchColumn();
            
            return $stats;
        } catch(PDOException $e) {
            error_log("Get admin stats error: " . $e->getMessage());
            throw new Exception("Failed to retrieve dashboard stats");
        }
    }

    public function getCashierStats() {
        try {
            $stats = [];
            
            // Pending payments
            $stmt = $this->db->prepare("SELECT COUNT(DISTINCT o.order_id) 
                                      FROM orders o
                                      LEFT JOIN payments p ON o.order_id = p.order_id
                                      WHERE (p.payment_id IS NULL OR p.status != 'completed')");
            $stmt->execute();
            $stats['pending_payments'] = $stmt->fetchColumn();
            
            // Today's payments
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM payments 
                                      WHERE DATE(payment_date) = CURRENT_DATE()");
            $stmt->execute();
            $stats['todays_payments'] = $stmt->fetchColumn();
            
            // Today's revenue
            $stmt = $this->db->prepare("SELECT SUM(amount) FROM payments 
                                      WHERE DATE(payment_date) = CURRENT_DATE()");
            $stmt->execute();
            $stats['todays_revenue'] = $stmt->fetchColumn();
            
            return $stats;
        } catch(PDOException $e) {
            error_log("Get cashier stats error: " . $e->getMessage());
            throw new Exception("Failed to retrieve dashboard stats");
        }
    }

    public function getOrderChartData($time_period = 'monthly') {
        try {
            $data = [];
            
            switch($time_period) {
                case 'weekly':
                    $sql = "SELECT DAYNAME(order_date) as label, COUNT(*) as count 
                           FROM orders 
                           WHERE order_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY)
                           GROUP BY DAYNAME(order_date), DAYOFWEEK(order_date)
                           ORDER BY DAYOFWEEK(order_date)";
                    break;
                case 'monthly':
                default:
                    $sql = "SELECT DATE_FORMAT(order_date, '%Y-%m') as label, COUNT(*) as count 
                           FROM orders 
                           WHERE order_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 12 MONTH)
                           GROUP BY DATE_FORMAT(order_date, '%Y-%m')
                           ORDER BY DATE_FORMAT(order_date, '%Y-%m')";
                    break;
                case 'yearly':
                    $sql = "SELECT YEAR(order_date) as label, COUNT(*) as count 
                           FROM orders 
                           WHERE order_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 5 YEAR)
                           GROUP BY YEAR(order_date)
                           ORDER BY YEAR(order_date)";
                    break;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            foreach($results as $row) {
                $data['labels'][] = $row['label'];
                $data['data'][] = $row['count'];
            }
            
            return $data;
        } catch(PDOException $e) {
            error_log("Get chart data error: " . $e->getMessage());
            throw new Exception("Failed to retrieve chart data");
        }
    }
}
?>