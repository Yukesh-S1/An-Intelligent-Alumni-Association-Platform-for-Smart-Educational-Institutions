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

// Handle feedback actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $feedbackId = $_POST['feedback_id'] ?? '';
    
    switch ($action) {
        case 'update_status':
            if (!empty($feedbackId)) {
                $status = $_POST['status'] ?? '';
                $adminResponse = $_POST['admin_response'] ?? '';
                
                $stmt = $conn->prepare("UPDATE feedback_table SET status = ?, admin_response = ?, updated_at = NOW() WHERE id = ?");
                $stmt->bind_param("ssi", $status, $adminResponse, $feedbackId);
                
                if ($stmt->execute()) {
                    $message = "Feedback updated successfully!";
                    $messageType = "success";
                } else {
                    $message = "Error updating feedback: " . $conn->error;
                    $messageType = "danger";
                }
            }
            break;
            
        case 'delete':
            if (!empty($feedbackId)) {
                $stmt = $conn->prepare("DELETE FROM feedback_table WHERE id = ?");
                $stmt->bind_param("i", $feedbackId);
                
                if ($stmt->execute()) {
                    $message = "Feedback deleted successfully!";
                    $messageType = "success";
                } else {
                    $message = "Error deleting feedback: " . $conn->error;
                    $messageType = "danger";
                }
            }
            break;
    }
}

// Get filter parameters
$statusFilter = $_GET['status'] ?? 'all';
$categoryFilter = $_GET['category'] ?? 'all';
$searchQuery = $_GET['search'] ?? '';

// Build WHERE clause
$whereConditions = [];

if ($statusFilter !== 'all') {
    $whereConditions[] = "f.status = '" . $conn->real_escape_string($statusFilter) . "'";
}

if ($categoryFilter !== 'all') {
    $whereConditions[] = "f.category = '" . $conn->real_escape_string($categoryFilter) . "'";
}

if (!empty($searchQuery)) {
    $search = $conn->real_escape_string($searchQuery);
    $whereConditions[] = "(f.subject LIKE '%$search%' OR f.feedback_text LIKE '%$search%' OR u.first_name LIKE '%$search%' OR u.last_name LIKE '%$search%')";
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Get feedback
$query = "SELECT f.*, u.first_name, u.last_name, u.profile_image 
          FROM feedback_table f 
          LEFT JOIN user_table u ON f.user_email = u.email 
          $whereClause 
          ORDER BY f.created_at DESC";
$result = $conn->query($query);
$feedback = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $feedback[] = $row;
    }
}

// Get statistics
$stats = [];
$stats['total'] = $conn->query("SELECT COUNT(*) as count FROM feedback_table")->fetch_assoc()['count'] ?? 0;
$stats['pending'] = $conn->query("SELECT COUNT(*) as count FROM feedback_table WHERE status = 'pending'")->fetch_assoc()['count'] ?? 0;
$stats['resolved'] = $conn->query("SELECT COUNT(*) as count FROM feedback_table WHERE status = 'resolved'")->fetch_assoc()['count'] ?? 0;
$stats['in_progress'] = $conn->query("SELECT COUNT(*) as count FROM feedback_table WHERE status = 'in_progress'")->fetch_assoc()['count'] ?? 0;

// Don't close connection yet - needed for admin_nav.php component
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Feedback - Alumni Portal</title>
    
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
            <h1 class="admin-page-title">Manage Feedback</h1>
            <p class="admin-page-subtitle">Review and respond to alumni feedback and suggestions</p>
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
                    <div class="stat-label">Total Feedback</div>
                    <p class="stat-description">All submissions</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card fade-in" style="animation-delay: 0.1s;">
                    <div class="stat-number warning"><?php echo $stats['pending']; ?></div>
                    <div class="stat-label">Pending</div>
                    <p class="stat-description">Awaiting review</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card fade-in" style="animation-delay: 0.2s;">
                    <div class="stat-number info"><?php echo $stats['in_progress']; ?></div>
                    <div class="stat-label">In Progress</div>
                    <p class="stat-description">Being addressed</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card fade-in" style="animation-delay: 0.3s;">
                    <div class="stat-number success"><?php echo $stats['resolved']; ?></div>
                    <div class="stat-label">Resolved</div>
                    <p class="stat-description">Completed</p>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="admin-card slide-in">
            <div class="admin-card-header">
                <h4 class="admin-card-title">
                    <i class="bi bi-funnel text-primary"></i>
                    Filter Feedback
                </h4>
            </div>
            <div class="admin-card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Subject, content, or alumni name..." 
                               value="<?php echo htmlspecialchars($searchQuery); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Status</option>
                            <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="in_progress" <?php echo $statusFilter === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="resolved" <?php echo $statusFilter === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <option value="all" <?php echo $categoryFilter === 'all' ? 'selected' : ''; ?>>All Categories</option>
                            <option value="general" <?php echo $categoryFilter === 'general' ? 'selected' : ''; ?>>General</option>
                            <option value="technical" <?php echo $categoryFilter === 'technical' ? 'selected' : ''; ?>>Technical</option>
                            <option value="suggestion" <?php echo $categoryFilter === 'suggestion' ? 'selected' : ''; ?>>Suggestion</option>
                            <option value="complaint" <?php echo $categoryFilter === 'complaint' ? 'selected' : ''; ?>>Complaint</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search"></i> Filter
                        </button>
                        <a href="manage_feedback_fixed.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Feedback Table -->
        <div class="admin-card slide-in" style="animation-delay: 0.1s;">
            <div class="admin-card-header">
                <h4 class="admin-card-title">
                    <i class="bi bi-chat-square-text text-success"></i>
                    Feedback Submissions (<?php echo count($feedback); ?> records)
                </h4>
            </div>
            <div class="admin-card-body">
                <?php if (empty($feedback)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-chat-square-text" style="font-size: 4rem; color: #dee2e6;"></i>
                        <h5 class="mt-3 text-muted">No Feedback Found</h5>
                        <p class="text-muted">No feedback matches your current filters.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Alumni</th>
                                    <th>Subject</th>
                                    <th>Category</th>
                                    <th>Rating</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($feedback as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo !empty($item['profile_image']) ? 'profile_images/' . $item['profile_image'] : 'profile_images/default-male-user-profile-icon.jpg'; ?>" 
                                                     alt="Profile" class="rounded-circle me-2" width="32" height="32">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?></strong>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($item['alumni_email']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($item['subject']); ?></strong>
                                            <br><small class="text-muted"><?php echo htmlspecialchars(substr($item['feedback_text'], 0, 50)) . '...'; ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo ucfirst(htmlspecialchars($item['category'] ?? 'general')); ?></span>
                                        </td>
                                        <td>
                                            <?php if (!empty($item['rating'])): ?>
                                                <div class="text-warning">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="bi bi-star<?php echo $i <= $item['rating'] ? '-fill' : ''; ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                                <small class="text-muted"><?php echo $item['rating']; ?>/5</small>
                                            <?php else: ?>
                                                <span class="text-muted">No rating</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = '';
                                            switch ($item['status']) {
                                                case 'resolved': $statusClass = 'success'; break;
                                                case 'in_progress': $statusClass = 'info'; break;
                                                case 'pending': $statusClass = 'warning'; break;
                                                default: $statusClass = 'secondary';
                                            }
                                            ?>
                                            <span class="badge bg-<?php echo $statusClass; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($item['status']))); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo date('M j, Y', strtotime($item['created_at'])); ?>
                                            <br><small class="text-muted"><?php echo date('g:i A', strtotime($item['created_at'])); ?></small>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-primary btn-sm" onclick="viewFeedback(<?php echo $item['id']; ?>)" title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="btn btn-success btn-sm" onclick="respondFeedback(<?php echo $item['id']; ?>)" title="Respond">
                                                    <i class="bi bi-reply"></i>
                                                </button>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this feedback?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="feedback_id" value="<?php echo $item['id']; ?>">
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

    <!-- Feedback Details Modal -->
    <div class="modal fade" id="feedbackModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Feedback Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="feedbackModalBody">
                    <!-- Content loaded via JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <!-- Response Modal -->
    <div class="modal fade" id="responseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Respond to Feedback</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="feedback_id" id="responseFeedbackId">
                        
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="resolved">Resolved</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Admin Response</label>
                            <textarea name="admin_response" class="form-control" rows="4" placeholder="Enter your response to the alumni..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Feedback</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'components/admin_footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        function viewFeedback(feedbackId) {
            // Load feedback details via AJAX
            fetch('get_feedback_details.php?id=' + feedbackId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const feedback = data.feedback;
                        document.getElementById('feedbackModalBody').innerHTML = `
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Alumni Information</h6>
                                    <p><strong>Name:</strong> ${feedback.first_name} ${feedback.last_name}</p>
                                    <p><strong>Email:</strong> ${feedback.alumni_email}</p>
                                    <p><strong>Date:</strong> ${new Date(feedback.created_at).toLocaleDateString()}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Feedback Details</h6>
                                    <p><strong>Category:</strong> ${feedback.category}</p>
                                    <p><strong>Rating:</strong> ${feedback.rating ? feedback.rating + '/5' : 'No rating'}</p>
                                    <p><strong>Status:</strong> <span class="badge bg-primary">${feedback.status}</span></p>
                                </div>
                            </div>
                            <hr>
                            <h6>Subject</h6>
                            <p>${feedback.subject}</p>
                            <h6>Feedback</h6>
                            <p>${feedback.feedback_text}</p>
                            ${feedback.admin_response ? '<hr><h6>Admin Response</h6><p>' + feedback.admin_response + '</p>' : ''}
                        `;
                        new bootstrap.Modal(document.getElementById('feedbackModal')).show();
                    } else {
                        alert('Error loading feedback details');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading feedback details');
                });
        }

        function respondFeedback(feedbackId) {
            document.getElementById('responseFeedbackId').value = feedbackId;
            new bootstrap.Modal(document.getElementById('responseModal')).show();
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