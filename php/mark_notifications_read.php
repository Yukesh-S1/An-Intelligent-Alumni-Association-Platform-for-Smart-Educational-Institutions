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
    
    // Mark regular notifications as read
    $table_check = $conn->query("SHOW TABLES LIKE 'notifications_table'");
    if ($table_check->num_rows > 0) {
        $update_notifications = $conn->prepare("UPDATE notifications_table SET is_read = TRUE WHERE user_email = ? AND is_read = FALSE");
        $update_notifications->bind_param("s", $current_user);
        $update_notifications->execute();
    }
    
    // Mark chat messages as read
    $update_messages = $conn->prepare("
        UPDATE chat_messages_table cm 
        JOIN chat_conversations_table cc ON cm.conversation_id = cc.id 
        SET cm.is_read = TRUE 
        WHERE (cc.user1_email = ? OR cc.user2_email = ?) 
        AND cm.sender_email != ? 
        AND cm.is_read = FALSE
    ");
    $update_messages->bind_param("sss", $current_user, $current_user, $current_user);
    $update_messages->execute();
    
    echo json_encode(['success' => true, 'message' => 'All notifications marked as read']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
?>