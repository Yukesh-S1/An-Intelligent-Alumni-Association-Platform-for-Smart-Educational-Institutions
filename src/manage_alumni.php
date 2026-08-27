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
    
    if (!empty($email)) {
        switch ($action) {
            case 'approve':
                $stmt = $conn->prepare("UPDATE account_table SET status = 'Approved' WHERE email = ?");
                $stmt->bind_param("s", $email);
                if ($stmt->execute()) {
                    $message = "Alumni approved successfully!";
                    $messageType = "success";
                }
                break;
                
            case 'reject':
                $stmt = $conn->prepare("UPDATE account_table SET status = 'Rejected' WHERE email = ?");
                $stmt->bind_param("s", $email);
                if ($stmt->execute()) {
                    $message = "Alumni rejected successfully!";
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
                    $message = "Alumni deleted successfully!";
                    $messageType = "success";
                }
                break;
        }
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
    $whereConditions[] = "(u.first_name LIKE '%$search%' OR u.last_name LIKE '%$search%' OR u.email LIKE '%$search%' OR u.college_name LIKE '%$search%' OR u.company LIKE '%$search%')";
}

$whereClause = "WHERE " . implode(" AND ", $whereConditions);

// Get alumni
$query = "SELECT u.*, a.status 
          FROM user_table u 
          JOIN account_table a ON u.email = a.email 
          $whereClause 
          ORDER BY u.first_name ASC, u.last_name ASC";
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

// Don't close connection yet - needed for admin_nav.php component
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
    <?php include 'components/admin_nav.php'; ?>

    <!-- Main Content -->
    <div class="admin-container">
        <!-- Page Header -->
        <div class="admin-page-header fade-in">
            <h1 class="admin-page-title">Manage Alumni</h1>
            <p class="admin-page-subtitle">Manage alumni accounts, approvals, and directory</p>
        </div>

        <!-- Alert Messages -->
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-6">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card fade-in">
                    <div class="stat-number primary"><?php echo $stats['total']; ?></div>
                    <div class="stat-label">Total Alumni</div>
                    <p class="stat-description">All registered</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card fade-in" style="animation-delay: 0.1s;">
                    <div class="stat-number success"><?php echo $stats['approved']; ?></div>
                    <div class="stat-label">Approved</div>
                    <p class="stat-description">Active members</p>
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
                    <div class="stat-number danger"><?php echo $stats['rejected']; ?></div>
                    <div class="stat-label">Rejected</div>
                    <p class="stat-description">Declined accounts</p>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="admin-card slide-in">
            <div class="admin-card-header">
                <h4 class="admin-card-title">
                    <i class="bi bi-funnel text-primary"></i>
                    Filter Alumni
                </h4>
            </div>
            <div class="admin-card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Name, email, college, company..." 
                               value="<?php echo htmlspecialchars($searchQuery); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Status</option>
                            <option value="Approved" <?php echo $statusFilter === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="Pending" <?php echo $statusFilter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Rejected" <?php echo $statusFilter === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search"></i> Filter
                        </button>
                        <a href="manage_alumni_fixed.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </a>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-success" onclick="exportAlumni()">
                            <i class="bi bi-download"></i> Export
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Alumni Table -->
        <div class="admin-card slide-in" style="animation-delay: 0.1s;">
            <div class="admin-card-header">
                <h4 class="admin-card-title">
                    <i class="bi bi-people text-success"></i>
                    Alumni Directory (<?php echo count($alumni); ?> records)
                </h4>
            </div>
            <div class="admin-card-body">
                <?php if (empty($alumni)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-people" style="font-size: 4rem; color: #dee2e6;"></i>
                        <h5 class="mt-3 text-muted">No Alumni Found</h5>
                        <p class="text-muted">No alumni match your current filters.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Profile</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>College</th>
                                    <th>Company</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alumni as $person): ?>
                                    <tr>
                                        <td>
                                            <img src="<?php echo !empty($person['profile_image']) ? 'profile_images/' . $person['profile_image'] : 'profile_images/default-male-user-profile-icon.jpg'; ?>" 
                                                 alt="Profile" class="rounded-circle" width="40" height="40">
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($person['first_name'] . ' ' . $person['last_name']); ?></strong>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($person['gender'] ?? 'Not specified'); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($person['email']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($person['college_name'] ?? 'Not specified'); ?>
                                            <br><small class="text-muted">Grad: <?php echo htmlspecialchars($person['graduation_year'] ?? 'N/A'); ?></small>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($person['company'] ?? 'Not specified'); ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($person['job_title'] ?? 'N/A'); ?></small>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = '';
                                            switch ($person['status']) {
                                                case 'Approved': $statusClass = 'success'; break;
                                                case 'Pending': $statusClass = 'warning'; break;
                                                case 'Rejected': $statusClass = 'danger'; break;
                                                default: $statusClass = 'secondary';
                                            }
                                            ?>
                                            <span class="badge bg-<?php echo $statusClass; ?>">
                                                <?php echo htmlspecialchars($person['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <?php if ($person['status'] === 'Pending'): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="approve">
                                                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($person['email']); ?>">
                                                        <button type="submit" class="btn btn-success btn-sm" title="Approve">
                                                            <i class="bi bi-check-lg"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="reject">
                                                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($person['email']); ?>">
                                                        <button type="submit" class="btn btn-warning btn-sm" title="Reject">
                                                            <i class="bi bi-x-lg"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                
                                                <button class="btn btn-primary btn-sm" onclick="viewAlumni('<?php echo htmlspecialchars($person['email']); ?>')" title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this alumni? This action cannot be undone.')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($person['email']); ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete">
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
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'components/admin_footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        function viewAlumni(email) {
            // Open alumni profile in new tab
            window.open('view_alumni.php?email=' + encodeURIComponent(email), '_blank');
        }

        function exportAlumni() {
            // Simple CSV export
            let csv = 'Name,Email,College,Graduation Year,Company,Job Title,Status\n';
            
            const table = document.querySelector('table tbody');
            const rows = table.querySelectorAll('tr');
            
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                const name = cells[1].querySelector('strong').textContent.trim();
                const email = cells[2].textContent.trim();
                const college = cells[3].textContent.split('Grad:')[0].trim();
                const gradYear = cells[3].textContent.includes('Grad:') ? cells[3].textContent.split('Grad:')[1].trim() : 'N/A';
                const company = cells[4].textContent.split('\n')[0].trim();
                const jobTitle = cells[4].textContent.includes('\n') ? cells[4].textContent.split('\n')[1].trim() : 'N/A';
                const status = cells[5].textContent.trim();
                
                csv += `"${name}","${email}","${college}","${gradYear}","${company}","${jobTitle}","${status}"\n`;
            });
            
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'alumni_list_' + new Date().toISOString().split('T')[0] + '.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }

        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
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