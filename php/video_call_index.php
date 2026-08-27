<?php
session_start();
include 'db_controller.php';
$conn->select_db("Alumni");

if (!isset($_SESSION['logged_account'])) {
    header('Location: login.php');
    exit();
}

$isAdmin = $_SESSION['logged_account']['type'] == 'admin';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Video Calls | Alumni Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
        }
        .option-card {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            display: block;
            color: inherit;
        }
        .option-card:hover {
            border-color: #667eea;
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.2);
        }
        .option-icon {
            font-size: 2.5rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1 class="text-center mb-4">
            <i class="bi bi-camera-video text-primary"></i>
            Video Calls
        </h1>
        
        <p class="text-center text-muted mb-4">
            Choose how you want to access video calls
        </p>
        
        <a href="video_calls_dashboard.php" class="option-card">
            <div class="text-center">
                <div class="option-icon">
                    <i class="bi bi-speedometer2"></i>
                </div>
                <h4 class="fw-bold mb-2">Dashboard</h4>
                <p class="text-muted mb-0">
                    Professional dashboard with statistics and call management
                </p>
            </div>
        </a>
        
        <?php if ($isAdmin): ?>
            <a href="manage_video_calls.php" class="option-card">
                <div class="text-center">
                    <div class="option-icon">
                        <i class="bi bi-list-ul"></i>
                    </div>
                    <h4 class="fw-bold mb-2">Manage Calls</h4>
                    <p class="text-muted mb-0">
                        View and manage all video call records
                    </p>
                </div>
            </a>
        <?php else: ?>
            <a href="video_call_invitations.php" class="option-card">
                <div class="text-center">
                    <div class="option-icon">
                        <i class="bi bi-envelope"></i>
                    </div>
                    <h4 class="fw-bold mb-2">Invitations</h4>
                    <p class="text-muted mb-0">
                        View your incoming video call invitations
                    </p>
                </div>
            </a>
        <?php endif; ?>
        
        <a href="test_call.php" class="option-card">
            <div class="text-center">
                <div class="option-icon">
                    <i class="bi bi-play-circle"></i>
                </div>
                <h4 class="fw-bold mb-2">Quick Test</h4>
                <p class="text-muted mb-0">
                    Simple interface for testing video calls
                </p>
            </div>
        </a>
        
        <hr class="my-4">
        
        <div class="text-center">
            <a href="<?php echo $isAdmin ? 'main_menu_admin.php' : 'main_menu.php'; ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
