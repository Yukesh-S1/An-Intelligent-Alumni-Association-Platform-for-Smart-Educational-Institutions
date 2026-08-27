<?php
session_start();
include 'db_controller.php';

header('Content-Type: application/json');

// Check database connection
if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// Select database
$conn->select_db("Alumni");

if (!isset($_SESSION['logged_account']['email'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['emails']) || !is_array($input['emails'])) {
    echo json_encode(['success' => false, 'error' => 'Missing emails parameter']);
    exit;
}

$emails = $input['emails'];
$current_user = $_SESSION['logged_account']['email'];

// Sanitize emails and create placeholders
$email_placeholders = [];
$sanitized_emails = [];
foreach ($emails as $email) {
    $sanitized_email = $conn->real_escape_string($email);
    $sanitized_emails[] = $sanitized_email;
    $email_placeholders[] = "'$sanitized_email'";
}

if (empty($email_placeholders)) {
    echo json_encode(['success' => true, 'users' => []]);
    exit;
}

$emails_list = implode(',', $email_placeholders);

// Get online status for specified users
$status_query = "SELECT email, last_activity,
    CASE WHEN last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN 1 ELSE 0 END as is_online
    FROM user_table 
    WHERE email IN ($emails_list)
    ORDER BY FIELD(email, " . $emails_list . ")";

$result = $conn->query($status_query);

if (!$result) {
    echo json_encode(['success' => false, 'error' => 'Query failed: ' . $conn->error]);
    exit;
}

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = [
        'email' => $row['email'],
        'is_online' => (bool)$row['is_online'],
        'last_activity' => $row['last_activity']
    ];
}

echo json_encode(['success' => true, 'users' => $users]);
?>
