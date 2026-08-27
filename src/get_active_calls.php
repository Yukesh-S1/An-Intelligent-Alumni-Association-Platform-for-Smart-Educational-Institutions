<?php
session_start();
include 'db_controller.php';
$conn->select_db("Alumni");

header('Content-Type: application/json');

if (!isset($_SESSION['logged_account'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$userEmail = $_SESSION['logged_account']['email'];

try {
    // Get active calls for current user (within last 30 minutes)
    $query = "SELECT vc.*, 
              u1.first_name as caller_first, u1.last_name as caller_last,
              u2.first_name as callee_first, u2.last_name as callee_last
              FROM video_call_table vc
              LEFT JOIN user_table u1 ON vc.caller_email = u1.email
              LEFT JOIN user_table u2 ON vc.callee_email = u2.email
              WHERE (vc.caller_email = ? OR vc.callee_email = ?)
              AND vc.call_status = 'active'
              AND vc.created_at >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
              ORDER BY vc.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $userEmail, $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $calls = [];
    while ($row = $result->fetch_assoc()) {
        $otherUser = ($row['caller_email'] == $userEmail) 
            ? ($row['callee_first'] . ' ' . $row['callee_last'])
            : ($row['caller_first'] . ' ' . $row['caller_last']);
        
        $timeAgo = timeAgo($row['created_at']);
        
        $calls[] = [
            'id' => $row['id'],
            'room_id' => $row['room_id'],
            'status' => $row['call_status'],
            'other_user' => $otherUser,
            'created_at' => $row['created_at'],
            'time_ago' => $timeAgo
        ];
    }
    
    echo json_encode(['success' => true, 'calls' => $calls]);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'Just now';
    if ($time < 3600) return floor($time/60) . ' min ago';
    if ($time < 86400) return floor($time/3600) . ' hr ago';
    return floor($time/86400) . ' days ago';
}

$conn->close();
?>
