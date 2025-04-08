<?php
require_once 'auth.php';
require_once 'orders.php';
require_once 'payments.php';

header("Content-Type: application/json");

// Initialize systems
$auth = new Auth();
$orderSystem = new OrderSystem();
$paymentSystem = new PaymentSystem();

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));

// API routing
try {
    // Public endpoints
    if($path === '/api/login' && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $user = $auth->login($data['username'], $data['password']);
        echo json_encode(['success' => true, 'user' => $user]);
        exit;
    }
    
    if($path === '/api/register' && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $user_id = $auth->register($data);
        echo json_encode(['success' => true, 'user_id' => $user_id]);
        exit;
    }
    
    // Check authentication for all other endpoints
    if(!$auth->isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }
    
    $user_id = $auth->getCurrentUserId();
    $user_role = $auth->getUserRole();
    
    // User-specific endpoints
    if($path === '/api/user' && $method === 'GET') {
        $db = new Database();
        $conn = $db->connect();
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get role-specific details
        if($user_role === 'customer') {
            $stmt = $conn->prepare("SELECT * FROM customer_details WHERE customer_id = ?");
            $stmt->execute([$user_id]);
            $details = $stmt->fetch(PDO::FETCH_ASSOC);
        } elseif($user_role === 'tailor') {
            $stmt = $conn->prepare("SELECT * FROM tailor_details WHERE tailor_id = ?");
            $stmt->execute([$user_id]);
            $details = $stmt->fetch(PDO::FETCH_ASSOC);
        } elseif($user_role === 'manager') {
            $stmt = $conn->prepare("SELECT * FROM manager_details WHERE manager_id = ?");
            $stmt->execute([$user_id]);
            $details = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        $user['details'] = $details ?? null;
        
        echo json_encode(['success' => true, 'user' => $user]);
        exit;
    }
    
    if($path === '/api/logout' && $method === 'POST') {
        $auth->logout();
        echo json_encode(['success' => true]);
        exit;
    }
    
    // Order endpoints
    if($path === '/api/orders' && $method === 'GET') {
        if($user_role === 'customer') {
            $orders = $orderSystem->getCustomerOrders($user_id);
        } elseif($user_role === 'tailor') {
            $orders = $orderSystem->getTailorOrders($user_id);
        } else {
            $orders = $orderSystem->getAllOrders();
        }
        
        echo json_encode(['success' => true, 'orders' => $orders]);
        exit;
    }
    
    if($path === '/api/orders' && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if($user_role !== 'customer') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Only customers can create orders']);
            exit;
        }
        
        $order_id = $orderSystem->createOrder($user_id, $data['garment_type_id'], $data);
        echo json_encode(['success' => true, 'order_id' => $order_id]);
        exit;
    }
    
    if(preg_match('/^\/api\/orders\/(\d+)$/', $path, $matches) && $method === 'GET') {
        $order_id = $matches[1];
        $order = $orderSystem->getOrder($order_id);
        
        // Check if user has access to this order
        if($user_role === 'customer' && $order['customer_id'] != $user_id) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Access denied']);
            exit;
        }
        
        if($user_role === 'tailor' && $order['assigned_tailor_id'] != $user_id) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Access denied']);
            exit;
        }
        
        echo json_encode(['success' => true, 'order' => $order]);
        exit;
    }
    
    if(preg_match('/^\/api\/orders\/(\d+)\/status$/', $path, $matches) && $method === 'PUT') {
        $order_id = $matches[1];
        $data = json_decode(file_get_contents('php://input'), true);
        
        if(!in_array($user_role, ['manager', 'tailor'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Only managers and tailors can update order status']);
            exit;
        }
        
        $orderSystem->updateOrderStatus(
            $order_id, 
            $data['status'], 
            $user_role === 'tailor' ? $user_id : null,
            $data['notes'] ?? null
        );
        
        echo json_encode(['success' => true]);
        exit;
    }
    
    if(preg_match('/^\/api\/orders\/(\d+)\/assign$/', $path, $matches) && $method === 'PUT') {
        $order_id = $matches[1];
        $data = json_decode(file_get_contents('php://input'), true);
        
        if($user_role !== 'manager') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Only managers can assign orders']);
            exit;
        }
        
        $orderSystem->assignOrderToTailor(
            $order_id, 
            $data['tailor_id'], 
            $data['due_date'], 
            $data['priority'] ?? 'medium',
            $data['notes'] ?? null
        );
        
        echo json_encode(['success' => true]);
        exit;
    }
    
    // Payment endpoints
    if(preg_match('/^\/api\/orders\/(\d+)\/payments$/', $path, $matches) && $method === 'POST') {
        $order_id = $matches[1];
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Check if user owns the order
        $order = $orderSystem->getOrder($order_id);
        if($order['customer_id'] != $user_id) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Access denied']);
            exit;
        }
        
        $result = $paymentSystem->processPayment($order_id, $data['payment_method'], $data);
        echo json_encode(['success' => true, 'result' => $result]);
        exit;
    }
    
    // Notifications
    if($path === '/api/notifications' && $method === 'GET') {
        $db = new Database();
        $conn = $db->connect();
        
        $stmt = $conn->prepare("SELECT * FROM notifications 
                               WHERE user_id = ? 
                               ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'notifications' => $notifications]);
        exit;
    }
    
    if(preg_match('/^\/api\/notifications\/(\d+)\/read$/', $path, $matches) && $method === 'PUT') {
        $notification_id = $matches[1];
        
        $db = new Database();
        $conn = $db->connect();
        
        $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE 
                               WHERE notification_id = ? AND user_id = ?");
        $stmt->execute([$notification_id, $user_id]);
        
        echo json_encode(['success' => true]);
        exit;
    }
    
    // 404 for unknown endpoints
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Endpoint not found']);
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>