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

// Handle event actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $eventId = $_POST['event_id'] ?? '';
    
    switch ($action) {
        case 'delete':
            if (!empty($eventId)) {
                $stmt = $conn->prepare("DELETE FROM event_table WHERE id = ?");
                $stmt->bind_param("i", $eventId);
                if ($stmt->execute()) {
                    $message = "Event deleted successfully!";
                    $messageType = "success";
                }
            }
            break;
            
        case 'add':
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $eventDate = $_POST['event_date'] ?? '';
            $location = $_POST['location'] ?? '';
            $type = $_POST['type'] ?? 'Event';
            
            if (!empty($title) && !empty($eventDate)) {
                $stmt = $conn->prepare("INSERT INTO event_table (title, description, event_date, location, type) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $title, $description, $eventDate, $location, $type);
                if ($stmt->execute()) {
                    $message = "Event created successfully!";
                    $messageType = "success";
                }
            }
            break;
    }
}

// Get filter parameters
$typeFilter = $_GET['type'] ?? 'all';
$searchQuery = $_GET['search'] ?? '';

// Build WHERE clause
$whereConditions = [];

if ($typeFilter !== 'all') {
    $whereConditions[] = "type = '" . $conn->real_escape_string($typeFilter) . "'";
}

if (!empty($searchQuery)) {
    $search = $conn->real_escape_string($searchQuery);
    $whereConditions[] = "(title LIKE '%$search%' OR description LIKE '%$search%' OR location LIKE '%$search%')";
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Get events (fix for missing event_time column)
$query = "SELECT * FROM event_table $whereClause ORDER BY event_date DESC, id DESC";
$result = $conn->query($query);
$events = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}

// Get statistics
$stats = [];
$stats['total'] = $conn->query("SELECT COUNT(*) as count FROM event_table")->fetch_assoc()['count'] ?? 0;
$stats['events'] = $conn->query("SELECT COUNT(*) as count FROM event_table WHERE type != 'News'")->fetch_assoc()['count'] ?? 0;
$stats['news'] = $conn->query("SELECT COUNT(*) as count FROM event_table WHERE type = 'News'")->fetch_assoc()['count'] ?? 0;
$stats['upcoming'] = $conn->query("SELECT COUNT(*) as count FROM event_table WHERE event_date >= CURDATE()")->fetch_assoc()['count'] ?? 0;

// Don't close connection yet - needed for admin_nav.php component
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - Alumni Portal</title>
    
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
                        <a class="nav-link active" href="manage_events.php">
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
            <h1 class="admin-page-title">Manage Events</h1>
            <p class="admin-page-subtitle">Create, edit, and manage alumni events and news</p>
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
                    <div class="stat-label">Total Items</div>
                    <p class="stat-description">Events and news</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card fade-in" style="animation-delay: 0.1s;">
                    <div class="stat-number success"><?php echo $stats['events']; ?></div>
                    <div class="stat-label">Events</div>
                    <p class="stat-description">Alumni events</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card fade-in" style="animation-delay: 0.2s;">
                    <div class="stat-number warning"><?php echo $stats['news']; ?></div>
                    <div class="stat-label">News</div>
                    <p class="stat-description">News articles</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card fade-in" style="animation-delay: 0.3s;">
                    <div class="stat-number primary"><?php echo $stats['upcoming']; ?></div>
                    <div class="stat-label">Upcoming</div>
                    <p class="stat-description">Future events</p>
                </div>
            </div>
        </div>

        <!-- Add Event Form -->
        <div class="admin-card mb-6 slide-in">
            <div class="admin-card-header">
                <h4 class="admin-card-title">
                    <i class="bi bi-plus-circle text-success"></i>
                    Add New Event
                </h4>
            </div>
            <div class="admin-card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-control">
                                <option value="Event">Event</option>
                                <option value="News">News</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Date *</label>
                            <input type="date" name="event_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" placeholder="Event location">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Event description..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-plus-lg"></i>
                        Create Event
                    </button>
                </form>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="admin-card mb-6 slide-in" style="animation-delay: 0.1s;">
            <div class="admin-card-body">
                <form method="GET" class="row align-items-end">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Filter by Type</label>
                        <select name="type" class="form-control">
                            <option value="all" <?php echo $typeFilter === 'all' ? 'selected' : ''; ?>>All Items</option>
                            <option value="Event" <?php echo $typeFilter === 'Event' ? 'selected' : ''; ?>>Events</option>
                            <option value="News" <?php echo $typeFilter === 'News' ? 'selected' : ''; ?>>News</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search events..." value="<?php echo htmlspecialchars($searchQuery); ?>">
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

        <!-- Events Table -->
        <div class="admin-card slide-in" style="animation-delay: 0.2s;">
            <div class="admin-card-header">
                <h4 class="admin-card-title">
                    <i class="bi bi-calendar-event text-primary"></i>
                    Events & News
                </h4>
            </div>
            <div class="admin-card-body">
                <?php if (!empty($events)): ?>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Location</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($events as $event): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                            <?php if (!empty($event['description'])): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars(substr($event['description'], 0, 100)) . (strlen($event['description']) > 100 ? '...' : ''); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $event['type'] === 'News' ? 'badge-warning' : 'badge-primary'; ?>">
                                                <?php echo htmlspecialchars($event['type']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            $eventDate = new DateTime($event['event_date']);
                                            echo $eventDate->format('M d, Y');
                                            ?>
                                        </td>
                                        <td>
                                            <span class="text-muted">All Day</span>
                                        </td>
                                        <td>
                                            <?php echo !empty($event['location']) ? htmlspecialchars($event['location']) : '<span class="text-muted">-</span>'; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-primary btn-sm" onclick="viewEvent(<?php echo $event['id']; ?>)" title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this event?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete Event">
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
                        <i class="bi bi-calendar-x text-muted" style="font-size: 4rem;"></i>
                        <h5 class="mt-3 text-muted">No events found</h5>
                        <p class="text-muted">Create your first event using the form above.</p>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($events)): ?>
                <div class="admin-card-footer">
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i>
                        Showing <?php echo count($events); ?> event(s)
                    </small>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Event Details Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Event Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="eventModalBody">
                    <!-- Event details will be loaded here -->
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
        
        // View event details
        function viewEvent(eventId) {
            // In a real implementation, you would fetch event details via AJAX
            // For now, we'll show a placeholder
            document.getElementById('eventModalBody').innerHTML = `
                <div class="text-center py-4">
                    <i class="bi bi-calendar-event text-primary" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Event ID: ${eventId}</h5>
                    <p class="text-muted">Event details would be loaded here via AJAX.</p>
                </div>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('eventModal'));
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

    <?php
    // Close database connection
    if (isset($conn)) {
        $conn->close();
    }
    ?>
</body>
</html>