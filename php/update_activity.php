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

// Update user's last activity
$update_query = "UPDATE user_table SET last_activity = NOW() WHERE email = '$current_user'";

if ($conn->query($update_query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to update activity: ' . $conn->error]);
}
?>
