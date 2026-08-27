<?php
session_start();
include 'db_controller.php';
$conn->select_db("Alumni");

// Check if user is logged in and is admin
if (!isset($_SESSION['logged_account']) || $_SESSION['logged_account']['type'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Get all video calls
$query = "SELECT vc.*, 
          u1.first_name as caller_first, u1.last_name as caller_last,
          u2.first_name as callee_first, u2.last_name as callee_last
          FROM video_call_table vc
          LEFT JOIN user_table u1 ON vc.caller_email = u1.email
          LEFT JOIN user_table u2 ON vc.callee_email = u2.email
          ORDER BY vc.created_at DESC
          LIMIT 50";

$result = $conn->query($query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Video Calls | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
    <?php include 'components/admin_nav.php'; ?>
    
    <div class="container mt-4">
        <h1 class="mb-4">📹 Manage Video Calls</h1>
        
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">All Video Calls</h5>
                    <a href="test_call.php" class="btn btn-primary">
                        <i class="bi bi-telephone-plus"></i> Make Test Call
                    </a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Caller</th>
                                <th>Callee</th>
                                <th>Status</th>
                                <th>Started</th>
                                <th>Duration</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($call = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $call['id']; ?></td>
                                        <td><?php echo htmlspecialchars($call['caller_first'] . ' ' . $call['caller_last']); ?></td>
                                        <td><?php echo htmlspecialchars($call['callee_first'] . ' ' . $call['callee_last']); ?></td>
                                        <td>
                                            <?php
                                            $statusClass = [
                                                'active' => 'success',
                                                'ended' => 'secondary',
                                                'pending' => 'warning',
                                                'declined' => 'danger'
                                            ];
                                            $class = $statusClass[$call['call_status']] ?? 'info';
                                            ?>
                                            <span class="badge bg-<?php echo $class; ?>">
                                                <?php echo ucfirst($call['call_status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $call['created_at'] ? date('M j, Y g:i A', strtotime($call['created_at'])) : 'N/A'; ?></td>
                                        <td>
                                            <?php
                                            if ($call['call_start_time'] && $call['call_end_time']) {
                                                $start = strtotime($call['call_start_time']);
                                                $end = strtotime($call['call_end_time']);
                                                $duration = $end - $start;
                                                $minutes = floor($duration / 60);
                                                $seconds = $duration % 60;
                                                echo sprintf('%d:%02d', $minutes, $seconds);
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($call['call_status'] == 'active'): ?>
                                                <a href="call.php?room=<?php echo $call['room_id']; ?>&call_id=<?php echo $call['id']; ?>" 
                                                   class="btn btn-sm btn-success" target="_blank">
                                                    <i class="bi bi-box-arrow-in-right"></i> Join
                                                </a>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-danger" onclick="deleteCall(<?php echo $call['id']; ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No video calls found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteCall(id) {
            if (confirm('Delete this call record?')) {
                fetch('delete_call.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ call_id: id })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to delete call');
                    }
                });
            }
        }
    </script>
</body>
</html>
