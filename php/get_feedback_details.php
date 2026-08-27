<?php
session_start();
include 'db_controller.php';

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['logged_account']) || $_SESSION['logged_account']['type'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Admin access required']);
    exit();
}

$conn->select_db("Alumni");

if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Feedback ID is required']);
    exit();
}

$feedback_id = intval($_GET['id']);

try {
    $stmt = $conn->prepare("SELECT f.*, u.first_name, u.last_name 
                           FROM feedback_table f 
                           LEFT JOIN user_table u ON f.alumni_email = u.email 
                           WHERE f.id = ?");
    $stmt->bind_param("i", $feedback_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $feedback = $result->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'feedback' => [
                'id' => $feedback['id'],
                'alumni_name' => $feedback['first_name'] . ' ' . $feedback['last_name'],
                'alumni_email' => $feedback['alumni_email'],
                'feedback_type' => ucfirst($feedback['feedback_type']),
                'subject' => $feedback['subject'],
                'feedback_text' => $feedback['feedback_text'],
                'rating' => $feedback['rating'],
                'category' => $feedback['category'],
                'status' => $feedback['status'],
                'admin_response' => $feedback['admin_response'],
                'created_at' => $feedback['created_at'],
                'updated_at' => $feedback['updated_at']
            ]
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Feedback not found']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
?>