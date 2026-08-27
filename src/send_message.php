<?php
session_start();
include 'db_controller.php';

header('Content-Type: application/json');

// Check database connection
if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// Select database
$conn->select_db("Alumni");

if (!isset($_SESSION['logged_account']['email'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['conversation_id']) || !isset($input['message'])) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

$conversation_id = intval($input['conversation_id']);
$message = $conn->real_escape_string(trim($input['message']));
$current_user = $_SESSION['logged_account']['email'];

if (empty($message)) {
    echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
    exit;
}

// Verify user is part of this conversation
$verify_query = "SELECT * FROM chat_conversations_table 
                WHERE id = $conversation_id 
                AND (user1_email = '$current_user' OR user2_email = '$current_user')";
$verify_result = $conn->query($verify_query);

if (!$verify_result) {
    echo json_encode(['success' => false, 'error' => 'Database query failed: ' . $conn->error]);
    exit;
}

if ($verify_result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized - conversation not found or access denied']);
    exit;
}

// Insert message
$insert_query = "INSERT INTO chat_messages_table (conversation_id, sender_email, message) 
                VALUES ($conversation_id, '$current_user', '$message')";

if ($conn->query($insert_query)) {
    // Update conversation last_message_at
    $update_conv = "UPDATE chat_conversations_table 
                   SET last_message_at = CURRENT_TIMESTAMP 
                   WHERE id = $conversation_id";
    if (!$conn->query($update_conv)) {
        // Log error but don't fail the message send
        error_log("Failed to update conversation timestamp: " . $conn->error);
    }
    
    // Get conversation details to find the other user
    $conv_query = "SELECT user1_email, user2_email FROM chat_conversations_table WHERE id = $conversation_id";
    $conv_result = $conn->query($conv_query);
    
    if ($conv_result && $conv_result->num_rows > 0) {
        $conversation = $conv_result->fetch_assoc();
        $other_user = ($conversation['user1_email'] === $current_user) ? $conversation['user2_email'] : $conversation['user1_email'];
        
        // Get sender details for notification
        $sender_query = "SELECT first_name, last_name FROM user_table WHERE email = '$current_user'";
        $sender_result = $conn->query($sender_query);
        
        if ($sender_result && $sender_result->num_rows > 0) {
            $sender = $sender_result->fetch_assoc();
            
            // Create notification for the other user
            $notification_title = "New Message";
            $notification_message = $sender['first_name'] . ' ' . $sender['last_name'] . ' sent you a message: ' . substr($message, 0, 50) . (strlen($message) > 50 ? '...' : '');
            
            $notification_query = "INSERT INTO notifications_table (user_email, type, title, message, related_id, related_email) 
                                  VALUES ('$other_user', 'message', '$notification_title', '$notification_message', $conversation_id, '$current_user')";
            if (!$conn->query($notification_query)) {
                // Log error but don't fail the message send
                error_log("Failed to create message notification: " . $conn->error);
            }
        }
    }
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to send message: ' . $conn->error]);
}
?>
