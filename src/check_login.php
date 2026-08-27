<?php
session_start();

echo "<h2>Login Status Check</h2>";

if (!isset($_SESSION['logged_account'])) {
    echo "<div style='color: red;'>❌ You are not logged in!</div>";
    echo "<p><a href='login.php'>Please log in here</a></p>";
} else {
    echo "<div style='color: green;'>✅ You are logged in!</div>";
    echo "<p><strong>Email:</strong> " . ($_SESSION['logged_account']['email'] ?? 'Not set') . "</p>";
    echo "<p><strong>Type:</strong> " . ($_SESSION['logged_account']['type'] ?? 'Not set') . "</p>";
    echo "<p><strong>Status:</strong> " . ($_SESSION['logged_account']['status'] ?? 'Not set') . "</p>";
}

echo "<h3>All Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?>