
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
        $stmt = $pdo->prepare('SELECT * FROM feedback WHERE user_id = ?');
        $stmt->execute([$user_id]);
        $feedback = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($feedback);
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare('INSERT INTO feedback (user_id, message, rating) VALUES (?, ?, ?)');
        $stmt->execute([$user_id, $data['message'], $data['rating']]);
        echo json_encode(['success' => 'Feedback submitted']);
    } else {
        echo json_encode(['error' => 'Invalid request method']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
