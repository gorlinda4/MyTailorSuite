
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
        $stmt = $pdo->prepare('SELECT * FROM orders WHERE customer_id = ?');
        $stmt->execute([$user_id]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($orders);
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare('INSERT INTO orders (customer_id, garment_type_id, status, measurements, design_notes, priority) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$user_id, $data['garment_type_id'], 'pending', $data['measurements'], $data['design_notes'], $data['priority']]);
        echo json_encode(['success' => 'Order created']);
    } elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare('UPDATE orders SET status = ?, measurements = ?, design_notes = ?, priority = ? WHERE order_id = ? AND customer_id = ?');
        $stmt->execute([$data['status'], $data['measurements'], $data['design_notes'], $data['priority'], $data['order_id'], $user_id]);
        echo json_encode(['success' => 'Order updated']);
    } elseif ($method === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare('DELETE FROM orders WHERE order_id = ? AND customer_id = ?');
        $stmt->execute([$data['order_id'], $user_id]);
        echo json_encode(['success' => 'Order deleted']);
    } else {
        echo json_encode(['error' => 'Invalid request method']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
