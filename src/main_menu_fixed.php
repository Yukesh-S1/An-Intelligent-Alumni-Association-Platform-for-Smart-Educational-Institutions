<?php
session_start();
include 'db_controller.php';
$conn->select_db("Alumni");

// Check if user is logged in
if (!isset($_SESSION['logged_account']) || $_SESSION['logged_account']['type'] !== 'user') {
    header('Location: login.php');
    exit();
}

// Get dashboard statistics
$current_user = $_SESSION['logged_account']['email'] ?? '';
$stats = [];

// Total alumni count
try {
    $alumni_result = $conn->query("SELECT COUNT(*) as count FROM user_table u JOIN account_table a ON u.email = a.email WHERE a.type = 'user' AND a.status = 'Approved'");
    $stats['alumni'] = $alumni_result ? $alumni_result->fetch_assoc()['count'] : 0;
} catch (Exception $e) {
    $stats['alumni'] = 0;
}

// Upcoming events count
try {
    $events_result = $conn->query("SELECT COUNT(*) as count FROM event_table WHERE event_date >= CURDATE()");
    $stats['events'] = $events_result ? $events_result->fetch_assoc()['count'] : 0;
} catch (Exception $e) {
    $stats['events'] = 0;
}

// Active news count
try {
    $news_result = $conn->query("SELECT COUNT(*) as count FROM event_table WHERE type = 'News'");
    $stats['news'] = $news_result ? $news_result->fetch_assoc()['count'] : 0;
} catch (Exception $e) {
    $stats['news'] = 0;
}

// Unread messages count
try {
    if (!empty($current_user)) {
        $messages_result = $conn->query("SELECT COUNT(*) as count FROM chat_messages_table cm 
                                        JOIN chat_conversations_table cc ON cm.conversation_id = cc.id 
                                        WHERE (cc.user1_email = '$current_user' OR cc.user2_email = '$current_user') 
                                        AND cm.sender_email != '$current_user' AND cm.is_read = FALSE");
        $stats['messages'] = $messages_result ? $messages_result->fetch_assoc()['count'] : 0;
    } else {
        $stats['messages'] = 0;
    }
} catch (Exception $e) {
    $stats['messages'] = 0;
}

// Get user profile info
$user_info = [];
try {
    $user_result = $conn->query("SELECT * FROM user_table WHERE email = '$current_user'");
    $user_info = $user_result ? $user_result->fetch_assoc() : [];
} catch (Exception $e) {
    $user_info = [];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Alumni Portal</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&di