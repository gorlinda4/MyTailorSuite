
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
        $stmt = $pdo->prepare('SELECT * FROM appointments WHERE user_id = ?');
        $stmt->execute([$user_id]);
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($appointments);
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare('INSERT INTO appointments (user_id, appointment_date, notes) VALUES (?, ?, ?)');
        $stmt->execute([$user_id, $data['appointment_date'], $data['notes']]);
        echo json_encode(['success' => 'Appointment booked']);
    } else {
        echo json_encode(['error' => 'Invalid request method']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
