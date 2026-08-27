<?php
session_start();

// Check admin authentication
if (!isset($_SESSION['logged_account']) || $_SESSION['logged_account']['type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Database connection
include 'db_controller.php';
$conn->select_db("Alumni");

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Update admin profile
        $admin_email = $_SESSION['logged_account']['email'];
        $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
        $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
        
        $update_query = "UPDATE user_table SET first_name = '$first_name', last_name = '$last_name' WHERE email = '$admin_email'";
        
        if ($conn->query($update_query)) {
            $message = 'Profile updated successfully!';
            $message_type = 'success';
        } else {
            $message = 'Error updating profile: ' . $conn->error;
            $message_type = 'danger';
        }
    }
    
    if (isset($_POST['change_password'])) {
        // Change password
        $admin_email = $_SESSION['logged_account']['email'];
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        $check_query = "SELECT password FROM account_table WHERE email = '$admin_email'";
        $result = $conn->query($check_query);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($current_password, $row['password'])) {
                if ($new_password === $confirm_password) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_query = "UPDATE account_table SET password = '$hashed_password' WHERE email = '$admin_email'";
                    
                    if ($conn->query($update_query)) {
                        $message = 'Password changed successfully!';
                        $message_type = 'success';
                    } else {
                        $message = 'Error changing password: ' . $conn->error;
                        $message_type = 'danger';
                    }
                } else {
                    $message = 'New passwords do not match!';
                    $message_type = 'danger';
                }
            } else {
                $message = 'Current password is incorrect!';
                $message_type = 'danger';
            }
        }
    }
}

// Get admin profile data
$admin_email = $_SESSION['logged_account']['email'];
$profile_query = "SELECT u.first_name, u.last_name, a.email FROM account_table a LEFT JOIN user_table u ON a.email = u.email WHERE a.email = '$admin_email'";
$profile_result = $conn->query($profile_query);
$admin_profile = $profile_result ? $profile_result->fetch_assoc() : null;

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Alumni Portal</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Admin Theme -->
    <link rel="stylesheet" href="css/admin-theme.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg admin-navbar">
        <div class="container">
            <a class="navbar-brand" href="admin_dashboard.php">
                <i class="bi bi-mortarboard"></i>
                Alumni Portal Admin
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="admin_dashboard.php">
                            <i class="bi bi-house-door"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_users.php">
                            <i class="bi bi-people"></i>
                            Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_events.php">
                            <i class="bi bi-calendar-event"></i>
                            Events
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_alumni_new.php">
                            <i class="bi bi-mortarboard"></i>
                            Alumni
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">
                            <i class="bi bi-graph-up"></i>
                            Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="settings.php">
                            <i class="bi bi-gear"></i>
                            Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i>
                            Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="admin-container">
        <!-- Page Header -->
        <div class="admin-page-header fade-in">
            <h1 class="admin-page-title">Settings</h1>
            <p class="admin-page-subtitle">Manage your admin account and system preferences</p>
        </div>

        <!-- Alert Messages -->
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Profile Settings -->
            <div class="col-lg-6 mb-4">
                <div class="admin-card slide-in">
                    <div class="admin-card-header">
                        <h4 class="admin-card-title">
                            <i class="bi bi-person-circle text-primary"></i>
                            Profile Settings
                        </h4>
                    </div>
                    <div class="admin-card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($admin_profile['first_name'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($admin_profile['last_name'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" 
                                       value="<?php echo htmlspecialchars($admin_profile['email'] ?? ''); ?>" disabled>
                                <div class="form-text">Email cannot be changed</div>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i>
                                Save Changes
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Password Settings -->
            <div class="col-lg-6 mb-4">
                <div class="admin-card slide-in" style="animation-delay: 0.1s;">
                    <div class="admin-card-header">
                        <h4 class="admin-card-title">
                            <i class="bi bi-shield-lock text-warning"></i>
                            Change Password
                        </h4>
                    </div>
                    <div class="admin-card-body">
                        <form method="POST" id="passwordForm">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <div class="form-text">Password must be at least 6 characters long</div>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" name="change_password" class="btn btn-warning">
                                <i class="bi bi-key"></i>
                                Change Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="admin-card slide-in" style="animation-delay: 0.2s;">
            <div class="admin-card-header">
                <h4 class="admin-card-title">
                    <i class="bi bi-info-circle text-info"></i>
                    System Information
                </h4>
            </div>
            <div class="admin-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>System Details</h6>
                        <ul class="list-unstyled">
                            <li><strong>PHP Version:</strong> <?php echo phpversion(); ?></li>
                            <li><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></li>
                            <li><strong>Database:</strong> MySQL</li>
                            <li><strong>Session ID:</strong> <?php echo session_id(); ?></li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Admin Session</h6>
                        <ul class="list-unstyled">
                            <li><strong>Logged in as:</strong> <?php echo htmlspecialchars($_SESSION['logged_account']['email']); ?></li>
                            <li><strong>Account Type:</strong> <?php echo ucfirst($_SESSION['logged_account']['type']); ?></li>
                            <li><strong>Login Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="admin-card slide-in" style="animation-delay: 0.3s;">
            <div class="admin-card-header">
                <h4 class="admin-card-title">
                    <i class="bi bi-lightning text-success"></i>
                    Quick Actions
                </h4>
            </div>
            <div class="admin-card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="quick-action text-center">
                            <i class="bi bi-people text-primary" style="font-size: 2rem;"></i>
                            <h6 class="mt-2">Manage Users</h6>
                            <a href="manage_users.php" class="btn btn-primary btn-sm">
                                <i class="bi bi-arrow-right"></i> Go
                            </a>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="quick-action text-center">
                            <i class="bi bi-calendar-event text-warning" style="font-size: 2rem;"></i>
                            <h6 class="mt-2">Manage Events</h6>
                            <a href="manage_events.php" class="btn btn-warning btn-sm">
                                <i class="bi bi-arrow-right"></i> Go
                            </a>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="quick-action text-center">
                            <i class="bi bi-graph-up text-success" style="font-size: 2rem;"></i>
                            <h6 class="mt-2">View Reports</h6>
                            <a href="reports.php" class="btn btn-success btn-sm">
                                <i class="bi bi-arrow-right"></i> Go
                            </a>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="quick-action text-center">
                            <i class="bi bi-house-door text-info" style="font-size: 2rem;"></i>
                            <h6 class="mt-2">Dashboard</h6>
                            <a href="admin_dashboard.php" class="btn btn-info btn-sm">
                                <i class="bi bi-arrow-right"></i> Go
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Password validation
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long');
                return;
            }
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New passwords do not match');
                return;
            }
        });
    </script>
</body>
</html>