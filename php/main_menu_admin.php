<?php
session_start();
include 'logged_admin.php';
include 'db_controller.php';

$conn->select_db("Alumni");

// Get comprehensive admin statistics
$stats = [];

// User statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM user_table u JOIN account_table a ON u.email = a.email WHERE a.type = 'user'")->fetch_assoc()['count'];
$approved_users = $conn->query("SELECT COUNT(*) as count FROM user_table u JOIN account_table a ON u.email = a.email WHERE a.type = 'user' AND a.status = 'Approved'")->fetch_assoc()['count'];
$pending_users = $conn->query("SELECT COUNT(*) as count FROM user_table u JOIN account_table a ON u.email = a.email WHERE a.type = 'user' AND a.status = 'Pending'")->fetch_assoc()['count'];

// Event statistics
$total_events = $conn->query("SELECT COUNT(*) as count FROM event_table")->fetch_assoc()['count'];
$upcoming_events = $conn->query("SELECT COUNT(*) as count FROM event_table WHERE event_date >= CURDATE()")->fetch_assoc()['count'];

// News statistics
$total_news = $conn->query("SELECT COUNT(*) as count FROM event_table WHERE type = 'News'")->fetch_assoc()['count'];
$active_news = $conn->query("SELECT COUNT(*) as count FROM event_table WHERE type = 'News' AND event_date >= CURDATE()")->fetch_assoc()['count'];

// Message statistics
$total_messages = $conn->query("SELECT COUNT(*) as count FROM chat_messages_table")->fetch_assoc()['count'];
$total_conversations = $conn->query("SELECT COUNT(*) as count FROM chat_conversations_table")->fetch_assoc()['count'];

// Feedback statistics
$total_feedback = $conn->query("SELECT COUNT(*) as count FROM feedback_table")->fetch_assoc()['count'];
$pending_feedback = $conn->query("SELECT COUNT(*) as count FROM feedback_table WHERE status = 'pending'")->fetch_assoc()['count'];

// Recent activities
$recent_registrations = $conn->query("SELECT u.*, a.status FROM user_table u JOIN account_table a ON u.email = a.email WHERE a.type = 'user' ORDER BY u.email DESC LIMIT 5");
$recent_events = $conn->query("SELECT * FROM event_table ORDER BY event_date DESC LIMIT 5");
$recent_news = $conn->query("SELECT * FROM event_table WHERE type = 'News' ORDER BY event_date DESC LIMIT 5");

// Monthly user registration chart data
$monthly_data = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $count = $conn->query("SELECT COUNT(*) as count FROM user_table u JOIN account_table a ON u.email = a.email WHERE a.type = 'user' AND DATE_FORMAT(u.email, '%Y-%m') = '$month'")->fetch_assoc()['count'];
    $monthly_data[] = ['month' => date('M Y', strtotime("-$i months")), 'count' => $count];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Alumni Portal</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Modern Design System -->

    <script>
        // Registration Chart
        const ctx = document.getElementById('registrationChart').getContext('2d');
        const registrationChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($monthly_data, 'month')); ?>,
                datasets: [{
                    label: 'New Registrations',
                    data: <?php echo json_encode(array_column($monthly_data, 'count')); ?>,
                    borderColor: 'rgb(14, 165, 233)',
                    backgroundColor: 'rgba(14, 165, 233, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Dashboard functions
        function exportData() {
            // Implement data export functionality
            alert('Export functionality will be implemented');
        }

        function refreshDashboard() {
            location.reload();
        }

        // Add animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.animate-fade-in-up').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            observer.observe(el);
        });
    </script>
</body>
</html>