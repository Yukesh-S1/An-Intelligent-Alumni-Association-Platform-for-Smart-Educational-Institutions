<?php
/**
 * Cleanup Old Notifications Script
 * This script automatically marks old video calls as expired
 * Run this periodically or include it in your cron jobs
 */

include 'db_controller.php';
$conn->select_db("Alumni");

try {
    // Mark video calls older than 10 minutes as 'expired' if still pending
    $update_old_calls = "UPDATE video_call_table 
                        SET call_status = 'expired', 
                            call_end_time = NOW() 
                        WHERE call_status = 'pending' 
                        AND created_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE)";
    
    $result = $conn->query($update_old_calls);
    
    if ($result) {
        $affected_rows = $conn->affected_rows;
        echo json_encode([
            'success' => true,
            'message' => "Cleaned up $affected_rows old video call notifications",
            'affected_rows' => $affected_rows
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to cleanup old notifications'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?>
