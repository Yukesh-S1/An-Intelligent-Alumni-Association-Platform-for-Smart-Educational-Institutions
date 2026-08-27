<!-- Ensures account type 'admin' is logged in to access any page that includes this -->

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize variables
$pendingCount = 0;

// Check if user is logged in and is an admin
if (!isset($_SESSION['logged_account']) || empty($_SESSION['logged_account']) || $_SESSION['logged_account']['type'] !== 'admin') {
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
    
    // Set flash message and redirect
    $_SESSION['flash_mode'] = "alert-warning";
    $_SESSION['flash'] = "You must log in as admin to access this page.";
    header('Location: login.php');
    exit();
} 

// Get pending account count for admin dashboard
include 'db_controller.php';
$conn->select_db("Alumni");

$result = $conn->query("SELECT COUNT(*) AS count FROM account_table WHERE status = 'Pending' AND type != 'admin'");
if ($result && $row = $result->fetch_assoc()) {
    $pendingCount = (int)$row['count'];
}
?>