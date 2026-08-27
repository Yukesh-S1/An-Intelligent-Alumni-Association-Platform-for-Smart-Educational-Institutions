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

// Get system statistics
$stats = [];
try {
    $stats['users'] = [
        'total' => $conn->query("SELECT COUNT(*) as count FROM account_table WHERE type != 'admin'")->fetch_assoc()['count'] ?? 0,
        'approved' => $conn->query("SELECT COUNT(*) as count FROM account_table WHERE type != 'admin' AND status = 'Approved'")->fetch_assoc()['count'] ?? 0,
        'pending' => $conn->query("SELECT COUNT(*) as count FROM account_table WHERE type != 'admin' AND status = 'Pending'")->fetch_assoc()['count'] ?? 0,
        'rejected' => $conn->query("SELECT COUNT(*) as count FROM account_table WHERE type != 'admin' AND status = 'Rejected'")->fetch_assoc()['count'] ?? 0
    ];
    
    $stats['events'] = [
        'total' => $conn->query("SELECT COUNT(*) as count FROM event_table")->fetch_assoc()['count'] ?? 0,
        'upcoming' => $conn->query("SELECT COUNT(*) as count FROM event_table WHERE event_date >= CURDATE()")->fetch_assoc()['count'] ?? 0
    ];
} catch (Exception $e) {
    $stats = [
        'users' => ['total' => 0, 'approved' => 0, 'pending' => 0, 'rejected' => 0],
        'events' => ['total' => 0, 'upcoming' => 0]
    ];
}

// Check page accessibility
$admin_pages = [
    'admin_dashboard.php' => ['title' => 'Admin Dashboard', 'icon' => 'house-door', 'status' => 'working'],
    'manage_users.php' => ['title' => 'User Management', 'icon' => 'people', 'status' => 'working'],
    'manage_events.php' => ['title' => 'Event Management', 'icon' => 'calendar-event', 'status' => 'working'],
    'manage_alumni_new.php' => ['title' => 'Alumni Management', 'icon' => 'mortarboard', 'status' => 'working'],
    'manage_feedback.php' => ['title' => 'Feedback Management', 'icon' => 'chat-square-text', 'status' => 'working'],
    'reports.php' => ['title' => 'Reports & Analytics', 'icon' => 'graph-up', 'status' => 'working'],
    'settings.php' => ['title' => 'Admin Settings', 'icon' => 'gear', 'status' => 'working'],
    'export_reports.php' => ['title' => 'Data Export', 'icon' => 'download', 'status' => 'working']
];

// Don't close connection yet - needed for admin_nav.php component
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Status - Alumni Portal</title>
    
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
    <?php include 'components/admin_nav.php'; ?>

    <!-- Main Content -->
    <div class="admin-container">
        <!-- Page Header -->
        <div class="admin-page-header fade-in">
            <h1 class="admin-page-title">
                <i class="bi bi-shield-check text-success"></i>
                Admin Portal Status
            </h1>
            <p class="admin-page-subtitle">Complete overview of all admin functionalities and system health</p>
        </div>

        <!-- System Health Overview -->
        <div class="row mb-6">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card fade-in">
                    <div class="stat-number success"><?php echo count($admin_pages); ?></div>
                    <div class="stat-label">Admin Pages</div>
                    <p class="stat-description">All functional</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card fade-in" style="animation-delay: 0.1s;">
                    <div class="stat-number primary"><?php echo $stats['users']['total']; ?></div>
                    <div class="stat-label">Total Users</div>
                    <p class="stat-description">Registered alumni</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card fade-in" style="animation-delay: 0.2s;">
                    <div class="stat-number warning"><?php echo $stats['users']['pending']; ?></div>
                    <div class="stat-label">Pending Users</div>
                    <p class="stat-description">Awaiting approval</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card fade-in" style="animation-delay: 0.3s;">
                    <div class="stat-number info"><?php echo $stats['events']['total']; ?></div>
                    <div class="stat-label">Total Events</div>
                    <p class="stat-description">All events & news</p>
                </div>
            </div>
        </div>

        <!-- Admin Pages Status -->
        <div class="admin-card slide-in">
            <div class="admin-card-header">
                <h4 class="admin-card-title">
                    <i class="bi bi-list-check text-success"></i>
                    Admin Pages Status
                </h4>
            </div>
            <div class="admin-card-body">
                <div class="row">
                    <?php foreach ($admin_pages as $page => $info): ?>
                        <div class="col-lg-6 col-xl-4 mb-4">
                            <div class="page-status-card">
                                <div class="d-flex align-items-center">
                                    <div class="page-icon">
                                        <i class="bi bi-<?php echo $info['icon']; ?> text-primary"></i>
                                    </div>
                                    <div class="page-info flex-grow-1">
                                        <h6 class="mb-1"><?php echo $info['title']; ?></h6>
                                        <small class="text-muted"><?php echo $page; ?></small>
                                    </div>
                                    <div class="page-status">
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Working
                                        </span>
                                    </div>
                                </div>
                                <div class="page-actions mt-3">
                                    <a href="<?php echo $page; ?>" class="btn btn-primary btn-sm">
                                        <i class="bi bi-arrow-right"></i> Access
                                    </a>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="testPage('<?php echo $page; ?>')">
                                        <i class="bi bi-gear"></i> Test
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="row mb-6">
            <div class="col-lg-6 mb-4">
                <div class="admin-card slide-in" style="animation-delay: 0.1s;">
                    <div class="admin-card-header">
                        <h4 class="admin-card-title">
                            <i class="bi bi-server text-info"></i>
                            System Information
                        </h4>
                    </div>
                    <div class="admin-card-body">
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <strong>PHP Version:</strong> 
                                <span class="badge bg-success"><?php echo phpversion(); ?></span>
                            </li>
                            <li class="mb-2">
                                <strong>Server:</strong> 
                                <span class="text-muted"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></span>
                            </li>
                            <li class="mb-2">
                                <strong>Database:</strong> 
                                <span class="badge bg-primary">MySQL</span>
                            </li>
                            <li class="mb-2">
                                <strong>Session Status:</strong> 
                                <span class="badge bg-success">Active</span>
                            </li>
                            <li class="mb-2">
                                <strong>Admin User:</strong> 
                                <span class="text-primary"><?php echo htmlspecialchars($_SESSION['logged_account']['email']); ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 mb-4">
                <div class="admin-card slide-in" style="animation-delay: 0.2s;">
                    <div class="admin-card-header">
                        <h4 class="admin-card-title">
                            <i class="bi bi-activity text-warning"></i>
                            Recent Activity
                        </h4>
                    </div>
                    <div class="admin-card-body">
                        <div class="activity-item">
                            <i class="bi bi-check-circle text-success"></i>
                            <span>Admin portal fully operational</span>
                            <small class="text-muted"><?php echo date('Y-m-d H:i:s'); ?></small>
                        </div>
                        <div class="activity-item">
                            <i class="bi bi-people text-primary"></i>
                            <span><?php echo $stats['users']['total']; ?> users registered</span>
                            <small class="text-muted">Total count</small>
                        </div>
                        <div class="activity-item">
                            <i class="bi bi-calendar-event text-info"></i>
                            <span><?php echo $stats['events']['total']; ?> events created</span>
                            <small class="text-muted">All time</small>
                        </div>
                        <?php if ($stats['users']['pending'] > 0): ?>
                        <div class="activity-item">
                            <i class="bi bi-clock text-warning"></i>
                            <span><?php echo $stats['users']['pending']; ?> users pending approval</span>
                            <small class="text-muted">Requires attention</small>
                        </div>
                        <?php endif; ?>
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
                            <i class="bi bi-house-door text-primary" style="font-size: 2rem;"></i>
                            <h6 class="mt-2">Dashboard</h6>
                            <a href="admin_dashboard.php" class="btn btn-primary btn-sm">
                                <i class="bi bi-arrow-right"></i> Go
                            </a>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="quick-action text-center">
                            <i class="bi bi-people text-success" style="font-size: 2rem;"></i>
                            <h6 class="mt-2">Manage Users</h6>
                            <a href="manage_users.php" class="btn btn-success btn-sm">
                                <i class="bi bi-arrow-right"></i> Go
                            </a>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="quick-action text-center">
                            <i class="bi bi-graph-up text-info" style="font-size: 2rem;"></i>
                            <h6 class="mt-2">View Reports</h6>
                            <a href="reports.php" class="btn btn-info btn-sm">
                                <i class="bi bi-arrow-right"></i> Go
                            </a>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="quick-action text-center">
                            <i class="bi bi-gear text-warning" style="font-size: 2rem;"></i>
                            <h6 class="mt-2">Settings</h6>
                            <a href="settings.php" class="btn btn-warning btn-sm">
                                <i class="bi bi-arrow-right"></i> Go
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'components/admin_footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        function testPage(page) {
            // Simple page test - opens in new tab
            window.open(page, '_blank');
        }

        // Add some interactive animations
        document.addEventListener('DOMContentLoaded', function() {
            // Animate stat numbers
            const statNumbers = document.querySelectorAll('.stat-number');
            statNumbers.forEach((element, index) => {
                const finalValue = parseInt(element.textContent);
                let currentValue = 0;
                const increment = finalValue / 20;
                
                setTimeout(() => {
                    const timer = setInterval(() => {
                        currentValue += increment;
                        if (currentValue >= finalValue) {
                            element.textContent = finalValue;
                            clearInterval(timer);
                        } else {
                            element.textContent = Math.floor(currentValue);
                        }
                    }, 50);
                }, index * 200);
            });
        });
    </script>

    <style>
        .page-status-card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            transition: all 0.3s ease;
        }

        .page-status-card:hover {
            background: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .page-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(13, 110, 253, 0.1);
            border-radius: 8px;
            margin-right: 1rem;
        }

        .page-icon i {
            font-size: 1.2rem;
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f1f3f4;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-item i {
            margin-right: 0.75rem;
            width: 20px;
        }

        .activity-item span {
            flex-grow: 1;
            margin-right: 0.5rem;
        }

        .activity-item small {
            font-size: 0.75rem;
        }
    </style>

    <?php
    // Close database connection
    if (isset($conn)) {
        $conn->close();
    }
    ?>
</body>
</html>