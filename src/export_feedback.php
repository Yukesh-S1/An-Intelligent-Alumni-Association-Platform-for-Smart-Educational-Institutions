<?php
session_start();
include 'db_controller.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['logged_account']) || $_SESSION['logged_account']['type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$conn->select_db("Alumni");

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="feedback_report_' . date('Y-m-d') . '.csv"');

// Create file pointer
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, [
    'ID',
    'Alumni Name',
    'Alumni Email',
    'Feedback Type',
    'Category',
    'Subject',
    'Feedback Text',
    'Rating',
    'Status',
    'Admin Response',
    'Submitted Date',
    'Last Updated'
]);

try {
    // Get all feedback data
    $query = "SELECT f.*, u.first_name, u.last_name 
              FROM feedback_table f 
              LEFT JOIN user_table u ON f.alumni_email = u.email 
              ORDER BY f.created_at DESC";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['id'],
                $row['first_name'] . ' ' . $row['last_name'],
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
    
} catch (Exception $e) {
    // If error, output error message
    fputcsv($output, ['Error', $e->getMessage()]);
}

fclose($output);
$conn->close();
?>