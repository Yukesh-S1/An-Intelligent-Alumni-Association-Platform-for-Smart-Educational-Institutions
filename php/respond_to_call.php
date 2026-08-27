<?php
include 'db_controller.php';
$conn->select_db("Alumni");

session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_account'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $callId = $_POST['call_id'];
    $response = $_POST['response']; // 'accept' or 'decline'
    $userEmail = $_SESSION['logged_account']['email'];
    
    try {
        // Verify user is the callee
        $verifyQuery = $conn->prepare("SELECT * FROM video_call_table WHERE id = ? AND callee_email = ?");
        $verifyQuery->bind_param("is", $callId, $userEmail);
        $verifyQuery->execute();
        $result = $verifyQuery->get_result();
        
        if ($result->num_rows == 0) {
            $_SESSION['flash_mode'] = "alert-danger";
            $_SESSION['flash'] = "Invalid call request.";
            header('Location: main_menu.php');
            exit();
        }
        
        $call = $result->fetch_assoc();
        
        if ($response == 'accept') {
            // Mark notification as read
            $updateNotifyQuery = $conn->prepare("UPDATE video_call_notifications_table SET is_read = TRUE WHERE call_id = ? AND recipient_email = ?");
            $updateNotifyQuery->bind_param("is", $callId, $userEmail);
            $updateNotifyQuery->execute();
            
            // Redirect to video call
            header("Location: video_call.php?room=" . $call['room_id'] . "&call_id=" . $callId);
            exit();
            
        } elseif ($response == 'decline') {
            // Update call status to declined
            $updateCallQuery = $conn->prepare("UPDATE video_call_table SET call_status = 'declined' WHERE id = ?");
            $updateCallQuery->bind_param("i", $callId);
            $updateCallQuery->execute();
            
            // Mark notification as read
            $updateNotifyQuery = $conn->prepare("UPDATE video_call_notifications_table SET is_read = TRUE WHERE call_id = ? AND recipient_email = ?");
            $updateNotifyQuery->bind_param("is", $callId, $userEmail);
            $updateNotifyQuery->execute();
            
            $_SESSION['flash_mode'] = "alert-info";
            $_SESSION['flash'] = "Call declined.";
        }
        
    } catch (Exception $e) {
        $_SESSION['flash_mode'] = "alert-danger";
        $_SESSION['flash'] = "Error processing call response.";
    }
}

header('Location: main_menu.php');
exit();
?>
