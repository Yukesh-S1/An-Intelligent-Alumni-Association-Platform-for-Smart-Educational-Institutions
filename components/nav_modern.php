<?php
// Get current page for active navigation
$current_page = basename($_SERVER['PHP_SELF']);
$user_type = $_SESSION['logged_account']['type'] ?? 'user';
?>

<style>
/* NAVIGATION TEXT FIX */
.navbar-brand, .nav-link, .navbar-nav .nav-link { color: #ffffff !important; }
.dropdown-item { color: #000000 !important; }

/* NOTIFICATION ENHANCEMENTS */
.notification-item {
    transition: all 0.2s ease;
    cursor: pointer;
    border-radius: 8px;
    margin: 2px 0;
}

.notification-item:hover {
    background-color: #f8f9fa !important;
    transform: translateX(2px);
}

.notification-item img {
    object-fit: cover;
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.dropdown-menu {
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

#notificationBadge {
    font-size: 0.7rem;
    min-width: 18px;
    height: 18px;
    line-height: 18px;
}
</style>

<nav class="navbar navbar-expand-lg navbar-modern">
    <div class="container-fluid px-4">
        <!-- Brand -->
        <a class="navbar-brand nav-brand-modern d-flex align-items-center" href="<?php echo ($user_type === 'admin') ? 'main_menu_admin.php' : 'main_menu.php'; ?>">
            <i class="bi bi-mortarboard-fill me-2"></i>
            Alumni Portal
        </a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation Links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if ($user_type === 'admin'): ?>
                    <!-- Admin Navigation -->
                    <li class="nav-item">
                        <a class="nav-link nav-link-modern <?php echo ($current_page === 'main_menu_admin.php') ? 'active' : ''; ?>" 
                           href="main_menu_admin.php">
                            <i class="bi bi-house-door me-1"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-modern <?php echo (strpos($current_page, 'manage_') !== false) ? 'active' : ''; ?>" 
                           href="manage_users.php">
                            <i class="bi bi-people me-1"></i>
                            Manage Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-modern <?php echo (strpos($current_page, 'event') !== false) ? 'active' : ''; ?>" 
                           href="manage_events.php">
                            <i class="bi bi-calendar-event me-1"></i>
                            Events
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-modern <?php echo (strpos($current_page, 'news') !== false) ? 'active' : ''; ?>" 
                           href="manage_news.php">
                            <i class="bi bi-newspaper me-1"></i>
                            News
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-modern <?php echo (strpos($current_page, 'video_call') !== false) ? 'active' : ''; ?>" 
                           href="manage_video_calls.php">
                            <i class="bi bi-camera-video me-1"></i>
                            Video Calls
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-modern <?php echo (strpos($current_page, 'feedback') !== false) ? 'active' : ''; ?>" 
                           href="manage_feedback.php">
                            <i class="bi bi-chat-square-text me-1"></i>
                            Feedback
                        </a>
                    </li>
                <?php else: ?>
                    <!-- User Navigation -->
                    <li class="nav-item">
                        <a class="nav-link nav-link-modern <?php echo ($current_page === 'main_menu.php') ? 'active' : ''; ?>" 
                           href="main_menu.php">
                            <i class="bi bi-house-door me-1"></i>
                            Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-modern <?php echo (strpos($current_page, 'alumni') !== false) ? 'active' : ''; ?>" 
                           href="view_alumni.php">
                            <i class="bi bi-people me-1"></i>
                            Alumni Network
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-modern <?php echo (strpos($current_page, 'event') !== false) ? 'active' : ''; ?>" 
                           href="view_events.php">
                            <i class="bi bi-calendar-event me-1"></i>
                            Events
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-modern <?php echo (strpos($current_page, 'news') !== false) ? 'active' : ''; ?>" 
                           href="view_news_modern.php">
                            <i class="bi bi-newspaper me-1"></i>
                            News
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-modern <?php echo (strpos($current_page, 'message') !== false || strpos($current_page, 'chat') !== false) ? 'active' : ''; ?>" 
                           href="messages.php">
                            <i class="bi bi-chat-dots me-1"></i>
                            Messages
                            <?php
                            // Show unread message count
                            if (isset($_SESSION['logged_account']['email']) && isset($conn)) {
                                $current_user = $_SESSION['logged_account']['email'];
                                try {
                                    $unread_query = "SELECT COUNT(*) as unread FROM chat_messages_table cm 
                                                   JOIN chat_conversations_table cc ON cm.conversation_id = cc.id 
                                                   WHERE (cc.user1_email = '$current_user' OR cc.user2_email = '$current_user') 
                                                   AND cm.sender_email != '$current_user' AND cm.is_read = FALSE";
                                    $unread_result = $conn->query($unread_query);
                                    if ($unread_result && $unread_result->num_rows > 0) {
                                        $unread_count = $unread_result->fetch_assoc()['unread'];
                                        if ($unread_count > 0) {
                                            echo '<span class="badge bg-danger ms-1">' . $unread_count . '</span>';
                                        }
                                    }
                                } catch (Exception $e) {
                                    // Silently handle database errors in navigation
                                }
                            }
                            ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-modern <?php echo (strpos($current_page, 'video_call_invitations') !== false) ? 'active' : ''; ?>" 
                           href="video_call_invitations.php">
                            <i class="bi bi-camera-video me-1"></i>
                            Video Calls
                            <?php
                            // Show pending video call count
                            if (isset($_SESSION['logged_account']['email']) && isset($conn) && $conn->ping()) {
                                $current_user = $_SESSION['logged_account']['email'];
                                try {
                                    $video_calls_query = "SELECT COUNT(*) as pending FROM video_call_table WHERE callee_email = '$current_user' AND call_status = 'pending' AND created_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)";
                                    $video_calls_result = $conn->query($video_calls_query);
                                    if ($video_calls_result && $video_calls_result->num_rows > 0) {
                                        $row = $video_calls_result->fetch_assoc();
                                        $pending_calls = $row['pending'] ?? 0;
                                        if ($pending_calls > 0) {
                                            echo '<span class="badge bg-success ms-1">' . $pending_calls . '</span>';
                                        }
                                    }
                                } catch (Exception $e) {
                                    // Silently handle database errors in navigation
                                    error_log("Video call count error: " . $e->getMessage());
                                }
                            }
                            ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-modern <?php echo (strpos($current_page, 'notifications') !== false) ? 'active' : ''; ?>" 
                           href="notifications_center.php">
                            <i class="bi bi-bell me-1"></i>
                            Notifications
                            <?php
                            // Show total notification count
                            if (isset($_SESSION['logged_account']['email']) && isset($conn) && $conn->ping()) {
                                $current_user = $_SESSION['logged_account']['email'];
                                try {
                                    // Count all types of notifications
                                    $total_notifications = 0;
                                    
                                    // Regular notifications
                                    $notif_check = $conn->query("SHOW TABLES LIKE 'notifications_table'");
                                    if ($notif_check && $notif_check->num_rows > 0) {
                                        $notif_result = $conn->query("SELECT COUNT(*) as count FROM notifications_table WHERE user_email = '$current_user' AND is_read = FALSE");
                                        if ($notif_result) {
                                            $row = $notif_result->fetch_assoc();
                                            $total_notifications += $row['count'] ?? 0;
                                        }
                                    }
                                    
                                    // Video calls (only recent ones within 10 minutes)
                                    $video_result = $conn->query("SELECT COUNT(*) as count FROM video_call_table WHERE callee_email = '$current_user' AND call_status = 'pending' AND created_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)");
                                    if ($video_result) {
                                        $row = $video_result->fetch_assoc();
                                        $total_notifications += $row['count'] ?? 0;
                                    }
                                    
                                    // Unread messages
                                    $msg_result = $conn->query("SELECT COUNT(*) as count FROM chat_messages_table cm JOIN chat_conversations_table cc ON cm.conversation_id = cc.id WHERE (cc.user1_email = '$current_user' OR cc.user2_email = '$current_user') AND cm.sender_email != '$current_user' AND cm.is_read = FALSE");
                                    if ($msg_result) {
                                        $row = $msg_result->fetch_assoc();
                                        $total_notifications += $row['count'] ?? 0;
                                    }
                                    
                                    if ($total_notifications > 0) {
                                        echo '<span class="badge bg-danger ms-1">' . $total_notifications . '</span>';
                                    }
                                } catch (Exception $e) {
                                    // Silently handle database errors in navigation
                                    error_log("Navigation notification count error: " . $e->getMessage());
                                }
                            }
                            ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-modern <?php echo (strpos($current_page, 'feedback') !== false) ? 'active' : ''; ?>" 
                           href="feedback_form.php">
                            <i class="bi bi-chat-square-text me-1"></i>
                            Feedback
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <!-- Right Side Navigation -->
            <ul class="navbar-nav">
                <!-- Video Call Notifications -->
                <?php if ($user_type !== 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link nav-link-modern position-relative" href="#" onclick="showVideoCallNotifications(); return false;">
                        <i class="bi bi-camera-video"></i>
                        <?php
                        // Show pending video call count
                        if (isset($_SESSION['logged_account']['email']) && isset($conn) && $conn->ping()) {
                            $current_user = $_SESSION['logged_account']['email'];
                            try {
                                $video_calls_query = "SELECT COUNT(*) as pending FROM video_call_table WHERE callee_email = '$current_user' AND call_status = 'pending'";
                                $video_calls_result = $conn->query($video_calls_query);
                                if ($video_calls_result && $video_calls_result->num_rows > 0) {
                                    $row = $video_calls_result->fetch_assoc();
                                    $pending_calls = $row['pending'] ?? 0;
                                    if ($pending_calls > 0) {
                                        echo '<span class="badge bg-success ms-1">' . $pending_calls . '</span>';
                                    }
                                }
                            } catch (Exception $e) {
                                // Silently handle database errors in navigation
                                error_log("Video call count error: " . $e->getMessage());
                            }
                        }
                        ?>
                    </a>
                </li>
                <?php endif; ?>
                
                <!-- Notifications -->
                <li class="nav-item dropdown">
                    <a class="nav-link nav-link-modern position-relative" href="#" id="notificationsDropdown" 
                       data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
                        <i class="bi bi-bell" style="font-size: 1.2rem;"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
                              id="notificationBadge" style="display: none; font-size: 0.7rem;">
                            0
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="width: 350px;">
                        <li class="dropdown-header d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Notifications</span>
                            <button class="btn btn-sm btn-ghost" onclick="markAllAsRead()">
                                <i class="bi bi-check-all"></i>
                            </button>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <div id="notificationsList" style="max-height: 400px; overflow-y: auto;">
                            <li class="dropdown-item text-muted text-center py-3">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <div class="mt-2">Loading notifications...</div>
                            </li>
                        </div>
                        <li><hr class="dropdown-divider"></li>
                        <li class="dropdown-item text-center">
                            <a href="notifications_center.php" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-bell me-1"></i>
                                View All Notifications
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- User Profile -->
                <li class="nav-item dropdown">
                    <a class="nav-link nav-link-modern d-flex align-items-center" href="#" id="userDropdown" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <?php
                        $user_info = $_SESSION['logged_account'];
                        $profile_image = $user_info['profile_image'] ?? 'profile_images/default-male-user-profile-icon.jpg';
                        $first_name = $user_info['first_name'] ?? '';
                        $last_name = $user_info['last_name'] ?? '';
                        $full_name = trim($first_name . ' ' . $last_name);
                        if (empty($full_name)) {
                            $full_name = $user_info['email'] ?? 'User';
                        }
                        ?>
                        <img src="<?php echo $profile_image; ?>" alt="Profile" 
                             class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">
                        <span class="d-none d-md-inline">
                            <?php echo htmlspecialchars($full_name); ?>
                        </span>
                        <i class="bi bi-chevron-down ms-1"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                        <li class="dropdown-header">
                            <div class="fw-semibold"><?php echo htmlspecialchars($full_name); ?></div>
                            <div class="text-muted small"><?php echo htmlspecialchars($user_info['email'] ?? ''); ?></div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="profile_detail.php?email=<?php echo urlencode($user_info['email'] ?? ''); ?>">
                                <i class="bi bi-person me-2"></i>
                                My Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="update_profile.php">
                                <i class="bi bi-gear me-2"></i>
                                Settings
                            </a>
                        </li>
                        <?php if ($user_type === 'user'): ?>
                        <li>
                            <a class="dropdown-item" href="video_call.php">
                                <i class="bi bi-camera-video me-2"></i>
                                Video Call
                            </a>
                        </li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Sign Out
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<script>
// Enhanced Notification functionality
let notificationPolling;

function loadNotifications() {
    fetch('get_unified_notifications.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('notificationBadge');
            const list = document.getElementById('notificationsList');
            
            if (data.notifications && data.notifications.length > 0) {
                badge.textContent = data.total_count;
                badge.style.display = 'block';
                
                list.innerHTML = data.notifications.map(notification => {
                    let content = '';
                    
                    if (notification.type === 'video_call') {
                        content = `
                            <li class="dropdown-item notification-item p-3" style="border-left: 4px solid #10b981;">
                                <div class="d-flex align-items-center">
                                    <img src="${notification.caller_image}" class="rounded-circle me-3" width="40" height="40" alt="Caller">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold text-success">
                                            <i class="bi bi-camera-video me-1"></i>
                                            ${notification.title}
                                        </div>
                                        <div class="text-muted small">${notification.message}</div>
                                        <div class="text-muted small">${notification.time_ago}</div>
                                    </div>
                                    <div class="d-flex flex-column gap-1">
                                        <button class="btn btn-success btn-sm" onclick="acceptCallFromNotification(${notification.call_id}, '${notification.room_id}')">
                                            <i class="bi bi-telephone-fill"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" onclick="declineCallFromNotification(${notification.call_id})">
                                            <i class="bi bi-telephone-x"></i>
                                        </button>
                                    </div>
                                </div>
                            </li>
                        `;
                    } else if (notification.type === 'message') {
                        content = `
                            <li class="dropdown-item notification-item p-3" style="border-left: 4px solid #0ea5e9;" onclick="openChat(${notification.conversation_id})">
                                <div class="d-flex align-items-center">
                                    <img src="${notification.sender_image}" class="rounded-circle me-3" width="40" height="40" alt="Sender">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold text-info">
                                            <i class="bi bi-chat-dots me-1"></i>
                                            ${notification.title}
                                        </div>
                                        <div class="text-muted small">${notification.message}</div>
                                        <div class="text-muted small">${notification.time_ago}</div>
                                    </div>
                                    <div>
                                        <i class="bi bi-chevron-right text-muted"></i>
                                    </div>
                                </div>
                            </li>
                        `;
                    } else {
                        content = `
                            <li class="dropdown-item notification-item p-3" style="border-left: 4px solid #6366f1;">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <i class="bi ${notification.icon} text-${notification.color}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">${notification.title}</div>
                                        <div class="text-muted small">${notification.message}</div>
                                        <div class="text-muted small">${notification.time_ago}</div>
                                    </div>
                                </div>
                            </li>
                        `;
                    }
                    
                    return content;
                }).join('');
            } else {
                badge.style.display = 'none';
                list.innerHTML = `
                    <li class="dropdown-item text-muted text-center py-4">
                        <i class="bi bi-bell-slash" style="font-size: 2rem;"></i>
                        <div class="mt-2">No new notifications</div>
                        <small class="text-muted">You're all caught up!</small>
                    </li>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            const list = document.getElementById('notificationsList');
            list.innerHTML = `
                <li class="dropdown-item text-danger text-center py-3">
                    <i class="bi bi-exclamation-triangle"></i>
                    <div class="mt-2">Failed to load notifications</div>
                </li>
            `;
        });
}

function getNotificationIcon(type) {
    const icons = {
        'message': 'bi-chat-dots',
        'call': 'bi-telephone',
        'video_call': 'bi-camera-video',
        'event': 'bi-calendar-event',
        'advertisement': 'bi-briefcase',
        'chat_invitation': 'bi-person-plus',
        'feedback': 'bi-chat-square-text'
    };
    return icons[type] || 'bi-info-circle';
}

// Handle video call actions from notifications
function acceptCallFromNotification(callId, roomId) {
    fetch('respond_video_call.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            call_id: callId,
            action: 'accept'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close dropdown
            const dropdown = bootstrap.Dropdown.getInstance(document.getElementById('notificationsDropdown'));
            if (dropdown) dropdown.hide();
            
            // Open video call in new window
            window.open(`video_call.php?room=${roomId}&call_id=${callId}`, '_blank');
            
            // Refresh notifications and update badges
            loadNotifications();
            updateAllBadgeCounts();
        } else {
            alert('Failed to accept call: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error accepting call:', error);
        alert('Network error occurred while accepting the call.');
    });
}

function declineCallFromNotification(callId) {
    if (confirm('Decline this video call?')) {
        fetch('respond_video_call.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                call_id: callId,
                action: 'decline'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Refresh notifications and update badges
                loadNotifications();
                updateAllBadgeCounts();
            } else {
                alert('Failed to decline call: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error declining call:', error);
            alert('Network error occurred while declining the call.');
        });
    }
}

// Handle message notifications
function openChat(conversationId) {
    // Close dropdown
    const dropdown = bootstrap.Dropdown.getInstance(document.getElementById('notificationsDropdown'));
    if (dropdown) dropdown.hide();
    
    // Open messages page with conversation
    window.location.href = `messages.php?conversation=${conversationId}`;
}

function formatTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) return 'Just now';
    if (diff < 3600000) return Math.floor(diff / 60000) + 'm ago';
    if (diff < 86400000) return Math.floor(diff / 3600000) + 'h ago';
    return Math.floor(diff / 86400000) + 'd ago';
}

function markAllAsRead() {
    fetch('mark_notifications_read.php', { method: 'POST' })
        .then(() => loadNotifications())
        .catch(error => console.error('Error marking notifications as read:', error));
}

// Video Call Notifications
function showVideoCallNotifications() {
    fetch('get_video_call_notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.notifications.length > 0) {
                showVideoCallModal(data.notifications);
            } else {
                showNoCallsModal();
            }
        })
        .catch(error => {
            console.error('Error loading video call notifications:', error);
            alert('Failed to load video call notifications');
        });
}

function showVideoCallModal(notifications) {
    const modalHtml = `
        <div class="modal fade" id="videoCallModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-camera-video me-2"></i>
                            Incoming Video Calls
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        ${notifications.map(call => `
                            <div class="call-invitation border rounded-3 p-3 mb-3" style="border: 2px solid #10b981;">
                                <div class="d-flex align-items-center">
                                    <img src="${call.caller_image}" class="rounded-circle me-3" width="60" height="60" alt="Caller">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">${call.caller_name}</h6>
                                        <p class="mb-1 text-muted">${call.caller_email}</p>
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i>
                                            ${call.time_ago}
                                        </small>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-success" onclick="acceptCall(${call.id}, '${call.room_id}')">
                                            <i class="bi bi-telephone-fill me-1"></i>
                                            Accept
                                        </button>
                                        <button class="btn btn-danger" onclick="declineCall(${call.id})">
                                            <i class="bi bi-telephone-x me-1"></i>
                                            Decline
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('videoCallModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('videoCallModal'));
    modal.show();
}

function showNoCallsModal() {
    const modalHtml = `
        <div class="modal fade" id="noCallsModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-camera-video me-2"></i>
                            Video Calls
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <i class="bi bi-camera-video-off text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3 text-muted">No Pending Calls</h5>
                        <p class="text-muted">You don't have any pending video call invitations at the moment.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('noCallsModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('noCallsModal'));
    modal.show();
}

function acceptCall(callId, roomId) {
    fetch('respond_video_call.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            call_id: callId,
            action: 'accept'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('videoCallModal'));
            if (modal) modal.hide();
            
            // Open video call in new window
            window.open(`video_call.php?room=${roomId}&call_id=${callId}`, '_blank');
            
            // Update notification counts immediately
            loadNotifications();
            updateAllBadgeCounts();
        } else {
            alert('Failed to accept call: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error accepting call:', error);
        alert('Network error occurred while accepting the call.');
    });
}

function declineCall(callId) {
    if (confirm('Are you sure you want to decline this call?')) {
        fetch('respond_video_call.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                call_id: callId,
                action: 'decline'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('videoCallModal'));
                if (modal) modal.hide();
                
                // Update notification counts immediately
                loadNotifications();
                updateAllBadgeCounts();
            } else {
                alert('Failed to decline call: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error declining call:', error);
            alert('Network error occurred while declining the call.');
        });
    }
}

// Function to update all badge counts dynamically
function updateAllBadgeCounts() {
    // Update all navigation badges by fetching fresh counts
    fetch('get_unified_notifications.php')
        .then(response => response.json())
        .then(data => {
            // Update video call badges
            const videoCalls = data.notifications.filter(n => n.type === 'video_call').length;
            document.querySelectorAll('.nav-link').forEach(link => {
                if (link.textContent.includes('Video Calls')) {
                    const badge = link.querySelector('.badge');
                    if (videoCalls > 0) {
                        if (badge) {
                            badge.textContent = videoCalls;
                        } else {
                            link.innerHTML += `<span class="badge bg-success ms-1">${videoCalls}</span>`;
                        }
                    } else if (badge) {
                        badge.remove();
                    }
                }
            });
            
            // Update message badges
            const messages = data.notifications.filter(n => n.type === 'message').length;
            document.querySelectorAll('.nav-link').forEach(link => {
                if (link.textContent.includes('Messages')) {
                    const badge = link.querySelector('.badge');
                    if (messages > 0) {
                        if (badge) {
                            badge.textContent = messages;
                        } else {
                            link.innerHTML += `<span class="badge bg-danger ms-1">${messages}</span>`;
                        }
                    } else if (badge) {
                        badge.remove();
                    }
                }
            });
            
            // Update total notification badges
            const total = data.total_count || 0;
            document.querySelectorAll('.nav-link').forEach(link => {
                if (link.textContent.includes('Notifications') && !link.textContent.includes('Video') && !link.textContent.includes('Messages')) {
                    const badge = link.querySelector('.badge');
                    if (total > 0) {
                        if (badge) {
                            badge.textContent = total;
                        } else {
                            link.innerHTML += `<span class="badge bg-danger ms-1">${total}</span>`;
                        }
                    } else if (badge) {
                        badge.remove();
                    }
                }
            });
        })
        .catch(error => console.error('Error updating badge counts:', error));
}

// Initialize notifications
document.addEventListener('DOMContentLoaded', function() {
    loadNotifications();
    updateAllBadgeCounts();
    
    // Poll for notifications every 30 seconds
    notificationPolling = setInterval(() => {
        loadNotifications();
        updateAllBadgeCounts();
    }, 30000);
});
</script>