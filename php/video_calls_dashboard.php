<?php
session_start();
include 'db_controller.php';
$conn->select_db("Alumni");

if (!isset($_SESSION['logged_account'])) {
    header('Location: login.php');
    exit();
}

$userEmail = $_SESSION['logged_account']['email'];
$userType = $_SESSION['logged_account']['type'];
$isAdmin = ($userType == 'admin');

// Get user info
$userQuery = $conn->prepare("SELECT * FROM user_table WHERE email = ?");
$userQuery->bind_param("s", $userEmail);
$userQuery->execute();
$userInfo = $userQuery->get_result()->fetch_assoc();
$userName = $userInfo ? $userInfo['first_name'] . ' ' . $userInfo['last_name'] : 'User';

// Get statistics
if ($isAdmin) {
    $totalCallsQuery = $conn->query("SELECT COUNT(*) as count FROM video_call_table WHERE caller_email = '$userEmail'");
    $activeCallsQuery = $conn->query("SELECT COUNT(*) as count FROM video_call_table WHERE caller_email = '$userEmail' AND call_status = 'active'");
} else {
    $totalCallsQuery = $conn->query("SELECT COUNT(*) as count FROM video_call_table WHERE caller_email = '$userEmail' OR callee_email = '$userEmail'");
    $activeCallsQuery = $conn->query("SELECT COUNT(*) as count FROM video_call_table WHERE (caller_email = '$userEmail' OR callee_email = '$userEmail') AND call_status = 'active'");
}

$totalCalls = $totalCallsQuery->fetch_assoc()['count'];
$activeCalls = $activeCallsQuery->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Calls Dashboard | Alumni Portal</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/admin-theme.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .dashboard-container {
            padding: 2rem 0;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border-left: 4px solid;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }
        
        .stat-card.primary { border-left-color: #667eea; }
        .stat-card.success { border-left-color: #10b981; }
        .stat-card.warning { border-left-color: #f59e0b; }
        .stat-card.danger { border-left-color: #ef4444; }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .call-item {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        
        .call-item:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        }
        
        .call-item.active {
            border-color: #10b981;
            background: #f0fdf4;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.9; }
        }
        
        .btn-call {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-call:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(16, 185, 129, 0.3);
            color: white;
        }
        
        .btn-end {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        
        .btn-end:hover {
            box-shadow: 0 8px 15px rgba(239, 68, 68, 0.3);
        }
        
        .alumni-card {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .alumni-card:hover {
            border-color: #667eea;
            transform: translateX(5px);
        }
        
        .alumni-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #667eea;
        }
        
        .page-header {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            color: white;
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
        }
        
        .badge-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .badge-active {
            background: #d1fae5;
            color: #065f46;
        }
        
        .badge-ended {
            background: #e5e7eb;
            color: #374151;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
        }
        
        .empty-state i {
            font-size: 4rem;
            opacity: 0.5;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php 
    if ($isAdmin) {
        include 'components/admin_nav.php';
    } else {
        include 'components/nav_modern.php';
    }
    ?>
    
    <div class="dashboard-container">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i class="bi bi-camera-video me-3"></i>
                            Video Calls Dashboard
                        </h1>
                        <p class="lead mb-0">
                            <?php echo $isAdmin ? 'Manage and initiate video calls with alumni' : 'View and join video calls'; ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="d-flex justify-content-md-end gap-2">
                            <?php if ($isAdmin): ?>
                                <a href="manage_video_calls.php" class="btn btn-light">
                                    <i class="bi bi-list-ul me-2"></i>Manage All
                                </a>
                            <?php endif; ?>
                            <button class="btn btn-outline-light" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise me-2"></i>Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="stat-card primary">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="stat-number text-primary"><?php echo $totalCalls; ?></div>
                                <div class="stat-label">Total Calls</div>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                                <i class="bi bi-telephone text-primary" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="stat-card success">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="stat-number text-success" id="activeCallsCount"><?php echo $activeCalls; ?></div>
                                <div class="stat-label">Active Calls</div>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                                <i class="bi bi-camera-video text-success" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="stat-card warning">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="stat-number text-warning" id="pendingCallsCount">0</div>
                                <div class="stat-label">Pending Calls</div>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                                <i class="bi bi-clock text-warning" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="stat-card danger">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="stat-number text-info">HD</div>
                                <div class="stat-label">Video Quality</div>
                            </div>
                            <div class="bg-info bg-opacity-10 p-3 rounded-circle">
                                <i class="bi bi-badge-hd text-info" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Left Column -->
                <div class="col-lg-8">
                    <?php if ($isAdmin): ?>
                        <!-- Admin: Start New Call -->
                        <div class="glass-card">
                            <h4 class="fw-bold mb-4">
                                <i class="bi bi-telephone-plus text-success me-2"></i>
                                Start New Call
                            </h4>
                            
                            <div id="alumniList">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary"></div>
                                    <p class="mt-2 text-muted">Loading alumni...</p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Alumni: Incoming Calls -->
                        <div class="glass-card">
                            <h4 class="fw-bold mb-4">
                                <i class="bi bi-telephone-inbound text-success me-2"></i>
                                Incoming Calls
                            </h4>
                            
                            <div id="incomingCalls">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary"></div>
                                    <p class="mt-2 text-muted">Loading calls...</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Right Column -->
                <div class="col-lg-4">
                    <!-- Active Calls -->
                    <div class="glass-card">
                        <h5 class="fw-bold mb-4">
                            <i class="bi bi-broadcast text-danger me-2"></i>
                            Active Calls
                        </h5>
                        
                        <div id="activeCallsList">
                            <div class="text-center py-4">
                                <div class="spinner-border spinner-border-sm text-primary"></div>
                                <p class="mt-2 text-muted small">Loading...</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="glass-card mt-4">
                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-lightning text-warning me-2"></i>
                            Quick Actions
                        </h5>
                        
                        <div class="d-grid gap-2">
                            <?php if ($isAdmin): ?>
                                <a href="manage_video_calls.php" class="btn btn-outline-primary">
                                    <i class="bi bi-list-ul me-2"></i>View All Calls
                                </a>
                            <?php else: ?>
                                <a href="video_call_invitations.php" class="btn btn-outline-primary">
                                    <i class="bi bi-envelope me-2"></i>View Invitations
                                </a>
                            <?php endif; ?>
                            <button class="btn btn-outline-secondary" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise me-2"></i>Refresh Page
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;
        
        // Load alumni list for admin
        <?php if ($isAdmin): ?>
        function loadAlumni() {
            fetch('get_alumni_list.php')
                .then(r => r.json())
                .then(data => {
                    const div = document.getElementById('alumniList');
                    
                    if (data.success && data.alumni.length > 0) {
                        div.innerHTML = data.alumni.map(alumni => `
                            <div class="alumni-card" onclick="callAlumni('${alumni.email}', '${alumni.name}')">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <img src="${alumni.image}" class="alumni-avatar me-3" alt="${alumni.name}">
                                        <div>
                                            <h6 class="mb-0 fw-bold">${alumni.name}</h6>
                                            <small class="text-muted">${alumni.email}</small>
                                        </div>
                                    </div>
                                    <button class="btn btn-call btn-sm">
                                        <i class="bi bi-telephone-fill me-1"></i>Call
                                    </button>
                                </div>
                            </div>
                        `).join('');
                    } else {
                        div.innerHTML = `
                            <div class="empty-state">
                                <i class="bi bi-people"></i>
                                <h5>No Alumni Available</h5>
                                <p class="text-muted">There are no alumni available to call at the moment.</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('alumniList').innerHTML = `
                        <div class="alert alert-danger">
                            Failed to load alumni. Please refresh the page.
                        </div>
                    `;
                });
        }
        
        function callAlumni(email, name) {
            if (confirm(`Start video call with ${name}?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'initiate_call.php';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'callee_email';
                input.value = email;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        loadAlumni();
        <?php else: ?>
        // Load incoming calls for alumni
        function loadIncomingCalls() {
            fetch('get_video_call_notifications.php')
                .then(r => r.json())
                .then(data => {
                    const div = document.getElementById('incomingCalls');
                    
                    if (data.success && data.notifications.length > 0) {
                        div.innerHTML = data.notifications.map(call => `
                            <div class="call-item active">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h5 class="mb-2 fw-bold">
                                            <i class="bi bi-telephone-inbound text-success me-2"></i>
                                            ${call.caller_name}
                                        </h5>
                                        <p class="mb-1 text-muted small">
                                            <i class="bi bi-envelope me-1"></i>${call.caller_email}
                                        </p>
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i>${call.time_ago}
                                        </small>
                                    </div>
                                    <div>
                                        <a href="videocall.php?room=${call.room_id}&call_id=${call.id}" 
                                           class="btn btn-call">
                                            <i class="bi bi-telephone-fill me-2"></i>Join Call
                                        </a>
                                    </div>
                                </div>
                            </div>
                        `).join('');
                        
                        document.getElementById('pendingCallsCount').textContent = data.notifications.length;
                    } else {
                        div.innerHTML = `
                            <div class="empty-state">
                                <i class="bi bi-telephone-x"></i>
                                <h5>No Incoming Calls</h5>
                                <p class="text-muted">You don't have any pending video call invitations.</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
        
        loadIncomingCalls();
        setInterval(loadIncomingCalls, 5000);
        <?php endif; ?>
        
        // Load active calls
        function loadActiveCalls() {
            fetch('get_active_calls.php')
                .then(r => r.json())
                .then(data => {
                    const div = document.getElementById('activeCallsList');
                    
                    if (data.success && data.calls.length > 0) {
                        div.innerHTML = data.calls.map(call => `
                            <div class="call-item active mb-2">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">${call.other_user}</h6>
                                        <small class="text-muted">${call.time_ago}</small>
                                    </div>
                                    <a href="videocall.php?room=${call.room_id}&call_id=${call.id}" 
                                       class="btn btn-success btn-sm">
                                        <i class="bi bi-box-arrow-in-right"></i>
                                    </a>
                                </div>
                            </div>
                        `).join('');
                        
                        document.getElementById('activeCallsCount').textContent = data.calls.length;
                    } else {
                        div.innerHTML = `
                            <div class="text-center py-3">
                                <i class="bi bi-camera-video-off text-muted" style="font-size: 2rem;"></i>
                                <p class="text-muted small mb-0 mt-2">No active calls</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
        
        loadActiveCalls();
        setInterval(loadActiveCalls, 5000);
    </script>
</body>
</html>
