<?php
// Database setup script - creates database without requiring it to exist first
$servername = "localhost";
$username = "root";
$password = "";
$port = 3306;

echo "<h2>Database Setup</h2>";
echo "<p>Connecting to MySQL server...</p>";

// Create connection WITHOUT specifying database
try {
    $conn = new mysqli($servername, $username, $password, null, $port);
    
    if ($conn->connect_error) {
        die("<div style='color: red;'>Connection failed: " . $conn->connect_error . "</div>");
    }
    
    echo "<p style='color: green;'>✓ Connected to MySQL server successfully!</p>";
    
    // Create database
    echo "<p>Creating 'Alumni' database...</p>";
    $sql = "CREATE DATABASE IF NOT EXISTS Alumni";
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✓ Database 'Alumni' created successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error creating database: " . $conn->error . "</p>";
    }
    
    // Select the database
    $conn->select_db("Alumni");
    echo "<p style='color: green;'>✓ Database selected!</p>";
    
    // Now run the init_db.php to create tables
    echo "<hr>";
    echo "<h3>Creating tables and populating data...</h3>";
    
    $conn->close();
    
    // Include the init script
    include 'init_db.php';
    
    echo "<hr>";
    echo "<h2 style='color: green;'>✓ Setup Complete!</h2>";
    echo "<p><a href='index.php'>Go to Home Page</a></p>";
    echo "<p><a href='login.php'>Go to Login Page</a></p>";
    
} catch (Exception $e) {
    echo "<div style='color: red;'>Error: " . $e->getMessage() . "</div>";
}
?>
