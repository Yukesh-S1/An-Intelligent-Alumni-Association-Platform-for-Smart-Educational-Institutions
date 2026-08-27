<?php
session_start();
include 'db_controller.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['logged_account']['email'])) {
    echo json_encode(['notifications' => [], 'total_count' => 0]);
    exit;
}

try {
    $conn->select_db("Alumni");
    $current_user = $_SESSION['logged_account']['email'];
    $all_notifications = [];

    // Auto-cleanup: Mark old video calls as expired
    $conn->query("UPDATE video_call_table 
                  SET call_status = 'expired', call_end_time = NOW() 
                  WHERE call_status = 'pending' 
                  AND created_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE)");

    // 1. Get regular notifications
    $table_check = $conn->query("SHOW TABLES LIKE 'notifications_table'");
    if ($table_check->num_rows > 0) {
        $query = "SELECT *, 'notification' as notification_type, created_at as timestamp 
                  FROM notifications_table 
                  WHERE user_email = '$current_user' AND is_read = FALSE 
                  ORDER BY created_at DESC LIMIT 5";
        
        $result = $conn->query($query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $all_notifications[] = [
                    'id' => $row['id'],
                    'type' => 'notification',
                    'subtype' => $row['type'] ?? 'general',
                    'title' => $row['title'] ?? 'Notification',
                    'message' => $row['message'] ?? '',
                    'timestamp' => $row['timestamp'],
                    'time_ago' => timeAgo($row['timestamp']),
                    'is_read' => false,
                    'icon' => getNotificationIcon($row['type'] ?? 'general'),
                    'color' => 'primary'
                ];
            }
        }
    }

    // 2. Get pending video call invitations (only recent ones within 10 minutes)
    $video_calls_query = "SELECT vc.*, 
                          u.first_name as caller_first_name, 
                          u.last_name as caller_last_name,
                          u.profile_image as caller_image,
                          vc.created_at as timestamp
                          FROM video_call_table vc
                          JOIN user_table u ON vc.caller_email = u.email
                          WHERE vc.callee_email = '$current_user' 
                          AND vc.call_status = 'pending'
                          AND vc.created_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
                          ORDER BY vc.created_at DESC LIMIT 5";
    
    $video_result = $conn->query($video_calls_query);
    if ($video_result) {
        while ($row = $video_result->fetch_assoc()) {
            $caller_name = trim($row['caller_first_name'] . ' ' . $row['caller_last_name']);
            $all_notifications[] = [
                'id' => $row['id'],
                'type' => 'video_call',
                'subtype' => 'incoming_call',
                'title' => 'Incoming Video Call',
                'message' => $caller_name . ' is calling you',
                'timestamp' => $row['timestamp'],
                'time_ago' => timeAgo($row['timestamp']),
                'is_read' => false,
                'icon' => 'bi-camera-video',
                'color' => 'success',
                'caller_name' => $caller_name,
                'caller_email' => $row['caller_email'],
                'caller_image' => $row['caller_image'] ?? 'profile_images/default-male-user-profile-icon.jpg',
                'room_id' => $row['room_id'],
                'call_id' => $row['id']
            ];
        }
    }

    // 3. Get unread messages
    $messages_query = "SELECT cm.*, cc.*, 
                       u.first_name as sender_first_name, 
                       u.last_name as sender_last_name,
                       u.profile_image as sender_image,
                       cm.created_at as timestamp
                       FROM chat_messages_table cm 
                       JOIN chat_conversations_table cc ON cm.conversation_id = cc.id 
                       JOIN user_table u ON cm.sender_email = u.email
                       WHERE (cc.user1_email = '$current_user' OR cc.user2_email = '$current_user') 
                       AND cm.sender_email != '$current_user' 
                       AND cm.is_read = FALSE
                       ORDER BY cm.created_at DESC LIMIT 5";
    
    $messages_result = $conn->query($messages_query);
    if ($messages_result) {
        while ($row = $messages_result->fetch_assoc()) {
            $sender_name = trim($row['sender_first_name'] . ' ' . $row['sender_last_name']);
            $message_preview = strlen($row['message']) > 50 ? substr($row['message'], 0, 50) . '...' : $row['message'];
            
            $all_notifications[] = [
                'id' => $row['id'],
                'type' => 'message',
                'subtype' => 'chat_message',
                'title' => 'New Message from ' . $sender_name,
                'message' => $message_preview,
                'timestamp' => $row['timestamp'],
                'time_ago' => timeAgo($row['timestamp']),
                'is_read' => false,
                'icon' => 'bi-chat-dots',
                'color' => 'info',
                'sender_name' => $sender_name,
                'sender_email' => $row['sender_email'],
                'sender_image' => $row['sender_image'] ?? 'profile_images/default-male-user-profile-icon.jpg',
                'conversation_id' => $row['conversation_id']
            ];
        }
    }

    // Sort all notifications by timestamp (newest first)
    usort($all_notifications, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });

    // Limit to 10 most recent notifications
    $all_notifications = array_slice($all_notifications, 0, 10);

    echo json_encode([
        'notifications' => $all_notifications,
        'total_count' => count($all_notifications)
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_unified_notifications.php: " . $e->getMessage());
    echo json_encode(['notifications' => [], 'total_count' => 0, 'error' => $e->getMessage()]);
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'Just now';
    if ($time < 3600) return floor($time/60) . 'm ago';
    if ($time < 86400) return floor($time/3600) . 'h ago';
    if ($time < 604800) return floor($time/86400) . 'd ago';
    return date('M j', strtotime($datetime));
}

function getNotificationIcon($type) {
    $icons = [
        'message' => 'bi-chat-dots',
        'call' => 'bi-telephone',
        'video_call' => 'bi-camera-video',
        'event' => 'bi-calendar-event',
        'advertisement' => 'bi-briefcase',
        'chat_invitation' => 'bi-person-plus',
        'feedback' => 'bi-chat-square-text',
        'general' => 'bi-info-circle'
    ];
    return $icons[$type] ?? 'bi-bell';
}

$conn->close();
?>