<?php
session_start();
include 'db_controller.php';
$conn->select_db("Alumni");

// Check if user is logged in
if (!isset($_SESSION['logged_account']) || empty($_SESSION['logged_account'])) {
    header('Location: login.php');
    exit();
}

$currentUser = $_SESSION['logged_account'];
$isAdmin = ($currentUser['type'] === 'admin');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_feedback'])) {
    $feedback_type = $conn->real_escape_string($_POST['feedback_type']);
    $subject = $conn->real_escape_string($_POST['subject']);
    $feedback_text = $conn->real_escape_string($_POST['feedback_text']);
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : null;
    $category = $conn->real_escape_string($_POST['category']);
    $alumni_email = $currentUser['email'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO feedback_table (alumni_email, feedback_type, subject, feedback_text, rating, category, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
        $stmt->bind_param("ssssis", $alumni_email, $feedback_type, $subject, $feedback_text, $rating, $category);
        
        if ($stmt->execute()) {
            $success_message = "Thank you! Your feedback has been submitted successfully.";
        } else {
            $error_message = "Failed to submit feedback. Please try again.";
        }
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Get user's previous feedback
$previous_feedback = [];
try {
    $stmt = $conn->prepare("SELECT * FROM feedback_table WHERE alumni_email = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->bind_param("s", $currentUser['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $previous_feedback[] = $row;
    }
} catch (Exception $e) {
    // Handle error silently
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback System | Alumni Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }
        .container {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }
        .card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
            border-radius: 10px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            transform: translateY(-1px);
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
        }
        .form-control:focus, .form-select:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 0.2rem rgba(245, 158, 11, 0.25);
        }
        .rating-stars {
            display: flex;
            gap: 5px;
        }
        .rating-stars input[type="radio"] {
            display: none;
        }
        .rating-stars label {
            font-size: 1.5rem;
            color: #e2e8f0;
            cursor: pointer;
        }
        .rating-stars input[type="radio"]:checked ~ label,
        .rating-stars label:hover {
            color: #f59e0b;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-resolved { background: #d1fae5; color: #065f46; }
        .status-in_progress { background: #dbeafe; color: #1e40af; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?php echo $isAdmin ? 'admin_dashboard.php' : 'main_menu.php'; ?>">
                <i class="bi bi-mortarboard text-primary"></i>
                Alumni Portal
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $isAdmin ? 'admin_dashboard.php' : 'main_menu.php'; ?>">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                    </li>
                    <?php if (!$isAdmin): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="view_events.php">
                            <i class="bi bi-calendar-event"></i> Events
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_alumni.php">
                            <i class="bi bi-people"></i> Alumni
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="messages.php">
                            <i class="bi bi-chat-dots"></i> Messages
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link active" href="feedback_form.php">
                            <i class="bi bi-chat-square-text"></i> Feedback
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($currentUser['email']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile_detail.php"><i class="bi bi-person"></i> My Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Welcome Header -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8 text-center text-md-start">
                        <h1 class="display-5 fw-bold text-primary mb-3">
                            <i class="bi bi-chat-square-text me-2"></i>
                            Feedback System
                        </h1>
                        <p class="lead text-muted">
                            Share your thoughts, suggestions, and experiences to help us improve the alumni portal.
                        </p>
                    </div>
                    <div class="col-md-4 text-center">
                        <?php include 'components/alumni_logo.php'; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Feedback Form -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">
                            <i class="bi bi-pencil-square text-primary me-2"></i>
                            Submit Feedback
                        </h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="feedback_type" class="form-label fw-semibold">Feedback Type</label>
                                    <select class="form-select" id="feedback_type" name="feedback_type" required>
                                        <option value="">Select feedback type</option>
                                        <option value="college">About College</option>
                                        <option value="portal">About Portal</option>
                                        <option value="suggestion">Suggestion</option>
                                        <option value="complaint">Complaint</option>
                                        <option value="appreciation">Appreciation</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="category" class="form-label fw-semibold">Category</label>
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="">Select category</option>
                                        <option value="academic">Academic</option>
                                        <option value="infrastructure">Infrastructure</option>
                                        <option value="career_services">Career Services</option>
                                        <option value="campus_life">Campus Life</option>
                                        <option value="technical">Technical</option>
                                        <option value="general">General</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="subject" class="form-label fw-semibold">Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject" 
                                       placeholder="Brief subject of your feedback" required maxlength="200">
                            </div>
                            
                            <div class="mb-3">
                                <label for="feedback_text" class="form-label fw-semibold">Your Feedback</label>
                                <textarea class="form-control" id="feedback_text" name="feedback_text" rows="5" 
                                          placeholder="Please share your detailed feedback..." required></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Rating (Optional)</label>
                                <div class="rating-stars">
                                    <input type="radio" id="star5" name="rating" value="5">
                                    <label for="star5" class="bi bi-star-fill"></label>
                                    <input type="radio" id="star4" name="rating" value="4">
                                    <label for="star4" class="bi bi-star-fill"></label>
                                    <input type="radio" id="star3" name="rating" value="3">
                                    <label for="star3" class="bi bi-star-fill"></label>
                                    <input type="radio" id="star2" name="rating" value="2">
                                    <label for="star2" class="bi bi-star-fill"></label>
                                    <input type="radio" id="star1" name="rating" value="1">
                                    <label for="star1" class="bi bi-star-fill"></label>
                                </div>
                                <small class="text-muted">Rate your overall experience (1-5 stars)</small>
                            </div>
                            
                            <div class="d-flex gap-3">
                                <button type="submit" name="submit_feedback" class="btn btn-primary">
                                    <i class="bi bi-send me-1"></i> Submit Feedback
                                </button>
                                <button type="reset" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Reset Form
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Previous Feedback -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="bi bi-clock-history text-success me-2"></i>
                            Your Previous Feedback
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($previous_feedback)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-chat-square-text" style="font-size: 3rem; color: #dee2e6;"></i>
                                <p class="text-muted mt-3">No previous feedback found</p>
                                <small class="text-muted">Your submitted feedback will appear here</small>
                            </div>
                        <?php else: ?>
                            <div style="max-height: 400px; overflow-y: auto;">
                                <?php foreach ($previous_feedback as $feedback): ?>
                                    <div class="border rounded p-3 mb-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0"><?php echo htmlspecialchars($feedback['subject']); ?></h6>
                                            <span class="status-badge status-<?php echo $feedback['status']; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $feedback['status'])); ?>
                                            </span>
                                        </div>
                                        <p class="text-muted small mb-2">
                                            <?php echo htmlspecialchars(substr($feedback['feedback_text'], 0, 100)) . '...'; ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="bi bi-calendar me-1"></i>
                                                <?php echo date('M j, Y', strtotime($feedback['created_at'])); ?>
                                            </small>
                                            <?php if ($feedback['rating']): ?>
                                                <div class="text-warning">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="bi bi-star<?php echo $i <= $feedback['rating'] ? '-fill' : ''; ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($feedback['admin_response'])): ?>
                                            <div class="mt-2 p-2 bg-light rounded">
                                                <small class="fw-semibold text-primary">Admin Response:</small>
                                                <small class="d-block text-muted"><?php echo htmlspecialchars($feedback['admin_response']); ?></small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple star rating functionality
        document.addEventListener('DOMContentLoaded', function() {
            const stars = document.querySelectorAll('.rating-stars input[type="radio"]');
            const labels = document.querySelectorAll('.rating-stars label');
            
            labels.forEach((label, index) => {
                label.addEventListener('click', function() {
                    const value = parseInt(this.getAttribute('for').replace('star', ''));
                    // Color stars up to clicked one
                    labels.forEach((l, i) => {
                        if (i >= (5 - value)) {
                            l.style.color = '#f59e0b';
                        } else {
                            l.style.color = '#e2e8f0';
                        }
                    });
                });
                
                label.addEventListener('mouseover', function() {
                    const value = parseInt(this.getAttribute('for').replace('star', ''));
                    labels.forEach((l, i) => {
                        if (i >= (5 - value)) {
                            l.style.color = '#f59e0b';
                        } else {
                            l.style.color = '#e2e8f0';
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>