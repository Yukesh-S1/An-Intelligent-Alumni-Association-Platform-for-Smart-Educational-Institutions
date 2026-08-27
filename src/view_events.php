<?php
// Start output buffering to prevent header issues
ob_start();

// Start session and include files BEFORE any HTML output
session_start();
include 'db_controller.php';
$conn->select_db("Alumni");
include 'logged_user.php';

// Initialize variables
$modalOn = false;
$eventTitle = '';
$modalType = '';
$modalMessage = '';

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // GET the POST (which is retrieved from GET previously)
    if (isset($_POST['filterType'])) 
        $_GET['filterType'] = $_POST['filterType'];
    if (isset($_POST['filterTime'])) 
        $_GET['filterTime'] = $_POST['filterTime'];
    if (isset($_POST['search'])) 
        $_GET['search'] = $_POST['search'];
    
    // Event registration
    if (isset($_POST['eventID'])) {
        $eventID = $_POST['eventID'];
        $eventTitle = $_POST['eventTitle'];
        $accountEmail = $_SESSION['logged_account']['email'];
        $result = $conn->query("SELECT * FROM event_registration_table WHERE event_id = $eventID AND participant_email = '$accountEmail'");

        // If user is already registered to event
        if ($result->num_rows > 0) {
            $modalOn = true;
            $modalType = 'error';
            $modalMessage = "You're already registered for " . htmlspecialchars($eventTitle) . "!";
        } else {
            // User is not registered, insert the registration
            if ($conn->query("INSERT INTO event_registration_table (event_id, participant_email) VALUES ($eventID, '$accountEmail')") == TRUE) {
                $modalOn = true;
                $modalType = 'success';
                $modalMessage = "You've successfully registered for " . htmlspecialchars($eventTitle) . "!";
            } else {
                $modalOn = true;
                $modalType = 'error2';
                $modalMessage = "An error has occurred while registering for " . htmlspecialchars($eventTitle) . "!";
            }
        }
    }
}

// Build query filters
$filterType = "";
$filterTime = "";
$filterSearch = "";

// Type filter for Events/News
if (isset($_GET['filterType']) && $_GET['filterType'] != 'All')
    $filterType = "type = '" . $_GET['filterType'] . "'";

// Time filter for Events/News
if (isset($_GET['filterTime']) && $_GET['filterTime'] != 'All') {
    $todayDate = date('Y-m-d');
    if ($_GET['filterTime'] == 'Upcoming')
        $filterTime = "event_date >= '" . $todayDate . "'";
    elseif ($_GET['filterTime'] == 'Past')
        $filterTime = "event_date < '" . $todayDate . "'";
}

// Search filter for Events/News
if (isset($_GET['search']) && $_GET['search'] != "") {
    $trimSearch = strtolower(trim($_GET['search']));
    $filterSearch = "(
        LOWER(id) LIKE '%$trimSearch%' OR 
        LOWER(title) LIKE '%$trimSearch%' OR 
        LOWER(location) LIKE '%$trimSearch%' OR 
        LOWER(description) LIKE '%$trimSearch%' OR 
        LOWER(event_date) LIKE '%$trimSearch%' OR 
        DATE_FORMAT(event_date, '%d/%m/%Y') LIKE '%$trimSearch%' OR 
        LOWER(type) LIKE '%$trimSearch%'
    )";
}

// Puts WHERE and AND to the query appropriately
$conditions = array_filter([$filterType, $filterTime, $filterSearch]);
if (!empty($conditions))
    $whereClause = "WHERE " . implode(" AND ", $conditions);
else
    $whereClause = "";

// Query the database with the WHERE from previous
$allEventsNews = $conn->query("SELECT e.*, 
    COALESCE(COUNT(er.participant_email), 0) as participant_count 
    FROM event_table e 
    LEFT JOIN event_registration_table er ON e.id = er.event_id 
    $whereClause 
    GROUP BY e.id 
    ORDER BY e.event_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events & News | Alumni Portal</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Modern Design System -->

    <style>
        /* Match Alumni Dashboard Design */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
        }
        
        .dashboard-container {
            padding: 2rem 0;
            min-height: calc(100vh - 80px);
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .welcome-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .page-description {
            color: #718096;
            font-size: 1.1rem;
            margin-bottom: 0;
        }
        
        .filter-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .event-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }
        
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        
        .event-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s ease;
            flex-shrink: 0;
        }
        
        .event-card:hover .event-image {
            transform: scale(1.05);
        }
        
        .event-content {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .event-type-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            z-index: 2;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .event-type-event {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
        }
        
        .event-type-news {
            background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
            color: white;
        }
        
        .event-date {
            background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.875rem;
            text-align: center;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .event-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.5rem;
            line-height: 1.4;
            min-height: 2.6rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .event-location {
            color: #718096;
            font-size: 0.875rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            min-height: 1.5rem;
        }
        
        .event-description {
            color: #4a5568;
            font-size: 0.875rem;
            line-height: 1.5;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex-grow: 1;
        }
        
        .event-stats {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 1rem;
            border-top: 1px solid rgba(226, 232, 240, 0.5);
            margin-top: auto;
            flex-shrink: 0;
        }
        
        .participant-count {
            color: #718096;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-register {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
        }
        
        .btn-register:hover {
            background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(72, 187, 120, 0.4);
        }
        
        .btn-view {
            background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
            color: white;
        }
        
        .btn-view:hover {
            background: linear-gradient(135deg, #3182ce 0%, #2c5282 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(66, 153, 225, 0.4);
        }
        
        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            background: white;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .form-check-input:checked {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
        }
        
        .form-check-label {
            font-weight: 500;
            color: #4a5568;
        }
        
        .badge.text-bg-success {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%) !important;
            color: white !important;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
        }
        
        .badge.text-bg-warning {
            background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%) !important;
            color: white !important;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #718096;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: rgba(255, 255, 255, 0.5);
        }
        
        /* Animation for cards */
        .event-card {
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem 0;
            }
            
            .welcome-card {
                padding: 1.5rem;
                margin-bottom: 1.5rem;
            }
            
            .page-title {
                font-size: 2rem;
            }
            
            .filter-card {
                padding: 1rem;
            }
            
            .event-stats {
                flex-direction: column;
                gap: 0.5rem;
                align-items: stretch;
            }
            
            .event-stats .btn {
                width: 100%;
                justify-content: center;
            }
        }
        
        @media (max-width: 576px) {
            .filter-card .row {
                flex-direction: column;
            }
            
            .filter-card .col-md-6,
            .filter-card .col-md-3 {
                margin-bottom: 1rem;
            }
        }
    </style>

    <!-- Navigation -->
    <?php include 'components/nav_modern.php'; ?>
    
    <div class="dashboard-container">
        <div class="container">
            <!-- Welcome Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="welcome-card">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h1 class="page-title">
                                    <i class="bi bi-calendar-event me-3"></i>Events & News
                                </h1>
                                <p class="page-description">Discover upcoming events and stay updated with the latest news from our alumni community.</p>
                            </div>
                            <div class="col-md-4 text-center">
                                <?php include 'components/alumni_logo.php'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Filters -->
        <div class="filter-card slide-left">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold mb-2">Filter by Type</label>
                        <div class="d-flex flex-wrap gap-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="filterType" id="filterType1" value="All" <?php echo (!isset($_GET['filterType']) || $_GET['filterType'] == 'All') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="filterType1">All</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="filterType" id="filterType2" value="Event" <?php echo (isset($_GET['filterType']) && $_GET['filterType'] == 'Event') ? 'checked' : ''; ?>>
                                <label class="form-check-label badge text-bg-success" for="filterType2">Events</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="filterType" id="filterType3" value="News" <?php echo (isset($_GET['filterType']) && $_GET['filterType'] == 'News') ? 'checked' : ''; ?>>
                                <label class="form-check-label badge text-bg-warning" for="filterType3">News</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-semibold mb-2">Time Period</label>
                        <select class="form-select" name="filterTime">
                            <option value="All" <?php echo (!isset($_GET['filterTime']) || $_GET['filterTime'] == 'All') ? 'selected' : ''; ?>>All Time</option>
                            <option value="Past" <?php echo (isset($_GET['filterTime']) && $_GET['filterTime'] == 'Past') ? 'selected' : ''; ?>>Past</option>
                            <option value="Upcoming" <?php echo (isset($_GET['filterTime']) && $_GET['filterTime'] == 'Upcoming') ? 'selected' : ''; ?>>Upcoming</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-semibold mb-2">Search</label>
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search events..." name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars(trim($_GET['search'])) : ''; ?>">
                            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Events Grid -->
        <div class="row g-4">
            <?php if ($allEventsNews && $allEventsNews->num_rows > 0): ?>
                <?php while ($row = $allEventsNews->fetch_assoc()): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="event-card h-100">
                            <!-- Event Image -->
                            <div class="position-relative">
                                <img src="images/<?php echo htmlspecialchars($row['photo']); ?>" 
                                     alt="<?php echo htmlspecialchars($row['title']); ?>" 
                                     class="event-image w-100">
                                <div class="event-type-badge <?php echo $row['type'] == 'Event' ? 'event-type-event' : 'event-type-news'; ?>">
                                    <?php echo htmlspecialchars($row['type']); ?>
                                </div>
                            </div>
                            
                            <!-- Event Content -->
                            <div class="p-3 d-flex flex-column flex-grow-1">
                                <div class="event-date">
                                    <i class="bi bi-calendar me-2"></i>
                                    <?php echo date('M j, Y', strtotime($row['event_date'])); ?>
                                </div>
                                
                                <h5 class="event-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                                
                                <div class="event-location">
                                    <i class="bi bi-geo-alt me-2"></i>
                                    <?php echo htmlspecialchars($row['location']); ?>
                                </div>
                                
                                <p class="event-description"><?php echo htmlspecialchars($row['description']); ?></p>
                                
                                <div class="event-stats mt-auto">
                                    <div class="participant-count">
                                        <i class="bi bi-people me-1"></i>
                                        <?php echo $row['participant_count']; ?> registered
                                    </div>
                                    
                                    <?php if ($row['type'] == 'Event'): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="eventID" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="eventTitle" value="<?php echo htmlspecialchars($row['title']); ?>">
                                            <button type="submit" class="btn btn-register">
                                                <i class="bi bi-calendar-plus me-1"></i>Register
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-view" onclick="viewDetails(<?php echo $row['id']; ?>)">
                                            <i class="bi bi-eye me-1"></i>Read More
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="empty-state">
                        <i class="bi bi-calendar-x"></i>
                        <h4>No Events or News Found</h4>
                        <p>Try adjusting your filters or check back later for new content.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal for Registration Status -->
    <?php if ($modalOn): ?>
    <div class="modal fade show" id="statusModal" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">
                        <?php if ($modalType == 'success'): ?>
                            <i class="bi bi-check-circle text-success me-2"></i>Success!
                        <?php else: ?>
                            <i class="bi bi-exclamation-triangle text-warning me-2"></i>Notice
                        <?php endif; ?>
                    </h5>
                    <button type="button" class="btn-close" onclick="closeModal()"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0"><?php echo $modalMessage; ?></p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-primary" onclick="closeModal()">OK</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function closeModal() {
            document.getElementById('statusModal').style.display = 'none';
        }
        
        function viewDetails(eventId) {
            // You can implement a detailed view modal or redirect to a detail page
            alert('View details for event ID: ' + eventId);
        }
        
        // Auto-close modal after 5 seconds
        <?php if ($modalOn): ?>
        setTimeout(closeModal, 5000);
        <?php endif; ?>
    </script>
</body>
</html>
<?php
$conn->close();
ob_end_flush();
?>