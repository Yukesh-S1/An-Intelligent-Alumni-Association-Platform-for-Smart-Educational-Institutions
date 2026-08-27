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

$current_user = $_SESSION['logged_account']['email'];

// Get online status for all users in current user's conversations
$status_query = "SELECT 
    CASE 
        WHEN c.user1_email = '$current_user' THEN c.user2_email
        ELSE c.user1_email
    END as other_email,
    CASE 
        WHEN c.user1_email = '$current_user' THEN u2.last_activity
        ELSE u1.last_activity
    END as last_activity,
    CASE 
        WHEN c.user1_email = '$current_user' THEN 
            CASE WHEN u2.last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN 1 ELSE 0 END
        ELSE 
            CASE WHEN u1.last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN 1 ELSE 0 END
    END as is_online
    FROM chat_conversations_table c
    LEFT JOIN user_table u1 ON c.user1_email = u1.email
    LEFT JOIN user_table u2 ON c.user2_email = u2.email
    WHERE c.user1_email = '$current_user' OR c.user2_email = '$current_user'
    ORDER BY c.last_message_at DESC";

$result = $conn->query($status_query);

if (!$result) {
    echo json_encode(['success' => false, 'error' => 'Query failed: ' . $conn->error]);
    exit;
}

$users = [];
while ($row = $result->fetch_assoc()) {
    $last_seen = $row['is_online'] ? 'Online' : date('h:i A', strtotime($row['last_activity']));
    $users[] = [
        'email' => $row['other_email'],
        'is_online' => (bool)$row['is_online'],
        'last_seen' => $last_seen
    ];
}

echo json_encode(['success' => true, 'users' => $users]);
?>
