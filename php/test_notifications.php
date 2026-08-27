<?php
session_start();
include 'db_controller.php';
$conn->select_db("Alumni");

if (!isset($_SESSION['logged_account'])) {
    die('Please login first');
}

$userEmail = $_SESSION['logged_account']['email'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Notification System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="p-4">
    <div class="container">
        <h1 class="mb-4">Notification System Test</h1>
        <p>Logged in as: <strong><?php echo htmlspecialchars($userEmail); ?></strong></p>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-bell"></i> Unread Notifications</h5>
                    </div>
                    <div class="card-body">
                        <div id="unreadNotifications">Loading...</div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-camera-video"></i> Video Call Notifications</h5>
                    </div>
                    <div class="card-body">
                        <div id="videoCallNotifications">Loading...</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-chat-dots"></i> Unread Messages</h5>
                    </div>
                    <div class="card-body">
                        <div id="unreadMessages">Loading...</div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-graph-up"></i> Summary</h5>
                    </div>
                    <div class="card-body">
                        <div id="summary">Loading...</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="d-flex gap-2 mb-4">
            <button class="btn btn-primary" onclick="loadData()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
            <a href="notifications_center.php" class="btn btn-success">
                <i class="bi bi-bell"></i> Go to Notifications Center
            </a>
            <a href="main_menu.php" class="btn btn-secondary">
                <i class="bi bi-house"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <script>
        function loadData() {
            loadUnreadNotifications();
            loadVideoCallNotifications();
            loadUnreadMessages();
            loadSummary();
        }

        function loadUnreadNotifications() {
            fetch('get_unified_notifications.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('unreadNotifications');
                    const notifications = data.notifications.filter(n => n.type === 'notification');
                    
                    if (notifications.length === 0) {
                        container.innerHTML = '<p class="text-muted">No unread notifications</p>';
                    } else {
                        container.innerHTML = '<ul class="list-group">' + 
                            notifications.map(n => `
                                <li class="list-group-item">
                                    <strong>${n.title}</strong><br>
                                    <small class="text-muted">${n.message}</small><br>
                                    <small class="text-muted"><i class="bi bi-clock"></i> ${n.time_ago}</small>
                                </li>
                            `).join('') + '</ul>';
                    }
                })
                .catch(error => {
                    document.getElementById('unreadNotifications').innerHTML = 
                        '<div class="alert alert-danger">Error loading notifications</div>';
                });
        }

        function loadVideoCallNotifications() {
            fetch('get_unified_notifications.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('videoCallNotifications');
                    const calls = data.notifications.filter(n => n.type === 'video_call');
                    
                    if (calls.length === 0) {
                        container.innerHTML = '<p class="text-muted">No pending video calls</p>';
                    } else {
                        container.innerHTML = '<ul class="list-group">' + 
                            calls.map(c => `
                                <li class="list-group-item">
                                    <strong>${c.caller_name}</strong> is calling<br>
                                    <small class="text-muted"><i class="bi bi-clock"></i> ${c.time_ago}</small><br>
                                    <span class="badge bg-success">Pending</span>
                                </li>
                            `).join('') + '</ul>';
                    }
                })
                .catch(error => {
                    document.getElementById('videoCallNotifications').innerHTML = 
                        '<div class="alert alert-danger">Error loading video calls</div>';
                });
        }

        function loadUnreadMessages() {
            fetch('get_unified_notifications.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('unreadMessages');
                    const messages = data.notifications.filter(n => n.type === 'message');
                    
                    if (messages.length === 0) {
                        container.innerHTML = '<p class="text-muted">No unread messages</p>';
                    } else {
                        container.innerHTML = '<ul class="list-group">' + 
                            messages.map(m => `
                                <li class="list-group-item">
                                    <strong>${m.sender_name}</strong><br>
                                    <small class="text-muted">${m.message}</small><br>
                                    <small class="text-muted"><i class="bi bi-clock"></i> ${m.time_ago}</small>
                                </li>
                            `).join('') + '</ul>';
                    }
                })
                .catch(error => {
                    document.getElementById('unreadMessages').innerHTML = 
                        '<div class="alert alert-danger">Error loading messages</div>';
                });
        }

        function loadSummary() {
            fetch('get_unified_notifications.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('summary');
                    const total = data.total_count || 0;
                    const calls = data.notifications.filter(n => n.type === 'video_call').length;
                    const messages = data.notifications.filter(n => n.type === 'message').length;
                    const notifications = data.notifications.filter(n => n.type === 'notification').length;
                    
                    container.innerHTML = `
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <h3 class="text-primary">${total}</h3>
                                <small class="text-muted">Total Unread</small>
                            </div>
                            <div class="col-6 mb-3">
                                <h3 class="text-success">${calls}</h3>
                                <small class="text-muted">Video Calls</small>
                            </div>
                            <div class="col-6">
                                <h3 class="text-info">${messages}</h3>
                                <small class="text-muted">Messages</small>
                            </div>
                            <div class="col-6">
                                <h3 class="text-warning">${notifications}</h3>
                                <small class="text-muted">Notifications</small>
                            </div>
                        </div>
                    `;
                })
                .catch(error => {
                    document.getElementById('summary').innerHTML = 
                        '<div class="alert alert-danger">Error loading summary</div>';
                });
        }

        // Load data on page load
        document.addEventListener('DOMContentLoaded', loadData);
        
        // Auto-refresh every 10 seconds
        setInterval(loadData, 10000);
    </script>
</body>
</html>
