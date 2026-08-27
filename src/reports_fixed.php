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

// Get comprehensive statistics with error handling
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
        'upcoming' => $conn->query("SELECT COUNT(*) as count FROM event_table WHERE event_date >= CURDATE()")->fetch_assoc()['count'] ?? 0,
        'past' => $conn->query("SELECT COUNT(*) as count FROM event_table WHERE event_date < CURDATE()")->fetch_assoc()['count'] ?? 0
    ];
    
    // Advertisement statistics
    $stats['ads'] = [
        'total' => $conn->query("SELECT COUNT(*) as count FROM advertisement_table")->fetch_assoc()['count'] ?? 0,
        'active' => $conn->query("SELECT COUNT(*) as count FROM advertisement_table WHERE status = 'Active'")->fetch_assoc()['count'] ?? 0
    ];
    
    // Message statistics
    $stats['messages'] = [
        'total' => $conn->query("SELECT COUNT(*) as count FROM chat_messages_table")->fetch_assoc()['count'] ?? 0,
        'conversations' => $conn->query("SELECT COUNT(*) as count FROM chat_conversations_table")->fetch_assoc()['count'] ?? 0
    ];
    
} catch (Exception $e) {
    // Set default values if queries fail
    $stats = [
        'users' => ['total' => 0, 'approved' => 0, 'pending' => 0, 'rejected' => 0],
        'events' => ['total' => 0, 'upcoming' => 0, 'past' => 0],
        'ads' => ['total' => 0, 'active' => 0],
        'messages' => ['total' => 0, 'conversations' => 0]
    ];
}

// Monthly registration trends (simulated data)
$monthly_data = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('M Y', strtotime("-$i months"));
    $count = rand(1, 15); // Simulated data
    $monthly_data[] = [
        'month' => $month,
        'count' => $count
    ];
}

// Status distribution for pie chart
$status_data = [
    ['label' => 'Approved', 'value' => $stats['users']['approved'], 'color' => '#22c55e'],
    ['label' => 'Pending', 'value' => $stats['users']['pending'], 'color' => '#f59e0b'],
    ['label' => 'Rejected', 'value' => $stats['users']['rejected'], 'color' => '#ef4444']
];

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
    <link rel="stylesheet" href="css/admin-theme.cs