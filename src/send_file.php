<?php
session_start();
include 'db_controller.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_account']['email'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$conn->select_db("Alumni");

if (!isset($_POST['conversation_id']) || !isset($_FILES['file'])) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

$conversation_id = intval($_POST['conversation_id']);
$current_user = $conn->real_escape_string($_SESSION['logged_account']['email']);
$receiver_email = isset($_POST['receiver_email']) ? $conn->real_escape_string($_POST['receiver_email']) : '';

// Verify user is part of this conversation
$verify_query = "SELECT * FROM chat_conversations_table 
                WHERE id = ? 
                AND (user1_email = ? OR user2_email = ?)";
$verify_stmt = $conn->prepare($verify_query);
$verify_stmt->bind_param("iss", $conversation_id, $current_user, $current_user);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$file = $_FILES['file'];
$file_name = $file['name'];
$file_size = $file['size'];
$file_tmp = $file['tmp_name'];
$file_error = $file['error'];

// Check for upload errors
if ($file_error !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'File upload error: ' . $file_error]);
    exit;
}

// Check file size (max 10MB)
if ($file_size > 10 * 1024 * 1024) {
    echo json_encode(['success' => false, 'error' => 'File too large (max 10MB)']);
    exit;
}

// Create uploads directory if it doesn't exist
$upload_dir = 'uploads/chat_files/';
if (!file_exists($upload_dir)) {
    if (!mkdir($upload_dir, 0777, true)) {
        echo json_encode(['success' => false, 'error' => 'Failed to create upload directory']);
        exit;
    }
}

// Generate unique filename
$file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
$unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
$file_path = $upload_dir . $unique_filename;

// Move uploaded file
if (move_uploaded_file($file_tmp, $file_path)) {
    // Determine file type
    $file_type = 'file';
    if (function_exists('mime_content_type')) {
        $mime_type = mime_content_type($file_path);
        
        if (strpos($mime_type, 'image/') === 0) {
            $file_type = 'image';
        } elseif (strpos($mime_type, 'video/') === 0) {
            $file_type = 'video';
        }
    } else {
        // Fallback based on file extension
        $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        $video_extensions = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'];
        
        if (in_array(strtolower($file_extension), $image_extensions)) {
            $file_type = 'image';
        } elseif (in_array(strtolower($file_extension), $video_extensions)) {
            $file_type = 'video';
        }
    }
    
    // Check if message_type column exists (migration check)
    $column_check = $conn->query("SHOW COLUMNS FROM chat_messages_table LIKE 'message_type'");
    if ($column_check->num_rows === 0) {
        // Run migration automatically
        $alter_queries = [
            "ALTER TABLE chat_messages_table ADD COLUMN message_type ENUM('text', 'image', 'video', 'file') DEFAULT 'text'",
            "ALTER TABLE chat_messages_table ADD COLUMN file_name VARCHAR(255) NULL",
            "ALTER TABLE chat_messages_table ADD COLUMN file_path VARCHAR(500) NULL", 
            "ALTER TABLE chat_messages_table ADD COLUMN file_size INT NULL"
        ];
        
        foreach ($alter_queries as $query) {
            $conn->query($query);
        }
    }
    
    // Insert file message into database using prepared statement
    $message = "File: " . $file_name;
    $insert_query = "INSERT INTO chat_messages_table (conversation_id, sender_email, message, message_type, file_name, file_path, file_size) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("isssssi", $conversation_id, $current_user, $message, $file_type, $file_name, $file_path, $file_size);
    
    if ($insert_stmt->execute()) {
        // Update conversation timestamp
        $update_conv = "UPDATE chat_conversations_table 
                       SET last_message_at = CURRENT_TIMESTAMP 
                       WHERE id = ?";
        $update_stmt = $conn->prepare($update_conv);
        $update_stmt->bind_param("i", $conversation_id);
        $update_stmt->execute();
        
        // Create notification
        if (!empty($receiver_email)) {
            $sender_query = "SELECT first_name, last_name FROM user_table WHERE email = ?";
            $sender_stmt = $conn->prepare($sender_query);
            $sender_stmt->bind_param("s", $current_user);
            $sender_stmt->execute();
            $sender_result = $sender_stmt->get_result();
            
            if ($sender_result && $sender_result->num_rows > 0) {
                $sender = $sender_result->fetch_assoc();
                $notification_title = "New File";
                $notification_message = $sender['first_name'] . ' ' . $sender['last_name'] . ' sent you a file: ' . $file_name;
                
                $notification_query = "INSERT INTO notifications_table (user_email, type, title, message, related_id, related_email) 
                                      VALUES (?, 'message', ?, ?, ?, ?)";
                $notification_stmt = $conn->prepare($notification_query);
                $notification_stmt->bind_param("sssis", $receiver_email, $notification_title, $notification_message, $conversation_id, $current_user);
                $notification_stmt->execute();
            }
        }
        
        echo json_encode(['success' => true, 'file_path' => $file_path]);
    } else {
        // Delete uploaded file if database insert fails
        unlink($file_path);
        echo json_encode(['success' => false, 'error' => 'Failed to save file info: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to upload file']);
}
?>
