<?php
session_start();
include 'db_controller.php';

header('Content-Type: application/json');

// Check database connection
if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// Select database
$conn->select_db("Alumni");

if (!isset($_SESSION['logged_account']['email'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['invitation_id']) || !isset($input['action'])) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

$invitation_id = intval($input['invitation_id']);
$action = $input['action']; // 'accept' or 'decline'
$current_user = $_SESSION['logged_account']['email'];

// Verify invitation exists and user is the receiver
$invitation_query = "SELECT * FROM chat_invitations_table 
                    WHERE id = $invitation_id AND receiver_email = '$current_user' AND status = 'pending'";
$invitation_result = $conn->query($invitation_query);

if (!$invitation_result) {
    echo json_encode(['success' => false, 'error' => 'Database query failed: ' . $conn->error]);
    exit;
}

if ($invitation_result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid invitation']);
    exit;
}

$invitation = $invitation_result->fetch_assoc();
$sender_email = $invitation['sender_email'];

if ($action === 'accept') {
    // Update invitation status
    $update_invitation = "UPDATE chat_invitations_table 
                         SET status = 'accepted', responded_at = CURRENT_TIMESTAMP 
                         WHERE id = $invitation_id";
    if (!$conn->query($update_invitation)) {
        echo json_encode(['success' => false, 'error' => 'Failed to update invitation: ' . $conn->error]);
        exit;
    }
    
    // Create conversation
    $create_conversation = "INSERT INTO chat_conversations_table (user1_email, user2_email) 
                           VALUES ('$sender_email', '$current_user')";
    if (!$conn->query($create_conversation)) {
        echo json_encode(['success' => false, 'error' => 'Failed to create conversation: ' . $conn->error]);
        exit;
    }
    
    // Get receiver details for notification
    $receiver_query = "SELECT first_name, last_name FROM user_table WHERE email = '$current_user'";
    $receiver_result = $conn->query($receiver_query);
    if (!$receiver_result) {
        echo json_encode(['success' => false, 'error' => 'Failed to get user details: ' . $conn->error]);
        exit;
    }
    $receiver = $receiver_result->fetch_assoc();
    
    // Notify sender about acceptance
    $notification_title = "Chat Invitation Accepted";
    $notification_message = $receiver['first_name'] . ' ' . $receiver['last_name'] . ' accepted your chat invitation. You can now start messaging!';
    
    $notification_query = "INSERT INTO notifications_table (user_email, type, title, message, related_email) 
                          VALUES ('$sender_email', 'message', '$notification_title', '$notification_message', '$current_user')";
    if (!$conn->query($notification_query)) {
        echo json_encode(['success' => false, 'error' => 'Failed to create notification: ' . $conn->error]);
        exit;
    }
    
    echo json_encode(['success' => true, 'message' => 'Chat invitation accepted']);
    
} elseif ($action === 'decline') {
    // Update invitation status
    $update_invitation = "UPDATE chat_invitations_table 
                         SET status = 'declined', responded_at = CURRENT_TIMESTAMP 
                         WHERE id = $invitation_id";
    if (!$conn->query($update_invitation)) {
        echo json_encode(['success' => false, 'error' => 'Failed to decline invitation: ' . $conn->error]);
        exit;
    }
    
    echo json_encode(['success' => true, 'message' => 'Chat invitation declined']);
    
} elseif ($action === 'cancel') {
    // Verify user is the sender for cancel action
    $cancel_check = "SELECT * FROM chat_invitations_table 
                    WHERE id = $invitation_id AND sender_email = '$current_user' AND status = 'pending'";
    $cancel_result = $conn->query($cancel_check);
    
    if (!$cancel_result) {
        echo json_encode(['success' => false, 'error' => 'Database query failed: ' . $conn->error]);
        exit;
    }
    
    if ($cancel_result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Cannot cancel this invitation']);
        exit;
    }
    
    // Delete the invitation completely
    $delete_invitation = "DELETE FROM chat_invitations_table WHERE id = $invitation_id";
    if (!$conn->query($delete_invitation)) {
        echo json_encode(['success' => false, 'error' => 'Failed to cancel invitation: ' . $conn->error]);
        exit;
    }
    
    echo json_encode(['success' => true, 'message' => 'Chat invitation cancelled']);
    
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
?>
