<?php
echo "PHP is working!<br>";
echo "PHP Version: " . phpversion() . "<br>";

// Test MySQL connection
$conn = new mysqli("localhost", "root", "", "", 3306);
if ($conn->connect_error) {
    echo "MySQL Connection Failed: " . $conn->connect_error;
} else {
    echo "MySQL Connection Successful!<br>";
    echo "MySQL Version: " . $conn->server_info;
}
?>
