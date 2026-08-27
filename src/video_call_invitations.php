<?php
session_start();
include 'db_controller.php';
$conn->select_db("Alumni");

if (!isset($_SESSION['logged_account'])) {
    header('Location: login.php');
    exit();
}

$userEmail = $_SESSION['logged_account']['email'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Video Call Invitations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem;
        }
        .card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        .call-item {
            border: 2px solid #10b981;
            background: #f0fdf4;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }
    </style>
</head>
<body>
    <?php include 'components/nav_modern.php'; ?>
    
    <div class="container">
        <div class="card p-4">
            <h1 class="mb-4">📞 Video Call Invitations</h1>
            
            <div id="calls">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-3">Loading invitations...</p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function loadCalls() {
            fetch('get_video_call_notifications.php')
                .then(r => r.json())
                .then(data => {
                    const div = document.getElementById('calls');
                    
                    if (data.success && data.notifications.length > 0) {
                        div.innerHTML = data.notifications.map(call => `
                            <div class="call-item rounded-3 p-4 mb-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h5 class="mb-2">
                                            <i class="bi bi-telephone-inbound text-success"></i>
                                            Incoming Call from ${call.caller_name}
                                        </h5>
                                        <p class="mb-1 text-muted">
                                            <i class="bi bi-envelope"></i> ${call.caller_email}
                                        </p>
                                        <small class="text-muted">
                                            <i class="bi bi-clock"></i> ${call.time_ago}
                                        </small>
                                    </div>
                                    <div>
                                        <a href="videocall.php?room=${call.room_id}&call_id=${call.id}" 
                                           class="btn btn-success btn-lg">
                                            <i class="bi bi-telephone-fill"></i> Join Call
                                        </a>
                                    </div>
                                </div>
                            </div>
                        `).join('');
                    } else {
                        div.innerHTML = `
                            <div class="text-center py-5">
                                <i class="bi bi-telephone-x text-muted" style="font-size: 4rem;"></i>
                                <h4 class="mt-3 text-muted">No Pending Calls</h4>
                                <p class="text-muted">You don't have any video call invitations</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('calls').innerHTML = `
                        <div class="alert alert-danger">
                            Failed to load invitations. Please refresh the page.
                        </div>
                    `;
                });
        }
        
        loadCalls();
        setInterval(loadCalls, 5000);
    </script>
</body>
</html>
