<?php
session_start();
include 'db_controller.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_account']['email'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$conn->select_db("Alumni");
$current_user = $_SESSION['logged_account']['email'];

// Get all conversations for current user
$conversations_query = "SELECT c.*, 
    CASE 
        WHEN c.user1_email = '$current_user' THEN u2.first_name
        ELSE u1.first_name
    END as other_first_name,
    CASE 
        WHEN c.user1_email = '$current_user' THEN u2.last_name
        ELSE u1.last_name
    END as other_last_name,
    CASE 
        WHEN c.user1_email = '$current_user' THEN u2.email
        ELSE u1.email
    END as other_email,
    CASE 
        WHEN c.user1_email = '$current_user' THEN COALESCE(u2.profile_image, 
            IF(u2.gender = 'Male', 'profile_images/default-male-user-profile-icon.jpg', 
                               'profile_images/default-female-user-profile-icon.jpg'))
        ELSE COALESCE(u1.profile_image, 
            IF(u1.gender = 'Male', 'profile_images/default-male-user-profile-icon.jpg', 
                               'profile_images/default-female-user-profile-icon.jpg'))
    END as other_profile_image,
    NULL as other_last_activity,
    0 as is_online,
    (SELECT COUNT(*) FROM chat_messages_table m WHERE m.conversation_id = c.id AND m.sender_email != '$current_user' AND m.is_read = FALSE) as unread_count,
    (SELECT message FROM chat_messages_table m WHERE m.conversation_id = c.id ORDER BY m.sent_at DESC LIMIT 1) as last_message
    FROM chat_conversations_table c
    LEFT JOIN user_table u1 ON c.user1_email = u1.email
    LEFT JOIN user_table u2 ON c.user2_email = u2.email
    WHERE c.user1_email = '$current_user' OR c.user2_email = '$current_user'
    ORDER BY c.last_message_at DESC";

$result = $conn->query($conversations_query);

if ($result) {
    $conversations = [];
    while ($row = $result->fetch_assoc()) {
        $conversations[] = $row;
    }
    echo json_encode($conversations);
} else {
    echo json_encode(['error' => 'Database error: ' . $conn->error]);
}
?>