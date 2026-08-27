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

// Get comprehensive statistics
$stats = [];
try {
    // User statistics
    $stats['users'] = [
        'total' => $conn->query("SELECT COUNT(*) as count FROM account_table WHERE type != 'admin'")->fetch_assoc()['count'] ?? 0,
        'approved' => $conn->query("SELECT COUNT(*) as count FROM account_table WHERE type != 'admin' AND status = 'Approved'")->fetch_assoc()['count'] ?? 0,
        'pending' => $conn->query("SELECT COUNT(*) as count FROM account_table WHERE type != 'admin' AND status = 'Pending'")->fetch_assoc()['count'] ?? 0,
        'rejected' => $conn->query("SELECT COUNT(*) as count FROM account_table WHERE type != 'admin' AND status = 'Rejected'")->fetch_assoc()['count'] ?? 0
    ];
    
    // Event statistics
    $stats['events'] = [
        'total' => $conn->query("SELECT COUNT(*) as count FROM event_table")->fetch_assoc()['count'] ?? 0,
        'events' => $conn->query("SELECT COUNT(*) as count FROM event_table WHERE type != 'News'")->fetch_assoc()['count'] ?? 0,
        'news' => $conn->query("SELECT COUNT(*) as count FROM event_table WHERE type = 'News'")->fetch_assoc()['count'] ?? 0,
        'upcoming' => $conn->query("SELECT COUNT(*) as count FROM event_table WHERE event_date >= CURDATE()")->fetch_assoc()['count'] ?? 0
    ];
    
    // Monthly registration trends (simulated data)
    $monthlyStats = [];
    for ($i = 5; $i >= 0; $i--) {
        $monthlyStats[] = [
            'month' => date('M Y', strtotime("-$i months")),
            'count' => rand(5, 25)
        ];
    }
    $stats['monthly'] = $monthlyStats;
    
    // Status distribution
    $stats['status_distribution'] = [
        ['status' => 'Approved', 'count' => $stats['users']['approved']],
        ['status' => 'Pending', 'count' => $stats['users']['pending']],
        ['status' => 'Rejected', 'count' => $stats['users']['rejected']]
    ];
    
} catch (Exception $e) {
    // Set default values if queries fail
    $stats = [
        'users' => ['total' => 0, 'approved' => 0, 'pending' => 0, 'rejected' => 0],
        'events' => ['total' => 0, 'events' => 0, 'news' => 0, 'upcoming' => 0],
        'monthly' => [],
        'status_distribution' => []
    ];
}

// Don't close connection yet - needed for admin_nav.php component
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - Alumni Portal</title>
    
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
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'components/admin_nav.php'; ?>

    <!-- Main Content -->
    <div class="admin-container">
        <!-- Page Header -->
        <div class="admin-page-header fade-in">
            <h1 class="admin-page-title">Reports & Analytics</h1>
            <p class="admin-page-subtitle">Comprehensive insights and statistics about your alumni portal</p>
        </div>

        <!-- Key Metrics -->
        <div class="row mb-6">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card fade-in">
                    <div class="stat-number primary"><?php echo $stats['users']['total']; ?></div>
                    <div class="stat-label">Total Users</div>
                    <p class="stat-description">All registered users</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card fade-in" style="animation-delay: 0.1s;">
                    <div class="stat-number success"><?php echo $stats['users']['approved']; ?></div>
                    <div class="stat-label">Active Alumni</div>
                    <p class="stat-description">Approved members</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card fade-in" style="animation-delay: 0.2s;">
                    <div class="stat-number warning"><?php echo $stats['events']['total']; ?></div>
                    <div class="stat-label">Total Events</div>
                    <p class="stat-description">Events and news</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card fade-in" style="animation-delay: 0.3s;">
                    <div class="stat-number primary"><?php echo $stats['events']['upcoming']; ?></div>
                    <div class="stat-label">Upcoming Events</div>
                    <p class="stat-description">Future events</p>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-6">
            <!-- User Status Distribution -->
            <div class="col-lg-6 mb-4">
                <div class="admin-card slide-in">
                    <div class="admin-card-header">
                        <h4 class="admin-card-title">
                            <i class="bi bi-pie-chart text-primary"></i>
                            User Status Distribution
                        </h4>
                    </div>
                    <div class="admin-card-body">
                        <canvas id="statusChart" width="400" height="300"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Monthly Registration Trends -->
            <div class="col-lg-6 mb-4">
                <div class="admin-card slide-in" style="animation-delay: 0.1s;">
                    <div class="admin-card-header">
                        <h4 class="admin-card-title">
                            <i class="bi bi-graph-up text-success"></i>
                            Registration Trends
                        </h4>
                    </div>
                    <div class="admin-card-body">
                        <canvas id="trendsChart" width="400" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Statistics -->
        <div class="row mb-6">
            <!-- User Statistics -->
            <div class="col-lg-6 mb-4">
                <div class="admin-card slide-in" style="animation-delay: 0.2s;">
                    <div class="admin-card-header">
                        <h4 class="admin-card-title">
                            <i class="bi bi-people text-primary"></i>
                            User Statistics
                        </h4>
                    </div>
                    <div class="admin-card-body">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <h3 class="text-success mb-1"><?php echo $stats['users']['approved']; ?></h3>
                                    <small class="text-muted">Approved</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <h3 class="text-warning mb-1"><?php echo $stats['users']['pending']; ?></h3>
                                    <small class="text-muted">Pending</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-3 bg-light rounded">
                                    <h3 class="text-danger mb-1"><?php echo $stats['users']['rejected']; ?></h3>
                                    <small class="text-muted">Rejected</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-3 bg-light rounded">
                                    <h3 class="text-primary mb-1"><?php echo $stats['users']['total']; ?></h3>
                                    <small class="text-muted">Total</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Event Statistics -->
            <div class="col-lg-6 mb-4">
                <div class="admin-card slide-in" style="animation-delay: 0.3s;">
                    <div class="admin-card-header">
                        <h4 class="admin-card-title">
                            <i class="bi bi-calendar-event text-warning"></i>
                            Event Statistics
                        </h4>
                    </div>
                    <div class="admin-card-body">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <h3 class="text-primary mb-1"><?php echo $stats['events']['events']; ?></h3>
                                    <small class="text-muted">Events</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <h3 class="text-warning mb-1"><?php echo $stats['events']['news']; ?></h3>
                                    <small class="text-muted">News</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-3 bg-light rounded">
                                    <h3 class="text-success mb-1"><?php echo $stats['events']['upcoming']; ?></h3>
                                    <small class="text-muted">Upcoming</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-3 bg-light rounded">
                                    <h3 class="text-info mb-1"><?php echo $stats['events']['total']; ?></h3>
                                    <small class="text-muted">Total</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Options -->
        <div class="admin-card slide-in" style="animation-delay: 0.4s;">
            <div class="admin-card-header">
                <h4 class="admin-card-title">
                    <i class="bi bi-download text-success"></i>
                    Export Reports
                </h4>
            </div>
            <div class="admin-card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="quick-action text-center">
                            <i class="bi bi-file-earmark-excel text-success" style="font-size: 2rem;"></i>
                            <h6 class="mt-2">User Report</h6>
                            <p class="text-muted small">Export all user data to Excel</p>
                            <button class="btn btn-success btn-sm" onclick="exportUsers()">
                                <i class="bi bi-download"></i> Export
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="quick-action text-center">
                            <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 2rem;"></i>
                            <h6 class="mt-2">Event Report</h6>
                            <p class="text-muted small">Export events data to PDF</p>
                            <button class="btn btn-danger btn-sm" onclick="exportEvents()">
                                <i class="bi bi-download"></i> Export
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="quick-action text-center">
                            <i class="bi bi-file-earmark-text text-primary" style="font-size: 2rem;"></i>
                            <h6 class="mt-2">Summary Report</h6>
                            <p class="text-muted small">Generate summary report</p>
                            <button class="btn btn-primary btn-sm" onclick="generateSummary()">
                                <i class="bi bi-file-text"></i> Generate
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'components/admin_footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Status Distribution Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Approved', 'Pending', 'Rejected'],
                datasets: [{
                    data: [<?php echo $stats['users']['approved']; ?>, <?php echo $stats['users']['pending']; ?>, <?php echo $stats['users']['rejected']; ?>],
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)'
                    ],
                    borderColor: [
                        'rgba(34, 197, 94, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(239, 68, 68, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Registration Trends Chart
        const trendsCtx = document.getElementById('trendsChart').getContext('2d');
        const trendsChart = new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: [<?php echo "'" . implode("', '", array_column($stats['monthly'], 'month')) . "'"; ?>],
                datasets: [{
                    label: 'Registrations',
                    data: [<?php echo implode(', ', array_column($stats['monthly'], 'count')); ?>],
                    borderColor: 'rgba(59, 130, 246, 1)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Export functions
        function exportUsers() {
            alert('User export functionality would be implemented here.');
        }

        function exportEvents() {
            alert('Event export functionality would be implemented here.');
        }

        function generateSummary() {
            alert('Summary report generation would be implemented here.');
        }
        
        // Animate stat numbers
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