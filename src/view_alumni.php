<?php
// Start output buffering to prevent header issues
ob_start();

// Start session and include files BEFORE any HTML output
session_start();
include 'db_controller.php';
$conn->select_db("Alumni");

// Check if user is logged in
if (!isset($_SESSION['logged_account']) || empty($_SESSION['logged_account'])) {
    // Clear all session data
    $_SESSION = array();
    
    // Destroy the session
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    
    // Start new session for flash message
    session_start();
    $_SESSION['flash_mode'] = "alert-warning";
    $_SESSION['flash'] = "You must log in to access this page.";
    header('Location: login.php');
    exit();
}

// Get current user info
$currentUser = $_SESSION['logged_account'];
$isAdmin = ($currentUser['type'] === 'admin');

// Build search filter
$filterSearch = "";
if (isset($_GET['search']) && $_GET['search'] != "") {
    $trimSearch = strtolower(trim($_GET['search']));
    $filterSearch = "AND (
        LOWER(u.first_name) LIKE '%$trimSearch%' OR 
        LOWER(u.last_name) LIKE '%$trimSearch%' OR 
        LOWER(u.email) LIKE '%$trimSearch%' OR 
        LOWER(u.college_name) LIKE '%$trimSearch%' OR 
        LOWER(u.company) LIKE '%$trimSearch%'
    )";
}

// Query the database for alumni (exclude current user and admins)
$query = "SELECT u.*, a.status, a.type 
          FROM user_table u 
          JOIN account_table a ON u.email = a.email 
          WHERE a.status = 'Approved' 
          AND a.type != 'admin' 
          AND u.email != '{$currentUser['email']}' 
          $filterSearch 
          ORDER BY u.first_name ASC, u.last_name ASC";
$allAlumni = $conn->query($query);

// Get statistics
$totalAlumni = $conn->query("SELECT COUNT(*) as count FROM user_table u JOIN account_table a ON u.email = a.email WHERE a.status = 'Approved' AND a.type != 'admin'")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Network | Alumni Portal</title>

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
        
        .alumni-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .alumni-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .profile-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .alumni-card:hover .profile-img {
            border-color: #f59e0b;
        }
        
        .search-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .form-control {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 16px;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
        }
        
        .form-control:focus {
            background: white;
            border-color: #f59e0b;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1);
            outline: none;
            transform: translateY(-1px);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 24px;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(245, 158, 11, 0.4);
        }
        
        .btn-outline-primary {
            border: 2px solid #f59e0b;
            color: #f59e0b;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .btn-outline-primary:hover {
            background: #f59e0b;
            border-color: #f59e0b;
            color: white;
            transform: translateY(-1px);
        }
        
        .btn-outline-secondary {
            border: 2px solid #6b7280;
            color: #6b7280;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .btn-outline-secondary:hover {
            background: #6b7280;
            border-color: #6b7280;
            color: white;
            transform: translateY(-1px);
        }
        
        /* Video Call Button Styling */
        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            border: none !important;
            color: white !important;
            font-weight: 600 !important;
            transition: all 0.3s ease !important;
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3) !important;
        }
        
        /* Statistics Cards - Match Dashboard Style */
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
        
        .section-title {
            color: white;
            font-weight: 600;
            margin-bottom: 1.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            z-index: 1000;
            margin-bottom: 0;
        }
        
        .navbar-brand {
            font-weight: 700;
            color: #333 !important;
        }
        
        .nav-link {
            color: #333 !important;
            font-weight: 500;
        }
        
        .nav-link:hover {
            color: #007bff !important;
        }
        
        .nav-link.active {
            color: #007bff !important;
            font-weight: 600;
        }
        
        .breadcrumb {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
        }
        
        .breadcrumb-link {
            color: #3b82f6 !important;
            font-weight: 500;
        }
        
        .breadcrumb-active {
            color: #1f2937 !important;
            font-weight: 600;
        }
        
        h1 {
            color: #1f2937;
            font-weight: 800;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #1f2937 0%, #f59e0b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .page-subtitle {
            color: #6b7280;
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 2rem;
        }
        
        /* Quick Action Buttons */
        .quick-action-btn {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            min-height: 150px;
        }
        
        .quick-action-btn:hover {
            border-color: #3b82f6;
            background: #f8fafc;
            transform: translateY(-2px);
            color: inherit;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .text-purple {
            color: #8b5cf6 !important;
        }
        
        /* Animations */
        .animate-fade-in-up {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }
        
        .slide-left {
            animation: slideInLeft 0.3s ease-out;
        }
        
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        /* Responsive improvements */
        @media (max-width: 768px) {
            h1 {
                font-size: 1.75rem;
            }
            
            .admin-header {
                padding: 1.5rem 0;
            }
            
            .alumni-card {
                padding: 1rem;
            }
        }
    </style>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand fw-bold" href="main_menu.php">
                <i class="bi bi-mortarboard text-primary"></i>
                Alumni Portal
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="main_menu.php">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_events.php">
                            <i class="bi bi-calendar-event"></i> Events
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="view_alumni.php">
                            <i class="bi bi-people"></i> Alumni
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="chat.php">
                            <i class="bi bi-chat-dots"></i> Messages
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($currentUser['email']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile_detail.php"><i class="bi bi-person"></i> My Profile</a></li>
                            <li><a class="dropdown-item" href="feedback_form.php"><i class="bi bi-chat-square-text"></i> Feedback</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="dashboard-container">
        <div class="container">
            <!-- Welcome Section -->
            <div class="welcome-card fade-in">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="display-5 fw-bold text-primary mb-3">Alumni Network</h1>
                        <p class="lead text-muted">
                            Connect with fellow alumni, expand your network, and discover new opportunities.
                        </p>
                    </div>
                    <div class="col-md-4 text-center">
                        <?php include 'components/alumni_logo.php'; ?>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-5">
                <?php
                // Get additional statistics with error handling
                try {
                    // Count alumni with recent activity (using a simpler approach)
                    $recent_alumni_result = $conn->query("SELECT COUNT(*) as count FROM user_table u JOIN account_table a ON u.email = a.email WHERE a.status = 'Approved' AND a.type != 'admin'");
                    $recent_alumni = $recent_alumni_result ? $recent_alumni_result->fetch_assoc()['count'] : 0;
                    
                    // Count alumni from current year
                    $current_year = date('Y');
                    $this_year_result = $conn->query("SELECT COUNT(*) as count FROM user_table u JOIN account_table a ON u.email = a.email WHERE a.status = 'Approved' AND a.type != 'admin' AND u.batch_year = $current_year");
                    $this_year = $this_year_result ? $this_year_result->fetch_assoc()['count'] : 0;
                    
                    // Count unique companies
                    $companies_result = $conn->query("SELECT COUNT(DISTINCT u.company) as count FROM user_table u JOIN account_table a ON u.email = a.email WHERE a.status = 'Approved' AND a.type != 'admin' AND u.company IS NOT NULL AND u.company != ''");
                    $companies = $companies_result ? $companies_result->fetch_assoc()['count'] : 0;
                } catch (Exception $e) {
                    $recent_alumni = 0;
                    $this_year = 0;
                    $companies = 0;
                }
                ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card fade-in">
                        <div class="stat-number text-primary"><?php echo number_format($totalAlumni); ?></div>
                        <div class="stat-label">Total Alumni</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card fade-in" style="animation-delay: 0.1s;">
                        <div class="stat-number text-success"><?php echo number_format($recent_alumni); ?></div>
                        <div class="stat-label">Active Members</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card fade-in" style="animation-delay: 0.2s;">
                        <div class="stat-number text-warning"><?php echo number_format($this_year); ?></div>
                        <div class="stat-label">Recent Graduates</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card fade-in" style="animation-delay: 0.3s;">
                        <div class="stat-number text-info"><?php echo number_format($companies); ?></div>
                        <div class="stat-label">Companies</div>
                    </div>
                </div>
            </div>

    <!-- Quick Actions -->
    <section class="py-3">
        <div class="container">
            <h5 class="mb-3">Quick Actions</h5>
            <div class="row g-3">
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <a href="#" onclick="filterByBatch()" class="quick-action-btn">
                        <i class="bi bi-mortarboard text-primary" style="font-size: 2rem;"></i>
                        <h6 class="mt-3 mb-1">By Batch</h6>
                        <small class="text-muted">Filter by year</small>
                    </a>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <a href="#" onclick="filterByCompany()" class="quick-action-btn">
                        <i class="bi bi-building text-success" style="font-size: 2rem;"></i>
                        <h6 class="mt-3 mb-1">By Company</h6>
                        <small class="text-muted">Filter by employer</small>
                    </a>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <a href="#" onclick="filterByLocation()" class="quick-action-btn">
                        <i class="bi bi-geo-alt text-warning" style="font-size: 2rem;"></i>
                        <h6 class="mt-3 mb-1">By Location</h6>
                        <small class="text-muted">Filter by city</small>
                    </a>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <a href="#" onclick="exportData()" class="quick-action-btn">
                        <i class="bi bi-download text-info" style="font-size: 2rem;"></i>
                        <h6 class="mt-3 mb-1">Export Data</h6>
                        <small class="text-muted">Download CSV</small>
                    </a>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <a href="chat.php" class="quick-action-btn">
                        <i class="bi bi-chat-dots text-purple" style="font-size: 2rem;"></i>
                        <h6 class="mt-3 mb-1">Messages</h6>
                        <small class="text-muted">View chats</small>
                    </a>
                </div>
                <?php if ($isAdmin): ?>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <a href="manage_video_calls.php" class="quick-action-btn">
                        <i class="bi bi-camera-video text-success" style="font-size: 2rem;"></i>
                        <h6 class="mt-3 mb-1">Video Calls</h6>
                        <small class="text-muted">Admin only</small>
                    </a>
                </div>
                <?php endif; ?>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <a href="<?php echo $isAdmin ? 'main_menu_admin.php' : 'main_menu.php'; ?>" class="quick-action-btn">
                        <i class="bi bi-house text-secondary" style="font-size: 2rem;"></i>
                        <h6 class="mt-3 mb-1">Dashboard</h6>
                        <small class="text-muted">Back to home</small>
                    </a>
                </div>
            </div>
        </div>
    </section>

            <!-- Search Section -->
            <div class="row mb-5">
                <div class="col-12">
                    <h3 class="section-title">Search Alumni</h3>
                </div>
                <div class="col-12">
                    <div class="search-card">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="GET">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-9">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-search text-muted"></i>
                                        </span>
                                        <input type="text" class="form-control border-start-0" 
                                               placeholder="Search alumni by name, course, or company..." 
                                               name="search" 
                                               value="<?php echo isset($_GET['search']) ? htmlspecialchars(trim($_GET['search'])) : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary flex-fill">
                                            <i class="bi bi-search me-1"></i> Search
                                        </button>
                                        <?php if (isset($_GET['search']) && $_GET['search'] != "") { ?>
                                            <a href="view_alumni.php" class="btn btn-outline-secondary">
                                                <i class="bi bi-x-lg"></i>
                                            </a>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <!-- Debug Information -->
        <?php if (isset($_GET['debug'])): ?>
        <div class="alert alert-info">
            <h6>Debug Information:</h6>
            <p><strong>Current User:</strong> <?php echo htmlspecialchars($currentUser['email']); ?></p>
            <p><strong>User Type:</strong> <?php echo htmlspecialchars($currentUser['type']); ?></p>
            <p><strong>Query:</strong> <?php echo htmlspecialchars($query); ?></p>
            <p><strong>Total Alumni Count:</strong> <?php echo $totalAlumni; ?></p>
            <p><strong>Query Result:</strong> <?php echo $allAlumni ? $allAlumni->num_rows . ' rows' : 'NULL'; ?></p>
            <?php if ($allAlumni && $allAlumni->num_rows > 0): ?>
                <p><strong>Sample Alumni:</strong></p>
                <?php 
                $allAlumni->data_seek(0);
                $sample = $allAlumni->fetch_assoc();
                if ($sample) {
                    echo '<pre>' . print_r($sample, true) . '</pre>';
                    $allAlumni->data_seek(0); // Reset pointer
                }
                ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>

            <!-- Alumni Directory -->
            <div class="row mb-3">
                <div class="col-12">
                    <h3 class="section-title">Alumni Directory</h3>
                </div>
            </div>
            
            <!-- Alumni Grid -->
            <div class="row g-4">
            <?php if ($allAlumni && $allAlumni->num_rows > 0) { ?>
                <?php while ($alumni = $allAlumni->fetch_assoc()) { ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="alumni-card">
                            <div class="d-flex align-items-start mb-3">
                                <img src="<?php echo !empty($alumni['profile_image']) ? 'profile_images/' . $alumni['profile_image'] : 'profile_images/default-male-user-profile-icon.jpg'; ?>" 
                                     alt="Profile" class="profile-img me-3" 
                                     onerror="this.src='profile_images/default-male-user-profile-icon.jpg'">
                                <div class="flex-grow-1">
                                    <h5 class="mb-1 fw-bold"><?php echo htmlspecialchars($alumni['first_name'] . ' ' . $alumni['last_name']); ?></h5>
                                    <p class="text-muted mb-1">
                                        <i class="bi bi-mortarboard me-1"></i>
                                        <?php echo htmlspecialchars($alumni['college_name'] ?? 'College not specified'); ?>
                                    </p>
                                    <?php if (!empty($alumni['company'])) { ?>
                                        <p class="text-muted mb-1">
                                            <i class="bi bi-building me-1"></i>
                                            <?php echo htmlspecialchars($alumni['company']); ?>
                                        </p>
                                    <?php } ?>
                                    <?php if (!empty($alumni['job_position'])) { ?>
                                        <p class="text-muted mb-0">
                                            <i class="bi bi-briefcase me-1"></i>
                                            <?php echo htmlspecialchars($alumni['job_position']); ?>
                                        </p>
                                    <?php } ?>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    <i class="bi bi-calendar me-1"></i>
                                    Batch <?php echo $alumni['batch_year'] ?? 'N/A'; ?>
                                    <?php if (!empty($alumni['hometown'])) { ?>
                                        <br><i class="bi bi-geo-alt me-1"></i>
                                        <?php echo htmlspecialchars($alumni['hometown']); ?>
                                    <?php } ?>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <a href="profile_detail.php?email=<?php echo urlencode($alumni['email']); ?>" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-person me-1"></i> View Profile
                                    </a>
                                    <a href="chat.php?user=<?php echo urlencode($alumni['email']); ?>" 
                                       class="btn btn-primary btn-sm">
                                        <i class="bi bi-chat-dots me-1"></i> Message
                                    </a>
                                    <?php if ($isAdmin): ?>
                                    <button onclick="startVideoCall('<?php echo htmlspecialchars($alumni['email']); ?>', '<?php echo htmlspecialchars($alumni['first_name'] . ' ' . $alumni['last_name']); ?>')" 
                                            class="btn btn-success btn-sm" title="Start Video Call (Admin Only)" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; font-weight: 600;">
                                        <i class="bi bi-camera-video me-1"></i> Call
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="bi bi-people text-muted" style="font-size: 4rem;"></i>
                        <h4 class="mt-3 text-muted">No Alumni Found</h4>
                        <p class="text-muted">
                            <?php if (isset($_GET['search']) && $_GET['search'] != "") { ?>
                                No alumni match your search criteria. Try adjusting your search terms.
                            <?php } else { ?>
                                There are no approved alumni members to display at this time.
                            <?php } ?>
                        </p>
                        <p class="text-muted small">
                            <a href="view_alumni.php?debug=1" class="text-decoration-none">
                                <i class="bi bi-bug me-1"></i>Show debug info
                            </a>
                        </p>
                        <?php if (isset($_GET['search']) && $_GET['search'] != "") { ?>
                            <a href="view_alumni.php" class="btn btn-primary">
                                <i class="bi bi-arrow-left me-1"></i> View All Alumni
                            </a>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Animate cards on page load
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.animate-fade-in-up');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
        
        // Quick action functions
        function filterByBatch() {
            const year = prompt('Enter batch year (e.g., 2020):');
            if (year) {
                const searchInput = document.querySelector('input[name="search"]');
                searchInput.value = year;
                searchInput.form.submit();
            }
        }
        
        function filterByCompany() {
            const company = prompt('Enter company name:');
            if (company) {
                const searchInput = document.querySelector('input[name="search"]');
                searchInput.value = company;
                searchInput.form.submit();
            }
        }
        
        function filterByLocation() {
            const location = prompt('Enter location/hometown:');
            if (location) {
                const searchInput = document.querySelector('input[name="search"]');
                searchInput.value = location;
                searchInput.form.submit();
            }
        }
        
        function exportData() {
            // Create a simple CSV export
            const alumni = <?php 
                $alumniData = [];
                if ($allAlumni && $allAlumni->num_rows > 0) {
                    $allAlumni->data_seek(0); // Reset result pointer
                    while ($row = $allAlumni->fetch_assoc()) {
                        $alumniData[] = [
                            'name' => $row['first_name'] . ' ' . $row['last_name'],
                            'email' => $row['email'],
                            'college' => $row['college_name'],
                            'batch_year' => $row['batch_year'],
                            'company' => $row['company'] ?? '',
                            'job_position' => $row['job_position'] ?? '',
                            'hometown' => $row['hometown'] ?? ''
                        ];
                    }
                }
                echo json_encode($alumniData);
            ?>;
            
            if (alumni.length === 0) {
                alert('No data to export');
                return;
            }
            
            let csv = 'Name,Email,College,Batch Year,Company,Job Position,Hometown\n';
            alumni.forEach(row => {
                csv += `"${row.name}","${row.email}","${row.college}","${row.batch_year}","${row.company}","${row.job_position}","${row.hometown}"\n`;
            });
            
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'alumni_network_' + new Date().toISOString().split('T')[0] + '.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }
        
        function refreshPage() {
            window.location.reload();
        }
        
        // Video call functionality
        function startVideoCall(userEmail, userName) {
            if (confirm(`Start a video call with ${userName}?`)) {
                // Create a new video call record
                fetch('create_video_call.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        callee_email: userEmail
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Redirect to video call page
                        window.open(`video_call.php?room=${data.room_id}&call_id=${data.call_id}`, '_blank');
                    } else {
                        alert('Failed to start video call: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Network error occurred while starting the call.');
                });
            }
        }
    </script>

    <!-- Video Call Modal -->
    <div class="modal fade" id="videoCallModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
                <div class="modal-header" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border-radius: 16px 16px 0 0;">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-camera-video me-2"></i>
                        Start Video Call
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center">
                        <div class="bg-success bg-opacity-10 p-4 rounded-circle d-inline-block mb-3">
                            <i class="bi bi-camera-video text-success" style="font-size: 3rem;"></i>
                        </div>
                        <h4 id="callUserName">Alumni Name</h4>
                        <p class="text-muted">You are about to start a video call. Make sure your camera and microphone are ready.</p>
                        
                        <div class="d-flex justify-content-center gap-3 mt-4">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-lg me-1"></i> Cancel
                            </button>
                            <button type="button" class="btn btn-success" id="confirmCallBtn">
                                <i class="bi bi-camera-video me-1"></i> Start Call
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>