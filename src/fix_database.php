<?php
// Direct database fix
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Alumni";
$port = 3306;

echo "<h2>Database Fix Script</h2>";

try {
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    
    if ($conn->connect_error) {
        die("<p style='color: red;'>Connection failed: " . $conn->connect_error . "</p>");
    }
    
    echo "<p style='color: green;'>✓ Connected to database</p>";
    
    // Drop and recreate feedback_table
    echo "<p>Creating feedback_table...</p>";
    
    $conn->query("DROP TABLE IF EXISTS feedback_table");
    
    $sql = "CREATE TABLE feedback_table (
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
        echo "<p style='color: green;'>✓ feedback_table created!</p>";
    } else {
        echo "<p style='color: red;'>Error: " . $conn->error . "</p>";
    }
    
    // Insert sample feedback
    echo "<p>Adding sample feedback...</p>";
    $sql = "INSERT INTO feedback_table (user_email, feedback_type, subject, message, status) VALUES
        ('user0@test.com', 'suggestion', 'Great Portal!', 'The alumni portal is very useful. Keep up the good work!', 'pending'),
        ('user1@test.com', 'bug', 'Login Issue', 'Sometimes the login page takes too long to load.', 'pending'),
        ('user2@test.com', 'feature', 'Mobile App Request', 'Please create a mobile app for easier access.', 'responded')";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✓ Sample feedback added!</p>";
    } else {
        echo "<p style='color: orange;'>Note: " . $conn->error . "</p>";
    }
    
    // Verify table exists
    $result = $conn->query("SHOW TABLES LIKE 'feedback_table'");
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>✓ Verified: feedback_table exists!</p>";
        
        // Count records
        $result = $conn->query("SELECT COUNT(*) as count FROM feedback_table");
        $row = $result->fetch_assoc();
        echo "<p style='color: blue;'>ℹ Total feedback records: " . $row['count'] . "</p>";
    }
    
    echo "<hr>";
    echo "<h3 style='color: green;'>✓ Database Fixed!</h3>";
    echo "<p><a href='admin_dashboard.php'>Go to Admin Dashboard</a></p>";
    echo "<p><a href='manage_feedback.php'>Go to Manage Feedback</a></p>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
