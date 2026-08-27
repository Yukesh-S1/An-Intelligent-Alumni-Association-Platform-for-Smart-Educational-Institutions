<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events & News | Alumni Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        /* Dashboard-Oriented Events Design */
        body {
            background-color: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .event-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
            overflow: hidden;
            position: relative;
        }
        
        .event-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-color: #f59e0b;
        }
        
        .event-image {
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .event-card:hover .event-image {
            transform: scale(1.05);
        }
        
        .event-type-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            z-index: 2;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.75rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .bg-success {
            background: #10b981 !important;
            color: white;
        }
        
        .bg-warning {
            background: #f59e0b !important;
            color: white;
        }
        
        .filter-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .search-input, .form-select {
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 10px 16px;
            transition: all 0.2s ease;
            font-size: 14px;
        }
        
        .search-input:focus, .form-select:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
            outline: none;
        }
        
        .btn-check:checked + .btn-outline-primary {
            background: #3b82f6;
            border-color: #3b82f6;
            color: white;
        }
        
        .btn-check:checked + .btn-outline-success {
            background: #10b981;
            border-color: #10b981;
            color: white;
        }
        
        .btn-check:checked + .btn-outline-warning {
            background: #f59e0b;
            border-color: #f59e0b;
            color: white;
        }
        
        .btn-outline-primary, .btn-outline-success, .btn-outline-warning {
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s ease;
            background: white;
            padding: 8px 16px;
        }
        
        .btn-outline-primary:hover {
            background: #3b82f6;
            border-color: #3b82f6;
            color: white;
        }
        
        .btn-outline-success:hover {
            background: #10b981;
            border-color: #10b981;
            color: white;
        }
        
        .btn-outline-warning:hover {
            background: #f59e0b;
            border-color: #f59e0b;
            color: white;
        }
        
        .btn-primary {
            background: #f59e0b;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        
        .btn-primary:hover {
            background: #d97706;
            border-color: #d97706;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(245, 158, 11, 0.3);
        }
        
        .hero-section {
            background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);
            padding: 3rem 0;
            margin-bottom: 2rem;
        }
        
        .hero-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.5rem;
        }
        
        .hero-subtitle {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 400;
        }
        
        .hero-stats {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
        }
        
        .stat-item {
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 1.5rem;
            min-width: 100px;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: white;
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }
        
        .card-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }
        
        .text-muted {
            color: #6b7280 !important;
            font-size: 0.875rem;
        }
        
        .text-muted.small i {
            color: #f59e0b;
            margin-right: 0.5rem;
        }
        
        .empty-state {
            padding: 3rem 2rem;
            text-align: center;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: #d1d5db;
            margin-bottom: 1rem;
        }
        
        .empty-state h3 {
            color: #6b7280;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .empty-state p {
            color: #9ca3af;
            font-size: 0.875rem;
        }
        
        /* Alert Styling */
        .alert {
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .alert-warning {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        
        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        /* Card Body Styling */
        .card-body {
            padding: 1.25rem;
        }
        
        .card-text {
            color: #6b7280;
            line-height: 1.5;
            font-size: 0.875rem;
        }
        
        /* Button group styling */
        .btn.w-100 {
            font-size: 0.875rem;
            padding: 10px 16px;
        }
        
        /* Container improvements */
        .container {
            max-width: 1200px;
        }
        
        /* Form styling */
        .form-label {
            font-weight: 600;
            color: #374151;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }
        
        .btn-group .btn {
            font-size: 0.875rem;
            padding: 8px 16px;
        }
        
        /* Responsive improvements */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .hero-stats {
                flex-direction: column;
                gap: 1rem;
            }
            
            .stat-item {
                min-width: auto;
            }
        }
        
        /* Loading states */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        /* Focus states for accessibility */
        .btn:focus {
            outline: 2px solid #f59e0b;
            outline-offset: 2px;
        }
    </style>

    <?php
        include 'db_controller.php';
        $conn->select_db("Alumni");
        session_start();
        include 'logged_user.php';
    ?>

    <?php include 'components/nav_modern.php'; ?>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="hero-title">Events & News</h1>
                    <p class="hero-subtitle">Stay connected with the latest happenings in our alumni community</p>
                </div>
                <div class="col-lg-4 text-end">
                    <div class="hero-stats">
                        <?php
                        $totalEvents = $conn->query("SELECT COUNT(*) as count FROM event_table WHERE type = 'Event'")->fetch_assoc()['count'];
                        $totalNews = $conn->query("SELECT COUNT(*) as count FROM event_table WHERE type = 'News'")->fetch_assoc()['count'];
                        ?>
                        <div class="stat-item">
                            <div class="stat-number"><?php echo $totalEvents; ?></div>
                            <div class="stat-label">Events</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number"><?php echo $totalNews; ?></div>
                            <div class="stat-label">News</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-4">
        <!-- Filters -->
        <div class="filter-card p-4 mb-4">
            <form method="GET" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-medium">Type</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="filterType" id="all" value="All" <?php echo (!isset($_GET['filterType']) || $_GET['filterType'] == 'All') ? 'checked' : ''; ?>>
                            <label class="btn btn-outline-primary" for="all">All</label>
                            
                            <input type="radio" class="btn-check" name="filterType" id="events" value="Event" <?php echo (isset($_GET['filterType']) && $_GET['filterType'] == 'Event') ? 'checked' : ''; ?>>
                            <label class="btn btn-outline-success" for="events">Events</label>
                            
                            <input type="radio" class="btn-check" name="filterType" id="news" value="News" <?php echo (isset($_GET['filterType']) && $_GET['filterType'] == 'News') ? 'checked' : ''; ?>>
                            <label class="btn btn-outline-warning" for="news">News</label>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-medium">Time Period</label>
                        <select class="form-select" name="filterTime">
                            <option value="All" <?php echo (!isset($_GET['filterTime']) || $_GET['filterTime'] == 'All') ? 'selected' : ''; ?>>All Time</option>
                            <option value="Upcoming" <?php echo (isset($_GET['filterTime']) && $_GET['filterTime'] == 'Upcoming') ? 'selected' : ''; ?>>Upcoming</option>
                            <option value="Past" <?php echo (isset($_GET['filterTime']) && $_GET['filterTime'] == 'Past') ? 'selected' : ''; ?>>Past</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Search</label>
                        <div class="input-group">
                            <input type="text" class="form-control search-input" placeholder="Search events and news..." 
                                   name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel me-2"></i>Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Events/News Grid -->
        <div class="row g-4">
            <?php
                // Handle POST for event registration
                if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['eventID'])) {
                    $eventID = $_POST['eventID'];
                    $eventTitle = $_POST['eventTitle'];
                    $accountEmail = $_SESSION['logged_account']['email'];
                    $result = $conn->query("SELECT * FROM event_registration_table WHERE event_id = $eventID AND participant_email = '$accountEmail'");

                    if ($result->num_rows > 0) {
                        echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                You are already registered for <strong>' . htmlspecialchars($eventTitle) . '</strong>!
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                              </div>';
                    } else {
                        if ($conn->query("INSERT INTO event_registration_table (event_id, participant_email) VALUES ($eventID, '$accountEmail')") == TRUE) {
                            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Successfully registered for <strong>' . htmlspecialchars($eventTitle) . '</strong>!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                  </div>';
                        } else {
                            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-x-circle me-2"></i>
                                    An error occurred while registering for <strong>' . htmlspecialchars($eventTitle) . '</strong>!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                  </div>';
                        }
                    }
                }

                // Build query filters
                $filterType = "";
                $filterTime = "";
                $filterSearch = "";

                if (isset($_GET['filterType']) && $_GET['filterType'] != 'All')
                    $filterType = "type = '" . $_GET['filterType'] . "'";

                if (isset($_GET['filterTime']) && $_GET['filterTime'] != 'All') {
                    date_default_timezone_set('Asia/Kuching');
                    $todayDate = date('Y-m-d');
                    if ($_GET['filterTime'] == 'Upcoming')
                        $filterTime = "event_date >= '" . $todayDate . "'";
                    elseif ($_GET['filterTime'] == 'Past')
                        $filterTime = "event_date < '" . $todayDate . "'";
                }

                if (isset($_GET['search']) && $_GET['search'] != "") {
                    $trimSearch = strtolower(trim($_GET['search']));
                    $filterSearch = "(
                        LOWER(title) LIKE '%$trimSearch%' OR 
                        LOWER(location) LIKE '%$trimSearch%' OR 
                        LOWER(description) LIKE '%$trimSearch%' OR 
                        LOWER(event_date) LIKE '%$trimSearch%' OR 
                        DATE_FORMAT(event_date, '%a, %e %b %Y') LIKE '%$trimSearch%' OR 
                        LOWER(type) LIKE '%$trimSearch%'
                        )";
                }

                $conditions = array_filter([$filterType, $filterTime, $filterSearch]);
                if (!empty($conditions))
                    $whereClause = "WHERE " . implode(" AND ", $conditions);
                else
                    $whereClause = "";

                $allEventsNews = $conn->query("SELECT * FROM event_table $whereClause ORDER BY event_date DESC, id DESC");

                if ($allEventsNews && $allEventsNews->num_rows > 0) {
                    while ($eventsNews = $allEventsNews->fetch_assoc()) {
                        $date = new DateTime($eventsNews['event_date']);
                        $formattedDate = $date->format('M j, Y');
                        $dayOfWeek = $date->format('D');
                        
                        $badgeClass = $eventsNews['type'] == 'Event' ? 'bg-success' : 'bg-warning';
                        $badgeText = $eventsNews['type'] == 'Event' ? 'Event' : 'News';
            ?>
                <div class="col-lg-6 col-xl-4">
                    <div class="card event-card h-100">
                        <div class="position-relative">
                            <img src="images/<?php echo $eventsNews['photo']; ?>" class="card-img-top event-image" alt="<?php echo htmlspecialchars($eventsNews['title']); ?>">
                            <span class="badge <?php echo $badgeClass; ?> event-type-badge"><?php echo $badgeText; ?></span>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="text-muted small">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    <?php echo $dayOfWeek . ', ' . $formattedDate; ?>
                                </div>
                            </div>
                            
                            <h5 class="card-title fw-bold mb-2"><?php echo htmlspecialchars($eventsNews['title']); ?></h5>
                            
                            <?php if (!empty($eventsNews['location'])): ?>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-geo-alt me-1"></i>
                                <?php echo htmlspecialchars($eventsNews['location']); ?>
                            </p>
                            <?php endif; ?>
                            
                            <p class="card-text flex-grow-1"><?php echo htmlspecialchars(substr($eventsNews['description'], 0, 120)) . (strlen($eventsNews['description']) > 120 ? '...' : ''); ?></p>
                            
                            <?php if ($eventsNews['type'] == 'Event'): ?>
                            <div class="mt-auto">
                                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                                    <input type="hidden" name="eventID" value="<?php echo $eventsNews['id']; ?>">
                                    <input type="hidden" name="eventTitle" value="<?php echo htmlspecialchars($eventsNews['title']); ?>">
                                    <?php if (isset($_GET['filterType'])): ?>
                                        <input type="hidden" name="filterType" value="<?php echo htmlspecialchars($_GET['filterType']); ?>">
                                    <?php endif; ?>
                                    <?php if (isset($_GET['filterTime'])): ?>
                                        <input type="hidden" name="filterTime" value="<?php echo htmlspecialchars($_GET['filterTime']); ?>">
                                    <?php endif; ?>
                                    <?php if (isset($_GET['search'])): ?>
                                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                                    <?php endif; ?>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-calendar-plus me-2"></i>Register for Event
                                    </button>
                                </form>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php 
                    }
                } else {
            ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <div class="empty-state">
                            <i class="bi bi-calendar-x display-1 text-muted mb-3"></i>
                            <h3 class="text-muted">No events or news found</h3>
                            <p class="text-muted">
                                <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                                    Try adjusting your search criteria or check back later.
                                <?php else: ?>
                                    Check back later for new events and news updates.
                                <?php endif; ?>
                            </p>
                            <?php if (isset($_GET['search']) || isset($_GET['filterType']) || isset($_GET['filterTime'])): ?>
                                <a href="view_events_modern.php" class="btn btn-primary">Clear Filters</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php 
                }
                $conn->close();
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Smooth scroll for better UX
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>