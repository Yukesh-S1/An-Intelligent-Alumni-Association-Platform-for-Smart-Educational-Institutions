<?php
    session_start();
    include 'logged_admin.php';
    
    // Check if email is provided
    if (isset($_POST['email']) || isset($_GET['email'])) {
        $email = isset($_POST['email']) ? $_POST['email'] : $_GET['email'];
        
        // Redirect to profile_detail.php with the email parameter
        header('Location: profile_detail.php?email=' . urlencode($email) . '&admin_view=1');
        exit();
    } else {
        // If no email provided, redirect back to manage accounts
        header('Location: manage_accounts.php');
        exit();
    }
?>
