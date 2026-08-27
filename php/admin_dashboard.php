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

// Get comprehensive statistics
$stats = [];
try {
    // User statistics
    $stats['total_users'] = $conn->query("SELECT COUNT(*) as count FROM account_table WHERE type != 'admin'")->fetch_assoc()['count'] ?? 0;
    $stats['pending_users'] = $conn->query("SELECT COUNT(*) as count FROM account_table WHERE type != 'admin' AND status = 'Pending'")->fetch_assoc()['count'] ?? 0;
    $stats['approved_users'] = $conn->query("SELECT COUNT(*) as count FROM account_table WHERE type != 'admin' AND status = 'Approved'")->fetch_assoc()['count'] ?? 0;
    $stats['rejected_users'] = $conn->query("SELECT COUNT(*) as count FROM account_table WHERE type != 'admin' AND status = 'Rejected'")->fetch_assoc()['count'] ?? 0;
    
    // Event statistics
    $stats['total_events'] = $conn->query("SELECT COUNT(*) as count FROM event_table WHERE type != 'News'")->fetch_assoc()['count'] ?? 0;
    $stats['upcoming_events'] = $conn->query("SELECT COUNT(*) as count FROM event_table WHERE type != 'News' AND event_date >= CURDATE()")->fetch_assoc()['count'] ?? 0;
    
    // News statistics
    $stats['total_news'] = $conn->query("SELECT COUNT(*) as count FROM event_table WHERE type = 'News'")->fetch_assoc()['count'] ?? 0;
    
    // Recent activities
    $recent_users = $conn->query("SELECT u.first_name, u.last_name, a.email, a.status, a.type FROM account_table a LEFT JOIN user_table u ON a.email = u.email WHERE a.type != 'admin' ORDER BY a.email DESC LIMIT 5");
    $stats['recent_users'] = [];
    if ($recent_users) {
        while ($row = $recent_users->fetch_assoc()) {
            $stats['recent_users'][] = $row;
        }
    }
    
} catch (Exception $e) {
    // Set default values if queries fail
    $stats = [
        'total_users' => 0,
        'pending_users' => 0,
        'approved_users' => 0,
        'rejected_users' => 0,
        'total_events' => 0,
        'upcoming_events' => 0,
        'total_news' => 0,
        'recent_users' => []
    ];
}

// Don't close connection yet - needed for admin_nav.php component
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Alumni Portal</title>
    
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
                        <a class="nav-link active" href="admin_dashboard.php">
                            <i class="bi bi-house-door"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_users.php">
                            <i class="bi bi-people"></i>
                            Users
                            <?php if ($stats['pending_users'] > 0): ?>
                                <span class="badge bg-danger"><?php echo $stats['pending_users']; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_events.php">
                            <i class="bi bi-calendar-event"></i>
                            Events
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_alumni.php">
                            <i class="bi bi-mortarboard"></i>
                            Alumni
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_video_calls.php">
                            <i class="bi bi-camera-video"></i>
                            Video Calls
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">
                            <i class="bi bi-graph-up"></i>
                            Reports
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
            <h1 class="admin-page-title">Dashboard</h1>
            <p class="admin-page-subtitle">Welcome back! Here's an overview of your alumni portal.</p>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-8">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card fade-in">
                    <div class="stat-number primary"><?php echo $stats['total_users']; ?></div>
                    <div class="stat-label">Total Users</div>
                    <p class="stat-description">Registered alumni</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card fade-in" style="animation-delay: 0.1s;">
                    <div class="stat-number warning"><?php echo $stats['pending_users']; ?></div>
                    <div class="stat-label">Pending Approval</div>
                    <p class="stat-description">Awaiting review</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card fade-in" style="animation-delay: 0.2s;">
                    <div class="stat-number success"><?php echo $stats['approved_users']; ?></div>
                    <div class="stat-label">Approved Users</div>
                    <p class="stat-description">Active alumni</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card fade-in" style="animation-delay: 0.3s;">
                    <div class="stat-number primary"><?php echo $stats['total_events']; ?></div>
                    <div class="stat-label">Total Events</div>
                    <p class="stat-description">All events created</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-8">
            <div class="col-12">
                <h3 class="mb-6">Quick Actions</h3>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <a href="manage_users.php" class="quick-action slide-in">
                    <i class="bi bi-people text-primary quick-action-icon"></i>
                    <div class="quick-action-title">Manage Users</div>
                    <p class="quick-action-description">Approve, reject, or manage user accounts</p>
                </a>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <a href="manage_events.php" class="quick-action slide-in" style="animation-delay: 0.1s;">
                    <i class="bi bi-calendar-event text-success quick-action-icon"></i>
                    <div class="quick-action-title">Manage Events</div>
                    <p class="quick-action-description">Create and manage alumni events</p>
                </a>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <a href="manage_alumni.php" class="quick-action slide-in" style="animation-delay: 0.2s;">
                    <i class="bi bi-mortarboard text-warning quick-action-icon"></i>
                    <div class="quick-action-title">Alumni Directory</div>
                    <p class="quick-action-description">View and manage alumni profiles</p>
                </a>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <a href="manage_video_calls.php" class="quick-action slide-in" style="animation-delay: 0.3s;">
                    <i class="bi bi-camera-video text-info quick-action-icon"></i>
                    <div class="quick-action-title">Video Calls</div>
                    <p class="quick-action-description">Manage and initiate video calls with alumni</p>
                </a>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <a href="export_interface.php" class="quick-action slide-in" style="animation-delay: 0.4s;">
                    <i class="bi bi-download text-success quick-action-icon"></i>
                    <div class="quick-action-title">Export Data</div>
                    <p class="quick-action-description">Export feedback, users, and event data</p>
                </a>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <a href="reports.php" class="quick-action slide-in" style="animation-delay: 0.3s;">
                    <i class="bi bi-graph-up text-info quick-action-icon"></i>
                    <div class="quick-action-title">Reports</div>
                    <p class="quick-action-description">View analytics and generate reports</p>
                </a>
            </div>
        </div>

        <!-- Recent Activity & System Status -->
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="admin-card fade-in">
                    <div class="admin-card-header">
                        <h4 class="admin-card-title">
                            <i class="bi bi-clock-history text-primary"></i>
                            Recent User Activity
                        </h4>
                    </div>
                    <div class="admin-card-body">
                        <?php if (!empty($stats['recent_users'])): ?>
                            <div class="table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Status</th>
                                            <th>Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($stats['recent_users'] as $user): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?></strong>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <?php
                                                    $badgeClass = match($user['status']) {
                                                        'Approved' => 'badge-success',
                                                        'Rejected' => 'badge-danger',
                                                        default => 'badge-warning'
                                                    };
                                                    ?>
                                                    <span class="badge <?php echo $badgeClass; ?>">
                                                        <?php echo htmlspecialchars($user['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-secondary">
                                                        <?php echo htmlspecialchars($user['type']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">No recent user activity</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="admin-card-footer">
                        <a href="manage_users.php" class="btn btn-primary">
                            <i class="bi bi-arrow-right"></i>
                            View All Users
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 mb-4">
                <div class="admin-card fade-in" style="animation-delay: 0.2s;">
                    <div class="admin-card-header">
                        <h4 class="admin-card-title">
                            <i class="bi bi-shield-check text-success"></i>
                            System Status
                        </h4>
                    </div>
                    <div class="admin-card-body">
                        <div class="d-flex align-items-center mb-4">
                            <i class="bi bi-check-circle text-success me-3" style="font-size: 1.5rem;"></i>
                            <div>
                                <div class="fw-semibold">Database Connection</div>
                                <small class="text-muted">Active and healthy</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-4">
                            <i class="bi bi-check-circle text-success me-3" style="font-size: 1.5rem;"></i>
                            <div>
                                <div class="fw-semibold">User Authentication</div>
                                <small class="text-muted">Working properly</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-4">
                            <i class="bi bi-check-circle text-success me-3" style="font-size: 1.5rem;"></i>
                            <div>
                                <div class="fw-semibold">Admin Portal</div>
                                <small class="text-muted">Fully optimized</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle text-success me-3" style="font-size: 1.5rem;"></i>
                            <div>
                                <div class="fw-semibold">Theme System</div>
                                <small class="text-muted">Unified design</small>
                            </div>
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
        // Add smooth animations on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Animate stat numbers
            const statNumbers = document.querySelectorAll('.stat-number');
            statNumbers.forEach((element, index) => {
                const finalValue = parseInt(element.textContent);
                let currentValue = 0;
                const increment = finalValue / 30;
                
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

    <?php
    // Close database connection
    if (isset($conn)) {
        $conn->close();
    }
    ?>
</body>
</html>