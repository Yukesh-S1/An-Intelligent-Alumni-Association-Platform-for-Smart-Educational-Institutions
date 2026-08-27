<?php
session_start();
include 'db_controller.php';
$conn->select_db("Alumni");

header('Content-Type: application/json');

if (!isset($_SESSION['logged_account']) || $_SESSION['logged_account']['type'] != 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$callId = $input['call_id'] ?? 0;

if ($callId > 0) {
    $stmt = $conn->prepare("DELETE FROM video_call_table WHERE id = ?");
    $stmt->bind_param("i", $callId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Failed to delete']);
    }
} else {
    echo json_encode(['error' => 'Invalid call ID']);
}

$conn->close();
?>
