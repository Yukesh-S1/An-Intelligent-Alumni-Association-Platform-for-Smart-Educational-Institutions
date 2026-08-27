<?php
    // Database configuration
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "Alumni";
    $port = 3306; // MySQL port (changed from 3307)

    // Create connection with error handling
    try {
        $conn = new mysqli($servername, $username, $password, $dbname, $port);
        
        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Set charset to ensure proper encoding
        $conn->set_charset("utf8mb4");
        
    } catch (Exception $e) {
        // Log the detailed error
        error_log("Database connection failed: " . $e->getMessage());
        
        // Check if it's a connection refused error
        if (strpos($e->getMessage(), 'refused') !== false) {
            die("
            <div style='padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px; color: #721c24; font-family: Arial, sans-serif;'>
                <h3>🚫 MySQL Server Not Running</h3>
                <p><strong>The database server is not started in XAMPP.</strong></p>
                <div style='background: #fff3cd; padding: 15px; border-radius: 4px; margin: 10px 0;'>
                    <h4>Quick Fix Steps:</h4>
                    <ol>
                        <li><strong>Open XAMPP Control Panel</strong> (Run as Administrator)</li>
                        <li><strong>Click 'Start'</strong> next to MySQL</li>
                        <li><strong>Wait for 'Running' status</strong> (green background)</li>
                        <li><strong>Refresh this page</strong></li>
                    </ol>
                </div>
                <p><small><strong>Technical Error:</strong> " . htmlspecialchars($e->getMessage()) . "</small></p>
                <p><small><strong>Location:</strong> XAMPP Control Panel → MySQL → Start</small></p>
            </div>
            ");
        } else {
            die("
            <div style='padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px; color: #721c24; font-family: Arial, sans-serif;'>
                <h3>Database Connection Error</h3>
                <p>Unable to connect to the database server.</p>
                <p><small><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</small></p>
            </div>
            ");
        }
    }

    // Set timezone if needed
    date_default_timezone_set('Asia/Kolkata');
?>