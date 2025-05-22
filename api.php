<?php
header("Content-Type: application/json");
require_once 'auth.php';
require_once 'payment.php';
require_once 'order.php';
require_once 'notification.php';

// Initialize classes
$auth = new Auth();
$payment = new Payment();
$order = new Order();
$notification = new Notification();

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get request data
$data = json_decode(file_get_contents('php://input'), true);
if (empty($data)) {
    $data = $_REQUEST;
}

// Route the request
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : '';

switch ($endpoint) {
    // Authentication endpoints
    case 'register':
        if ($method === 'POST') {
            echo json_encode($auth->register($data));
        } else {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        }
        break;
        
    case 'login':
        if ($method === 'POST') {
            if (empty($data['username']) || empty($data['password'])) {
                echo json_encode(['status' => 'error', 'message' => 'Username and password are required']);
            } else {
                echo json_encode($auth->login($data['username'], $data['password']));
            }
        } else {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        }
        break;
        
    case 'logout':
        if ($method === 'POST') {
            echo json_encode($auth->logout());
        } else {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        }
        break;
        
    case 'check-auth':
        if ($method === 'GET') {
            if ($auth->isLoggedIn()) {
                $user = $auth->getCurrentUser();
                echo json_encode(['status' => 'success', 'authenticated' => true, 'user' => $user]);
            } else {
                echo json_encode(['status' => 'success', 'authenticated' => false]);
            }
        } else {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        }
        break;
        
    // Payment endpoints
    case 'process-payment':
        if ($method === 'POST') {
            if (!$auth->isLoggedIn()) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
                break;
            }
            
            echo json_encode($payment->processPayment($data));
        } else {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        }
        break;
        
    case 'verify-mpesa':
        if ($method === 'POST') {
            if (empty($data['checkout_request_id'])) {
                echo json_encode(['status' => 'error', 'message' => 'Checkout request ID is required']);
                break;
            }
            
            echo json_encode($payment->verifyMpesaPayment($data['checkout_request_id']));
        } else {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        }
        break;
        
    case 'payment-history':
        if ($method === 'GET') {
            if (!$auth->isLoggedIn()) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
                break;
            }
            
            $user = $auth->getCurrentUser();
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
            echo json_encode($payment->getPaymentHistory($user['id'], $limit));
        } else {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        }
        break;
        
    // Order endpoints
    case 'create-order':
        if ($method === 'POST') {
            if (!$auth->isLoggedIn()) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
                break;
            }
            
            $user = $auth->getCurrentUser();
            $data['customer_id'] = $user['id'];
            echo json_encode($order->createOrder($data));
        } else {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        }
        break;
        
    case 'update-order':
        if ($method === 'PUT') {
            if (!$auth->isLoggedIn()) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
                break;
            }
            
            if (empty($data['order_id'])) {
                echo json_encode(['status' => 'error', 'message' => 'Order ID is required']);
                break;
            }
            
            echo json_encode($order->updateOrder($data['order_id'], $data));
        } else {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        }
        break;
        
    case 'cancel-order':
        if ($method === 'POST') {
            if (!$auth->isLoggedIn()) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
                break;
            }
            
            if (empty($data['order_id'])) {
                echo json_encode(['status' => 'error', 'message' => 'Order ID is required']);
                break;
            }
            
            echo json_encode($order->cancelOrder($data['order_id']));
        } else {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        }
        break;
        
    case 'order-details':
        if ($method === 'GET') {
            if (empty($_GET['order_id'])) {
                echo json_encode(['status' => 'error', 'message' => 'Order ID is required']);
                break;
            }
            
            echo json_encode($order->getOrderDetails($_GET['order_id']));
        } else {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        }
        break;
        
    case 'customer-orders':
        if ($method === 'GET') {
            if (!$auth->isLoggedIn()) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
                break;
            }
            
            $user = $auth->getCurrentUser();
            $status = isset($_GET['status']) ? $_GET['status'] : null;
            echo json_encode($order->getCustomerOrders($user['id'], $status));
        } else {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        }
        break;
        
    case 'tailor-orders':
        if ($method === 'GET') {
            if (!$auth->isLoggedIn()) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
                break;
            }
            
            $user = $auth->getCurrentUser();
            if ($user['role'] !== 'tailor') {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Forbidden']);
                break;
            }
            
            $status = isset($_GET['status']) ? $_GET['status'] : null;
            echo json_encode($order->getTailorOrders($user['id'], $status));
        } else {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        }
        break;
        
    case 'update-progress':
        if ($method === 'POST') {
            if (!$auth->isLoggedIn()) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
                break;
            }
            
            $user = $auth->getCurrentUser();
            if ($user['role'] !== 'tailor') {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Forbidden']);
                break;
            }
            
            if (empty($data['order_id']) || empty($data['status'])) {
                echo json_encode(['status' => 'error', 'message' => 'Order ID and status are required']);
                break;
            }
            
            $notes = $data['notes'] ?? '';
            echo json_encode($order->updateOrderProgress($data['order_id'], $user['id'], $data['status'], $notes));
        } else {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        }
        break;
        
    case 'dashboard-stats':
        if ($method === 'GET') {
            if (!$auth->isLoggedIn()) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
                break;
            }
            
            $user = $auth->getCurrentUser();
            echo json_encode($order->getDashboardStats($user['id'], $user['role']));
        } else {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        }
        break;
        
    case 'order-history-chart':
        if ($method === 'GET') {
            if (!$auth->isLoggedIn()) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
                break;
            }
            
            $user = $auth->getCurrentUser();
            $months = isset($_GET['months']) ? intval($_GET['months']) : 6;
            echo json_encode($order->getOrderHistoryChartData($user['id'], $user['role'], $months));
        } else {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        }
        break;
        
    // Notification endpoints
    case 'notifications':
        if ($method === 'GET') {
            if (!$auth->isLoggedIn()) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
                break;
            }
            
            $user = $auth->getCurrentUser();
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
            echo json_encode($notification->getUserNotifications($user['id'], $limit));
        } else {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        }
        break;
        
    case 'mark-notification-read':
        if ($method === 'POST') {
            if (!$auth->isLoggedIn()) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
                break;
            }
            
            if (empty($data['notification_id'])) {
                echo json_encode(['status' => 'error', 'message' => 'Notification ID is required']);
                break;
            }
            
            echo json_encode($notification->markAsRead($data['notification_id']));
        } else {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        }
        break;
        
    case 'unread-count':
        if ($method === 'GET') {
            if (!$auth->isLoggedIn()) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
                break;
            }
            
            $user = $auth->getCurrentUser();
            echo json_encode($notification->getUnreadCount($user['id']));
        } else {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        }
        break;
        
    // Default endpoint
    default:
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Endpoint not found']);
        break;
}
?>