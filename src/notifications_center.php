<?php
session_start();
include 'db_controller.php';

// Check if user is logged in
if (!isset($_SESSION['logged_account']) || $_SESSION['logged_account']['type'] !== 'user') {
    header('Location: login.php');
    exit();
}

$conn->select_db("Alumni");
$current_user = $_SESSION['logged_account']['email'];

// Get user info
$user_info = [];
try {
    $user_query = $conn->prepare("SELECT * FROM user_table WHERE email = ?");
    $user_query->bind_param("s", $current_user);
    $user_query->execute();
    $result = $user_query->get_result();
    $user_info = $result->fetch_assoc();
} catch (Exception $e) {
    $user_info = ['first_name' => 'User', 'last_name' => ''];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications Center | Alumni Portal</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .notification-card {
            transition: all 0.3s ease;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            margin-bottom: 1rem;
        }
        
        .notification-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .notification-video-call {
            border-left: 4px solid #10b981;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        }
        
        .notification-message {
            border-left: 4px solid #0ea5e9;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        }
        
        .notification-general {
            border-left: 4px solid #6366f1;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }
        
        .pulse-animation {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }
        
        .filter-tabs {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 0.5rem;
        }
        
        .filter-tab {
            border: none;
            background: transparent;
            color: #64748b;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .filter-tab.active {
            background: white;
            color: #1e293b;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>

<body>
    <?php include 'components/nav_modern.php'; ?>
    
    <div class="container py-5">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="glass-card p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h1 class="h3 mb-2 fw-bold text-dark">
                                        <i class="bi bi-bell text-primary me-2"></i>
                                        Notifications Center
                                    </h1>
                                    <p class="text-muted mb-0">Stay updated with messages, calls, and important updates</p>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-primary" onclick="loadAllNotifications()">
                                        <i class="bi bi-arrow-clockwise me-1"></i>
                                        Refresh
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="markAllAsRead()">
                                        <i class="bi bi-check-all me-1"></i>
                                        Mark All Read
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <?php include 'components/alumni_logo.php'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="glass-card p-3">
                    <div class="filter-tabs d-flex gap-2">
                        <button class="filter-tab active" onclick="filterNotifications('all')">
                            <i class="bi bi-list me-1"></i>
                            All Notifications
                            <span class="badge bg-primary ms-1" id="allCount">0</span>
                        </button>
                        <button class="filter-tab" onclick="filterNotifications('video_call')">
                            <i class="bi bi-camera-video me-1"></i>
                            Video Calls
                            <span class="badge bg-success ms-1" id="callCount">0</span>
                        </button>
                        <button class="filter-tab" onclick="filterNotifications('message')">
                            <i class="bi bi-chat-dots me-1"></i>
                            Messages
                            <span class="badge bg-info ms-1" id="messageCount">0</span>
                        </button>
                        <button class="filter-tab" onclick="filterNotifications('notification')">
                            <i class="bi bi-bell me-1"></i>
                            General
                            <span class="badge bg-secondary ms-1" id="generalCount">0</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="row">
            <div class="col-12">
                <div class="glass-card p-4">
                    <div id="notificationsContainer">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3 text-muted">Loading your notifications...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let allNotifications = [];
        let currentFilter = 'all';

        function loadAllNotifications() {
            fetch('get_unified_notifications.php')
                .then(response => response.json())
                .then(data => {
                    if (data.notifications) {
                        allNotifications = data.notifications;
                        updateCounts();
                        displayNotifications(allNotifications);
                    } else {
                        displayEmptyState();
                    }
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                    displayErrorState();
                });
        }

        function updateCounts() {
            const counts = {
                all: allNotifications.length,
                video_call: allNotifications.filter(n => n.type === 'video_call').length,
                message: allNotifications.filter(n => n.type === 'message').length,
                notification: allNotifications.filter(n => n.type === 'notification').length
            };

            document.getElementById('allCount').textContent = counts.all;
            document.getElementById('callCount').textContent = counts.video_call;
            document.getElementById('messageCount').textContent = counts.message;
            document.getElementById('generalCount').textContent = counts.notification;
        }

        function filterNotifications(type) {
            currentFilter = type;
            
            // Update active tab
            document.querySelectorAll('.filter-tab').forEach(tab => tab.classList.remove('active'));
            event.target.classList.add('active');
            
            // Filter and display notifications
            const filtered = type === 'all' ? allNotifications : allNotifications.filter(n => n.type === type);
            displayNotifications(filtered);
        }

        function displayNotifications(notifications) {
            const container = document.getElementById('notificationsContainer');
            
            if (notifications.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-bell-slash text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3 text-muted">No Notifications</h5>
                        <p class="text-muted">You don't have any notifications in this category.</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = notifications.map(notification => {
                let cardClass = 'notification-general';
                let content = '';

                if (notification.type === 'video_call') {
                    cardClass = 'notification-video-call pulse-animation';
                    content = `
                        <div class="notification-card ${cardClass} p-4">
                            <div class="d-flex align-items-center">
                                <img src="${notification.caller_image}" class="rounded-circle me-3" width="60" height="60" alt="Caller">
                                <div class="flex-grow-1">
                                    <h5 class="mb-2 fw-bold text-success">
                                        <i class="bi bi-camera-video me-2"></i>
                                        ${notification.title}
                                    </h5>
                                    <p class="mb-2 text-muted">${notification.message}</p>
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>
                                        ${notification.time_ago}
                                    </small>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-success" onclick="acceptCall(${notification.call_id}, '${notification.room_id}')">
                                        <i class="bi bi-telephone-fill me-1"></i>
                                        Accept
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="declineCall(${notification.call_id})">
                                        <i class="bi bi-telephone-x me-1"></i>
                                        Decline
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                } else if (notification.type === 'message') {
                    cardClass = 'notification-message';
                    content = `
                        <div class="notification-card ${cardClass} p-4" onclick="openChat(${notification.conversation_id})" style="cursor: pointer;">
                            <div class="d-flex align-items-center">
                                <img src="${notification.sender_image}" class="rounded-circle me-3" width="60" height="60" alt="Sender">
                                <div class="flex-grow-1">
                                    <h5 class="mb-2 fw-bold text-info">
                                        <i class="bi bi-chat-dots me-2"></i>
                                        ${notification.title}
                                    </h5>
                                    <p class="mb-2 text-muted">${notification.message}</p>
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>
                                        ${notification.time_ago}
                                    </small>
                                </div>
                                <div>
                                    <i class="bi bi-chevron-right text-muted" style="font-size: 1.5rem;"></i>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    content = `
                        <div class="notification-card ${cardClass} p-4">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="bi ${notification.icon}" style="font-size: 1.5rem;"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-2 fw-bold">
                                        ${notification.title}
                                    </h5>
                                    <p class="mb-2 text-muted">${notification.message}</p>
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>
                                        ${notification.time_ago}
                                    </small>
                                </div>
                            </div>
                        </div>
                    `;
                }

                return content;
            }).join('');
        }

        function displayEmptyState() {
            document.getElementById('notificationsContainer').innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-bell-slash text-muted" style="font-size: 4rem;"></i>
                    <h4 class="mt-3 text-muted">No Notifications</h4>
                    <p class="text-muted">You're all caught up! No new notifications at the moment.</p>
                </div>
            `;
        }

        function displayErrorState() {
            document.getElementById('notificationsContainer').innerHTML = `
                <div class="alert alert-danger text-center">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Failed to load notifications. Please try again.
                    <button class="btn btn-outline-danger btn-sm ms-2" onclick="loadAllNotifications()">
                        Retry
                    </button>
                </div>
            `;
        }

        // Mark individual notification as read
        function markNotificationRead(type, id) {
            fetch('mark_notification_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ type: type, id: id })
            }).catch(error => console.error('Error marking notification as read:', error));
        }

        // Video call functions
        function acceptCall(callId, roomId) {
            if (confirm('Accept this video call?')) {
                // Mark as read
                markNotificationRead('video_call', callId);
                
                fetch('respond_video_call.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ call_id: callId, action: 'accept' })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.open(`video_call.php?room=${roomId}&call_id=${callId}`, '_blank');
                        setTimeout(() => loadAllNotifications(), 1000);
                    } else {
                        alert('Failed to accept call: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error accepting call:', error);
                    alert('Network error occurred while accepting the call.');
                });
            }
        }

        function declineCall(callId) {
            if (confirm('Decline this video call?')) {
                // Mark as read
                markNotificationRead('video_call', callId);
                
                fetch('respond_video_call.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ call_id: callId, action: 'decline' })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadAllNotifications();
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

        function openChat(conversationId) {
            // Mark message notification as read
            const notification = allNotifications.find(n => n.type === 'message' && n.conversation_id === conversationId);
            if (notification) {
                markNotificationRead('message', notification.id);
            }
            window.location.href = `messages.php?conversation=${conversationId}`;
        }

        function markAllAsRead() {
            if (confirm('Mark all notifications as read?')) {
                fetch('mark_notifications_read.php', { method: 'POST' })
                    .then(() => loadAllNotifications())
                    .catch(error => console.error('Error marking notifications as read:', error));
            }
        }

        // Load notifications on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadAllNotifications();
            
            // Auto-refresh every 30 seconds
            setInterval(loadAllNotifications, 30000);
        });
    </script>
</body>
</html>