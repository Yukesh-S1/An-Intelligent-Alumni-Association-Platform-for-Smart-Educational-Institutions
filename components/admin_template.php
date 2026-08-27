<?php
/**
 * Admin Page Template
 * Usage: Include this file and define the required variables before including
 * 
 * Required variables:
 * - $page_title: Page title
 * - $page_subtitle: Page subtitle (optional)
 * - $page_content: Main page content (HTML)
 * 
 * Optional variables:
 * - $additional_css: Additional CSS files or inline styles
 * - $additional_js: Additional JavaScript files or inline scripts
 * - $breadcrumbs: Array of breadcrumb items
 */

// Check admin authentication
if (!isset($_SESSION['logged_account']) || $_SESSION['logged_account']['type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Set default values
$page_title = $page_title ?? 'Admin Panel';
$page_subtitle = $page_subtitle ?? '';
$additional_css = $additional_css ?? '';
$additional_js = $additional_js ?? '';
$breadcrumbs = $breadcrumbs ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Alumni Portal</title>
    
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
    
    <!-- Additional CSS -->
    <?php echo $additional_css; ?>
</head>
<body>
    <?php include 'components/admin_nav.php'; ?>

    <!-- Main Content -->
    <div class="admin-container">
        <!-- Breadcrumbs -->
        <?php if (!empty($breadcrumbs)): ?>
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="admin_dashboard.php" class="text-decoration-none">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                    </li>
                    <?php foreach ($breadcrumbs as $index => $crumb): ?>
                        <?php if ($index === count($breadcrumbs) - 1): ?>
                            <li class="breadcrumb-item active" aria-current="page">
                                <?php echo htmlspecialchars($crumb['title']); ?>
                            </li>
                        <?php else: ?>
                            <li class="breadcrumb-item">
                                <a href="<?php echo htmlspecialchars($crumb['url']); ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($crumb['title']); ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </nav>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="admin-page-header fade-in">
            <h1 class="admin-page-title"><?php echo htmlspecialchars($page_title); ?></h1>
            <?php if ($page_subtitle): ?>
                <p class="admin-page-subtitle"><?php echo htmlspecialchars($page_subtitle); ?></p>
            <?php endif; ?>
        </div>

        <!-- Page Content -->
        <div class="admin-page-content">
            <?php echo $page_content; ?>
        </div>
    </div>

    <?php include 'components/admin_footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Additional JS -->
    <?php echo $additional_js; ?>
    
    <!-- Common Admin JS -->
    <script>
        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });

        // Confirm delete actions
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-delete') || e.target.closest('.btn-delete')) {
                if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                    e.preventDefault();
                    return false;
                }
            }
        });

        // Loading states for forms
        document.addEventListener('submit', function(e) {
            const submitBtn = e.target.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
                
                // Re-enable after 10 seconds as fallback
                setTimeout(function() {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }, 10000);
            }
        });
    </script>
</body>
</html>