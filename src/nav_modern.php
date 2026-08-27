<?php
// Get current page for active navigation
$current_page = basename($_SERVER['PHP_SELF']);
$user_type = $_SESSION['logged_account']['type'] ?? 'user';
$user_name = $_SESSION['logged_account']['first_nam