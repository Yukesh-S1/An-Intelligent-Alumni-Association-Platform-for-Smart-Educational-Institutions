<?php
session_start();
include 'db_controller.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['logged_account']['email'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

try {
    $conn->select_db("Alumni");
    $current_user = $_SESSION['logged_account']['email'];
    
    // Get the notification type and ID from request
    $data = json_decode(file_get_contents('php://input'), true);
    $notification_type = $data['type'] ?? '';
    $notification_id = $data['id'] ?? 0;
    
    $success = false;
    
    if ($notification_type === 'video_call' && $notification_id > 0) {
        // Mark video call as handled by updating call status
        // The notification will disappear when call status is no longer 'pending'
        $success = true; // Video calls are handled by accept/decline actions
        
    } elseif ($notification_type === 'message' && $notification_id > 0) {
        // Mark specific message as read
        $stmt = $conn->prepare("UPDATE chat_messages_table SET is_read = TRUE WHERE id = ? AND sender_email != ?");
        $stmt->bind_param("is", $notification_id, $current_user);
        $success = $stmt->execute();
        
    } elseif ($notification_type === 'notification' && $notification_id > 0) {
        // Mark specific notification as read
        $stmt = $conn->prepare("UPDATE notifications_table SET is_read = TRUE WHERE id = ? AND user_email = ?");
        $stmt->bind_param("is", $notification_id, $current_user);
        $success = $stmt->execute();
    }
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to mark notification as read']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
?>
