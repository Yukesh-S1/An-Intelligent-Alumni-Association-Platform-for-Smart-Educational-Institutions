<?php
session_start();
include 'db_controller.php';
$conn->select_db("Alumni");

// Check if user is logged in and is admin
if (!isset($_SESSION['logged_account']) || $_SESSION['logged_account']['type'] != 'admin') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $calleeEmail = $_POST['callee_email'];
    $callerEmail = $_SESSION['logged_account']['email'];
    
    // Generate unique room ID
    $roomId = 'room_' . uniqid() . '_' . time();
    
    try {
        // Create new call record
        $insertQuery = $conn->prepare("INSERT INTO video_call_table (caller_email, callee_email, room_id, call_status, call_start_time) VALUES (?, ?, ?, 'active', NOW())");
        $insertQuery->bind_param("sss", $callerEmail, $calleeEmail, $roomId);
        
        if ($insertQuery->execute()) {
            $callId = $conn->insert_id;
            
            // Get caller name
            $callerQuery = $conn->prepare("SELECT first_name, last_name FROM user_table WHERE email = ?");
            $callerQuery->bind_param("s", $callerEmail);
            $callerQuery->execute();
            $callerResult = $callerQuery->get_result();
            $callerName = "Admin";
            if ($callerResult->num_rows > 0) {
                $caller = $callerResult->fetch_assoc();
                $callerName = $caller['first_name'] . ' ' . $caller['last_name'];
            }
            
            // Create notification in video_call_notifications_table (if exists)
            $tableCheck = $conn->query("SHOW TABLES LIKE 'video_call_notifications_table'");
            if ($tableCheck->num_rows > 0) {
                $notifyQuery = $conn->prepare("INSERT INTO video_call_notifications_table (call_id, recipient_email, notification_type) VALUES (?, ?, 'incoming_call')");
                $notifyQuery->bind_param("is", $callId, $calleeEmail);
                $notifyQuery->execute();
            }
            
            // Create notification in main notifications_table
            $tableCheck2 = $conn->query("SHOW TABLES LIKE 'notifications_table'");
            if ($tableCheck2->num_rows > 0) {
                $notificationTitle = "Incoming Video Call";
                $notificationMessage = "$callerName is calling you. Click to join the video call.";
                $notificationStmt = $conn->prepare("INSERT INTO notifications_table (user_email, type, title, message, related_id, related_email, created_at) VALUES (?, 'call', ?, ?, ?, ?, NOW())");
                $notificationStmt->bind_param("sssis", $calleeEmail, $notificationTitle, $notificationMessage, $callId, $callerEmail);
                $notificationStmt->execute();
            }
            
            // Redirect to video call page
            header("Location: videocall.php?room=$roomId&call_id=$callId");
            exit();
        } else {
            $_SESSION['flash_mode'] = "alert-danger";
            $_SESSION['flash'] = "Failed to initiate call.";
        }
        
    } catch (Exception $e) {
        $_SESSION['flash_mode'] = "alert-danger";
        $_SESSION['flash'] = "Error: " . $e->getMessage();
    }
}

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'main_menu_admin.php'));
exit();
?>
