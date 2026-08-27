<?php
session_start();
include 'logged_user.php';
include 'db_controller.php';

$conn->select_db("Alumni");
$current_user = $_SESSION['logged_account']['email'];

// Get all invitations for current user
$invitations_query = "SELECT ci.*, 
    CASE 
        WHEN ci.sender_email = '$current_user' THEN u2.first_name
        ELSE u1.first_name
    END as other_first_name,
    CASE 
        WHEN ci.sender_email = '$current_user' THEN u2.last_name
        ELSE u1.last_name
    END as other_last_name,
    CASE 
        WHEN ci.sender_email = '$current_user' THEN u2.email
        ELSE u1.email
    END as other_email,
    CASE 
        WHEN ci.sender_email = '$current_user' THEN 'sent'
        ELSE 'received'
    END as invitation_type
    FROM chat_invitations_table ci
    LEFT JOIN user_table u1 ON ci.sender_email = u1.email
    LEFT JOIN user_table u2 ON ci.receiver_email = u2.email
    WHERE ci.sender_email = '$current_user' OR ci.receiver_email = '$current_user'
    ORDER BY ci.sent_at DESC";

$invitations = $conn->query($invitations_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Chat Invitations - Alumni Engaging Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>
    <?php include 'nav_user.php'; ?>
    
    <div class="container mt-4">
        <h2><i class="bi bi-envelope-paper me-2"></i>Chat Invitations</h2>
        
        <div class="row mt-4">
            <div class="col-12">
                <?php if ($invitations->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-primary">
                                <tr>
                                    <th>User</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Message</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($invitation = $invitations->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo $invitation['other_first_name'] . ' ' . $invitation['other_last_name']; ?></strong><br>
                                            <small class="text-muted"><?php echo $invitation['other_email']; ?></small>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $invitation['invitation_type'] == 'sent' ? 'bg-primary' : 'bg-success'; ?>">
                                                <?php echo ucfirst($invitation['invitation_type']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge 
                                                <?php 
                                                    echo $invitation['status'] == 'pending' ? 'bg-warning' : 
                                                        ($invitation['status'] == 'accepted' ? 'bg-success' : 'bg-danger'); 
                                                ?>">
                                                <?php echo ucfirst($invitation['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $invitation['message'] ?: '<em>No message</em>'; ?></td>
                                        <td><?php echo date('M j, Y g:i A', strtotime($invitation['sent_at'])); ?></td>
                                        <td>
                                            <?php if ($invitation['status'] == 'pending'): ?>
                                                <?php if ($invitation['invitation_type'] == 'received'): ?>
                                                    <button class="btn btn-sm btn-success me-1" onclick="handleInvitation(<?php echo $invitation['id']; ?>, 'accept')">
                                                        Accept
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-secondary" onclick="handleInvitation(<?php echo $invitation['id']; ?>, 'decline')">
                                                        Decline
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="cancelInvitation(<?php echo $invitation['id']; ?>)">
                                                        Cancel
                                                    </button>
                                                <?php endif; ?>
                                            <?php elseif ($invitation['status'] == 'accepted'): ?>
                                                <a href="chat.php" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-chat-dots me-1"></i>Chat
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center p-5">
                        <i class="bi bi-envelope-slash fs-1 text-muted"></i>
                        <h4 class="mt-3 text-muted">No Chat Invitations</h4>
                        <p class="text-muted">You haven't sent or received any chat invitations yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function handleInvitation(invitationId, action) {
            fetch('handle_chat_invitation.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    invitation_id: invitationId, 
                    action: action 
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    location.reload();
                } else {
                    alert('Error: ' + result.error);
                }
            });
        }

        function cancelInvitation(invitationId) {
            if (confirm('Are you sure you want to cancel this invitation?')) {
                fetch('handle_chat_invitation.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        invitation_id: invitationId, 
                        action: 'cancel' 
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + result.error);
                    }
                });
            }
        }
    </script>
</body>
</html>
