<!-- Admin Footer -->
<footer class="admin-footer mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-0">
                    <i class="bi bi-mortarboard text-primary"></i>
                    <strong>Alumni Portal Admin</strong> - Managing connections, building futures
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-0">
                    <small class="text-muted">
                        <i class="bi bi-person-check"></i>
                        Logged in as: <?php echo htmlspecialchars($_SESSION['logged_account']['email'] ?? 'Admin'); ?>
                        | 
                        <i class="bi bi-clock"></i>
                        <?php echo date('Y-m-d H:i:s'); ?>
                    </small>
                </p>
            </div>
        </div>
        <hr class="my-3">
        <div class="row">
            <div class="col-md-8">
                <div class="admin-footer-links">
                    <a href="admin_dashboard.php" class="text-decoration-none me-3">
                        <i class="bi bi-house-door"></i> Dashboard
                    </a>
                    <a href="manage_users.php" class="text-decoration-none me-3">
                        <i class="bi bi-people"></i> Users
                    </a>
                    <a href="manage_events.php" class="text-decoration-none me-3">
                        <i class="bi bi-calendar-event"></i> Events
                    </a>
                    <a href="reports.php" class="text-decoration-none me-3">
                        <i class="bi bi-graph-up"></i> Reports
                    </a>
                    <a href="settings.php" class="text-decoration-none">
                        <i class="bi bi-gear"></i> Settings
                    </a>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <small class="text-muted">
                    © <?php echo date('Y'); ?> Alumni Portal. All rights reserved.
                </small>
            </div>
        </div>
    </div>
</footer>

<style>
.admin-footer {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-top: 1px solid #dee2e6;
    padding: 2rem 0 1rem;
    margin-top: auto;
}

.admin-footer-links a {
    color: #6c757d;
    font-size: 0.9rem;
    transition: color 0.3s ease;
}

.admin-footer-links a:hover {
    color: #0d6efd;
}

.admin-footer hr {
    border-color: #dee2e6;
    opacity: 0.5;
}
</style>