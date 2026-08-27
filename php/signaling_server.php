<?php
session_start();
include 'db_controller.php';
$conn->select_db("Alumni");

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['logged_account'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Store signaling message
    $input = json_decode(file_get_contents('php://input'), true);
    
    $roomId = $input['room_id'] ?? '';
    $type = $input['type'] ?? '';
    $data = json_encode($input['data'] ?? []);
    $senderEmail = $_SESSION['logged_account']['email'];
    
    if (empty($roomId) || empty($type)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing parameters']);
        exit();
    }
    
    try {
        // Check if table exists
        $tableCheck = $conn->query("SHOW TABLES LIKE 'signaling_messages'");
        if ($tableCheck->num_rows == 0) {
            // Create table
            $createTable = "CREATE TABLE signaling_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                room_id VARCHAR(100) NOT NULL,
                sender_email VARCHAR(255) NOT NULL,
                message_type VARCHAR(50) NOT NULL,
                message_data TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_room (room_id),
                INDEX idx_created (created_at)
            )";
            $conn->query($createTable);
        }
        
        // Insert message
        $stmt = $conn->prepare("INSERT INTO signaling_messages (room_id, sender_email, message_type, message_data) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $roomId, $senderEmail, $type, $data);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to store message']);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    
} elseif ($method === 'GET') {
    // Retrieve signaling messages
    $roomId = $_GET['room_id'] ?? '';
    $lastId = intval($_GET['last_id'] ?? 0);
    $userEmail = $_SESSION['logged_account']['email'];
    
    if (empty($roomId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing room_id']);
        exit();
    }
    
    try {
        // Check if table exists
        $tableCheck = $conn->query("SHOW TABLES LIKE 'signaling_messages'");
        if ($tableCheck->num_rows == 0) {
            echo json_encode(['messages' => []]);
            exit();
        }
        
        // Get messages for this room from other users
        $stmt = $conn->prepare("SELECT id, sender_email, message_type, message_data, created_at 
                               FROM signaling_messages 
                               WHERE room_id = ? AND sender_email != ? AND id > ? 
                               ORDER BY id ASC");
        $stmt->bind_param("ssi", $roomId, $userEmail, $lastId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = [
                'id' => $row['id'],
                'sender_email' => $row['sender_email'],
                'type' => $row['message_type'],
                'data' => json_decode($row['message_data'], true),
                'timestamp' => $row['created_at']
            ];
        }
        
        echo json_encode(['messages' => $messages]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

$conn->close();
?>
