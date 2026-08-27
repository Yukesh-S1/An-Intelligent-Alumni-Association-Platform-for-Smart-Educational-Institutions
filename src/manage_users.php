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

$message = '';
$messageType = '';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $email = $_POST['email'] ?? '';
    
    if (!empty($email)) {
        switch ($action) {
            case 'approve':
                $stmt = $conn->prepare("UPDATE account_table SET status = 'Approved' WHERE email = ?");
                $stmt->bind_param("s", $email);
                if ($stmt->execute()) {
                    $message = "User approved successfully!";
                    $messageType = "success";
                }
                break;
                
            case 'reject':
                $stmt = $conn->prepare("UPDATE account_table SET status = 'Rejected' WHERE email = ?");
                $stmt->bind_param("s", $email);
                if ($stmt->execute()) {
                    $message = "User rejected successfully!";
                    $messageType = "warning";
                }
                break;
                
            case 'delete':
                // Delete from user_table first, then account_table
                $stmt1 = $conn->prepare("DELETE FROM user_table WHERE email = ?");
                $stmt1->bind_param("s", $email);
                $stmt1->execute();
                
                $stmt2 = $conn->prepare("DELETE FROM account_table WHERE email = ?");
                $stmt2->bind_param("s", $email);
                if ($stmt2->execute()) {
                    $message = "User deleted successfully!";
                    $messageType = "danger";
                }
                break;
        }
    }
}

// Get filter parameters
$statusFilter = $_GET['status'] ?? 'all';
$searchQuery = $_GET['search'] ?? '';

// Build WHERE clause
$whereConditions = ["a.type != 'admin'"];

if ($statusFilter !== 'all') {
    $whereConditions[] = "a.status = '" . $conn->real_escape_string($statusFilter) . "'";
}

if (!empty($searchQuery)) {
    $search = $conn->real_escape_string($searchQuery);
    $whereConditions[] = "(u.first_name LIKE '%$search%' OR u.last_name LIKE '%$search%' OR a.email LIKE '%$search%')";
}

$whereClause = "WHERE " . implode(" AND ", $whereConditions);

// Get users
$query = "SELECT a.email, a.status, a.type, u.first_name, u.last_name, u.gender, u.profile_image 
          FROM account_table a 
          LEFT JOIN user_table u ON a.email = u.email 
          $whereClause 
          ORDER BY a.status ASC, a.email ASC";

$result = $conn->query($query);
$users = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Get statistics
$stats = [];
$stats['total'] = $conn->query("SELECT COUNT(*) as count FROM account_table WHERE type != 'admin'")->fetch_assoc()['count'] ?? 0;
$stats['pending'] = $conn->query("SELECT COUNT(*) as count FROM account_table WHERE type != 'admin' AND status = 'Pending'")->fetch_assoc()['count'] ?? 0;
$stats['approved'] = $conn->query("SELECT COUNT(*) as count FROM account_table WHERE type != 'admin' AND status = 'Approved'")->fetch_assoc()['count'] ?? 0;
$stats['rejected'] = $conn->query("SELECT COUNT(*) as count FROM account_table WHERE type != 'admin' AND status = 'Rejected'")->fetch_assoc()['count'] ?? 0;

// Don't close connection yet - needed for admin_nav.php component
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Alumni Portal</title>
    
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
                        <a class="nav-link active" href="manage_users.php">
                            <i class="bi bi-people"></i>
                            Users
                            <?php if ($stats['pending'] > 0): ?>
                                <span class="badge bg-danger"><?php echo $stats['pending']; ?></span>
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
            <h1 class="admin-page-title">Manage Users</h1>
            <p class="admin-page-subtitle">Review, approve, and manage user accounts</p>
        </div>

        <!-- Alert Messages -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : ($messageType === 'warning' ? 'exclamation-triangle' : 'x-circle'); ?>"></i>
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-6">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card fade-in">
                    <div class="stat-number primary"><?php echo $stats['total']; ?></div>
                    <div class="stat-label">Total Users</div>
                    <p class="stat-description">All registered users</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card fade-in" style="animation-delay: 0.1s;">
                    <div class="stat-number warning"><?php echo $stats['pending']; ?></div>
                    <div class="stat-label">Pending</div>
                    <p class="stat-description">Awaiting approval</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card fade-in" style="animation-delay: 0.2s;">
                    <div class="stat-number success"><?php echo $stats['approved']; ?></div>
                    <div class="stat-label">Approved</div>
                    <p class="stat-description">Active users</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card fade-in" style="animation-delay: 0.3s;">
                    <div class="stat-number error"><?php echo $stats['rejected']; ?></div>
                    <div class="stat-label">Rejected</div>
                    <p class="stat-description">Declined users</p>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="admin-card mb-6 slide-in">
            <div class="admin-card-body">
                <form method="GET" class="row align-items-end">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Filter by Status</label>
                        <select name="status" class="form-control">
                            <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Users</option>
                            <option value="Pending" <?php echo $statusFilter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Approved" <?php echo $statusFilter === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="Rejected" <?php echo $statusFilter === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Search Users</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                    </div>
                    <div class="col-md-2 mb-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i>
                            Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div class="admin-card slide-in" style="animation-delay: 0.2s;">
            <div class="admin-card-header">
                <h4 class="admin-card-title">
                    <i class="bi bi-people text-primary"></i>
                    User Accounts
                </h4>
            </div>
            <div class="admin-card-body">
                <?php if (!empty($users)): ?>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Profile</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <?php
                                            if (!empty($user['profile_image'])) {
                                                $profileImg = 'profile_images/' . $user['profile_image'];
                                            } else {
                                                $profileImg = 'profile_images/' . ($user['gender'] === 'Female' ? 'default-female-user-profile-icon.jpg' : 'default-male-user-profile-icon.jpg');
                                            }
                                            ?>
                                            <img src="<?php echo $profileImg; ?>" 
                                                 class="profile-img" 
                                                 alt="Profile"
                                                 onerror="this.src='https://via.placeholder.com/40x40/6c757d/ffffff?text=U'">
                                        </td>
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
                                        <td>
                                            <div class="d-flex gap-2">
                                                <?php if ($user['status'] === 'Pending'): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="approve">
                                                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                                                        <button type="submit" class="btn btn-success btn-sm" title="Approve User">
                                                            <i class="bi bi-check-lg"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="reject">
                                                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                                                        <button type="submit" class="btn btn-warning btn-sm" title="Reject User">
                                                            <i class="bi bi-x-lg"></i>
                                                        </button>
                                                    </form>
                                                <?php elseif ($user['status'] === 'Approved'): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="reject">
                                                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                                                        <button type="submit" class="btn btn-outline-warning btn-sm" title="Reject User">
                                                            <i class="bi bi-x-lg"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="approve">
                                                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                                                        <button type="submit" class="btn btn-outline-success btn-sm" title="Approve User">
                                                            <i class="bi bi-check-lg"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete User">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                        <h5 class="mt-3 text-muted">No users found</h5>
                        <p class="text-muted">Try adjusting your search criteria or filters.</p>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($users)): ?>
                <div class="admin-card-footer">
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i>
                        Showing <?php echo count($users); ?> user(s)
                    </small>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Animate stat numbers
        document.addEventListener('DOMContentLoaded', function() {
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
                }, index * 100);
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