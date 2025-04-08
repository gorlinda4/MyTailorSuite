<?php
require_once 'auth.php';
require_once 'orders.php';
require_once 'payments.php';

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

$auth = new Auth();
$orderSystem = new OrderSystem();

// Verify user is logged in
if(!$auth->isLoggedIn()) {
    echo "event: error\ndata: Unauthorized\n\n";
    ob_flush();
    flush();
    exit;
}

$user_id = $auth->getCurrentUserId();
$user_role = $auth->getUserRole();

// Get last event ID from client
$last_event_id = $_SERVER['HTTP_LAST_EVENT_ID'] ?? 0;

// Keep connection open
while(true) {
    // Check for new notifications
    $db = new Database();
    $conn = $db->connect();
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications 
                           WHERE user_id = ? AND notification_id > ?");
    $stmt->execute([$user_id, $last_event_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($result['count'] > 0) {
        // Get new notifications
        $stmt = $conn->prepare("SELECT * FROM notifications 
                               WHERE user_id = ? AND notification_id > ?
                               ORDER BY created_at DESC");
        $stmt->execute([$user_id, $last_event_id]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($notifications as $notification) {
            echo "event: notification\n";
            echo "id: " . $notification['notification_id'] . "\n";
            echo "data: " . json_encode($notification) . "\n\n";
            ob_flush();
            flush();
            
            $last_event_id = max($last_event_id, $notification['notification_id']);
        }
    }
    
    // Check for order updates (for customers and tailors)
    if(in_array($user_role, ['customer', 'tailor'])) {
        if($user_role === 'customer') {
            $stmt = $conn->prepare("SELECT o.order_id, wp.status, wp.progress_date 
                                   FROM orders o
                                   JOIN work_progress wp ON o.order_id = wp.order_id
                                   WHERE o.customer_id = ? AND wp.progress_id > ?
                                   ORDER BY wp.progress_date DESC");
        } else { // tailor
            $stmt = $conn->prepare("SELECT o.order_id, wp.status, wp.progress_date 
                                   FROM orders o
                                   JOIN work_progress wp ON o.order_id = wp.order_id
                                   WHERE o.assigned_tailor_id = ? AND wp.progress_id > ?
                                   ORDER BY wp.progress_date DESC");
        }
        
        $stmt->execute([$user_id, $last_event_id]);
        $updates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($updates as $update) {
            echo "event: order_update\n";
            echo "id: " . $update['order_id'] . "_" . strtotime($update['progress_date']) . "\n";
            echo "data: " . json_encode($update) . "\n\n";
            ob_flush();
            flush();
            
            $last_event_id = max($last_event_id, $update['order_id']);
        }
    }
    
    // Check for payment updates (for customers)
    if($user_role === 'customer') {
        $stmt = $conn->prepare("SELECT p.payment_id, p.status, p.payment_date 
                               FROM payments p
                               JOIN orders o ON p.order_id = o.order_id
                               WHERE o.customer_id = ? AND p.payment_id > ?
                               ORDER BY p.payment_date DESC");
        $stmt->execute([$user_id, $last_event_id]);
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($payments as $payment) {
            echo "event: payment_update\n";
            echo "id: " . $payment['payment_id'] . "\n";
            echo "data: " . json_encode($payment) . "\n\n";
            ob_flush();
            flush();
            
            $last_event_id = max($last_event_id, $payment['payment_id']);
        }
    }
    
    // Sleep for 5 seconds before checking again
    sleep(5);
    
    // Send a keep-alive comment
    echo ": keep-alive\n\n";
    ob_flush();
    flush();
}
?>