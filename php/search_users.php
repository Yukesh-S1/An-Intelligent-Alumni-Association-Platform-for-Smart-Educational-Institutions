<?php
session_start();
include 'db_controller.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_account']['email']) || !isset($_GET['q'])) {
    echo json_encode([]);
    exit;
}

$query = $conn->real_escape_string($_GET['q']);
$current_user = $_SESSION['logged_account']['email'];

// Search users by name or email, excluding current user
$search_query = "SELECT email, first_name, last_name, profile_image, college_name, batch_year 
                FROM user_table 
                WHERE email != '$current_user' 
                AND (first_name LIKE '%$query%' 
                     OR last_name LIKE '%$query%' 
                     OR email LIKE '%$query%'
                     OR college_name LIKE '%$query%')
                ORDER BY first_name, last_name
                LIMIT 20";

$result = $conn->query($search_query);
$users = [];

while ($user = $result->fetch_assoc()) {
    $users[] = $user;
}

echo json_encode($users);
?>
