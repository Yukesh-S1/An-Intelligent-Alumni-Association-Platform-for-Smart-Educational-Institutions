<?php
session_start();
include 'logged_user.php';
include 'db_controller.php';

$conn->select_db("Alumni");
$current_user = $_SESSION['logged_account']['email'];

// Get dashboard statistics
$stats = [];

// Total alumni count
$alumni_result = $conn->query("SELECT COUNT(*) as count FROM user_table u JOIN account_table a ON u.email = a.email WHERE a.type = 'user' AND a.status = 'Approved'");
$stats['alumni'] = $alumni_result->fetch_assoc()['count'];

// Upcoming events count
$events_result = $conn->query("SELECT COUNT(*) as count FROM event_table WHERE event_date >= CURDATE()");
$stats['events'] = $events_result->fetch_assoc()['count'];

// Active opportunities count
$opportunities_result = $conn->query("SELECT COUNT(*) as count FROM advertisement_table WHERE status = 'Active'");
$stats['opportunities'] = $opportunities_result->fetch_assoc()['count'];

// Unread messages count
$messages_result = $conn->query("SELECT COUNT(*) as count FROM chat_messages_table cm 
                                JOIN chat_conversations_table cc ON cm.conversation_id = cc.id 
                                WHERE (cc.user1_email = '$current_user' OR cc.user2_email = '$current_user') 
                                AND cm.sender_email != '$current_user' AND cm.is_read = FALSE");
$stats['messages'] = $messages_result->fetch_assoc()['count'];

// Get recent activities
$recent_events = $conn->query("SELECT * FROM event_table WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 3");
$recent_opportunities = $conn->query("SELECT * FROM advertisement_table WHERE status = 'Active' ORDER BY date_added DESC LIMIT 3");
$recent_alumni = $conn->query("SELECT u.*, a.status FROM user_table u JOIN account_table a ON u.email = a.email WHERE a.type = 'user' AND a.status = 'Approved' AND u.email != '$current_user' ORDER BY u.email DESC LIMIT 6");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Alumni Portal</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Modern Design System -->

    <style>
        .hero-section {
            background: var(--gradient-hero);
            color: white;
            padding: 4rem 0;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .quick-actions {
            margin-top: -3rem;
            position: relative;
            z-index: 2;
        }
        
        .action-card {
            background: white;
            border-radius: var(--radius-xl);
            padding: 2rem;
            text-align: center;
            box-shadow: var(--shadow-xl);
            transition: all var(--transition-normal);
            border: none;
            text-decoration: none;
            color: inherit;
            height: 100%;
        }
        
        .action-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
            color: inherit;
        }
        
        .action-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: white;
        }
        
        .activity-item {
            padding: 1rem;
            border-radius: var(--radius-lg);
            border: 1px solid var(--secondary-200);
            transition: all var(--transition-fast);
        }
        
        .activity-item:hover {
            background: var(--secondary-50);
            border-color: var(--primary-300);
        }
        
        .alumni-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary-200);
        }
    </style>

    <?php include 'components/nav_modern.php'; ?>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content text-center">
                <h1 class="display-4 fw-bold mb-3 animate-fade-in-up">
                    Welcome back, <?php echo htmlspecialchars($_SESSION['logged_account']['first_name']); ?>! 👋
                </h1>
                <p class="lead mb-4 animate-fade-in-up" style="animation-delay: 0.2s;">
                    Stay connected with your alumni network and discover new opportunities
                </p>
                <div class="d-flex justify-content-center gap-3 animate-fade-in-up" style="animation-delay: 0.4s;">
                    <a href="view_alumni.php" class="btn btn-light btn-lg">
                        <i class="bi bi-people me-2"></i>
                        Explore Network
                    </a>
                    <a href="messages.php" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-chat-dots me-2"></i>
                        Start Chatting
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Actions -->
    <section class="quick-actions">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-3 col-sm-6">
                    <a href="view_alumni.php" class="action-card d-block animate-fade-in-up">
                        <div class="action-icon" style="background: var(--gradient-primary);">
                            <i class="bi bi-people"></i>
                        </div>
                        <h5 class="fw-semibold mb-2">Alumni Network</h5>
                        <p class="text-muted mb-3">Connect with <?php echo number_format($stats['alumni']); ?> alumni</p>
                        <span class="badge badge-info"><?php echo $stats['alumni']; ?> Members</span>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6">
                    <a href="view_events.php" class="action-card d-block animate-fade-in-up" style="animation-delay: 0.1s;">
                        <div class="action-icon" style="background: var(--accent-green);">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                        <h5 class="fw-semibold mb-2">Upcoming Events</h5>
                        <p class="text-muted mb-3">Join exciting events</p>
                        <span class="badge badge-success"><?php echo $stats['events']; ?> Events</span>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6">
                    <a href="view_advertisements.php" class="action-card d-block animate-fade-in-up" style="animation-delay: 0.2s;">
                        <div class="action-icon" style="background: var(--accent-orange);">
                            <i class="bi bi-briefcase"></i>
                        </div>
                        <h5 class="fw-semibold mb-2">Opportunities</h5>
                        <p class="text-muted mb-3">Discover career opportunities</p>
                        <span class="badge badge-warning"><?php echo $stats['opportunities']; ?> Active</span>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6">
                    <a href="messages.php" class="action-card d-block animate-fade-in-up" style="animation-delay: 0.3s;">
                        <div class="action-icon" style="background: var(--accent-purple);">
                            <i class="bi bi-chat-dots"></i>
                        </div>
                        <h5 class="fw-semibold mb-2">Messages</h5>
                        <p class="text-muted mb-3">Stay in touch</p>
                        <?php if ($stats['messages'] > 0): ?>
                            <span class="badge badge-danger"><?php echo $stats['messages']; ?> Unread</span>
                        <?php else: ?>
                            <span class="badge badge-info">All Caught Up</span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <!-- Recent Events -->
                <div class="col-lg-4">
                    <div class="card-modern h-100 animate-slide-in-right">
                        <div class="card-header-modern">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="bi bi-calendar-event text-primary me-2"></i>
                                    Upcoming Events
                                </h5>
                                <a href="view_events.php" class="btn btn-sm btn-ghost">View All</a>
                            </div>
                        </div>
                        <div class="card-body-modern">
                            <?php if ($recent_events->num_rows > 0): ?>
                                <div class="d-flex flex-column gap-3">
                                    <?php while ($event = $recent_events->fetch_assoc()): ?>
                                        <div class="activity-item">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($event['title']); ?></h6>
                                                    <p class="text-muted small mb-1">
                                                        <i class="bi bi-geo-alt me-1"></i>
                                                        <?php echo htmlspecialchars($event['location']); ?>
                                                    </p>
                                                    <p class="text-muted small mb-0">
                                                        <i class="bi bi-calendar me-1"></i>
                                                        <?php echo date('M j, Y', strtotime($event['event_date'])); ?>
                                                    </p>
                                                </div>
                                                <span class="badge badge-<?php echo ($event['type'] === 'Event') ? 'success' : 'info'; ?>">
                                                    <?php echo $event['type']; ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-calendar-x" style="font-size: 2rem;"></i>
                                    <p class="mt-2 mb-0">No upcoming events</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Opportunities -->
                <div class="col-lg-4">
                    <div class="card-modern h-100 animate-slide-in-right" style="animation-delay: 0.1s;">
                        <div class="card-header-modern">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="bi bi-briefcase text-warning me-2"></i>
                                    Latest Opportunities
                                </h5>
                                <a href="view_advertisements.php" class="btn btn-sm btn-ghost">View All</a>
                            </div>
                        </div>
                        <div class="card-body-modern">
                            <?php if ($recent_opportunities->num_rows > 0): ?>
                                <div class="d-flex flex-column gap-3">
                                    <?php while ($opportunity = $recent_opportunities->fetch_assoc()): ?>
                                        <div class="activity-item">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($opportunity['title']); ?></h6>
                                                    <p class="text-muted small mb-1">
                                                        <i class="bi bi-tag me-1"></i>
                                                        <?php echo htmlspecialchars($opportunity['category']); ?>
                                                    </p>
                                                    <p class="text-muted small mb-0">
                                                        <i class="bi bi-clock me-1"></i>
                                                        <?php echo date('M j, Y', strtotime($opportunity['date_added'])); ?>
                                                    </p>
                                                </div>
                                                <span class="badge badge-success">Active</span>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-briefcase" style="font-size: 2rem;"></i>
                                    <p class="mt-2 mb-0">No opportunities available</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Alumni -->
                <div class="col-lg-4">
                    <div class="card-modern h-100 animate-slide-in-right" style="animation-delay: 0.2s;">
                        <div class="card-header-modern">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="bi bi-people text-success me-2"></i>
                                    Alumni Network
                                </h5>
                                <a href="view_alumni.php" class="btn btn-sm btn-ghost">View All</a>
                            </div>
                        </div>
                        <div class="card-body-modern">
                            <?php if ($recent_alumni->num_rows > 0): ?>
                                <div class="row g-3">
                                    <?php while ($alumni = $recent_alumni->fetch_assoc()): ?>
                                        <div class="col-6">
                                            <a href="profile_detail.php?email=<?php echo urlencode($alumni['email']); ?>" 
                                               class="text-decoration-none">
                                                <div class="text-center p-2 rounded hover-lift">
                                                    <img src="<?php echo $alumni['profile_image'] ?: 'profile_images/default-male-user-profile-icon.jpg'; ?>" 
                                                         alt="<?php echo htmlspecialchars($alumni['first_name']); ?>" 
                                                         class="alumni-avatar mb-2">
                                                    <h6 class="mb-1 small"><?php echo htmlspecialchars($alumni['first_name'] . ' ' . $alumni['last_name']); ?></h6>
                                                    <p class="text-muted small mb-0"><?php echo htmlspecialchars($alumni['job_position'] ?: 'Alumni'); ?></p>
                                                </div>
                                            </a>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-people" style="font-size: 2rem;"></i>
                                    <p class="mt-2 mb-0">No alumni found</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white border-top py-4 mt-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">
                        &copy; <?php echo date('Y'); ?> Alumni Engaging Portal. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="d-flex justify-content-md-end gap-3">
                        <a href="#" class="text-muted text-decoration-none">Privacy Policy</a>
                        <a href="#" class="text-muted text-decoration-none">Terms of Service</a>
                        <a href="#" class="text-muted text-decoration-none">Support</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Add smooth animations on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe all animated elements
        document.querySelectorAll('.animate-fade-in-up, .animate-slide-in-right').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            observer.observe(el);
        });

        // Add loading states to action cards
        document.querySelectorAll('.action-card').forEach(card => {
            card.addEventListener('click', function(e) {
                const icon = this.querySelector('.action-icon i');
                icon.className = 'bi bi-arrow-clockwise';
                icon.style.animation = 'spin 1s linear infinite';
            });
        });

        // CSS for spin animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>