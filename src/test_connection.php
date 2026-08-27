<?php
// Test database connection
echo "<h2>Testing Server Connection...</h2>";

// Include database controller
require_once 'db_controller.php';

if ($conn) {
    echo "<div style='padding: 20px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; color: #155724;'>";
    echo "<h3>✅ Connection Successful!</h3>";
    echo "<p><strong>Server:</strong> $servername</p>";
    echo "<p><strong>Database:</strong> $dbname</p>";
    echo "<p><strong>Port:</strong> $port</p>";
    
    // Test query
    $result = $conn->query("SELECT DATABASE() as db_name, VERSION() as version");
    if ($result && $row = $result->fetch_assoc()) {
        echo "<p><strong>MySQL Version:</strong> " . $row['version'] . "</p>";
        echo "<p><strong>Active Database:</strong> " . $row['db_name'] . "</p>";
    }
    
    // Check tables
    $tables = $conn->query("SHOW TABLES");
    if ($tables) {
        echo "<p><strong>Tables Found:</strong> " . $tables->num_rows . "</p>";
    }
    
    echo "</div>";
} else {
    echo "<div style='padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; color: #721c24;'>";
    echo "<h3>❌ Connection Failed</h3>";
    echo "</div>";
}
?>
