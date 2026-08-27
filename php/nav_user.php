<!-- Dropdown menu on the top nav bar (User button) -->
<div class="d-flex align-items-center">
    <!-- Notifications -->
    <div class="dropdown me-3">
        <button class="btn btn-outline-secondary position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="notificationBell">
            <i class="bi bi-bell"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationCount" style="display: none;">
                0
            </span>
        </button>
        <div class="dropdown-menu dropdown-menu-end notification-dropdown" style="width: 350px; max-height: 400px; overflow-y: auto;">
            <div class="dropdown-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Notifications</h6>
                <button class="btn btn-sm btn-link text-decoration-none" onclick="markAllNotificationsRead()">Mark all read</button>
            </div>
            <div id="notificationsList">
                <div class="text-center p-3 text-muted">
                    <i class="bi bi-bell-slash fs-3"></i>
                    <p class="mb-0 mt-2">No notifications</p>
                </div>
            </div>
        </div>
    </div>

    <!-- User Profile Dropdown -->
    <div class="dropdown">
        <!-- Set image to display according to set profile image, or gender -->
        <div class="nav-user" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <?php 
                // Give admins an icon as indication
                $adminIcon = ($_SESSION['logged_account']['type'] == 'admin') ? '<img src="images/admin-icon.png" class="admin-icon" alt="admin">' : "";

                if (isset($_SESSION['logged_user']['profile_image']))
                    echo '<div class="image-container-nav"><img src="profile_images/'.$_SESSION['logged_user']['profile_image'].'" class="img-fluid nav-user" alt="profile_picture"></div>';
                elseif ($_SESSION['logged_user']['gender'] == "Male")
                    echo '<img src="profile_images/default-male-user-profile-icon.jpg" class="img-fluid nav-user" alt="profile_picture">';
                elseif ($_SESSION['logged_user']['gender'] == "Female")
                    echo '<img src="profile_images/default-female-user-profile-icon.jpg" class="img-fluid nav-user" alt="profile_picture">';

                echo $adminIcon;
            ?>
        </div>

        <!-- Menu option -->
        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-nav mt-1 px-2">
            <!-- If logged account type is 'user', enable options to view own profile and update profile -->
            <?php if ($_SESSION['logged_account']['type'] == 'user') { ?>
                <!-- My Profile -->
                <li><a class="dropdown-item py-2 pe-5" href="<?php echo 'profile_detail.php?email='.htmlspecialchars($_SESSION['logged_account']['email']) ?>">
                    <div class="circle-container me-2"><i class="bi bi-person-fill nav-user-bi"></i></div><div class="fw-medium">My Profile</div>
                </a></li>
                
                <!-- Edit profile -->
                <li><a class="dropdown-item py-2 pe-5" href="<?php echo 'update_profile.php?email='.htmlspecialchars($_SESSION['logged_account']['email'])?>">
                    <div class="circle-container me-2"><i class="bi bi-pencil-fill nav-user-bi"></i></div><div class="fw-medium">Edit My Profile</div>
                </a></li>

                <!-- Chat -->
                <li><a class="dropdown-item py-2 pe-5" href="chat.php">
                    <div class="circle-container me-2"><i class="bi bi-chat-dots-fill nav-user-bi"></i></div><div class="fw-medium">Messages</div>
                </a></li>
                <li><hr class="dropdown-divider border-2 mx-2"></li>
            <?php } ?>

            <!-- Logout -->
            <li><a class="dropdown-item py-2 pe-5" href="logout.php">
                <div class="circle-container me-2"><i class="bi bi-door-open-fill nav-user-bi"></i></div><div class="fw-medium">Log out</div>
            </a></li>
        </ul>
    </div>
</div>

<script>
// Notification system JavaScript
let notificationPolling = null;

function loadNotifications() {
    fetch('get_notifications.php')
        .then(response => response.json())
        .then(notifications => {
            const notificationsList = document.getElementById('notificationsList');
            const notificationCount = document.getElementById('notificationCount');
            
            // Count unread notifications
            const unreadCount = notifications.filter(n => !n.is_read).length;
            
            if (unreadCount > 0) {
                notificationCount.textContent = unreadCount;
                notificationCount.style.display = 'block';
            } else {
                notificationCount.style.display = 'none';
            }
            
            if (notifications.length === 0) {
                notificationsList.innerHTML = `
                    <div class="text-center p-3 text-muted">
                        <i class="bi bi-bell-slash fs-3"></i>
                        <p class="mb-0 mt-2">No notifications</p>
                    </div>
                `;
                return;
            }
            
            notificationsList.innerHTML = '';
            
            notifications.forEach(notification => {
                const notificationDiv = document.createElement('div');
                notificationDiv.className = `dropdown-item-text p-3 border-bottom ${!notification.is_read ? 'bg-light' : ''}`;
                
                let actionButtons = '';
                if (notification.type === 'chat_invitation' && notification.action_id) {
                    actionButtons = `
                        <div class="mt-2">
                            <button class="btn btn-sm btn-success me-2" onclick="handleChatInvitation(${notification.action_id}, 'accept')">Accept</button>
                            <button class="btn btn-sm btn-secondary" onclick="handleChatInvitation(${notification.action_id}, 'decline')">Decline</button>
                        </div>
                    `;
                }
                
                const profileImage = notification.related_user_image || 'profile_images/default-male-user-profile-icon.jpg';
                
                notificationDiv.innerHTML = `
                    <div class="d-flex align-items-start">
                        <img src="${profileImage}" class="rounded-circle me-3" width="40" height="40" alt="Profile">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">${notification.title}</h6>
                            <p class="mb-1 small text-muted">${notification.message}</p>
                            <small class="text-muted">${formatTimeAgo(notification.created_at)}</small>
                            ${actionButtons}
                        </div>
                        ${!notification.is_read ? '<div class="ms-2"><span class="badge bg-primary rounded-pill">&nbsp;</span></div>' : ''}
                    </div>
                `;
                
                // Mark as read when clicked (except for chat invitations)
                if (notification.type !== 'chat_invitation') {
                    notificationDiv.style.cursor = 'pointer';
                    notificationDiv.onclick = () => markNotificationRead(notification.id);
                }
                
                notificationsList.appendChild(notificationDiv);
            });
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
        });
}

function handleChatInvitation(invitationId, action) {
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
            loadNotifications(); // Refresh notifications
            if (action === 'accept') {
                // Optionally redirect to chat
                setTimeout(() => {
                    window.location.href = 'chat.php';
                }, 1000);
            }
        } else {
            alert('Error: ' + result.error);
        }
    })
    .catch(error => {
        console.error('Error handling chat invitation:', error);
    });
}

function markNotificationRead(notificationId) {
    fetch('mark_notification_read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ notification_id: notificationId })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            loadNotifications();
        }
    });
}

function markAllNotificationsRead() {
    fetch('mark_notification_read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ mark_all: true })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            loadNotifications();
        }
    });
}

function formatTimeAgo(timestamp) {
    const now = new Date();
    const time = new Date(timestamp);
    const diffInSeconds = Math.floor((now - time) / 1000);
    
    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + 'm ago';
    if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + 'h ago';
    return Math.floor(diffInSeconds / 86400) + 'd ago';
}

// Start notification polling when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadNotifications();
    // Poll every 10 seconds for new notifications
    notificationPolling = setInterval(loadNotifications, 10000);
});
</script>