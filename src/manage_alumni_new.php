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

// Handle alumni actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $email = $_POST['email'] ?? '';
    
    switch ($action) {
        case 'update_status':
            $status = $_POST['status'] ?? '';
            if (!empty($email) && !empty($status)) {
                $stmt = $conn->prepare("UPDATE account_table SET status = ? WHERE email = ?");
                $stmt->bind_param("ss", $status, $email);
                if ($stmt->execute()) {
                    $message = "Alumni status updated successfully!";
                    $messageType = "success";
                }
            }
            break;
            
        case 'delete':
            if (!empty($email)) {
                // Delete from user_table first, then account_table
                $stmt1 = $conn->prepare("DELETE FROM user_table WHERE email = ?");
                $stmt1->bind_param("s", $email);
                $stmt1->execute();
                
                $stmt2 = $conn->prepare("DELETE FROM account_table WHERE email = ?");
                $stmt2->bind_param("s", $email);
                if ($stmt2->execute()) {
                    $message = "Alumni deleted successfully!";
                    $messageType = "success";
                }
            }
            break;
    }
}

// Get filter parameters
$statusFilter = $_GET['status'] ?? 'all';
$searchQuery = $_GET['search'] ?? '';

// Build WHERE clause
$whereConditions = ["a.type = 'user'"];

if ($statusFilter !== 'all') {
    $whereConditions[] = "a.status = '" . $conn->real_escape_string($statusFilter) . "'";
}

if (!empty($searchQuery)) {
    $search = $conn->real_escape_string($searchQuery);
    $whereConditions[] = "(u.first_name LIKE '%$search%' OR u.last_name LIKE '%$search%' OR a.email LIKE '%$search%' OR u.college_name LIKE '%$search%' OR u.batch_year LIKE '%$search%')";
}

$whereClause = "WHERE " . implode(" AND ", $whereConditions);

// Get alumni
$query = "SELECT a.email, a.status, u.first_name, u.last_name, u.gender, u.profile_image, 
          u.college_name, u.batch_year, u.hometown, u.current_location, u.dob
          FROM account_table a 
          LEFT JOIN user_table u ON a.email = u.email 
          $whereClause 
          ORDER BY u.last_name ASC, u.first_name ASC";

$result = $conn->query($query);
$alumni = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $alumni[] = $row;
    }
}

// Get statistics
$stats = [];
$stats['total'] = $conn->query("SELECT COUNT(*) as count FROM account_table WHERE type = 'user'")->fetch_assoc()['count'] ?? 0;
$stats['approved'] = $conn->query("SELECT COUNT(*) as count FROM account_table WHERE type = 'user' AND status = 'Approved'")->fetch_assoc()['count'] ?? 0;
$stats['pending'] = $conn->query("SELECT COUNT(*) as count FROM account_table WHERE type = 'user' AND status = 'Pending'")->fetch_assoc()['count'] ?? 0;
$stats['rejected'] = $conn->query("SELECT COUNT(*) as count FROM account_table WHERE type = 'user' AND status = 'Rejected'")->fetch_assoc()['count'] ?? 0;

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Alumni - Alumni Portal</title>
    
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
                        <a class="nav-link active" href="manage_alumni_new.php">
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
            <h1 class="admin-page-title">Alumni Directory</h1>
            <p class="admin-page-subtitle">View and manage alumni profiles and information</p>
        </div>

        <!-- Alert Messages -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'x-circle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-6">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card fade-in">
                    <div class="stat-number primary"><?php echo $stats['total']; ?></div>
                    <div class="stat-label">Total Alumni</div>
                    <p class="stat-description">All registered alumni</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card fade-in" style="animation-delay: 0.1s;">
                    <div class="stat-number success"><?php echo $stats['approved']; ?></div>
                    <div class="stat-label">Active Alumni</div>
                    <p class="stat-description">Approved members</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card fade-in" style="animation-delay: 0.2s;">
                    <div class="stat-number warning"><?php echo $stats['pending']; ?></div>
                    <div class="stat-label">Pending</div>
                    <p class="stat-description">Awaiting approval</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card fade-in" style="animation-delay: 0.3s;">
                    <div class="stat-number error"><?php echo $stats['rejected']; ?></div>
                    <div class="stat-label">Rejected</div>
                    <p class="stat-description">Declined applications</p>
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
                            <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Alumni</option>
                            <option value="Approved" <?php echo $statusFilter === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="Pending" <?php echo $statusFilter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Rejected" <?php echo $statusFilter === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Search Alumni</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by name, email, college..." value="<?php echo htmlspecialchars($searchQuery); ?>">
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

        <!-- Alumni Grid -->
        <div class="admin-card slide-in" style="animation-delay: 0.2s;">
            <div class="admin-card-header">
                <h4 class="admin-card-title">
                    <i class="bi bi-mortarboard text-primary"></i>
                    Alumni Profiles
                </h4>
            </div>
            <div class="admin-card-body">
                <?php if (!empty($alumni)): ?>
                    <div class="row">
                        <?php foreach ($alumni as $person): ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="admin-card h-100">
                                    <div class="admin-card-body text-center">
                                        <!-- Profile Image -->
                                        <div class="mb-3">
                                            <?php
                                            if (!empty($person['profile_image'])) {
                                                $profileImg = 'profile_images/' . $person['profile_image'];
                                            } else {
                                                $profileImg = 'profile_images/' . ($person['gender'] === 'Female' ? 'default-female-user-profile-icon.jpg' : 'default-male-user-profile-icon.jpg');
                                            }
                                            ?>
                                            <img src="<?php echo $profileImg; ?>" 
                                                 class="profile-img" 
                                                 style="width: 80px; height: 80px;"
                                                 alt="Profile"
                                                 onerror="this.src='https://via.placeholder.com/80x80/6c757d/ffffff?text=<?php echo strtoupper(substr($person['first_name'] ?? 'U', 0, 1)); ?>'">
                                        </div>
                                        
                                        <!-- Name and Status -->
                                        <h5 class="mb-2"><?php echo htmlspecialchars(($person['first_name'] ?? '') . ' ' . ($person['last_name'] ?? '')); ?></h5>
                                        <div class="mb-3">
                                            <?php
                                            $badgeClass = match($person['status']) {
                                                'Approved' => 'badge-success',
                                                'Rejected' => 'badge-danger',
                                                default => 'badge-warning'
                                            };
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?>">
                                                <?php echo htmlspecialchars($person['status']); ?>
                                            </span>
                                        </div>
                                        
                                        <!-- Details -->
                                        <div class="text-start mb-3">
                                            <small class="text-muted d-block">
                                                <i class="bi bi-envelope me-2"></i>
                                                <?php echo htmlspecialchars($person['email']); ?>
                                            </small>
                                            <?php if (!empty($person['college_name'])): ?>
                                                <small class="text-muted d-block">
                                                    <i class="bi bi-building me-2"></i>
                                                    <?php echo htmlspecialchars($person['college_name']); ?>
                                                    <?php if (!empty($person['batch_year'])): ?>
                                                        - Class of <?php echo htmlspecialchars($person['batch_year']); ?>
                                                    <?php endif; ?>
                                                </small>
                                            <?php endif; ?>
                                            <?php if (!empty($person['year_graduated'])): ?>
                                                <small class="text-muted d-block">
                                                    <i class="bi bi-calendar me-2"></i>
                                                    Class of <?php echo htmlspecialchars($person['year_graduated']); ?>
                                                </small>
                                            <?php endif; ?>
                                            <?php if (!empty($person['phone_number'])): ?>
                                                <small class="text-muted d-block">
                                                    <i class="bi bi-phone me-2"></i>
                                                    <?php echo htmlspecialchars($person['phone_number']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Actions -->
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button class="btn btn-primary btn-sm" onclick="viewAlumni('<?php echo htmlspecialchars($person['email']); ?>')" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            
                                            <?php if ($person['status'] !== 'Approved'): ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($person['email']); ?>">
                                                    <input type="hidden" name="status" value="Approved">
                                                    <button type="submit" class="btn btn-success btn-sm" title="Approve">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this alumni?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="email" value="<?php echo htmlspecialchars($person['email']); ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-people text-muted" style="font-size: 4rem;"></i>
                        <h5 class="mt-3 text-muted">No alumni found</h5>
                        <p class="text-muted">Try adjusting your search criteria or filters.</p>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($alumni)): ?>
                <div class="admin-card-footer">
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i>
                        Showing <?php echo count($alumni); ?> alumni
                    </small>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alumni Details Modal -->
    <div class="modal fade" id="alumniModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Alumni Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="alumniModalBody">
                    <!-- Alumni details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Auto-dismiss alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // View alumni details
        function viewAlumni(email) {
            document.getElementById('alumniModalBody').innerHTML = `
                <div class="text-center py-4">
                    <i class="bi bi-mortarboard text-primary" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Alumni: ${email}</h5>
                    <p class="text-muted">Detailed alumni information would be loaded here via AJAX.</p>
                </div>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('alumniModal'));
            modal.show();
        }
        
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
</body>
</html>