<?php
include 'db_controller.php';

$conn->select_db("Alumni");

// Add columns for file attachments
$alter_queries = [
    "ALTER TABLE chat_messages_table ADD COLUMN message_type ENUM('text', 'image', 'video', 'file') DEFAULT 'text'",
    "ALTER TABLE chat_messages_table ADD COLUMN file_name VARCHAR(255) NULL",
    "ALTER TABLE chat_messages_table ADD COLUMN file_path VARCHAR(500) NULL", 
    "ALTER TABLE chat_messages_table ADD COLUMN file_size INT NULL"
];

echo "Adding file attachment columns to chat_messages_table...\n";

foreach ($alter_queries as $query) {
    if ($conn->query($query)) {
        echo "✓ Successfully executed: " . substr($query, 0, 50) . "...\n";
    } else {
        echo "✗ Error executing: " . substr($query, 0, 50) . "...\n";
        echo "Error: " . $conn->error . "\n";
    }
}

echo "Migration completed!\n";
?>
