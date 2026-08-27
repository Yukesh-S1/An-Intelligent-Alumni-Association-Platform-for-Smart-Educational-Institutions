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
    // Get active calls for this user (within last 10 minutes)
    $query = "SELECT vc.*, 
              u.first_name as caller_first, u.last_name as caller_last,
              u.profile_image
              FROM video_call_table vc
              JOIN user_table u ON vc.caller_email = u.email
              WHERE vc.callee_email = ? 
              AND vc.call_status = 'active'
              AND vc.created_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
              ORDER BY vc.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $timeAgo = timeAgo($row['created_at']);
        
        $notifications[] = [
            'id' => $row['id'],
            'caller_name' => $row['caller_first'] . ' ' . $row['caller_last'],
            'caller_email' => $row['caller_email'],
            'caller_image' => $row['profile_image'] ?? 'profile_images/default-male-user-profile-icon.jpg',
            'room_id' => $row['room_id'],
            'created_at' => $row['created_at'],
            'time_ago' => $timeAgo
        ];
    }
    
    echo json_encode(['success' => true, 'notifications' => $notifications]);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'Just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    return floor($time/86400) . ' days ago';
}

$conn->close();
?>
