<?php
session_start();
include 'db_controller.php';

header('Content-Type: application/json');

// Check database connection
if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Select database
$conn->select_db("Alumni");

if (!isset($_SESSION['logged_account']['email']) || !isset($_GET['conversation_id'])) {
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

$conversation_id = intval($_GET['conversation_id']);
$current_user = $_SESSION['logged_account']['email'];

// Verify user is part of this conversation
$verify_query = "SELECT * FROM chat_conversations_table 
                WHERE id = $conversation_id 
                AND (user1_email = '$current_user' OR user2_email = '$current_user')";
$verify_result = $conn->query($verify_query);

if (!$verify_result) {
    echo json_encode(['error' => 'Database query failed: ' . $conn->error]);
    exit;
}

if ($verify_result->num_rows === 0) {
    echo json_encode(['error' => 'Conversation not found or access denied']);
    exit;
}

// Get messages (with optional after_id parameter for new messages only)
$after_id = isset($_GET['after_id']) ? intval($_GET['after_id']) : 0;

if ($after_id > 0) {
    $messages_query = "SELECT * FROM chat_messages_table 
                      WHERE conversation_id = $conversation_id 
                      AND id > $after_id
                      ORDER BY sent_at ASC";
} else {
    $messages_query = "SELECT * FROM chat_messages_table 
                      WHERE conversation_id = $conversation_id 
                      ORDER BY sent_at ASC";
}
$messages_result = $conn->query($messages_query);

if (!$messages_result) {
    echo json_encode(['error' => 'Failed to fetch messages: ' . $conn->error]);
    exit;
}

$messages = [];
while ($message = $messages_result->fetch_assoc()) {
    $messages[] = $message;
}

// Mark messages as read
$mark_read = "UPDATE chat_messages_table 
             SET is_read = TRUE 
             WHERE conversation_id = $conversation_id 
             AND sender_email != '$current_user'";
if (!$conn->query($mark_read)) {
    // Log error but don't fail the response
    error_log("Failed to mark messages as read: " . $conn->error);
}

echo json_encode($messages);
?>
