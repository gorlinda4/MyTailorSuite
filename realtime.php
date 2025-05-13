<?php
header("Content-Type: text/event-stream");
header("Cache-Control: no-cache");
header("Connection: keep-alive");

require_once 'auth.php';
require_once 'order.php';
require_once 'notification.php';

// Initialize classes
$auth = new Auth();
$order = new Order();
$notification = new Notification();

// Get last event ID
$lastEventId = isset($_SERVER["HTTP_LAST_EVENT_ID"]) ? intval($_SERVER["HTTP_LAST_EVENT_ID"]) : 0;
if ($lastEventId === 0 && isset($_GET['lastEventId'])) {
    $lastEventId = intval($_GET['lastEventId']);
}

// Check authentication
if (!$auth->isLoggedIn()) {
    echo "event: error\n";
    echo "data: Unauthorized\n\n";
    ob_flush();
    flush();
    exit();
}

$user = $auth->getCurrentUser();

// Set a long timeout
set_time_limit(0);

// Keep the connection open
while (true) {
    // Check for new notifications
    $unreadCount = $notification->getUnreadCount($user['id']);
    if ($unreadCount['status'] === 'success') {
        $currentCount = $unreadCount['count'];
        
        if ($lastEventId < $currentCount) {
            echo "event: notification\n";
            echo "id: " . $currentCount . "\n";
            echo "data: " . json_encode(['count' => $currentCount]) . "\n\n";
            ob_flush();
            flush();
            $lastEventId = $currentCount;
        }
    }
    
    // Check for order updates (for tailors and managers)
    if (in_array($user['role'], ['tailor', 'manager'])) {
        $orders = $order->getTailorOrders($user['id']);
        if ($orders['status'] === 'success') {
            $orderCount = count($orders['orders']);
            
            if ($lastEventId < $orderCount) {
                echo "event: order_update\n";
                echo "id: " . $orderCount . "\n";
                echo "data: " . json_encode(['count' => $orderCount]) . "\n\n";
                ob_flush();
                flush();
                $lastEventId = $orderCount;
            }
        }
    }
    
    // Sleep for a while before checking again
    sleep(5);
}
?>