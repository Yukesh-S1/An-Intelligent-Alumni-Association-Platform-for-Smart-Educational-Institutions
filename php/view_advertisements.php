<?php
// Redirect to news page since opportunities have been replaced with news
session_start();
header('Location: view_news_modern.php');
exit();

// Initialize variables
$tempFlash = '';
if (isset($_SESSION['flash_mode'])){
    $tempFlash = $_SESSION['flash_mode'];
    unset($_SESSION['flash_mode']);
}

$modalOn = false;
$modalType = '';
$modalMessage = '';

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // GET the POST (which was retrieved from GET previously)
    if (isset($_POST['filterStatus'])) 
        $_GET['filterStatus'] = $_POST['filterStatus'];
    if (isset($_POST['filterCategory'])) 
        $_GET['filterCategory'] = $_POST['filterCategory'];
    if (isset($_POST['search'])) 
        $_GET['search'] = $_POST['search'];
}

// Handle session-based modals
if (isset($_SESSION['ad_apply_error']) && $_SESSION['ad_apply_error'] == true) {
    $modalOn = true;
    $modalType = 'error';
    $modalMessage = 'There was an error processing your application. Please try again.';
    unset($_SESSION['ad_apply_error']);
}

if (isset($_SESSION['ad_apply_success']) && $_SESSION['ad_apply_success'] == true) {
    $modalOn = true;
    $modalType = 'success';
    $modalMessage = 'Your application has been submitted successfully!';
    unset($_SESSION['ad_apply_success']);
}

// Build query filters
$filterStatus = "";
$filterCategory = "";
$filterSearch = "";

// Status filter
if (isset($_GET['filterStatus']) && $_GET['filterStatus'] != 'All')
    $filterStatus = "status = '" . $_GET['filterStatus'] . "'";

// Category filter
if (isset($_GET['filterCategory']) && $_GET['filterCategory'] != 'All')
    $filterCategory = "category = '" . $_GET['filterCategory'] . "'";

// Search filter
if (isset($_GET['search']) && $_GET['search'] != "") {
    $trimSearch = strtolower(trim($_GET['search']));
    $filterSearch = "(
        LOWER(title) LIKE '%$trimSearch%' OR 
        LOWER(advertiser) LIKE '%$trimSearch%' OR 
        LOWER(description) LIKE '%$trimSearch%' OR 
        LOWER(category) LIKE '%$trimSearch%'
    )";
}

// Combine filters
$conditions = array_filter([$filterStatus, $filterCategory, $filterSearch]);
if (!empty($conditions))
    $whereClause = "WHERE " . implode(" AND ", $conditions);
else
    $whereClause = "WHERE status = 'Active'"; // Default to show only active opportunities

// Query the database
$allOpportunities = $conn->query("SELECT * FROM advertisement_table $whereClause ORDER BY date_added DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Career Opportunities | Alumni Portal</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Modern Design System -->

    <style>
        /* Dashboard-Oriented Opportunities Design */
        body {
            background-color: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
        }
        
        .container {
            position: relative;
            z-index: 1;
        }
        
        .card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }
        
        .card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .navbar {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%) !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.25rem;
        }
        
        .nav-link {
            border-radius: 8px;
            transition: all 0.2s ease;
            margin: 0 2px;
        }
        
        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .nav-main-active {
            background: rgba(255, 255, 255, 0.2);
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
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 1.5rem;
        }
        
        .opportunity-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
            overflow: hidden;
            position: relative;
            height: 100%;
        }
        
        .opportunity-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-color: #8b5cf6;
        }
        
        .opportunity-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 1rem;
            position: relative;
        }
        
        .opportunity-category {
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .opportunity-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }
        
        .opportunity-company {
            font-size: 1rem;
            font-weight: 500;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }
        
        .opportunity-location {
            font-size: 0.875rem;
            opacity: 0.8;
            display: flex;
            align-items: center;
        }
        
        .opportunity-advertiser {
            font-size: 1rem;
            font-weight: 500;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }
        
        .opportunity-category {
            font-size: 0.875rem;
            opacity: 0.8;
            display: flex;
            align-items: center;
        }
        
        .opportunity-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            height: calc(100% - 120px);
        }
        
        .opportunity-description {
            color: #4b5563;
            font-size: 0.875rem;
            line-height: 1.5;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex-grow: 1;
        }
        
        .opportunity-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }
        
        .opportunity-date {
            color: #6b7280;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
        }
        
        .opportunity-salary {
            color: #059669;
            font-weight: 700;
            font-size: 0.875rem;
        }
        
        .opportunity-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: auto;
        }
        
        .btn-apply {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            flex: 1;
        }
        
        .btn-apply:hover {
            background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(139, 92, 246, 0.3);
        }
        
        .btn-details {
            background: #3b82f6;
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        
        .btn-details:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }
        
        .filter-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .form-control, .form-select {
            background: #f8fafc;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 8px 12px;
            transition: all 0.2s ease;
            font-size: 14px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
            background: white;
        }
        
        .btn-primary {
            background: #3b82f6;
            border: 1px solid #3b82f6;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        
        .btn-primary:hover {
            background: #2563eb;
            border-color: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
        }
        
        .form-check-input:checked {
            background-color: #8b5cf6;
            border-color: #8b5cf6;
        }
        
        .form-check-label {
            font-weight: 500;
            font-size: 14px;
        }
        
        .badge.text-bg-success {
            background: #10b981 !important;
            color: white !important;
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.75rem;
        }
        
        .badge.text-bg-warning {
            background: #f59e0b !important;
            color: white !important;
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.75rem;
        }
        
        .badge.text-bg-danger {
            background: #ef4444 !important;
            color: white !important;
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.75rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6b7280;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #d1d5db;
        }
        
        /* Animations */
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
                font-size: 1.5rem;
            }
            
            .opportunity-card {
                margin-bottom: 1rem;
            }
            
            .filter-card {
                padding: 1rem;
            }
            
            .opportunity-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'components/nav_modern.php'; ?>
    
    <!-- Breadcrumb -->
    <div class="container my-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a class="breadcrumb-link text-decoration-none" href="main_menu.php">Home</a></li>
                <li class="breadcrumb-item breadcrumb-active" aria-current="page">Career Opportunities</li>
            </ol>
        </nav>
    </div>

    <div class="container mb-5">
        <!-- Page Header -->
        <div class="row slide-left">
            <div class="col">
                <h1><i class="bi bi-briefcase me-3"></i>Career Opportunities</h1>
                <p class="text-muted mb-4">Discover exciting career opportunities shared by our alumni network and partner companies.</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-card slide-left">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="GET">
                <div class="row g-3 align-items-center">
                    <div class="col-md-3">
                        <!-- Status Filter -->
                        <select class="form-select" name="filterStatus">
                            <option value="All" <?php echo (!isset($_GET['filterStatus']) || $_GET['filterStatus'] == 'All') ? 'selected' : ''; ?>>All Status</option>
                            <option value="Active" <?php echo (isset($_GET['filterStatus']) && $_GET['filterStatus'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                            <option value="Closed" <?php echo (isset($_GET['filterStatus']) && $_GET['filterStatus'] == 'Closed') ? 'selected' : ''; ?>>Closed</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <!-- Category Filter -->
                        <select class="form-select" name="filterCategory">
                            <option value="All" <?php echo (!isset($_GET['filterCategory']) || $_GET['filterCategory'] == 'All') ? 'selected' : ''; ?>>All Categories</option>
                            <option value="Full-time" <?php echo (isset($_GET['filterCategory']) && $_GET['filterCategory'] == 'Full-time') ? 'selected' : ''; ?>>Full-time</option>
                            <option value="Part-time" <?php echo (isset($_GET['filterCategory']) && $_GET['filterCategory'] == 'Part-time') ? 'selected' : ''; ?>>Part-time</option>
                            <option value="Contract" <?php echo (isset($_GET['filterCategory']) && $_GET['filterCategory'] == 'Contract') ? 'selected' : ''; ?>>Contract</option>
                            <option value="Internship" <?php echo (isset($_GET['filterCategory']) && $_GET['filterCategory'] == 'Internship') ? 'selected' : ''; ?>>Internship</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <!-- Search -->
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search opportunities..." name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars(trim($_GET['search'])) : ''; ?>">
                            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Opportunities Grid -->
        <div class="row">
            <?php if ($allOpportunities && $allOpportunities->num_rows > 0): ?>
                <?php while ($row = $allOpportunities->fetch_assoc()): ?>
                    <div class="col-lg-6 col-xl-4 mb-4">
                        <div class="opportunity-card">
                            <!-- Opportunity Header -->
                            <div class="opportunity-header">
                                <div class="opportunity-category"><?php echo htmlspecialchars($row['category']); ?></div>
                                <h5 class="opportunity-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                                <div class="opportunity-advertiser">
                                    <i class="bi bi-person me-2"></i>
                                    <?php echo htmlspecialchars($row['advertiser']); ?>
                                </div>
                                <div class="opportunity-category">
                                    <i class="bi bi-tag me-2"></i>
                                    <?php echo htmlspecialchars($row['category']); ?>
                                </div>
                            </div>
                            
                            <!-- Opportunity Body -->
                            <div class="opportunity-body">
                                <p class="opportunity-description"><?php echo htmlspecialchars($row['description']); ?></p>
                                
                                <div class="opportunity-meta">
                                    <div class="opportunity-date">
                                        <i class="bi bi-calendar me-1"></i>
                                        <?php echo date('M j, Y', strtotime($row['date_added'])); ?>
                                    </div>
                                    <?php if (!empty($row['date_to_hide'])): ?>
                                        <div class="opportunity-salary">
                                            <i class="bi bi-clock me-1"></i>
                                            Expires: <?php echo date('M j', strtotime($row['date_to_hide'])); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="opportunity-actions">
                                    <?php if ($row['status'] == 'Active' && $row['appliable'] == 1): ?>
                                        <?php if (!empty($row['button_link'])): ?>
                                            <a href="<?php echo htmlspecialchars($row['button_link']); ?>" target="_blank" class="btn btn-apply">
                                                <i class="bi bi-box-arrow-up-right me-1"></i>
                                                <?php echo !empty($row['button_message']) ? htmlspecialchars($row['button_message']) : 'Apply Now'; ?>
                                            </a>
                                        <?php else: ?>
                                            <a href="advertisement_apply.php?id=<?php echo $row['id']; ?>" class="btn btn-apply">
                                                <i class="bi bi-send me-1"></i>Apply Now
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button class="btn btn-apply" disabled>
                                            <i class="bi bi-x-circle me-1"></i>
                                            <?php echo $row['status'] != 'Active' ? 'Closed' : 'Not Appliable'; ?>
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-details" onclick="viewDetails(<?php echo $row['id']; ?>)">
                                        <i class="bi bi-eye me-1"></i>Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="empty-state">
                        <i class="bi bi-briefcase"></i>
                        <h4>No Opportunities Found</h4>
                        <p>
                            <?php if (!empty($_GET['search']) || !empty($_GET['filterCategory']) || !empty($_GET['filterStatus'])): ?>
                                No opportunities match your current filters. Try adjusting your search criteria.
                                <br><a href="view_advertisements.php" class="text-primary">View all opportunities</a>
                            <?php else: ?>
                                Check back later for new career opportunities from our alumni network.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal for Application Status -->
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function closeModal() {
            document.getElementById('statusModal').style.display = 'none';
        }
        
        function viewDetails(opportunityId) {
            // You can implement a detailed view modal or redirect to a detail page
            alert('View details for opportunity ID: ' + opportunityId);
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