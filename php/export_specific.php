<?php
session_start();
include 'logged_admin.php';
include 'db_controller.php';

$conn->select_db("Alumni");

$type = $_GET['type'] ?? 'users';

switch ($type) {
    case 'users':
        exportUsers();
        break;
    case 'colleges':
        exportColleges();
        break;
    case 'events':
        exportEvents();
        break;
    default:
        exportUsers();
}

function exportUsers() {
    global $conn;
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="Alumni_Directory_' . date('Y-m-d') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $users = $conn->query("SELECT 
        u.email, u.first_name, u.last_name, u.dob, u.gender, u.contact_number,
        u.hometown, u.college_name, u.batch_year, u.current_location, 
        u.job_position, u.qualification, u.year, u.university, u.company, a.status
        FROM user_table u 
        JOIN account_table a ON u.email = a.email 
        WHERE a.type = 'user' 
        ORDER BY u.college_name, u.batch_year DESC, u.last_name");
    
    echo '<html><head><meta charset="UTF-8"><title>Alumni Directory</title></head><body>';
    echo '<h1>Alumni Directory Export</h1>';
    echo '<p>Generated on: ' . date('F j, Y g:i A') . '</p>';
    echo '<p>Total Records: ' . $users->num_rows . '</p>';
    echo '<hr>';
    
    echo '<table border="1" cellpadding="5" cellspacing="0">';
    echo '<tr style="background-color: #f0f0f0;">';
    echo '<th>Email</th><th>First Name</th><th>Last Name</th><th>Gender</th><th>Contact</th>';
    echo '<th>College</th><th>Batch Year</th><th>Current Location</th><th>Job Position</th>';
    echo '<th>Company</th><th>Qualification</th><th>University</th><th>Status</th>';
    echo '</tr>';
    
    while ($user = $users->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($user['email']) . '</td>';
        echo '<td>' . htmlspecialchars($user['first_name'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($user['last_name'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($user['gender'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($user['contact_number'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($user['college_name'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($user['batch_year'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($user['current_location'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($user['job_position'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($user['company'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($user['qualification'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($user['university'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($user['status']) . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    echo '</body></html>';
}

function exportColleges() {
    global $conn;
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="College_Statistics_' . date('Y-m-d') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $colleges = $conn->query("SELECT 
        college_name, 
        COUNT(*) as total_alumni,
        COUNT(CASE WHEN a.status = 'Approved' THEN 1 END) as approved_alumni,
        COUNT(CASE WHEN a.status = 'Pending' THEN 1 END) as pending_alumni,
        GROUP_CONCAT(DISTINCT batch_year ORDER BY batch_year DESC SEPARATOR ', ') as batch_years,
        MIN(batch_year) as earliest_batch,
        MAX(batch_year) as latest_batch,
        COUNT(DISTINCT batch_year) as batch_count,
        COUNT(CASE WHEN u.gender = 'Male' THEN 1 END) as male_count,
        COUNT(CASE WHEN u.gender = 'Female' THEN 1 END) as female_count
        FROM user_table u 
        JOIN account_table a ON u.email = a.email 
        WHERE a.type = 'user' AND college_name IS NOT NULL AND college_name != ''
        GROUP BY college_name 
        ORDER BY total_alumni DESC");
    
    echo '<html><head><meta charset="UTF-8"><title>College Statistics</title></head><body>';
    echo '<h1>College Statistics Export</h1>';
    echo '<p>Generated on: ' . date('F j, Y g:i A') . '</p>';
    echo '<p>Total Colleges: ' . $colleges->num_rows . '</p>';
    echo '<hr>';
    
    echo '<table border="1" cellpadding="5" cellspacing="0">';
    echo '<tr style="background-color: #f0f0f0;">';
    echo '<th>College Name</th><th>Total Alumni</th><th>Approved</th><th>Pending</th>';
    echo '<th>Male</th><th>Female</th><th>Batch Count</th><th>Batch Range</th><th>All Batches</th>';
    echo '</tr>';
    
    while ($college = $colleges->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($college['college_name']) . '</td>';
        echo '<td>' . number_format($college['total_alumni']) . '</td>';
        echo '<td>' . number_format($college['approved_alumni']) . '</td>';
        echo '<td>' . number_format($college['pending_alumni']) . '</td>';
        echo '<td>' . number_format($college['male_count']) . '</td>';
        echo '<td>' . number_format($college['female_count']) . '</td>';
        echo '<td>' . $college['batch_count'] . '</td>';
        echo '<td>' . $college['earliest_batch'] . ' - ' . $college['latest_batch'] . '</td>';
        echo '<td>' . htmlspecialchars($college['batch_years']) . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    echo '</body></html>';
}

function exportEvents() {
    global $conn;
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="Events_Report_' . date('Y-m-d') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $events = $conn->query("SELECT 
        e.id, e.title, e.location, e.description, e.event_date, e.type,
        COUNT(er.participant_email) as registration_count
        FROM event_table e
        LEFT JOIN event_registration_table er ON e.id = er.event_id
        GROUP BY e.id
        ORDER BY e.event_date DESC");
    
    echo '<html><head><meta charset="UTF-8"><title>Events Report</title></head><body>';
    echo '<h1>Events Report Export</h1>';
    echo '<p>Generated on: ' . date('F j, Y g:i A') . '</p>';
    echo '<p>Total Events: ' . $events->num_rows . '</p>';
    echo '<hr>';
    
    echo '<table border="1" cellpadding="5" cellspacing="0">';
    echo '<tr style="background-color: #f0f0f0;">';
    echo '<th>Event ID</th><th>Title</th><th>Type</th><th>Date</th><th>Location</th>';
    echo '<th>Registrations</th><th>Status</th><th>Description</th>';
    echo '</tr>';
    
    while ($event = $events->fetch_assoc()) {
        $status = (strtotime($event['event_date']) >= strtotime('today')) ? 'Upcoming' : 'Past';
        echo '<tr>';
        echo '<td>' . $event['id'] . '</td>';
        echo '<td>' . htmlspecialchars($event['title']) . '</td>';
        echo '<td>' . htmlspecialchars($event['type']) . '</td>';
        echo '<td>' . date('M j, Y', strtotime($event['event_date'])) . '</td>';
        echo '<td>' . htmlspecialchars($event['location']) . '</td>';
        echo '<td>' . number_format($event['registration_count']) . '</td>';
        echo '<td>' . $status . '</td>';
        echo '<td>' . htmlspecialchars(substr($event['description'], 0, 100)) . '...</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    echo '</body></html>';
}
?>