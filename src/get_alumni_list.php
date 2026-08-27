<?php
session_start();
include 'db_controller.php';

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['logged_account']) || $_SESSION['logged_account']['type'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Admin access required']);
    exit();
}

$conn->select_db("Alumni");

try {
    // Get all approved alumni (non-admin users)
    $query = "SELECT u.email, u.first_name, u.last_name, u.profile_image, u.college_name, u.company, u.job_position, u.hometown, u.batch_year
              FROM user_table u 
              JOIN account_table a ON u.email = a.email 
              WHERE a.status = 'Approved' 
              AND a.type = 'user'
              ORDER BY u.first_name ASC, u.last_name ASC";
    
    $result = $conn->query($query);
    
    if ($result) {
        $alumni = [];
        while ($row = $result->fetch_assoc()) {
            $alumni[] = [
                'email' => $row['email'],
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'profile_image' => $row['profile_image'],
                'college_name' => $row['college_name'],
                'company' => $row['company'],
                'job_position' => $row['job_position'],
                'hometown' => $row['hometown'],
                'batch_year' => $row['batch_year']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'alumni' => $alumni,
            'count' => count($alumni)
        ]);
        
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Database query failed: ' . $conn->error]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
?>