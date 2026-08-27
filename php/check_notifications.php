<?php
session_start();
include 'db_controller.php';
$conn->select_db("Alumni");

if (!isset($_SESSION['logged_account'])) {
    die('Please login first');
}

$userEmail = $_SESSION['logged_account']['email'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Check Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <div class="container">
        <h1>Notification Checker</h1>
        <p>Logged in as: <strong><?php echo htmlspecialchars($userEmail); ?></strong></p>
        
        <hr>
        
        <h3>Video Call Notifications (video_call_notifications_table)</h3>
        <?php
        $query1 = "SELECT vcn.*, vc.caller_email, vc.room_id, vc.call_status 
                   FROM video_call_notifications_table vcn
                   LEFT JOIN video_call_table vc ON vcn.call_id = vc.id
                   WHERE vcn.recipient_email = '$userEmail'
                   ORDER BY vcn.created_at DESC
                   LIMIT 10";
        $result1 = $conn->query($query1);
        
        if ($result1 && $result1->num_rows > 0) {
            echo '<table class="table table-bordered">';
            echo '<tr><th>ID</th><th>Call ID</th><th>Caller</th><th>Type</th><th>Status</th><th>Room</th><th>Created</th></tr>';
            while ($row = $result1->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . $row['id'] . '</td>';
                echo '<td>' . $row['call_id'] . '</td>';
                echo '<td>' . htmlspecialchars($row['caller_email']) . '</td>';
                echo '<td>' . $row['notification_type'] . '</td>';
                echo '<td>' . $row['call_status'] . '</td>';
                echo '<td>' . $row['room_id'] . '</td>';
                echo '<td>' . $row['created_at'] . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<div class="alert alert-info">No video call notifications found</div>';
        }
        ?>
        
        <hr>
        
        <h3>Main Notifications (notifications_table)</h3>
        <?php
        $query2 = "SELECT * FROM notifications_table 
                   WHERE user_email = '$userEmail' AND type = 'call'
                   ORDER BY created_at DESC
                   LIMIT 10";
        $result2 = $conn->query($query2);
        
        if ($result2 && $result2->num_rows > 0) {
            echo '<table class="table table-bordered">';
            echo '<tr><th>ID</th><th>Title</th><th>Message</th><th>Related ID</th><th>From</th><th>Created</th></tr>';
            while ($row = $result2->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . $row['id'] . '</td>';
                echo '<td>' . htmlspecialchars($row['title']) . '</td>';
                echo '<td>' . htmlspecialchars($row['message']) . '</td>';
                echo '<td>' . $row['related_id'] . '</td>';
                echo '<td>' . htmlspecialchars($row['related_email']) . '</td>';
                echo '<td>' . $row['created_at'] . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<div class="alert alert-info">No call notifications found</div>';
        }
        ?>
        
        <hr>
        
        <h3>Active Calls</h3>
        <?php
        $query3 = "SELECT * FROM video_call_table 
                   WHERE (caller_email = '$userEmail' OR callee_email = '$userEmail')
                   AND call_status = 'active'
                   ORDER BY created_at DESC";
        $result3 = $conn->query($query3);
        
        if ($result3 && $result3->num_rows > 0) {
            echo '<table class="table table-bordered">';
            echo '<tr><th>ID</th><th>Caller</th><th>Callee</th><th>Room</th><th>Status</th><th>Created</th><th>Action</th></tr>';
            while ($row = $result3->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . $row['id'] . '</td>';
                echo '<td>' . htmlspecialchars($row['caller_email']) . '</td>';
                echo '<td>' . htmlspecialchars($row['callee_email']) . '</td>';
                echo '<td>' . $row['room_id'] . '</td>';
                echo '<td><span class="badge bg-success">' . $row['call_status'] . '</span></td>';
                echo '<td>' . $row['created_at'] . '</td>';
                echo '<td><a href="videocall.php?room=' . $row['room_id'] . '&call_id=' . $row['id'] . '" class="btn btn-sm btn-success">Join</a></td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<div class="alert alert-info">No active calls</div>';
        }
        ?>
        
        <hr>
        
        <div class="d-flex gap-2">
            <a href="video_calls_dashboard.php" class="btn btn-primary">Go to Dashboard</a>
            <a href="video_call_invitations.php" class="btn btn-success">View Invitations</a>
            <button onclick="location.reload()" class="btn btn-secondary">Refresh</button>
        </div>
    </div>
</body>
</html>
