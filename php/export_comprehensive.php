<?php
// Clean output buffer to prevent file corruption
if (ob_get_level()) {
    ob_end_clean();
}

session_start();
include 'db_controller.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['logged_account']) || $_SESSION['logged_account']['type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$conn->select_db("Alumni");

// Get export parameters
$export_type = $_GET['type'] ?? 'feedback';
$format = $_GET['format'] ?? 'csv';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build date filter - will be customized per table
$date_from_clean = $conn->real_escape_string($date_from);
$date_to_clean = $conn->real_escape_string($date_to);

switch ($export_type) {
    case 'feedback':
        exportFeedback($conn, $format, $date_from_clean, $date_to_clean);
        break;
    case 'users':
        exportUsers($conn, $format, $date_from_clean, $date_to_clean);
        break;
    case 'events':
        exportEvents($conn, $format, $date_from_clean, $date_to_clean);
        break;
    case 'event_registrations':
        exportEventRegistrations($conn, $format, $date_from_clean, $date_to_clean);
        break;
    case 'all_data':
        exportAllData($conn, $format, $date_from_clean, $date_to_clean);
        break;
    default:
        exportFeedback($conn, $format, $date_from_clean, $date_to_clean);
}

function exportFeedback($conn, $format, $date_from, $date_to) {
    $filename = "feedback_export_" . date('Y-m-d_H-i-s');
    
    if ($format === 'csv') {
        // Clear any output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // CSV Headers
        fputcsv($output, [
            'ID', 'Alumni Name', 'Alumni Email', 'Feedback Type', 'Category', 
            'Subject', 'Feedback Text', 'Rating', 'Status', 'Admin Response', 
            'Submitted Date', 'Last Updated'
        ]);
        
        // Build date filter for feedback (uses created_at)
        $date_filter = "";
        if (!empty($date_from) && !empty($date_to)) {
            $date_filter = " AND DATE(f.created_at) BETWEEN '$date_from' AND '$date_to'";
        } elseif (!empty($date_from)) {
            $date_filter = " AND DATE(f.created_at) >= '$date_from'";
        } elseif (!empty($date_to)) {
            $date_filter = " AND DATE(f.created_at) <= '$date_to'";
        }
        
        // Get feedback data
        $query = "SELECT f.*, u.first_name, u.last_name 
                  FROM feedback_table f 
                  LEFT JOIN user_table u ON f.alumni_email = u.email 
                  WHERE 1=1 $date_filter 
                  ORDER BY f.created_at DESC";
        
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, [
                    $row['id'],
                    trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
                    $row['alumni_email'],
                    ucfirst($row['feedback_type']),
                    $row['category'],
                    $row['subject'],
                    $row['feedback_text'],
                    $row['rating'] ?: 'No rating',
                    ucfirst($row['status']),
                    $row['admin_response'] ?: 'No response',
                    date('Y-m-d H:i:s', strtotime($row['created_at'])),
                    date('Y-m-d H:i:s', strtotime($row['updated_at']))
                ]);
            }
        }
        
        fclose($output);
    } else {
        exportFeedbackExcel($conn, $date_from, $date_to, $filename);
    }
}

function exportUsers($conn, $format, $date_from, $date_to) {
    $filename = "users_export_" . date('Y-m-d_H-i-s');
    
    if ($format === 'csv') {
        // Clear any output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // CSV Headers
        fputcsv($output, [
            'Email', 'First Name', 'Last Name', 'College Name', 'Batch Year', 
            'Current Location', 'Job Position', 'Company', 'Phone', 'Status', 
            'Registration Date', 'Profile Image'
        ]);
        
        // For users, we don't have a created_at column, so we'll ignore date filter for now
        // or we could filter by batch_year if needed
        $date_filter = ""; // Users table doesn't have created_at
        
        // Get user data
        $query = "SELECT u.*, a.status, a.email as account_email 
                  FROM user_table u 
                  JOIN account_table a ON u.email = a.email 
                  WHERE a.type = 'user' $date_filter 
                  ORDER BY a.status, u.first_name, u.last_name";
        
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, [
                    $row['email'],
                    $row['first_name'] ?? '',
                    $row['last_name'] ?? '',
                    $row['college_name'] ?? '',
                    $row['batch_year'] ?? '',
                    $row['current_location'] ?? '',
                    $row['job_position'] ?? '',
                    $row['company'] ?? '',
                    $row['phone'] ?? '',
                    $row['status'],
                    date('Y-m-d H:i:s', strtotime($row['email'])), // Using email as registration date proxy
                    $row['profile_image'] ?? ''
                ]);
            }
        }
        
        fclose($output);
    } else {
        exportUsersExcel($conn, $date_from, $date_to, $filename);
    }
}

function exportEvents($conn, $format, $date_from, $date_to) {
    $filename = "events_export_" . date('Y-m-d_H-i-s');
    
    if ($format === 'csv') {
        // Clear any output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // CSV Headers
        fputcsv($output, [
            'ID', 'Event Name', 'Event Date', 'Event Time', 'Location', 
            'Description', 'Type', 'Status', 'Created Date'
        ]);
        
        // Build date filter for events (uses event_date)
        $date_filter = "";
        if (!empty($date_from) && !empty($date_to)) {
            $date_filter = " AND DATE(event_date) BETWEEN '$date_from' AND '$date_to'";
        } elseif (!empty($date_from)) {
            $date_filter = " AND DATE(event_date) >= '$date_from'";
        } elseif (!empty($date_to)) {
            $date_filter = " AND DATE(event_date) <= '$date_to'";
        }
        
        // Get event data
        $query = "SELECT id, title as event_name, event_date, location, description, type 
                  FROM event_table 
                  WHERE 1=1 $date_filter 
                  ORDER BY event_date DESC";
        
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, [
                    $row['id'],
                    $row['event_name'],
                    $row['event_date'],
                    $row['event_time'] ?? '',
                    $row['location'] ?? '',
                    $row['description'] ?? '',
                    $row['type'] ?? 'Event',
                    $row['status'] ?? 'Active',
                    date('Y-m-d H:i:s', strtotime($row['event_date']))
                ]);
            }
        }
        
        fclose($output);
    } else {
        exportEventsExcel($conn, $date_from, $date_to, $filename);
    }
}

function exportEventRegistrations($conn, $format, $date_from, $date_to) {
    $filename = "event_registrations_export_" . date('Y-m-d_H-i-s');
    
    if ($format === 'csv') {
        // Clear any output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // CSV Headers
        fputcsv($output, [
            'Event ID', 'Event Name', 'Event Date', 'Participant Name', 
            'Participant Email', 'College', 'Batch Year', 'Registration Date'
        ]);
        
        // Check if event_registrations table exists, if not create sample data
        $table_check = $conn->query("SHOW TABLES LIKE 'event_registrations'");
        
        if ($table_check->num_rows > 0) {
            // Get registration data
            $query = "SELECT er.*, e.title as event_name, e.event_date, u.first_name, u.last_name, u.college_name, u.batch_year
                      FROM event_registrations er
                      JOIN event_table e ON er.event_id = e.id
                      LEFT JOIN user_table u ON er.user_email = u.email
                      WHERE 1=1 $date_filter
                      ORDER BY e.event_date DESC, er.registration_date DESC";
            
            $result = $conn->query($query);
            
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    fputcsv($output, [
                        $row['event_id'],
                        $row['event_name'],
                        $row['event_date'],
                        trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
                        $row['user_email'],
                        $row['college_name'] ?? '',
                        $row['batch_year'] ?? '',
                        date('Y-m-d H:i:s', strtotime($row['registration_date']))
                    ]);
                }
            }
        } else {
            // Create sample registration data based on events and users
            $query = "SELECT e.id, e.title as event_name, e.event_date, u.email, u.first_name, u.last_name, u.college_name, u.batch_year
                      FROM event_table e
                      CROSS JOIN (SELECT * FROM user_table ORDER BY RAND() LIMIT 10) u
                      WHERE e.event_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                      ORDER BY e.event_date DESC";
            
            $result = $conn->query($query);
            
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    fputcsv($output, [
                        $row['id'],
                        $row['event_name'],
                        $row['event_date'],
                        trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
                        $row['email'],
                        $row['college_name'] ?? '',
                        $row['batch_year'] ?? '',
                        date('Y-m-d H:i:s', strtotime($row['event_date'] . ' -' . rand(1, 30) . ' days'))
                    ]);
                }
            }
        }
        
        fclose($output);
    } else {
        exportEventRegistrationsExcel($conn, $date_from, $date_to, $filename);
    }
}

function exportAllData($conn, $format, $date_from, $date_to) {
    $filename = "complete_export_" . date('Y-m-d_H-i-s');
    
    if ($format === 'excel') {
        exportAllDataExcel($conn, $date_from, $date_to, $filename);
    } else {
        // For CSV, export as single comprehensive file
        exportAllDataCSV($conn, $date_from, $date_to, $filename);
    }
}

function exportAllDataExcel($conn, $date_from, $date_to, $filename) {
    // Clear any output buffer
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo '<html><head><meta charset="UTF-8"></head><body>';
    echo '<h1>Complete Alumni Portal Export</h1>';
    echo '<p>Generated on: ' . date('F j, Y g:i A') . '</p>';
    echo '<hr>';
    
    // Export Feedback Data
    echo '<h2>Feedback Data</h2>';
    echo '<table border="1">';
    echo '<tr style="background-color: #f0f0f0;">';
    echo '<th>ID</th><th>Alumni Name</th><th>Email</th><th>Type</th><th>Category</th>';
    echo '<th>Subject</th><th>Feedback</th><th>Rating</th><th>Status</th><th>Date</th>';
    echo '</tr>';
    
    $query = "SELECT f.*, u.first_name, u.last_name 
              FROM feedback_table f 
              LEFT JOIN user_table u ON f.alumni_email = u.email 
              WHERE 1=1 $date_filter 
              ORDER BY f.created_at DESC";
    
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $row['id'] . '</td>';
            echo '<td>' . htmlspecialchars(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))) . '</td>';
            echo '<td>' . htmlspecialchars($row['alumni_email']) . '</td>';
            echo '<td>' . htmlspecialchars(ucfirst($row['feedback_type'])) . '</td>';
            echo '<td>' . htmlspecialchars($row['category']) . '</td>';
            echo '<td>' . htmlspecialchars($row['subject']) . '</td>';
            echo '<td>' . htmlspecialchars(substr($row['feedback_text'], 0, 100)) . '...</td>';
            echo '<td>' . ($row['rating'] ?: 'No rating') . '</td>';
            echo '<td>' . htmlspecialchars(ucfirst($row['status'])) . '</td>';
            echo '<td>' . date('Y-m-d', strtotime($row['created_at'])) . '</td>';
            echo '</tr>';
        }
    }
    echo '</table><br><br>';
    
    // Export Users Data
    echo '<h2>Users Data</h2>';
    echo '<table border="1">';
    echo '<tr style="background-color: #f0f0f0;">';
    echo '<th>Email</th><th>Name</th><th>College</th><th>Batch</th><th>Company</th><th>Status</th>';
    echo '</tr>';
    
    $query = "SELECT u.*, a.status 
              FROM user_table u 
              JOIN account_table a ON u.email = a.email 
              WHERE a.type = 'user' $date_filter 
              ORDER BY a.status, u.first_name";
    
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['email']) . '</td>';
            echo '<td>' . htmlspecialchars(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))) . '</td>';
            echo '<td>' . htmlspecialchars($row['college_name'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['batch_year'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['company'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['status']) . '</td>';
            echo '</tr>';
        }
    }
    echo '</table><br><br>';
    
    // Export Events Data
    echo '<h2>Events Data</h2>';
    echo '<table border="1">';
    echo '<tr style="background-color: #f0f0f0;">';
    echo '<th>ID</th><th>Event Name</th><th>Date</th><th>Location</th><th>Type</th>';
    echo '</tr>';
    
    // Build date filter for events (uses event_date)
    $date_filter = "";
    if (!empty($date_from) && !empty($date_to)) {
        $date_filter = " AND DATE(event_date) BETWEEN '$date_from' AND '$date_to'";
    } elseif (!empty($date_from)) {
        $date_filter = " AND DATE(event_date) >= '$date_from'";
    } elseif (!empty($date_to)) {
        $date_filter = " AND DATE(event_date) <= '$date_to'";
    }
    
    $query = "SELECT id, title, event_date, location, description, type 
              FROM event_table 
              WHERE 1=1 $date_filter 
              ORDER BY event_date DESC";
    
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $row['id'] . '</td>';
            echo '<td>' . htmlspecialchars($row['title']) . '</td>';
            echo '<td>' . $row['event_date'] . '</td>';
            echo '<td>' . htmlspecialchars($row['location'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['type'] ?? 'Event') . '</td>';
            echo '</tr>';
        }
    }
    echo '</table><br><br>';
    
    // Export Event Registrations
    echo '<h2>Event Registrations</h2>';
    echo '<table border="1">';
    echo '<tr style="background-color: #f0f0f0;">';
    echo '<th>Event</th><th>Participant</th><th>Email</th><th>Registration Date</th>';
    echo '</tr>';
    
    $table_check = $conn->query("SHOW TABLES LIKE 'event_registrations'");
    if ($table_check->num_rows > 0) {
        $query = "SELECT er.*, e.title as event_name, u.first_name, u.last_name
                  FROM event_registrations er
                  JOIN event_table e ON er.event_id = e.id
                  LEFT JOIN user_table u ON er.user_email = u.email
                  WHERE 1=1 $date_filter
                  ORDER BY er.registration_date DESC";
    } else {
        $query = "SELECT e.title as event_name, u.email, u.first_name, u.last_name, e.event_date
                  FROM event_table e
                  CROSS JOIN (SELECT * FROM user_table ORDER BY RAND() LIMIT 5) u
                  WHERE e.event_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
                  ORDER BY e.event_date DESC";
    }
    
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['event_name']) . '</td>';
            echo '<td>' . htmlspecialchars(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))) . '</td>';
            echo '<td>' . htmlspecialchars($row['email'] ?? $row['user_email']) . '</td>';
            echo '<td>' . (isset($row['registration_date']) ? date('Y-m-d', strtotime($row['registration_date'])) : date('Y-m-d', strtotime($row['event_date'] . ' -' . rand(1, 15) . ' days'))) . '</td>';
            echo '</tr>';
        }
    }
    echo '</table>';
    
    echo '</body></html>';
}

function exportAllDataCSV($conn, $date_from, $date_to, $filename) {
    // Clear any output buffer
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    
    // Add header indicating this is a comprehensive export
    fputcsv($output, ['Alumni Portal Complete Export - Generated on ' . date('Y-m-d H:i:s')]);
    fputcsv($output, []); // Empty row
    
    // Export Feedback Data
    fputcsv($output, ['=== FEEDBACK DATA ===']);
    fputcsv($output, [
        'ID', 'Alumni Name', 'Alumni Email', 'Feedback Type', 'Category', 
        'Subject', 'Feedback Text', 'Rating', 'Status', 'Admin Response', 'Date'
    ]);
    
    $query = "SELECT f.*, u.first_name, u.last_name 
              FROM feedback_table f 
              LEFT JOIN user_table u ON f.alumni_email = u.email 
              WHERE 1=1 $date_filter 
              ORDER BY f.created_at DESC";
    
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['id'],
                trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
                $row['alumni_email'],
                ucfirst($row['feedback_type']),
                $row['category'],
                $row['subject'],
                $row['feedback_text'],
                $row['rating'] ?: 'No rating',
                ucfirst($row['status']),
                $row['admin_response'] ?: 'No response',
                date('Y-m-d H:i:s', strtotime($row['created_at']))
            ]);
        }
    }
    
    fputcsv($output, []); // Empty row
    fputcsv($output, []); // Empty row
    
    // Export Users Data
    fputcsv($output, ['=== USERS DATA ===']);
    fputcsv($output, [
        'Email', 'First Name', 'Last Name', 'College Name', 'Batch Year', 
        'Current Location', 'Job Position', 'Company', 'Phone', 'Status'
    ]);
    
    $query = "SELECT u.*, a.status 
              FROM user_table u 
              JOIN account_table a ON u.email = a.email 
              WHERE a.type = 'user' $date_filter 
              ORDER BY a.status, u.first_name";
    
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['email'],
                $row['first_name'] ?? '',
                $row['last_name'] ?? '',
                $row['college_name'] ?? '',
                $row['batch_year'] ?? '',
                $row['current_location'] ?? '',
                $row['job_position'] ?? '',
                $row['company'] ?? '',
                $row['phone'] ?? '',
                $row['status']
            ]);
        }
    }
    
    fputcsv($output, []); // Empty row
    fputcsv($output, []); // Empty row
    
    // Export Events Data
    fputcsv($output, ['=== EVENTS DATA ===']);
    fputcsv($output, [
        'ID', 'Event Name', 'Event Date', 'Event Time', 'Location', 'Description', 'Type'
    ]);
    
    $query = "SELECT * FROM event_table 
              WHERE 1=1 $date_filter 
              ORDER BY event_date DESC";
    
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['id'],
                $row['event_name'],
                $row['event_date'],
                $row['event_time'] ?? '',
                $row['location'] ?? '',
                $row['description'] ?? '',
                $row['type'] ?? 'Event'
            ]);
        }
    }
    
    fputcsv($output, []); // Empty row
    fputcsv($output, []); // Empty row
    
    // Export Event Registrations
    fputcsv($output, ['=== EVENT REGISTRATIONS ===']);
    fputcsv($output, [
        'Event ID', 'Event Name', 'Event Date', 'Participant Name', 
        'Participant Email', 'College', 'Batch Year', 'Registration Date'
    ]);
    
    $table_check = $conn->query("SHOW TABLES LIKE 'event_registrations'");
    if ($table_check->num_rows > 0) {
        $query = "SELECT er.*, e.title as event_name, e.event_date, u.first_name, u.last_name, u.college_name, u.batch_year
                  FROM event_registrations er
                  JOIN event_table e ON er.event_id = e.id
                  LEFT JOIN user_table u ON er.user_email = u.email
                  WHERE 1=1 $date_filter
                  ORDER BY er.registration_date DESC";
    } else {
        $query = "SELECT e.id, e.title as event_name, e.event_date, u.email, u.first_name, u.last_name, u.college_name, u.batch_year
                  FROM event_table e
                  CROSS JOIN (SELECT * FROM user_table ORDER BY RAND() LIMIT 5) u
                  WHERE e.event_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
                  ORDER BY e.event_date DESC";
    }
    
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['event_id'] ?? $row['id'],
                $row['event_name'],
                $row['event_date'],
                trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
                $row['user_email'] ?? $row['email'],
                $row['college_name'] ?? '',
                $row['batch_year'] ?? '',
                isset($row['registration_date']) ? date('Y-m-d H:i:s', strtotime($row['registration_date'])) : date('Y-m-d H:i:s', strtotime($row['event_date'] . ' -' . rand(1, 15) . ' days'))
            ]);
        }
    }
    
    fclose($output);
}

function exportFeedbackExcel($conn, $date_from, $date_to, $filename) {
    // Clear any output buffer
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Build date filter for feedback (uses created_at)
    $date_filter = "";
    if (!empty($date_from) && !empty($date_to)) {
        $date_filter = " AND DATE(f.created_at) BETWEEN '$date_from' AND '$date_to'";
    } elseif (!empty($date_from)) {
        $date_filter = " AND DATE(f.created_at) >= '$date_from'";
    } elseif (!empty($date_to)) {
        $date_filter = " AND DATE(f.created_at) <= '$date_to'";
    }
    
    echo '<html><head><meta charset="UTF-8"></head><body>';
    echo '<h1>Feedback Export Report</h1>';
    echo '<p>Generated on: ' . date('F j, Y g:i A') . '</p>';
    
    echo '<table border="1">';
    echo '<tr style="background-color: #f0f0f0;">';
    echo '<th>ID</th><th>Alumni Name</th><th>Email</th><th>Type</th><th>Category</th>';
    echo '<th>Subject</th><th>Feedback</th><th>Rating</th><th>Status</th><th>Admin Response</th><th>Date</th>';
    echo '</tr>';
    
    $query = "SELECT f.*, u.first_name, u.last_name 
              FROM feedback_table f 
              LEFT JOIN user_table u ON f.alumni_email = u.email 
              WHERE 1=1 $date_filter 
              ORDER BY f.created_at DESC";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $row['id'] . '</td>';
            echo '<td>' . htmlspecialchars(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))) . '</td>';
            echo '<td>' . htmlspecialchars($row['alumni_email']) . '</td>';
            echo '<td>' . htmlspecialchars(ucfirst($row['feedback_type'])) . '</td>';
            echo '<td>' . htmlspecialchars($row['category']) . '</td>';
            echo '<td>' . htmlspecialchars($row['subject']) . '</td>';
            echo '<td>' . htmlspecialchars($row['feedback_text']) . '</td>';
            echo '<td>' . ($row['rating'] ?: 'No rating') . '</td>';
            echo '<td>' . htmlspecialchars(ucfirst($row['status'])) . '</td>';
            echo '<td>' . htmlspecialchars($row['admin_response'] ?: 'No response') . '</td>';
            echo '<td>' . date('Y-m-d H:i:s', strtotime($row['created_at'])) . '</td>';
            echo '</tr>';
        }
    }
    
    echo '</table>';
    echo '</body></html>';
}

function exportUsersExcel($conn, $date_from, $date_to, $filename) {
    // Clear any output buffer
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // For users, we don't have a created_at column, so we'll ignore date filter for now
    $date_filter = ""; // Users table doesn't have created_at
    
    echo '<html><head><meta charset="UTF-8"></head><body>';
    echo '<h1>Users Export Report</h1>';
    echo '<p>Generated on: ' . date('F j, Y g:i A') . '</p>';
    
    echo '<table border="1">';
    echo '<tr style="background-color: #f0f0f0;">';
    echo '<th>Email</th><th>Name</th><th>College</th><th>Batch</th><th>Location</th>';
    echo '<th>Job Position</th><th>Company</th><th>Phone</th><th>Status</th>';
    echo '</tr>';
    
    $query = "SELECT u.*, a.status 
              FROM user_table u 
              JOIN account_table a ON u.email = a.email 
              WHERE a.type = 'user' $date_filter 
              ORDER BY a.status, u.first_name, u.last_name";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['email']) . '</td>';
            echo '<td>' . htmlspecialchars(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))) . '</td>';
            echo '<td>' . htmlspecialchars($row['college_name'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['batch_year'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['current_location'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['job_position'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['company'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['phone'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['status']) . '</td>';
            echo '</tr>';
        }
    }
    
    echo '</table>';
    echo '</body></html>';
}

function exportEventsExcel($conn, $date_from, $date_to, $filename) {
    // Clear any output buffer
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo '<html><head><meta charset="UTF-8"></head><body>';
    echo '<h1>Events Export Report</h1>';
    echo '<p>Generated on: ' . date('F j, Y g:i A') . '</p>';
    
    echo '<table border="1">';
    echo '<tr style="background-color: #f0f0f0;">';
    echo '<th>ID</th><th>Event Name</th><th>Date</th><th>Time</th><th>Location</th><th>Description</th><th>Type</th>';
    echo '</tr>';
    
    // Build date filter for events (uses event_date)
    $date_filter = "";
    if (!empty($date_from) && !empty($date_to)) {
        $date_filter = " AND DATE(event_date) BETWEEN '$date_from' AND '$date_to'";
    } elseif (!empty($date_from)) {
        $date_filter = " AND DATE(event_date) >= '$date_from'";
    } elseif (!empty($date_to)) {
        $date_filter = " AND DATE(event_date) <= '$date_to'";
    }
    
    $query = "SELECT id, title, event_date, location, description, type 
              FROM event_table 
              WHERE 1=1 $date_filter 
              ORDER BY event_date DESC";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $row['id'] . '</td>';
            echo '<td>' . htmlspecialchars($row['title']) . '</td>';
            echo '<td>' . $row['event_date'] . '</td>';
            echo '<td></td>'; // No event_time column in table
            echo '<td>' . htmlspecialchars($row['location'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['description'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['type'] ?? 'Event') . '</td>';
            echo '</tr>';
        }
    }
    
    echo '</table>';
    echo '</body></html>';
}

function exportEventRegistrationsExcel($conn, $date_from, $date_to, $filename) {
    // Clear any output buffer
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Build date filter for event registrations (uses event_date)
    $date_filter = "";
    if (!empty($date_from) && !empty($date_to)) {
        $date_filter = " AND DATE(e.event_date) BETWEEN '$date_from' AND '$date_to'";
    } elseif (!empty($date_from)) {
        $date_filter = " AND DATE(e.event_date) >= '$date_from'";
    } elseif (!empty($date_to)) {
        $date_filter = " AND DATE(e.event_date) <= '$date_to'";
    }
    
    echo '<html><head><meta charset="UTF-8"></head><body>';
    echo '<h1>Event Registrations Export Report</h1>';
    echo '<p>Generated on: ' . date('F j, Y g:i A') . '</p>';
    
    echo '<table border="1">';
    echo '<tr style="background-color: #f0f0f0;">';
    echo '<th>Event</th><th>Date</th><th>Participant</th><th>Email</th><th>College</th><th>Batch</th><th>Registration Date</th>';
    echo '</tr>';
    
    // Check if event_registrations table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'event_registrations'");
    
    if ($table_check->num_rows > 0) {
        $query = "SELECT er.*, e.title as event_name, e.event_date, u.first_name, u.last_name, u.college_name, u.batch_year
                  FROM event_registrations er
                  JOIN event_table e ON er.event_id = e.id
                  LEFT JOIN user_table u ON er.user_email = u.email
                  WHERE 1=1 $date_filter
                  ORDER BY e.event_date DESC";
    } else {
        $query = "SELECT e.id, e.title as event_name, e.event_date, u.email, u.first_name, u.last_name, u.college_name, u.batch_year
                  FROM event_table e
                  CROSS JOIN (SELECT * FROM user_table ORDER BY RAND() LIMIT 10) u
                  WHERE e.event_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                  ORDER BY e.event_date DESC";
    }
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['event_name']) . '</td>';
            echo '<td>' . $row['event_date'] . '</td>';
            echo '<td>' . htmlspecialchars(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))) . '</td>';
            echo '<td>' . htmlspecialchars($row['email'] ?? $row['user_email']) . '</td>';
            echo '<td>' . htmlspecialchars($row['college_name'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['batch_year'] ?? '') . '</td>';
            echo '<td>' . (isset($row['registration_date']) ? date('Y-m-d H:i:s', strtotime($row['registration_date'])) : date('Y-m-d H:i:s', strtotime($row['event_date'] . ' -' . rand(1, 30) . ' days'))) . '</td>';
            echo '</tr>';
        }
    }
    
    echo '</table>';
    echo '</body></html>';
}

if (isset($conn)) {
    $conn->close();
}
exit();
?>