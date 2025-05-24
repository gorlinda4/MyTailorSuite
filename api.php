
<?php
require 'db.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'] ?? null;

try {
    if ($action === 'dashboard_data') {
        // Admin stats
        $users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        $revenue = $pdo->query("SELECT SUM(amount) FROM payments WHERE status = 'completed'")->fetchColumn();

        echo json_encode([
            'users' => (int)$users,
            'orders' => (int)$orders,
            'revenue' => (float)$revenue
        ]);
    }

    elseif ($action === 'cashier_stats') {
        // Cashier stats
        $today = date('Y-m-d');
        $stmt = $pdo->prepare("SELECT SUM(amount) FROM payments WHERE DATE(payment_date) = ?");
        $stmt->execute([$today]);
        $todaysPayments = $stmt->fetchColumn();

        $totalTransactions = $pdo->query("SELECT COUNT(*) FROM payments")->fetchColumn();

        echo json_encode([
            'todays_payments' => (float)$todaysPayments,
            'total_transactions' => (int)$totalTransactions
        ]);
    }

    elseif ($action === 'manager_stats') {
        // Manager stats
        $totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        $pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
        $completedOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'completed'")->fetchColumn();

        echo json_encode([
            'total_orders' => (int)$totalOrders,
            'pending_orders' => (int)$pendingOrders,
            'completed_orders' => (int)$completedOrders
        ]);
    }

    elseif ($action === 'customer_stats' && $user_id) {
        // Customer stats
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = ?");
        $stmt->execute([$user_id]);
        $totalOrders = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = ? AND status = 'completed'");
        $stmt->execute([$user_id]);
        $completedOrders = $stmt->fetchColumn();

        echo json_encode([
            'total_orders' => (int)$totalOrders,
            'completed_orders' => (int)$completedOrders
        ]);
    }

    elseif ($action === 'tailor_stats' && $user_id) {
        // Tailor stats
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE assigned_tailor_id = ?");
        $stmt->execute([$user_id]);
        $assignedOrders = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE assigned_tailor_id = ? AND status = 'completed'");
        $stmt->execute([$user_id]);
        $completedOrders = $stmt->fetchColumn();

        echo json_encode([
            'assigned_orders' => (int)$assignedOrders,
            'completed_orders' => (int)$completedOrders
        ]);
    }

    elseif ($action === 'notifications' && $user_id) {
        // Notifications
        $stmt = $pdo->prepare("SELECT title, message, created_at FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($notifications);
    }

    else {
        echo json_encode(['error' => 'Invalid action or missing user_id']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
