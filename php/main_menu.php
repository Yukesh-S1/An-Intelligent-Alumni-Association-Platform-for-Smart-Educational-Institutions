<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_account']) || $_SESSION['logged_account']['type'] !== 'user') {
    header('Location: login.php');
    exit();
}

// Database connection
include 'db_controller.php';
$conn->select_db("Alumni");

// Get current user info
$current_user = $_SESSION['logged_account']['email'];
$user_info = [];

try {
    $user_query = $conn->prepare("SELECT u.*, a.status FROM user_table u JOIN account_table a ON u.email = a.email WHERE u.email = ?");
    $user_query->bind_param("s", $current_user);
    $user_query->execute();
    $result = $user_query->get_result();
    $user_info = $result->fetch_assoc();
} catch (Exception $e) {
    $user_info = ['first_name' => 'User', 'last_name' => ''];
}

// Get dashboard statistics
$stats = [];

// Total alumni count
try {
    $alumni_result = $conn->query("SELECT COUNT(*) as count FROM user_table u JOIN account_table a ON u.email = a.email WHERE a.type = 'user' AND a.status = 'Approved'");
    $stats['alumni'] = $alumni_result ? $alumni_result->fetch_assoc()['count'] : 0;
} catch (Exception $e) {
    $stats['alumni'] = 0;
}

// Upcoming events count
try {
    $events_result = $conn->query("SELECT COUNT(*) as count FROM event_table WHERE event_date >= CURDATE()");
    $stats['events'] = $events_result ? $events_result->fetch_assoc()['count'] : 0;
} catch (Exception $e) {
    $stats['events'] = 0;
}

// Active news count
try {
    $news_result = $conn->query("SELECT COUNT(*) as count FROM event_table WHERE type = 'News'");
    $stats['news'] = $news_result ? $news_result->fetch_assoc()['count'] : 0;
} catch (Exception $e) {
    $stats['news'] = 0;
}

// Recent events
$recent_events = [];
try {
    $events_query = $conn->query("SELECT * FROM event_table WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 3");
    if ($events_query) {
        while ($row = $events_query->fetch_assoc()) {
            $recent_events[] = $row;
        }
    }
} catch (Exception $e) {
    $recent_events = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Dashboard - Alumni Portal</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            padding-bottom: 2rem;
            margin: 0;
            padding-top: 0;
        }
        
        .dashboard-container {
            padding: 2rem 0;
            min-height: calc(100vh - 80px);
            margin-top: 0;
        }
        
        .welcome-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
            height: 100%;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .stat-label {
            font-size: 1rem;
            color: #666;
            font-weight: 500;
        }
        
        .quick-action {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
            color: #333;
            display: block;
            height: 100%;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        
        .quick-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            color: #333;
            text-decoration: none;
        }
        
        .quick-action i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .event-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .event-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        
        /* Navigation styles handled by nav_modern.php */
            font-weight: 500;
        }
        
        .nav-link:hover {
            color: #007bff !important;
        }
        
        .nav-link.active {
            color: #007bff !important;
            font-weight: 600;
        }
        
        .section-title {
            color: white;
            font-weight: 600;
            margin-bottom: 1.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .fade-in {
            animation: fadeIn 0.8s ease-in;
        }
        
        .slide-in {
            animation: slideIn 0.8s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        /* Ensure content is visible and scrollable */
        html, body {
            overflow-x: hidden;
            overflow-y: auto;
            margin: 0;
            padding: 0;
        }
        
        /* Ensure navbar is always visible */
        .navbar {
            width: 100%;
            top: 0;
            left: 0;
            right: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        /* Fix for mobile devices */
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem 0;
            }
            
            .welcome-card {
                padding: 1.5rem;
                margin-bottom: 1.5rem;
            }
            
            .stat-card, .quick-action, .event-card {
                margin-bottom: 1rem;
            }
            
            .section-title {
                font-size: 1.5rem;
                margin-bottom: 1rem;
            }
            
            .navbar {
                padding: 0.5rem 1rem;
            }
            
            .navbar-brand {
                font-size: 1.1rem;
            }
        }
        
        /* Section title styling */
        .section-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 1.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Modern Navigation -->
    <?php include 'components/nav_modern.php'; ?>

    <!-- Main Content -->
    <div class="dashboard-container">
        <div class="container">
            <!-- Welcome Section -->
            <div class="welcome-card fade-in">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="display-5 fw-bold text-primary mb-3">
                            Welcome back, <?php echo htmlspecialchars($user_info['first_name'] ?? 'User'); ?>!
                        </h1>
                        <p class="lead text-muted">
                            Connect with your alumni community and stay updated with the latest events and opportunities.
                        </p>
                    </div>
                    <div class="col-md-4 text-center">
                        <?php include 'components/alumni_logo.php'; ?>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-5">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="stat-card fade-in">
                        <div class="stat-number text-primary"><?php echo $stats['alumni']; ?></div>
                        <div class="stat-label">Active Alumni</div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="stat-card fade-in" style="animation-delay: 0.1s;">
                        <div class="stat-number text-success"><?php echo $stats['events']; ?></div>
                        <div class="stat-label">Upcoming Events</div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="stat-card fade-in" style="animation-delay: 0.2s;">
                        <div class="stat-number text-warning"><?php echo $stats['news']; ?></div>
                        <div class="stat-label">Latest News</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-5">
                <div class="col-12">
                    <h3 class="section-title">Quick Actions</h3>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <a href="view_events.php" class="quick-action slide-in">
                        <i class="bi bi-calendar-event text-primary"></i>
                        <h5>Browse Events</h5>
                        <p class="text-muted mb-0">Discover upcoming events and activities</p>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <a href="view_alumni.php" class="quick-action slide-in" style="animation-delay: 0.1s;">
                        <i class="bi bi-people text-success"></i>
                        <h5>Alumni Directory</h5>
                        <p class="text-muted mb-0">Connect with fellow alumni</p>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <a href="chat.php" class="quick-action slide-in" style="animation-delay: 0.2s;">
                        <i class="bi bi-chat-dots text-info"></i>
                        <h5>Messages</h5>
                        <p class="text-muted mb-0">Chat with other alumni</p>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <a href="notifications_center.php" class="quick-action slide-in" style="animation-delay: 0.3s;">
                        <i class="bi bi-bell text-danger"></i>
                        <h5>Notifications</h5>
                        <p class="text-muted mb-0">View messages and call requests</p>
                        <?php
                        // Show notification count in quick action
                        if (isset($_SESSION['logged_account']['email']) && isset($conn) && $conn->ping()) {
                            $current_user = $_SESSION['logged_account']['email'];
                            try {
                                $total_notifications = 0;
                                
                                // Regular notifications
                                $notif_check = $conn->query("SHOW TABLES LIKE 'notifications_table'");
                                if ($notif_check && $notif_check->num_rows > 0) {
                                    $notif_result = $conn->query("SELECT COUNT(*) as count FROM notifications_table WHERE user_email = '$current_user' AND is_read = FALSE");
                                    if ($notif_result) {
                                        $row = $notif_result->fetch_assoc();
                                        $total_notifications += $row['count'] ?? 0;
                                    }
                                }
                                
                                // Video calls (only recent ones within 10 minutes)
                                $video_result = $conn->query("SELECT COUNT(*) as count FROM video_call_table WHERE callee_email = '$current_user' AND call_status = 'pending' AND created_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)");
                                if ($video_result) {
                                    $row = $video_result->fetch_assoc();
                                    $total_notifications += $row['count'] ?? 0;
                                }
                                
                                // Unread messages
                                $msg_result = $conn->query("SELECT COUNT(*) as count FROM chat_messages_table cm JOIN chat_conversations_table cc ON cm.conversation_id = cc.id WHERE (cc.user1_email = '$current_user' OR cc.user2_email = '$current_user') AND cm.sender_email != '$current_user' AND cm.is_read = FALSE");
                                if ($msg_result) {
                                    $row = $msg_result->fetch_assoc();
                                    $total_notifications += $row['count'] ?? 0;
                                }
                                
                                if ($total_notifications > 0) {
                                    echo '<span class="position-absolute top-0 end-0 translate-middle badge rounded-pill bg-danger" style="font-size: 0.7rem;">' . $total_notifications . '</span>';
                                }
                            } catch (Exception $e) {
                                // Silently handle database errors
                                error_log("Notification count error: " . $e->getMessage());
                            }
                        }
                        ?>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <a href="update_profile.php" class="quick-action slide-in" style="animation-delay: 0.4s;">
                        <i class="bi bi-person-gear text-warning"></i>
                        <h5>Edit My Profile</h5>
                        <p class="text-muted mb-0">Manage your profile information</p>
                    </a>
                </div>
            </div>

            <!-- Recent Events -->
            <?php if (!empty($recent_events)): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <h3 class="section-title">Upcoming Events</h3>
                </div>
                <?php foreach ($recent_events as $index => $event): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="event-card slide-in" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                        <h5 class="text-primary"><?php echo htmlspecialchars($event['title']); ?></h5>
                        <p class="text-muted mb-2">
                            <i class="bi bi-calendar"></i>
                            <?php echo date('M j, Y', strtotime($event['event_date'])); ?>
                        </p>
                        <?php if (!empty($event['location'])): ?>
                        <p class="text-muted mb-2">
                            <i class="bi bi-geo-alt"></i>
                            <?php echo htmlspecialchars($event['location']); ?>
                        </p>
                        <?php endif; ?>
                        <p class="mb-3"><?php echo htmlspecialchars(substr($event['description'], 0, 100)) . '...'; ?></p>
                        <a href="view_events.php" class="btn btn-primary btn-sm">
                            <i class="bi bi-arrow-right"></i> View Details
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Animate numbers on load
        document.addEventListener('DOMContentLoaded', function() {
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
            
            // Update notification badge dynamically
            updateNotificationBadge();
            setInterval(updateNotificationBadge, 30000); // Update every 30 seconds
        });
        
        function updateNotificationBadge() {
            fetch('get_unified_notifications.php')
                .then(response => response.json())
                .then(data => {
                    const notificationCard = document.querySelector('a[href="notifications_center.php"]');
                    if (notificationCard) {
                        // Remove existing badge
                        const existingBadge = notificationCard.querySelector('.badge');
                        if (existingBadge) {
                            existingBadge.remove();
                        }
                        
                        // Add new badge if there are notifications
                        if (data.total_count > 0) {
                            const badge = document.createElement('span');
                            badge.className = 'position-absolute top-0 end-0 translate-middle badge rounded-pill bg-danger';
                            badge.style.fontSize = '0.7rem';
                            badge.textContent = data.total_count;
                            notificationCard.appendChild(badge);
                        }
                    }
                })
                .catch(error => console.error('Error updating notification badge:', error));
        }
    </script>
</body>
</html>
<?php
// Close database connection at the very end
if (isset($conn)) {
    $conn->close();
}
?>