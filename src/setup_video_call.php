<?php
include 'db_controller.php';
$conn->select_db("Alumni");

$message = "";
$success = false;

try {
    // Create signaling_messages table for WebRTC communication
    $sql = "CREATE TABLE IF NOT EXISTS signaling_messages (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        room_id VARCHAR(100) NOT NULL,
        sender_email VARCHAR(50) NOT NULL,
        message_type VARCHAR(20) NOT NULL,
        message_data TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (sender_email) REFERENCES user_table(email) ON DELETE CASCADE ON UPDATE CASCADE,
        INDEX idx_room_id (room_id),
        INDEX idx_created_at (created_at)
    )";

    if ($conn->query($sql) === TRUE) {
        $message = "Signaling messages table created successfully!";
        $success = true;
    } else {
        $message = "Error creating table: " . $conn->error;
    }
} catch (Exception $e) {
    $message = "Error: " . $e->getMessage();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Call Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Video Call Setup</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert <?php echo $success ? 'alert-success' : 'alert-danger'; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                        
                        <?php if ($success): ?>
                            <p>The video call system is now ready to use!</p>
                            <div class="d-grid gap-2">
                                <a href="main_menu.php" class="btn btn-primary">Go to Main Menu</a>
                                <a href="main_menu_admin.php" class="btn btn-secondary">Go to Admin Menu</a>
                            </div>
                        <?php else: ?>
                            <p>Please ensure your database is properly configured and try again.</p>
                            <a href="index.php" class="btn btn-primary">Go Back</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>