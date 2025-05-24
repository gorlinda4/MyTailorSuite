
<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$user_id = $_SESSION['user_id'];

try {
    if ($method === 'GET') {
        $stmt = $pdo->prepare('SELECT * FROM payments WHERE order_id IN (SELECT order_id FROM orders WHERE customer_id = ?)');
        $stmt->execute([$user_id]);
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($payments);
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare('INSERT INTO payments (order_id, amount, payment_method, transaction_id) VALUES (?, ?, ?, ?)');
        $stmt->execute([$data['order_id'], $data['amount'], $data['payment_method'], $data['transaction_id']]);
        echo json_encode(['success' => 'Payment recorded']);
    } else {
        echo json_encode(['error' => 'Invalid request method']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
