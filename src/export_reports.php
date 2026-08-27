<?php
session_start();
include 'logged_admin.php';
include 'db_controller.php';

$conn->select_db("Alumni");

// Get export format
$format = $_GET['format'] ?? 'pdf';
$type = $_GET['type'] ?? 'summary';

// Get all analytics data
$analytics = [];

// User Analytics
$analytics['users'] = [
    'total' => $conn->query("SELECT COUNT(*) as count FROM user_table u JOIN account_table a ON u.email = a.email WHERE a.type = 'user'")->fetch_assoc()['count'],
    'approved' => $conn->query("SELECT COUNT(*) as count FROM user_table u JOIN account_table a ON u.email = a.email WHERE a.type = 'user' AND a.status = 'Approved'")->fetch_assoc()['count'],
    'pending' => $conn->query("SELECT COUNT(*) as count FROM user_table u JOIN account_table a ON u.email = a.email WHERE a.type = 'user' AND a.status = 'Pending'")->fetch_assoc()['count'],
    'rejected' => $conn->query("SELECT COUNT(*) as count FROM user_table u JOIN account_table a ON u.email = a.email WHERE a.type = 'user' AND a.status = 'Rejected'")->fetch_assoc()['count']
];

// Event Analytics
$analytics['events'] = [
    'total' => $conn->query("SELECT COUNT(*) as count FROM event_table")->fetch_assoc()['count'],
    'upcoming' => $conn->query("SELECT COUNT(*) as count FROM event_table WHERE event_date >= CURDATE()")->fetch_assoc()['count'],
    'past' => $conn->query("SELECT COUNT(*) as count FROM event_table WHERE event_date < CURDATE()")->fetch_assoc()['count']
];

// Advertisement Analytics
$analytics['ads'] = [
    'total' => $conn->query("SELECT COUNT(*) as count FROM advertisement_table")->fetch_assoc()['count'],
    'active' => $conn->query("SELECT COUNT(*) as count FROM advertisement_table WHERE status = 'Active'")->fetch_assoc()['count'],
    'inactive' => $conn->query("SELECT COUNT(*) as count FROM advertisement_table WHERE status = 'Inactive'")->fetch_assoc()['count']
];

// Message Analytics
$analytics['messages'] = [
    'total' => $conn->query("SELECT COUNT(*) as count FROM chat_messages_table")->fetch_assoc()['count'],
    'conversations' => $conn->query("SELECT COUNT(*) as count FROM chat_conversations_table")->fetch_assoc()['count']
];

// Top colleges with detailed information
$top_colleges = $conn->query("SELECT 
    college_name, 
    COUNT(*) as total_count,
    GROUP_CONCAT(DISTINCT batch_year ORDER BY batch_year DESC SEPARATOR ', ') as batch_years,
    MIN(batch_year) as earliest_batch,
    MAX(batch_year) as latest_batch,
    COUNT(DISTINCT batch_year) as batch_count
    FROM user_table 
    WHERE college_name IS NOT NULL AND college_name != '' 
    GROUP BY college_name 
    ORDER BY total_count DESC 
    LIMIT 10");

// Monthly registration data
$monthly_registrations = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $count = $conn->query("SELECT COUNT(*) as count FROM user_table WHERE DATE_FORMAT(email, '%Y-%m') = '$month'")->fetch_assoc()['count'];
    $monthly_registrations[] = [
        'month' => date('M Y', strtotime("-$i months")),
        'count' => $count
    ];
}

// Detailed user list
$detailed_users = $conn->query("SELECT 
    u.email, u.first_name, u.last_name, u.college_name, u.batch_year, 
    u.current_location, u.job_position, u.company, a.status
    FROM user_table u 
    JOIN account_table a ON u.email = a.email 
    WHERE a.type = 'user' 
    ORDER BY a.status, u.college_name, u.batch_year DESC");

if ($format === 'excel') {
    exportToExcel($analytics, $top_colleges, $monthly_registrations, $detailed_users, $type);
} else {
    exportToPDF($analytics, $top_colleges, $monthly_registrations, $detailed_users, $type);
}

function exportToExcel($analytics, $top_colleges, $monthly_registrations, $detailed_users, $type) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="Alumni_Portal_Report_' . date('Y-m-d') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo '<html>';
    echo '<head><meta charset="UTF-8"><title>Alumni Portal Report</title></head>';
    echo '<body>';
    
    // Title
    echo '<h1>Alumni Portal Analytics Report</h1>';
    echo '<p>Generated on: ' . date('F j, Y g:i A') . '</p>';
    echo '<hr>';
    
    // Summary Statistics
    echo '<h2>Summary Statistics</h2>';
    echo '<table border="1" cellpadding="5" cellspacing="0">';
    echo '<tr style="background-color: #f0f0f0;">';
    echo '<th>Metric</th><th>Total</th><th>Approved</th><th>Pending</th><th>Rejected/Inactive</th>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<td><strong>Users</strong></td>';
    echo '<td>' . number_format($analytics['users']['total']) . '</td>';
    echo '<td>' . number_format($analytics['users']['approved']) . '</td>';
    echo '<td>' . number_format($analytics['users']['pending']) . '</td>';
    echo '<td>' . number_format($analytics['users']['rejected']) . '</td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<td><strong>Events</strong></td>';
    echo '<td>' . number_format($analytics['events']['total']) . '</td>';
    echo '<td>' . number_format($analytics['events']['upcoming']) . ' (Upcoming)</td>';
    echo '<td>' . number_format($analytics['events']['past']) . ' (Past)</td>';
    echo '<td>-</td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<td><strong>Advertisements</strong></td>';
    echo '<td>' . number_format($analytics['ads']['total']) . '</td>';
    echo '<td>' . number_format($analytics['ads']['active']) . '</td>';
    echo '<td>-</td>';
    echo '<td>' . number_format($analytics['ads']['inactive']) . '</td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<td><strong>Messages</strong></td>';
    echo '<td>' . number_format($analytics['messages']['total']) . '</td>';
    echo '<td>' . number_format($analytics['messages']['conversations']) . ' (Conversations)</td>';
    echo '<td>-</td>';
    echo '<td>-</td>';
    echo '</tr>';
    
    echo '</table>';
    echo '<br><br>';
    
    // Top Colleges
    echo '<h2>Top Colleges by Alumni Count</h2>';
    echo '<table border="1" cellpadding="5" cellspacing="0">';
    echo '<tr style="background-color: #f0f0f0;">';
    echo '<th>Rank</th><th>College Name</th><th>Total Alumni</th><th>Batch Count</th><th>Batch Range</th><th>Recent Batches</th>';
    echo '</tr>';
    
    $rank = 1;
    while ($college = $top_colleges->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $rank . '</td>';
        echo '<td>' . htmlspecialchars($college['college_name']) . '</td>';
        echo '<td>' . number_format($college['total_count']) . '</td>';
        echo '<td>' . $college['batch_count'] . '</td>';
        echo '<td>' . $college['earliest_batch'] . ' - ' . $college['latest_batch'] . '</td>';
        echo '<td>' . htmlspecialchars($college['batch_years']) . '</td>';
        echo '</tr>';
        $rank++;
    }
    echo '</table>';
    echo '<br><br>';
    
    // Monthly Registration Trends
    echo '<h2>Monthly Registration Trends</h2>';
    echo '<table border="1" cellpadding="5" cellspacing="0">';
    echo '<tr style="background-color: #f0f0f0;">';
    echo '<th>Month</th><th>New Registrations</th>';
    echo '</tr>';
    
    foreach ($monthly_registrations as $month_data) {
        echo '<tr>';
        echo '<td>' . $month_data['month'] . '</td>';
        echo '<td>' . number_format($month_data['count']) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '<br><br>';
    
    // Detailed User List (if requested)
    if ($type === 'detailed') {
        echo '<h2>Detailed User List</h2>';
        echo '<table border="1" cellpadding="5" cellspacing="0">';
        echo '<tr style="background-color: #f0f0f0;">';
        echo '<th>Email</th><th>Name</th><th>College</th><th>Batch Year</th><th>Location</th><th>Job Position</th><th>Company</th><th>Status</th>';
        echo '</tr>';
        
        while ($user = $detailed_users->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($user['email']) . '</td>';
            echo '<td>' . htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) . '</td>';
            echo '<td>' . htmlspecialchars($user['college_name'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($user['batch_year'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($user['current_location'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($user['job_position'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($user['company'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($user['status']) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
    
    echo '</body></html>';
}

function exportToPDF($analytics, $top_colleges, $monthly_registrations, $detailed_users, $type) {
    // For PDF, we'll create an HTML version that can be printed to PDF
    header('Content-Type: text/html; charset=UTF-8');
    
    echo '<!DOCTYPE html>';
    echo '<html><head>';
    echo '<meta charset="UTF-8">';
    echo '<title>Alumni Portal Report - ' . date('Y-m-d') . '</title>';
    echo '<style>';
    echo 'body { font-family: Arial, sans-serif; margin: 20px; color: #333; }';
    echo 'h1 { color: #2563eb; border-bottom: 3px solid #2563eb; padding-bottom: 10px; }';
    echo 'h2 { color: #1e40af; margin-top: 30px; margin-bottom: 15px; }';
    echo 'table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }';
    echo 'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }';
    echo 'th { background-color: #f8f9fa; font-weight: bold; }';
    echo 'tr:nth-child(even) { background-color: #f8f9fa; }';
    echo '.summary-stats { background: #e3f2fd; padding: 15px; border-radius: 5px; margin-bottom: 20px; }';
    echo '.metric-box { display: inline-block; margin: 10px; padding: 15px; background: white; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }';
    echo '.print-info { color: #666; font-size: 12px; margin-bottom: 20px; }';
    echo '@media print { body { margin: 0; } .no-print { display: none; } }';
    echo '</style>';
    echo '</head><body>';
    
    // Header
    echo '<h1>Alumni Portal Analytics Report</h1>';
    echo '<div class="print-info">';
    echo '<strong>Generated on:</strong> ' . date('F j, Y g:i A') . '<br>';
    echo '<strong>Report Type:</strong> ' . ucfirst($type) . ' Report<br>';
    echo '<strong>Total Records:</strong> ' . number_format($analytics['users']['total']) . ' users';
    echo '</div>';
    
    // Summary Statistics
    echo '<div class="summary-stats">';
    echo '<h2>Executive Summary</h2>';
    echo '<div class="metric-box">';
    echo '<strong>Total Users:</strong> ' . number_format($analytics['users']['total']) . '<br>';
    echo '<small>Approved: ' . number_format($analytics['users']['approved']) . ' | Pending: ' . number_format($analytics['users']['pending']) . '</small>';
    echo '</div>';
    
    echo '<div class="metric-box">';
    echo '<strong>Events:</strong> ' . number_format($analytics['events']['total']) . '<br>';
    echo '<small>Upcoming: ' . number_format($analytics['events']['upcoming']) . ' | Past: ' . number_format($analytics['events']['past']) . '</small>';
    echo '</div>';
    
    echo '<div class="metric-box">';
    echo '<strong>Opportunities:</strong> ' . number_format($analytics['ads']['total']) . '<br>';
    echo '<small>Active: ' . number_format($analytics['ads']['active']) . ' | Inactive: ' . number_format($analytics['ads']['inactive']) . '</small>';
    echo '</div>';
    
    echo '<div class="metric-box">';
    echo '<strong>Messages:</strong> ' . number_format($analytics['messages']['total']) . '<br>';
    echo '<small>Conversations: ' . number_format($analytics['messages']['conversations']) . '</small>';
    echo '</div>';
    echo '</div>';
    
    // Top Colleges
    echo '<h2>Top Colleges by Alumni Count</h2>';
    echo '<table>';
    echo '<thead><tr><th>Rank</th><th>College Name</th><th>Alumni Count</th><th>Batches</th><th>Batch Range</th></tr></thead>';
    echo '<tbody>';
    
    $rank = 1;
    $top_colleges->data_seek(0); // Reset pointer
    while ($college = $top_colleges->fetch_assoc()) {
        echo '<tr>';
        echo '<td>#' . $rank . '</td>';
        echo '<td>' . htmlspecialchars($college['college_name']) . '</td>';
        echo '<td>' . number_format($college['total_count']) . '</td>';
        echo '<td>' . $college['batch_count'] . ' different batches</td>';
        echo '<td>' . $college['earliest_batch'] . ' - ' . $college['latest_batch'] . '</td>';
        echo '</tr>';
        $rank++;
    }
    echo '</tbody></table>';
    
    // Monthly Trends
    echo '<h2>Registration Trends (Last 12 Months)</h2>';
    echo '<table>';
    echo '<thead><tr><th>Month</th><th>New Registrations</th><th>Cumulative</th></tr></thead>';
    echo '<tbody>';
    
    $cumulative = 0;
    foreach ($monthly_registrations as $month_data) {
        $cumulative += $month_data['count'];
        echo '<tr>';
        echo '<td>' . $month_data['month'] . '</td>';
        echo '<td>' . number_format($month_data['count']) . '</td>';
        echo '<td>' . number_format($cumulative) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    
    // Detailed User List (if requested)
    if ($type === 'detailed') {
        echo '<h2>Detailed Alumni Directory</h2>';
        echo '<table>';
        echo '<thead><tr><th>Name</th><th>Email</th><th>College</th><th>Batch</th><th>Current Role</th><th>Status</th></tr></thead>';
        echo '<tbody>';
        
        while ($user = $detailed_users->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))) . '</td>';
            echo '<td>' . htmlspecialchars($user['email']) . '</td>';
            echo '<td>' . htmlspecialchars($user['college_name'] ?? 'Not specified') . '</td>';
            echo '<td>' . htmlspecialchars($user['batch_year'] ?? 'N/A') . '</td>';
            echo '<td>' . htmlspecialchars(($user['job_position'] ?? '') . ($user['company'] ? ' at ' . $user['company'] : '')) . '</td>';
            echo '<td><span style="color: ' . ($user['status'] === 'Approved' ? 'green' : ($user['status'] === 'Pending' ? 'orange' : 'red')) . ';">' . $user['status'] . '</span></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }
    
    // Footer
    echo '<div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 12px;">';
    echo '<p><strong>Note:</strong> This report contains confidential information. Please handle according to your organization\'s data privacy policies.</p>';
    echo '<p>For questions about this report, contact the system administrator.</p>';
    echo '</div>';
    
    // Print button for PDF generation
    echo '<div class="no-print" style="position: fixed; top: 20px; right: 20px; background: white; padding: 10px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">';
    echo '<button onclick="window.print()" style="background: #2563eb; color: white; border: none; padding: 10px 20px; border-radius: 3px; cursor: pointer;">Print to PDF</button>';
    echo '</div>';
    
    echo '</body></html>';
}
?>