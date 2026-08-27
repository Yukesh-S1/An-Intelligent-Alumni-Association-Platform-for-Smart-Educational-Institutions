<?php
include 'db_controller.php';
$conn->select_db("Alumni");

echo "<h2>Creating Video Call Tables</h2>";

// Create video_call_notifications_table
$sql1 = "CREATE TABLE IF NOT EXISTS video_call_notifications_table (
    id INT AUTO_INCREMENT PRIMARY KEY,
    call_id INT NOT NULL,
    recipient_email VARCHAR(255) NOT NULL,
    notification_type VARCHAR(50) NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_recipient (recipient_email),
    INDEX idx_call (call_id),
    FOREIGN KEY (call_id) REFERENCES video_call_table(id) ON DELETE CASCADE
)";

if ($conn->query($sql1) === TRUE) {
    echo "<p>✓ video_call_notifications_table created/verified</p>";
} else {
    echo "<p>✗ Error: " . $conn->error . "</p>";
}

// Ensure notifications_table exists
$sql2 = "CREATE TABLE IF NOT EXISTS notifications_table (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    related_id INT NULL,
    related_email VARCHAR(255) NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_email),
    INDEX idx_read (is_read),
    INDEX idx_created (created_at)
)";

if ($conn->query($sql2) === TRUE) {
    echo "<p>✓ notifications_table created/verified</p>";
} else {
    echo "<p>✗ Error: " . $conn->error . "</p>";
}

echo "<h3>Done!</h3>";
echo "<p><a href='video_calls_dashboard.php'>Go to Video Calls Dashboard</a></p>";

$conn->close();
?>
