<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['logged_account']) || $_SESSION['logged_account']['type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

include 'db_controller.php';
$conn->select_db("Alumni");

// Get statistics for display
$stats = [];
try {
    $stats['feedback'] = $conn->query("SELECT COUNT(*) as count FROM feedback_table")->fetch_assoc()['count'] ?? 0;
    $stats['users'] = $conn->query("SELECT COUNT(*) as count FROM user_table u JOIN account_table a ON u.email = a.email WHERE a.type = 'user'")->fetch_assoc()['count'] ?? 0;
    $stats['events'] = $conn->query("SELECT COUNT(*) as count FROM event_table")->fetch_assoc()['count'] ?? 0;
    
    // Check if event_registrations table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'event_registrations'");
    if ($table_check->num_rows > 0) {
        $stats['registrations'] = $conn->query("SELECT COUNT(*) as count FROM event_registrations")->fetch_assoc()['count'] ?? 0;
    } else {
        $stats['registrations'] = $conn->query("SELECT COUNT(*) as count FROM event_table WHERE event_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)")->fetch_assoc()['count'] * 5; // Estimate
    }
} catch (Exception $e) {
    $stats = ['feedback' => 0, 'users' => 0, 'events' => 0, 'registrations' => 0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Data - Alumni Portal Admin</title>
    
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
    
    <style>
        .export-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .export-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .export-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .export-icon.feedback { background: #fef3c7; color: #d97706; }
        .export-icon.users { background: #dbeafe; color: #2563eb; }
        .export-icon.events { background: #d1fae5; color: #059669; }
        .export-icon.registrations { background: #fce7f3; color: #be185d; }
        .export-icon.all { background: #f3e8ff; color: #7c3aed; }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
        }
        
        .export-form {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1rem;
        }
        
        .btn-export {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-export:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            transform: translateY(-1px);
            color: white;
        }
        
        .format-selector {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .format-btn {
            flex: 1;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .format-btn.active {
            border-color: #3b82f6;
            background: #eff6ff;
            color: #1d4ed8;
        }
        
        .date-range {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php include 'components/admin_nav.php'; ?>

    <!-- Main Content -->
    <div class="admin-container">
        <!-- Page Header -->
        <div class="admin-page-header fade-in">
            <h1 class="admin-page-title">Export Data</h1>
            <p class="admin-page-subtitle">Export various data from the alumni portal for analysis and reporting</p>
        </div>

        <!-- Export Options -->
        <div class="row mb-6">
            <!-- Feedback Export -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="export-card">
                    <div class="export-icon feedback">
                        <i class="bi bi-chat-square-text"></i>
                    </div>
                    <h4 class="mb-2">Feedback Data</h4>
                    <div class="stat-number"><?php echo number_format($stats['feedback']); ?></div>
                    <p class="text-muted mb-3">Total feedback submissions</p>
                    
                    <div class="export-form">
                        <form action="export_comprehensive.php" method="GET" target="_blank">
                            <input type="hidden" name="type" value="feedback">
                            
                            <div class="format-selector">
                                <label class="format-btn active">
                                    <input type="radio" name="format" value="csv" checked style="display: none;">
                                    <i class="bi bi-filetype-csv me-1"></i> CSV
                                </label>
                                <label class="format-btn">
                                    <input type="radio" name="format" value="excel" style="display: none;">
                                    <i class="bi bi-file-earmark-excel me-1"></i> Excel
                                </label>
                            </div>
                            
                            <div class="date-range">
                                <div>
                                    <label class="form-label small">From Date</label>
                                    <input type="date" name="date_from" class="form-control form-control-sm">
                                </div>
                                <div>
                                    <label class="form-label small">To Date</label>
                                    <input type="date" name="date_to" class="form-control form-control-sm">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-export w-100">
                                <i class="bi bi-download me-1"></i> Export Feedback
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Users Export -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="export-card">
                    <div class="export-icon users">
                        <i class="bi bi-people"></i>
                    </div>
                    <h4 class="mb-2">User Data</h4>
                    <div class="stat-number"><?php echo number_format($stats['users']); ?></div>
                    <p class="text-muted mb-3">Registered alumni users</p>
                    
                    <div class="export-form">
                        <form action="export_comprehensive.php" method="GET" target="_blank">
                            <input type="hidden" name="type" value="users">
                            
                            <div class="format-selector">
                                <label class="format-btn active">
                                    <input type="radio" name="format" value="csv" checked style="display: none;">
                                    <i class="bi bi-filetype-csv me-1"></i> CSV
                                </label>
                                <label class="format-btn">
                                    <input type="radio" name="format" value="excel" style="display: none;">
                                    <i class="bi bi-file-earmark-excel me-1"></i> Excel
                                </label>
                            </div>
                            
                            <div class="date-range">
                                <div>
                                    <label class="form-label small">From Date</label>
                                    <input type="date" name="date_from" class="form-control form-control-sm">
                                </div>
                                <div>
                                    <label class="form-label small">To Date</label>
                                    <input type="date" name="date_to" class="form-control form-control-sm">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-export w-100">
                                <i class="bi bi-download me-1"></i> Export Users
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Events Export -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="export-card">
                    <div class="export-icon events">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <h4 class="mb-2">Events Data</h4>
                    <div class="stat-number"><?php echo number_format($stats['events']); ?></div>
                    <p class="text-muted mb-3">Total events created</p>
                    
                    <div class="export-form">
                        <form action="export_comprehensive.php" method="GET" target="_blank">
                            <input type="hidden" name="type" value="events">
                            
                            <div class="format-selector">
                                <label class="format-btn active">
                                    <input type="radio" name="format" value="csv" checked style="display: none;">
                                    <i class="bi bi-filetype-csv me-1"></i> CSV
                                </label>
                                <label class="format-btn">
                                    <input type="radio" name="format" value="excel" style="display: none;">
                                    <i class="bi bi-file-earmark-excel me-1"></i> Excel
                                </label>
                            </div>
                            
                            <div class="date-range">
                                <div>
                                    <label class="form-label small">From Date</label>
                                    <input type="date" name="date_from" class="form-control form-control-sm">
                                </div>
                                <div>
                                    <label class="form-label small">To Date</label>
                                    <input type="date" name="date_to" class="form-control form-control-sm">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-export w-100">
                                <i class="bi bi-download me-1"></i> Export Events
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Event Registrations Export -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="export-card">
                    <div class="export-icon registrations">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <h4 class="mb-2">Event Registrations</h4>
                    <div class="stat-number"><?php echo number_format($stats['registrations']); ?></div>
                    <p class="text-muted mb-3">Event sign-ups</p>
                    
                    <div class="export-form">
                        <form action="export_comprehensive.php" method="GET" target="_blank">
                            <input type="hidden" name="type" value="event_registrations">
                            
                            <div class="format-selector">
                                <label class="format-btn active">
                                    <input type="radio" name="format" value="csv" checked style="display: none;">
                                    <i class="bi bi-filetype-csv me-1"></i> CSV
                                </label>
                                <label class="format-btn">
                                    <input type="radio" name="format" value="excel" style="display: none;">
                                    <i class="bi bi-file-earmark-excel me-1"></i> Excel
                                </label>
                            </div>
                            
                            <div class="date-range">
                                <div>
                                    <label class="form-label small">From Date</label>
                                    <input type="date" name="date_from" class="form-control form-control-sm">
                                </div>
                                <div>
                                    <label class="form-label small">To Date</label>
                                    <input type="date" name="date_to" class="form-control form-control-sm">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-export w-100">
                                <i class="bi bi-download me-1"></i> Export Registrations
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Complete Export -->
            <div class="col-lg-8 col-md-12 mb-4">
                <div class="export-card">
                    <div class="d-flex align-items-center mb-3">
                        <div class="export-icon all me-3">
                            <i class="bi bi-database"></i>
                        </div>
                        <div>
                            <h4 class="mb-1">Complete Data Export</h4>
                            <p class="text-muted mb-0">Export all data including feedback, users, events, and registrations</p>
                        </div>
                    </div>
                    
                    <div class="export-form">
                        <form action="export_comprehensive.php" method="GET" target="_blank">
                            <input type="hidden" name="type" value="all_data">
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="format-selector">
                                        <label class="format-btn active">
                                            <input type="radio" name="format" value="excel" checked style="display: none;">
                                            <i class="bi bi-file-earmark-excel me-1"></i> Excel
                                        </label>
                                        <label class="format-btn">
                                            <input type="radio" name="format" value="csv" style="display: none;">
                                            <i class="bi bi-filetype-csv me-1"></i> CSV
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">From Date</label>
                                    <input type="date" name="date_from" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">To Date</label>
                                    <input type="date" name="date_to" class="form-control form-control-sm">
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <button type="submit" class="btn btn-export">
                                    <i class="bi bi-download me-1"></i> Export Complete Dataset
                                </button>
                                <small class="text-muted ms-3">
                                    <i class="bi bi-info-circle me-1"></i>
                                    This may take a few moments for large datasets
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Instructions -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h4 class="admin-card-title">
                    <i class="bi bi-info-circle text-info"></i>
                    Export Instructions
                </h4>
            </div>
            <div class="admin-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="bi bi-filetype-csv text-success me-2"></i>CSV Format</h6>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check text-success me-2"></i>Compatible with Excel, Google Sheets</li>
                            <li><i class="bi bi-check text-success me-2"></i>Smaller file size</li>
                            <li><i class="bi bi-check text-success me-2"></i>Easy to import into databases</li>
                            <li><i class="bi bi-check text-success me-2"></i>Plain text format</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="bi bi-file-earmark-excel text-primary me-2"></i>Excel Format</h6>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check text-success me-2"></i>Formatted tables with headers</li>
                            <li><i class="bi bi-check text-success me-2"></i>Ready for analysis</li>
                            <li><i class="bi bi-check text-success me-2"></i>Includes styling and formatting</li>
                            <li><i class="bi bi-check text-success me-2"></i>Multiple sheets for complete export</li>
                        </ul>
                    </div>
                </div>
                
                <div class="alert alert-info mt-3">
                    <i class="bi bi-lightbulb me-2"></i>
                    <strong>Tip:</strong> Use date filters to export specific time periods. Leave dates empty to export all data.
                </div>
            </div>
        </div>
    </div>

    <?php include 'components/admin_footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Format selector functionality
        document.addEventListener('DOMContentLoaded', function() {
            const formatSelectors = document.querySelectorAll('.format-selector');
            
            formatSelectors.forEach(selector => {
                const buttons = selector.querySelectorAll('.format-btn');
                const radios = selector.querySelectorAll('input[type="radio"]');
                
                buttons.forEach((button, index) => {
                    button.addEventListener('click', function() {
                        // Remove active class from all buttons in this selector
                        buttons.forEach(btn => btn.classList.remove('active'));
                        // Add active class to clicked button
                        this.classList.add('active');
                        // Check the corresponding radio button
                        radios[index].checked = true;
                    });
                });
            });
            
            // Set default dates (last 30 days)
            const today = new Date();
            const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
            
            const dateFromInputs = document.querySelectorAll('input[name="date_from"]');
            const dateToInputs = document.querySelectorAll('input[name="date_to"]');
            
            dateFromInputs.forEach(input => {
                input.value = thirtyDaysAgo.toISOString().split('T')[0];
            });
            
            dateToInputs.forEach(input => {
                input.value = today.toISOString().split('T')[0];
            });
        });
        
        // Show loading message when exporting
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const button = this.querySelector('.btn-export');
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Exporting...';
                button.disabled = true;
                
                // Re-enable button after 3 seconds
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                }, 3000);
            });
        });
    </script>
</body>
</html>