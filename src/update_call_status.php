<?php
include 'db_controller.php';
$conn->select_db("Alumni");

session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_account'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['call_id']) || !isset($input['status'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit();
}

$callId = $input['call_id'];
$status = $input['status'];
$userEmail = $_SESSION['logged_account']['email'];

try {
    // Verify user is part of this call
    $verifyQuery = $conn->prepare("SELECT * FROM video_call_table WHERE id = ? AND (caller_email = ? OR callee_email = ?)");
    $verifyQuery->bind_param("iss", $callId, $userEmail, $userEmail);
    $verifyQuery->execute();
    $result = $verifyQuery->get_result();
    
    if ($result->num_rows == 0) {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied']);
        exit();
    }
    
    // Update call status
    if ($status == 'active') {
        $updateQuery = $conn->prepare("UPDATE video_call_table SET call_status = ?, call_start_time = CURRENT_TIMESTAMP WHERE id = ?");
        $updateQuery->bind_param("si", $status, $callId);
    } elseif ($status == 'ended') {
        $updateQuery = $conn->prepare("UPDATE video_call_table SET call_status = ?, call_end_time = CURRENT_TIMESTAMP WHERE id = ?");
        $updateQuery->bind_param("si", $status, $callId);
    } else {
        $updateQuery = $conn->prepare("UPDATE video_call_table SET call_status = ? WHERE id = ?");
        $updateQuery->bind_param("si", $status, $callId);
    }
    
    if ($updateQuery->execute()) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Database update failed']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}

$conn->close();
?>
