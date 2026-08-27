<?php
include 'db_controller.php';
$conn->select_db("Alumni");

// Create signaling_messages table for WebRTC communication
$sql = "CREATE TABLE IF NOT EXISTS signaling_messages (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    room_id VARCHAR(100) NOT NULL,
    sender_email VARCHAR(50) NOT NULL,
    message_type VARCHAR(20) NOT NULL,
    message_data TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_email) REFERENCES user_table(email) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_room_id (room_id),
    INDEX idx_created_at (created_at)
)";

if ($conn->query($sql) === TRUE) {
    echo "Signaling messages table created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
