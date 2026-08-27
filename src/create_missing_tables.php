<?php
// Create missing tables
require_once 'db_controller.php';

echo "<h2>Creating Missing Tables</h2>";

try {
    // Create feedback_table
    $sql = "CREATE TABLE IF NOT EXISTS feedback_table (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        user_email VARCHAR(50) NOT NULL,
        feedback_type VARCHAR(50) NOT NULL,
        subject VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        status VARCHAR(20) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        admin_response TEXT NULL,
        responded_at TIMESTAMP NULL,
        FOREIGN KEY (user_email) REFERENCES user_table(email) ON DELETE CASCADE ON UPDATE CASCADE
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✓ feedback_table created successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error creating feedback_table: " . $conn->error . "</p>";
    }
    
    // Check if signaling_table exists
    $sql = "CREATE TABLE IF NOT EXISTS signaling_table (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        room_id VARCHAR(100) NOT NULL,
        sender_email VARCHAR(50) NOT NULL,
        receiver_email VARCHAR(50) NOT NULL,
        signal_type VARCHAR(20) NOT NULL,
        signal_data TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (sender_email) REFERENCES user_table(email) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (receiver_email) REFERENCES user_table(email) ON DELETE CASCADE ON UPDATE CASCADE
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✓ signaling_table created successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error creating signaling_table: " . $conn->error . "</p>";
    }
    
    // Add file columns to messages table if not exists
    $sql = "SHOW COLUMNS FROM chat_messages_table LIKE 'file_name'";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 0) {
        $sql = "ALTER TABLE chat_messages_table 
                ADD COLUMN file_name VARCHAR(255) NULL AFTER message,
                ADD COLUMN file_path VARCHAR(500) NULL AFTER file_name,
                ADD COLUMN file_type VARCHAR(50) NULL AFTER file_path,
                ADD COLUMN file_size INT NULL AFTER file_type";
        
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color: green;'>✓ File columns added to chat_messages_table!</p>";
        } else {
            echo "<p style='color: red;'>Error adding file columns: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ File columns already exist in chat_messages_table</p>";
    }
    
    echo "<hr>";
    echo "<h3 style='color: green;'>✓ All missing tables created!</h3>";
    echo "<p><a href='index.php'>Go to Home Page</a></p>";
    echo "<p><a href='login.php'>Go to Login Page</a></p>";
    echo "<p><a href='admin_dashboard.php'>Go to Admin Dashboard</a></p>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
