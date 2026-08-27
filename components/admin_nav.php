<?php
// Get current page name for active navigation
$current_page = basename($_SERVER['PHP_SELF']);

// Get pending users count for badge
$pending_count = 0;
if (isset($conn) && $conn instanceof mysqli) {
    try {
        $pending_result = $conn->query("SELECT COUNT(*) as count FROM account_table WHERE status = 'Pending' AND type != 'admin'");
        if ($pending_result) {
            $pending_count = $pending_result->fetch_assoc()['count'] ?? 0;
        }
    } catch (Exception $e) {
        // Connection might be closed, set count to 0
        $pending_count = 0;
    }
}
?>

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
                    <a class="nav-link <?php echo ($current_page == 'admin_dashboard.php') ? 'active' : ''; ?>" href="admin_dashboard.php">
                        <i class="bi bi-house-door"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'manage_users.php') ? 'active' : ''; ?>" href="manage_users.php">
                        <i class="bi bi-people"></i>
                        Users
                        <?php if ($pending_count > 0): ?>
                            <span class="badge bg-danger"><?php echo $pending_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'manage_events.php') ? 'active' : ''; ?>" href="manage_events.php">
                        <i class="bi bi-calendar-event"></i>
                        Events
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'manage_alumni_new.php' || $current_page == 'manage_alumni.php') ? 'active' : ''; ?>" href="manage_alumni_new.php">
                        <i class="bi bi-mortarboard"></i>
                        Alumni
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'manage_feedback.php') ? 'active' : ''; ?>" href="manage_feedback.php">
                        <i class="bi bi-chat-square-text"></i>
                        Feedback
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'manage_video_calls.php') ? 'active' : ''; ?>" href="manage_video_calls.php">
                        <i class="bi bi-camera-video"></i>
                        Video Calls
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>" href="reports.php">
                        <i class="bi bi-graph-up"></i>
                        Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'export_interface.php') ? 'active' : ''; ?>" href="export_interface.php">
                        <i class="bi bi-download"></i>
                        Export Data
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-gear"></i>
                        Settings
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="settings.php"><i class="bi bi-person-gear"></i> Profile Settings</a></li>
                        <li><a class="dropdown-item" href="export_reports.php"><i class="bi bi-download"></i> Legacy Export</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="system-health-check.php"><i class="bi bi-shield-check"></i> System Health</a></li>
                    </ul>
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