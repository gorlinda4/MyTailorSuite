<?php
require_once 'db.php';

class Notification {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function getUserNotifications($userId, $limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM notifications 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$userId, $limit]);
            
            return [
                'status' => 'success',
                'notifications' => $stmt->fetchAll()
            ];

        } catch (PDOException $e) {
            error_log("Notifications Error: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Failed to fetch notifications'];
        }
    }

    public function markAsRead($notificationId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE notifications 
                SET is_read = TRUE 
                WHERE id = ?
            ");
            $stmt->execute([$notificationId]);
            
            return [
                'status' => 'success',
                'message' => 'Notification marked as read'
            ];

        } catch (PDOException $e) {
            error_log("Notification Update Error: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Failed to update notification'];
        }
    }

    public function getUnreadCount($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM notifications 
                WHERE user_id = ? AND is_read = FALSE
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            
            return [
                'status' => 'success',
                'count' => $result['count'] ?? 0
            ];

        } catch (PDOException $e) {
            error_log("Unread Count Error: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Failed to get unread count'];
        }
    }
}
?>