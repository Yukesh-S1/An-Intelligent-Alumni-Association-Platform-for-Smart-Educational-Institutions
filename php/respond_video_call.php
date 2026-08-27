<?php
session_start();
include 'db_controller.php';
$conn->select_db("Alumni");

header('Content-Type: application/json');

if (!isset($_SESSION['logged_account'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$userEmail = $_SESSION['logged_account']['email'];
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['call_id']) || !isset($input['action'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing parameters']);
    exit();
}

$callId = intval($input['call_id']);
$action = $input['action'];

try {
    // Get call details
    $stmt = $conn->prepare("SELECT * FROM video_call_table WHERE id = ? AND callee_email = ?");
    $stmt->bind_param("is", $callId, $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Call not found']);
        exit();
    }
    
    $call = $result->fetch_assoc();
    
    if ($action === 'accept') {
        // For our simple system, just return success with room info
        // The call is already active, alumni just needs to join
        echo json_encode([
            'success' => true,
            'message' => 'Call accepted',
            'room_id' => $call['room_id'],
            'call_id' => $callId
        ]);
        
    } elseif ($action === 'decline') {
        // Update call status to declined
        $updateStmt = $conn->prepare("UPDATE video_call_table SET call_status = 'declined', call_end_time = NOW() WHERE id = ?");
        $updateStmt->bind_param("i", $callId);
        
        if ($updateStmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Call declined'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to decline call']);
        }
        
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>
