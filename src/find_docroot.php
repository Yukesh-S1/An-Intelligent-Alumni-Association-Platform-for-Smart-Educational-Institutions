<?php
// This will show us where Apache is actually serving files from
echo "<!DOCTYPE html><html><head><title>Server Info</title></head><body>";
echo "<h1>Apache Configuration Info</h1>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Current File Location:</strong> " . __FILE__ . "</p>";
echo "<p><strong>Current Directory:</strong> " . __DIR__ . "</p>";
echo "<p><strong>Script Filename:</strong> " . $_SERVER['SCRIPT_FILENAME'] . "</p>";
echo "<p><strong>Server Name:</strong> " . $_SERVER['SERVER_NAME'] . "</p>";
echo "<p><strong>Server Port:</strong> " . $_SERVER['SERVER_PORT'] . "</p>";
echo "<p><strong>Request URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";

// List files in current directory
echo "<h2>Files in current directory:</h2><ul>";
$files = scandir(__DIR__);
foreach($files as $file) {
    if($file != '.' && $file != '..') {
        echo "<li>$file</li>";
    }
}
echo "</ul>";

echo "</body></html>";
?>
