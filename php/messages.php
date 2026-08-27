<?php
session_start();
include 'db_controller.php';

// Check if user is logged in
if (!isset($_SESSION['logged_account'])) {
    header('Location: login.php');
    exit();
}

$conn->select_db("Alumni");
$current_user = $_SESSION['logged_account']['email'];

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'send_message':
            $conversation_id = intval($_POST['conversation_id']);
            $message = $conn->real_escape_string(trim($_POST['message']));
            
            if (!empty($message)) {
                $insert_query = "INSERT INTO chat_messages_table (conversation_id, sender_email, message) 
                                VALUES ($conversation_id, '$current_user', '$message')";
                
                if ($conn->query($insert_query)) {
                    $conn->query("UPDATE chat_conversations_table SET last_message_at = CURRENT_TIMESTAMP WHERE id = $conversation_id");
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to send message']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
            }
            exit();
            
        case 'get_messages':
            $conversation_id = intval($_POST['conversation_id']);
            $last_id = isset($_POST['last_id']) ? intval($_POST['last_id']) : 0;
            
            // Mark messages as read for current user
            $mark_read = "UPDATE chat_messages_table cm
                         JOIN chat_conversations_table cc ON cm.conversation_id = cc.id
                         SET cm.is_read = TRUE
                         WHERE cm.conversation_id = $conversation_id
                         AND cm.sender_email != '$current_user'
                         AND cm.is_read = FALSE";
            $conn->query($mark_read);
            
            $query = "SELECT * FROM chat_messages_table 
                     WHERE conversation_id = $conversation_id AND id > $last_id
                     ORDER BY sent_at ASC";
            $result = $conn->query($query);
            
            $messages = [];
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }
            
            echo json_encode($messages);
            exit();
            
        case 'start_conversation':
            $other_user = $conn->real_escape_string($_POST['other_user_email']);
            
            // Check if conversation exists
            $existing = $conn->query("SELECT id FROM chat_conversations_table 
                                    WHERE (user1_email = '$current_user' AND user2_email = '$other_user')
                                       OR (user1_email = '$other_user' AND user2_email = '$current_user')");
            
            if ($existing->num_rows > 0) {
                $conv = $existing->fetch_assoc();
                echo json_encode(['success' => true, 'conversation_id' => $conv['id']]);
            } else {
                $create = $conn->query("INSERT INTO chat_conversations_table (user1_email, user2_email) 
                                      VALUES ('$current_user', '$other_user')");
                if ($create) {
                    echo json_encode(['success' => true, 'conversation_id' => $conn->insert_id]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to create conversation']);
                }
            }
            exit();
            
        case 'search_users':
            $query = $conn->real_escape_string($_POST['query']);
            $search = $conn->query("SELECT email, first_name, last_name, profile_image 
                                   FROM user_table 
                                   WHERE email != '$current_user' 
                                   AND (first_name LIKE '%$query%' OR last_name LIKE '%$query%' OR email LIKE '%$query%')
                                   LIMIT 10");
            
            $users = [];
            while ($user = $search->fetch_assoc()) {
                $users[] = $user;
            }
            
            echo json_encode($users);
            exit();
    }
}

// Get conversations for current user
$conversations_query = "SELECT c.*, 
    CASE 
        WHEN c.user1_email = '$current_user' THEN u2.first_name
        ELSE u1.first_name
    END as other_first_name,
    CASE 
        WHEN c.user1_email = '$current_user' THEN u2.last_name
        ELSE u1.last_name
    END as other_last_name,
    CASE 
        WHEN c.user1_email = '$current_user' THEN u2.email
        ELSE u1.email
    END as other_email,
    CASE 
        WHEN c.user1_email = '$current_user' THEN COALESCE(u2.profile_image, 'profile_images/default-male-user-profile-icon.jpg')
        ELSE COALESCE(u1.profile_image, 'profile_images/default-male-user-profile-icon.jpg')
    END as other_profile_image,
    (SELECT COUNT(*) FROM chat_messages_table m WHERE m.conversation_id = c.id AND m.sender_email != '$current_user' AND m.is_read = FALSE) as unread_count,
    (SELECT message FROM chat_messages_table m WHERE m.conversation_id = c.id ORDER BY m.sent_at DESC LIMIT 1) as last_message
    FROM chat_conversations_table c
    LEFT JOIN user_table u1 ON c.user1_email = u1.email
    LEFT JOIN user_table u2 ON c.user2_email = u2.email
    WHERE c.user1_email = '$current_user' OR c.user2_email = '$current_user'
    ORDER BY c.last_message_at DESC";

$conversations = $conn->query($conversations_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | Alumni Portal</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Modern Design System -->

    <style>
        /* Match Alumni Dashboard Design */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            padding-bottom: 2rem;
            margin: 0;
            padding-top: 0;
        }
        
        .dashboard-container {
            padding: 2rem 0;
            min-height: calc(100vh - 80px);
            margin-top: 0;
        }
        
        .welcome-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
            height: 100%;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .stat-label {
            font-size: 1rem;
            color: #666;
            font-weight: 500;
        }
        
        .section-title {
            color: white;
            font-weight: 600;
            margin-bottom: 1.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .chat-wrapper {
            height: 75vh;
            max-height: 800px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            overflow: hidden;
        }
        
        .conversations-sidebar {
            background: rgba(248, 250, 252, 0.8);
            border-right: 1px solid rgba(255, 255, 255, 0.3);
            height: 100%;
        }
        
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            z-index: 1000;
            margin-bottom: 0;
        }
        
        .navbar-brand {
            font-weight: 700;
            color: #333 !important;
        }
        
        .nav-link {
            color: #333 !important;
            font-weight: 500;
        }
        
        .nav-link:hover {
            color: #007bff !important;
        }
        
        .nav-link.active {
            color: #007bff !important;
            font-weight: 600;
        }
        
        .conversation-item {
            padding: 16px;
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
            margin: 4px 8px;
            border-radius: 12px;
            margin-bottom: 8px;
        }
        
        .conversation-item:hover {
            background: #f8fafc;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border-color: #f59e0b;
        }
        
        .conversation-item.active {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.3);
        }
        
        .conversation-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
            border: 3px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .conversation-item:hover .conversation-avatar {
            border-color: #f59e0b;
        }
        
        .conversation-item.active .conversation-avatar {
            border-color: white;
        }
        
        .chat-area {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .chat-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 20px;
            border-bottom: 1px solid #d97706;
        }
        
        .chat-header .conversation-avatar {
            width: 45px;
            height: 45px;
            border: 3px solid white;
        }
        
        .messages-area {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f8fafc;
        }
        
        .message {
            margin-bottom: 16px;
            display: flex;
        }
        
        .message.sent {
            justify-content: flex-end;
        }
        
        .message.received {
            justify-content: flex-start;
        }
        
        .message-bubble {
            max-width: 70%;
            padding: 14px 18px;
            border-radius: 20px;
            word-wrap: break-word;
            position: relative;
            font-weight: 500;
        }
        
        .message.sent .message-bubble {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            border-bottom-right-radius: 6px;
            box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.3);
        }
        
        .message.received .message-bubble {
            background: white;
            color: #1f2937;
            border-bottom-left-radius: 6px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }
        
        .message-time {
            font-size: 11px;
            opacity: 0.8;
            margin-top: 6px;
            font-weight: 400;
        }
        
        .message-input-area {
            padding: 20px;
            background: white;
            border-top: 1px solid #e2e8f0;
        }
        
        .message-input {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 25px;
            padding: 14px 20px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .message-input:focus {
            background: white;
            border-color: #f59e0b;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1);
            outline: none;
        }
        
        .send-btn {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.3);
        }
        
        .send-btn:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(245, 158, 11, 0.4);
        }
        
        .no-chat-selected {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            text-align: center;
            color: #6b7280;
            background: #f8fafc;
        }
        
        .search-box {
            padding: 16px;
            border-bottom: 1px solid #e2e8f0;
            background: white;
        }
        
        .search-box .form-control {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 16px;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
        }
        
        .search-box .form-control:focus {
            background: white;
            border-color: #f59e0b;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1);
            outline: none;
        }
        
        .unread-badge {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border-radius: 50%;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: 700;
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
        }
        
        .online-dot {
            width: 12px;
            height: 12px;
            background: #10b981;
            border: 2px solid white;
            border-radius: 50%;
            position: absolute;
            bottom: 2px;
            right: 2px;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
        }
        
        .sidebar-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 20px;
            border-bottom: 1px solid #d97706;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
            border-radius: 10px;
            padding: 8px 16px;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.3);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(245, 158, 11, 0.4);
        }
        
        /* Fade-in animations */
        .fade-in {
            animation: fadeIn 0.8s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        

        
        /* Animations */
        .animate-fade-in-up {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }
        
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease;
        }
        
        .slide-left {
            animation: slideInLeft 0.3s ease-out;
        }
        
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        /* Responsive improvements */
        @media (max-width: 768px) {
            h1 {
                font-size: 1.75rem;
            }
            
            .admin-header {
                padding: 1.5rem 0;
            }
            
            .chat-wrapper {
                height: 70vh;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand fw-bold" href="main_menu.php">
                <i class="bi bi-mortarboard text-primary"></i>
                Alumni Portal
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="main_menu.php">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                    </li>
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
                        <a class="nav-link active" href="messages.php">
                            <i class="bi bi-chat-dots"></i> Messages
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($current_user); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile_detail.php"><i class="bi bi-person"></i> My Profile</a></li>
                            <li><a class="dropdown-item" href="feedback_form.php"><i class="bi bi-chat-square-text"></i> Feedback</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="dashboard-container">
        <div class="container">
            <!-- Welcome Section -->
            <div class="welcome-card fade-in">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="display-5 fw-bold text-primary mb-3">Messages Dashboard</h1>
                        <p class="lead text-muted">
                            Connect and communicate with fellow alumni in real-time.
                        </p>
                    </div>
                    <div class="col-md-4 text-center">
                        <?php include 'components/alumni_logo.php'; ?>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-5">
                <?php
                // Get message statistics
                try {
                    $total_conversations_result = $conn->query("SELECT COUNT(*) as count FROM chat_conversations_table WHERE user1_email = '$current_user' OR user2_email = '$current_user'");
                    $total_conversations = $total_conversations_result ? $total_conversations_result->fetch_assoc()['count'] : 0;
                    
                    $unread_messages_result = $conn->query("SELECT COUNT(*) as count FROM chat_messages_table cm JOIN chat_conversations_table cc ON cm.conversation_id = cc.id WHERE (cc.user1_email = '$current_user' OR cc.user2_email = '$current_user') AND cm.sender_email != '$current_user' AND cm.is_read = FALSE");
                    $unread_messages = $unread_messages_result ? $unread_messages_result->fetch_assoc()['count'] : 0;
                    
                    $total_messages_result = $conn->query("SELECT COUNT(*) as count FROM chat_messages_table cm JOIN chat_conversations_table cc ON cm.conversation_id = cc.id WHERE (cc.user1_email = '$current_user' OR cc.user2_email = '$current_user')");
                    $total_messages = $total_messages_result ? $total_messages_result->fetch_assoc()['count'] : 0;
                    
                    $active_today_result = $conn->query("SELECT COUNT(DISTINCT cc.id) as count FROM chat_conversations_table cc JOIN chat_messages_table cm ON cc.id = cm.conversation_id WHERE (cc.user1_email = '$current_user' OR cc.user2_email = '$current_user') AND DATE(cm.sent_at) = CURDATE()");
                    $active_today = $active_today_result ? $active_today_result->fetch_assoc()['count'] : 0;
                } catch (Exception $e) {
                    $total_conversations = 0;
                    $unread_messages = 0;
                    $total_messages = 0;
                    $active_today = 0;
                }
                ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card fade-in">
                        <div class="stat-number text-primary"><?php echo number_format($total_conversations); ?></div>
                        <div class="stat-label">Total Conversations</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card fade-in" style="animation-delay: 0.1s;">
                        <div class="stat-number text-danger"><?php echo number_format($unread_messages); ?></div>
                        <div class="stat-label">Unread Messages</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card fade-in" style="animation-delay: 0.2s;">
                        <div class="stat-number text-success"><?php echo number_format($total_messages); ?></div>
                        <div class="stat-label">Total Messages</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card fade-in" style="animation-delay: 0.3s;">
                        <div class="stat-number text-warning"><?php echo number_format($active_today); ?></div>
                        <div class="stat-label">Active Today</div>
                    </div>
                </div>
            </div>

            <!-- Chat Interface Section -->
            <div class="row mb-3">
                <div class="col-12">
                    <h3 class="section-title">Chat Interface</h3>
                </div>
            </div>
            
            <!-- Chat Interface -->
            <div class="chat-wrapper row g-0">
            <!-- Conversations Sidebar -->
            <div class="col-md-4 conversations-sidebar">
                <div class="sidebar-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">Conversations</h5>
                        <button class="btn btn-light btn-sm" onclick="showNewChatModal()">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                </div>
                
                <div class="search-box">
                    <input type="text" class="form-control" placeholder="Search conversations..." id="searchConversations">
                </div>
                
                <div class="conversations-list" id="conversationsList">
                    <?php if ($conversations && $conversations->num_rows > 0): ?>
                        <?php while ($conv = $conversations->fetch_assoc()): ?>
                            <div class="conversation-item" 
                                 data-conversation-id="<?php echo $conv['id']; ?>"
                                 data-other-user="<?php echo htmlspecialchars($conv['other_email']); ?>"
                                 data-other-name="<?php echo htmlspecialchars($conv['other_first_name'] . ' ' . $conv['other_last_name']); ?>"
                                 data-other-avatar="<?php echo $conv['other_profile_image']; ?>"
                                 onclick="loadConversation(this)">
                                
                                <div class="d-flex align-items-center">
                                    <div class="position-relative me-3" style="width: 50px; height: 50px; flex-shrink: 0;">
                                        <img src="<?php echo $conv['other_profile_image']; ?>" 
                                             class="conversation-avatar" 
                                             style="width: 50px !important; height: 50px !important; border-radius: 50%; object-fit: cover;"
                                             alt="<?php echo htmlspecialchars($conv['other_first_name']); ?>">
                                        <div class="online-dot"></div>
                                    </div>
                                    
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($conv['other_first_name'] . ' ' . $conv['other_last_name']); ?></h6>
                                        <p class="mb-0 text-muted small">
                                            <?php 
                                            $lastMsg = $conv['last_message'] ?: 'No messages yet';
                                            echo htmlspecialchars(strlen($lastMsg) > 30 ? substr($lastMsg, 0, 30) . '...' : $lastMsg);
                                            ?>
                                        </p>
                                    </div>
                                    
                                    <?php if ($conv['unread_count'] > 0): ?>
                                        <span class="unread-badge"><?php echo $conv['unread_count']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center p-4 text-muted">
                            <i class="bi bi-chat-square-text" style="font-size: 48px;"></i>
                            <p class="mt-3">No conversations yet</p>
                            <button class="btn btn-primary btn-sm" onclick="showNewChatModal()">
                                Start a conversation
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Chat Area -->
            <div class="col-md-8">
                <div class="chat-area">
                    <div class="no-chat-selected" id="noChatSelected">
                        <div>
                            <i class="bi bi-chat-dots" style="font-size: 64px; opacity: 0.3;"></i>
                            <h4 class="mt-3">Welcome to Messages</h4>
                            <p class="text-muted">Select a conversation to start messaging</p>
                        </div>
                    </div>
                    
                    <div class="d-none" id="chatInterface">
                        <div class="chat-header">
                            <div class="d-flex align-items-center">
                                <img src="" class="conversation-avatar me-3" id="chatAvatar" alt="User" 
                                     style="width: 40px !important; height: 40px !important; border-radius: 50%; object-fit: cover;">
                                <div>
                                    <h5 class="mb-0" id="chatUserName"></h5>
                                    <small class="opacity-75">Online</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="messages-area" id="messagesArea">
                            <!-- Messages will be loaded here -->
                        </div>
                        
                        <div class="message-input-area">
                            <form class="d-flex align-items-center" onsubmit="sendMessage(event)">
                                <input type="text" class="form-control message-input me-3" 
                                       id="messageInput" placeholder="Type a message..." required>
                                <button type="submit" class="send-btn">
                                    <i class="bi bi-send-fill"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- New Chat Modal -->
    <div class="modal fade" id="newChatModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
                <div class="modal-header" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; border-radius: 16px 16px 0 0;">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-plus-circle me-2"></i>
                        Start New Conversation
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-muted">Search Alumni</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" id="userSearchInput" 
                                   placeholder="Search by name or email..." oninput="searchUsers()"
                                   style="background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 0 10px 10px 0;">
                        </div>
                    </div>
                    <div id="userSearchResults" style="max-height: 400px; overflow-y: auto;"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentConversationId = null;
        let lastMessageId = 0;
        let messagePolling = null;
        
        function loadConversation(element) {
            // Remove active class from all conversations
            document.querySelectorAll('.conversation-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Add active class to selected conversation
            element.classList.add('active');
            
            // Get conversation data
            currentConversationId = element.dataset.conversationId;
            const otherUser = element.dataset.otherUser;
            const otherName = element.dataset.otherName;
            const otherAvatar = element.dataset.otherAvatar;
            
            // Update chat interface
            document.getElementById('noChatSelected').classList.add('d-none');
            document.getElementById('chatInterface').classList.remove('d-none');
            document.getElementById('chatUserName').textContent = otherName;
            document.getElementById('chatAvatar').src = otherAvatar;
            
            // Load messages
            loadMessages();
            
            // Start polling for new messages
            if (messagePolling) clearInterval(messagePolling);
            messagePolling = setInterval(loadNewMessages, 3000);
        }
        
        function loadMessages() {
            if (!currentConversationId) return;
            
            const formData = new FormData();
            formData.append('action', 'get_messages');
            formData.append('conversation_id', currentConversationId);
            formData.append('last_id', 0);
            
            fetch('messages.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(messages => {
                displayMessages(messages);
                if (messages.length > 0) {
                    lastMessageId = Math.max(...messages.map(m => parseInt(m.id)));
                }
            })
            .catch(error => console.error('Error loading messages:', error));
        }
        
        function loadNewMessages() {
            if (!currentConversationId) return;
            
            const formData = new FormData();
            formData.append('action', 'get_messages');
            formData.append('conversation_id', currentConversationId);
            formData.append('last_id', lastMessageId);
            
            fetch('messages.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(messages => {
                if (messages.length > 0) {
                    appendMessages(messages);
                    lastMessageId = Math.max(...messages.map(m => parseInt(m.id)));
                }
            })
            .catch(error => console.error('Error loading new messages:', error));
        }
        
        function displayMessages(messages) {
            const messagesArea = document.getElementById('messagesArea');
            messagesArea.innerHTML = '';
            
            if (messages.length === 0) {
                messagesArea.innerHTML = `
                    <div class="text-center text-muted mt-5">
                        <i class="bi bi-chat-dots" style="font-size: 48px;"></i>
                        <p class="mt-3">No messages yet. Start the conversation!</p>
                    </div>
                `;
                return;
            }
            
            messages.forEach(message => {
                appendSingleMessage(message);
            });
            
            scrollToBottom();
        }
        
        function appendMessages(messages) {
            messages.forEach(message => {
                appendSingleMessage(message);
            });
            scrollToBottom();
        }
        
        function appendSingleMessage(message) {
            const messagesArea = document.getElementById('messagesArea');
            const isSent = message.sender_email === '<?php echo $current_user; ?>';
            
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isSent ? 'sent' : 'received'}`;
            
            const time = new Date(message.sent_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            
            messageDiv.innerHTML = `
                <div class="message-bubble">
                    ${escapeHtml(message.message)}
                    <div class="message-time">${time}</div>
                </div>
            `;
            
            messagesArea.appendChild(messageDiv);
        }
        
        function sendMessage(event) {
            event.preventDefault();
            
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value.trim();
            
            if (!message || !currentConversationId) return;
            
            const formData = new FormData();
            formData.append('action', 'send_message');
            formData.append('conversation_id', currentConversationId);
            formData.append('message', message);
            
            fetch('messages.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    messageInput.value = '';
                    loadNewMessages();
                } else {
                    alert('Failed to send message: ' + (result.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
                alert('Network error occurred');
            });
        }
        
        function showNewChatModal() {
            const modal = new bootstrap.Modal(document.getElementById('newChatModal'));
            modal.show();
        }
        
        function searchUsers() {
            const query = document.getElementById('userSearchInput').value.trim();
            const resultsDiv = document.getElementById('userSearchResults');
            
            if (query.length < 2) {
                resultsDiv.innerHTML = '';
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'search_users');
            formData.append('query', query);
            
            fetch('messages.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(users => {
                resultsDiv.innerHTML = '';
                
                if (users.length === 0) {
                    resultsDiv.innerHTML = '<div class="alert alert-info">No users found</div>';
                    return;
                }
                
                users.forEach(user => {
                    const userDiv = document.createElement('div');
                    userDiv.className = 'border rounded-3 p-3 mb-3 cursor-pointer';
                    userDiv.style.cursor = 'pointer';
                    userDiv.style.border = '2px solid #e2e8f0';
                    userDiv.style.borderRadius = '12px';
                    userDiv.style.transition = 'all 0.3s ease';
                    userDiv.innerHTML = `
                        <div class="d-flex align-items-center">
                            <img src="${user.profile_image || 'profile_images/default-male-user-profile-icon.jpg'}" 
                                 class="rounded-circle me-3" width="50" height="50" alt="${user.first_name}"
                                 style="width: 50px !important; height: 50px !important; object-fit: cover !important; border: 3px solid #e2e8f0;">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold">${user.first_name} ${user.last_name}</h6>
                                <small class="text-muted">${user.email}</small>
                            </div>
                            <div>
                                <i class="bi bi-chat-dots text-primary" style="font-size: 1.2rem;"></i>
                            </div>
                        </div>
                    `;
                    userDiv.onmouseover = () => {
                        userDiv.style.borderColor = '#f59e0b';
                        userDiv.style.backgroundColor = '#f8fafc';
                        userDiv.style.transform = 'translateY(-2px)';
                        userDiv.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1)';
                    };
                    userDiv.onmouseout = () => {
                        userDiv.style.borderColor = '#e2e8f0';
                        userDiv.style.backgroundColor = 'white';
                        userDiv.style.transform = 'translateY(0)';
                        userDiv.style.boxShadow = 'none';
                    };
                    userDiv.onclick = () => startNewConversation(user.email);
                    resultsDiv.appendChild(userDiv);
                });
            })
            .catch(error => {
                console.error('Error searching users:', error);
                resultsDiv.innerHTML = '<div class="alert alert-danger">Error searching users</div>';
            });
        }
        
        function startNewConversation(userEmail) {
            const formData = new FormData();
            formData.append('action', 'start_conversation');
            formData.append('other_user_email', userEmail);
            
            fetch('messages.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    bootstrap.Modal.getInstance(document.getElementById('newChatModal')).hide();
                    window.location.reload();
                } else {
                    alert('Failed to start conversation: ' + (result.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error starting conversation:', error);
                alert('Network error occurred');
            });
        }
        
        function scrollToBottom() {
            const messagesArea = document.getElementById('messagesArea');
            messagesArea.scrollTop = messagesArea.scrollHeight;
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Animate cards on page load
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.animate-fade-in-up, .fade-in');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
        
        function refreshPage() {
            window.location.reload();
        }
        
        // Search conversations
        document.getElementById('searchConversations').addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const conversations = document.querySelectorAll('.conversation-item');
            
            conversations.forEach(conv => {
                const name = conv.dataset.otherName.toLowerCase();
                if (name.includes(query)) {
                    conv.style.display = 'block';
                } else {
                    conv.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>