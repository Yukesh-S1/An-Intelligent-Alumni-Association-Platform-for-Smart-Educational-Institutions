<?php
session_start();
include 'db_controller.php';

// Select database
$conn->select_db("Alumni");

header('Content-Type: application/json');

if (!isset($_SESSION['logged_account']['email'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['other_user_email'])) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

$current_user = $_SESSION['logged_account']['email'];
$other_user = $conn->real_escape_string($input['other_user_email']);

// Get other user's type and verify existence
$other_user_query = "SELECT u.email, u.first_name, u.last_name, a.type FROM user_table u 
                    JOIN account_table a ON u.email = a.email 
                    WHERE u.email = '$other_user'";
$other_user_result = $conn->query($other_user_query);

if ($other_user_result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'User not found']);
    exit;
}

$other_user_data = $other_user_result->fetch_assoc();

// Check if conversation already exists
$existing_query = "SELECT id FROM chat_conversations_table 
                  WHERE (user1_email = '$current_user' AND user2_email = '$other_user')
                     OR (user1_email = '$other_user' AND user2_email = '$current_user')";
$existing_result = $conn->query($existing_query);

if ($existing_result->num_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Conversation already exists']);
    exit;
}

// Create conversation directly (no invitation needed for user-to-user chats)
$create_conversation = "INSERT INTO chat_conversations_table (user1_email, user2_email) 
                       VALUES ('$current_user', '$other_user')";

if ($conn->query($create_conversation)) {
    echo json_encode(['success' => true, 'message' => 'Conversation created successfully']);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to create conversation: ' . $conn->error]);
}
?>
