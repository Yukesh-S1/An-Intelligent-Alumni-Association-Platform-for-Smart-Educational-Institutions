<?php
// Create feedback table in database
include 'db_controller.php';
$conn->select_db("Alumni");

try {
    // Create feedback_table
    $sql = "CREATE TABLE IF NOT EXISTS feedback_table (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        alumni_email VARCHAR(50) NOT NULL,
        feedback_type VARCHAR(50) NOT NULL,
        subject VARCHAR(200) NOT NULL,
        feedback_text TEXT NOT NULL,
        rating INT(1) DEFAULT NULL,
        category VARCHAR(100) DEFAULT NULL,
        status VARCHAR(20) DEFAULT 'pending',
        admin_response TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (alumni_email) REFERENCES user_table(email) ON DELETE CASCADE ON UPDATE CASCADE
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Feedback table created successfully!<br>";
        
        // Check existing users first
        $existing_users = $conn->query("SELECT email FROM user_table LIMIT 4");
        if ($existing_users && $existing_users->num_rows > 0) {
            $users = [];
            while ($user = $existing_users->fetch_assoc()) {
                $users[] = $user['email'];
            }
            
            if (count($users) >= 4) {
                // Insert sample feedback data with existing user emails
                $sample_data = "INSERT INTO feedback_table (alumni_email, feedback_type, subject, feedback_text, rating, category, status) VALUES
                    ('{$users[0]}', 'college', 'Great Experience', 'I had an amazing experience at the college. The faculty was very supportive and the infrastructure was excellent.', 5, 'Academic', 'pending'),
                    ('{$users[1]}', 'college', 'Improvement Needed', 'The library facilities need improvement. More books and better study spaces would be helpful.', 3, 'Infrastructure', 'pending'),
                    ('{$users[2]}', 'college', 'Career Services', 'The career services department helped me a lot in finding my current job. Very grateful!', 5, 'Career Services', 'reviewed'),
                    ('{$users[3]}', 'college', 'Campus Life', 'Campus life was vibrant with many activities and events. Made great memories here.', 4, 'Campus Life', 'pending')";
            } else {
                $sample_data = ""; // Skip sample data if not enough users
            }
        } else {
            $sample_data = ""; // Skip sample data if no users found
        }
        
        if (!empty($sample_data)) {
            if ($conn->query($sample_data) === TRUE) {
                echo "Sample feedback data inserted successfully!<br>";
            } else {
                echo "Error inserting sample data: " . $conn->error . "<br>";
            }
        } else {
            echo "Skipped sample data insertion (no existing users found).<br>";
        }
        
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

$conn->close();
echo "<br><a href='feedback_form.php'>Go to Feedback Form</a><br>";
echo "<a href='manage_feedback.php'>Go to Admin Feedback Management</a>";
?>