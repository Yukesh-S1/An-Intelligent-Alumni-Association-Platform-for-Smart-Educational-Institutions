<?php
session_start();
include 'db_controller.php';
$conn->select_db("Alumni");

if (!isset($_SESSION['logged_account'])) {
    header('Location: login.php');
    exit();
}

$userEmail = $_SESSION['logged_account']['email'];
$isAdmin = $_SESSION['logged_account']['type'] == 'admin';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Video Call</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem;
        }
        .card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 800px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1 class="mb-4">📹 Video Call Test</h1>
        
        <div class="alert alert-info">
            <strong>Logged in as:</strong> <?php echo htmlspecialchars($userEmail); ?><br>
            <strong>Role:</strong> <?php echo $isAdmin ? 'Admin' : 'Alumni'; ?>
        </div>
        
        <?php if ($isAdmin): ?>
            <h3>Call an Alumni</h3>
            <?php
            $query = $conn->query("SELECT u.email, u.first_name, u.last_name FROM user_table u JOIN account_table a ON u.email = a.email WHERE a.type = 'user' AND a.status = 'Approved' LIMIT 10");
            
            if ($query && $query->num_rows > 0):
            ?>
                <div class="list-group">
                    <?php while ($alumni = $query->fetch_assoc()): ?>
                        <form action="initiate_call.php" method="POST" class="mb-2">
                            <input type="hidden" name="callee_email" value="<?php echo $alumni['email']; ?>">
                            <button type="submit" class="list-group-item list-group-item-action d-flex justify-content-between">
                                <span><?php echo $alumni['first_name'] . ' ' . $alumni['last_name']; ?></span>
                                <span class="badge bg-success">📞 Call</span>
                            </button>
                        </form>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">No alumni available</div>
            <?php endif; ?>
            
        <?php else: ?>
            <h3>Incoming Calls</h3>
            <div id="calls">
                <div class="text-center py-4">
                    <div class="spinner-border"></div>
                    <p class="mt-2">Loading...</p>
                </div>
            </div>
            
            <script>
                function loadCalls() {
                    fetch('get_video_call_notifications.php')
                        .then(r => r.json())
                        .then(data => {
                            const div = document.getElementById('calls');
                            
                            if (data.success && data.notifications.length > 0) {
                                div.innerHTML = data.notifications.map(call => `
                                    <div class="alert alert-success d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>📞 Call from ${call.caller_name}</strong><br>
                                            <small>${call.time_ago}</small>
                                        </div>
                                        <a href="videocall.php?room=${call.room_id}&call_id=${call.id}" class="btn btn-success">
                                            Join Call
                                        </a>
                                    </div>
                                `).join('');
                            } else {
                                div.innerHTML = '<div class="alert alert-info">No incoming calls</div>';
                            }
                        });
                }
                
                loadCalls();
                setInterval(loadCalls, 3000);
            </script>
        <?php endif; ?>
        
        <hr class="my-4">
        
        <div class="text-center">
            <a href="<?php echo $isAdmin ? 'main_menu_admin.php' : 'main_menu.php'; ?>" class="btn btn-secondary">
                ← Back
            </a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
