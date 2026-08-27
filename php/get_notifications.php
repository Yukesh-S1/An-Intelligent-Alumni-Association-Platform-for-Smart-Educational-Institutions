<?php
session_start();
include 'db_controller.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['logged_account']['email'])) {
    echo json_encode([]);
    exit;
}

try {
    $conn->select_db("Alumni");
    $current_user = $_SESSION['logged_account']['email'];

    // Check if notifications table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'notifications_table'");
    if ($table_check->num_rows == 0) {
        echo json_encode([]);
        exit;
    }

    // Get unread notifications
    $query = "SELECT * FROM notifications_table 
              WHERE user_email = '$current_user' AND is_read = FALSE 
              ORDER BY created_at DESC LIMIT 10";
    
    $result = $conn->query($query);
    $notifications = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
    }
    
    echo json_encode($notifications);
    
} catch (Exception $e) {
    // Return empty array on error
    echo json_encode([]);
}
?>