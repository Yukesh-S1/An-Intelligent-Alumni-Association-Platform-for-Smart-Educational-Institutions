<?php
// Start output buffering to prevent header issues
ob_start();

// Start session and include files BEFORE any HTML output
session_start();
include 'db_controller.php';
$conn->select_db("Alumni");
include 'logged_user.php';

// Initialize variables
$modalOn = false;
$eventTitle = '';

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // GET the POST (which is retrieved from GET previously)
    if (isset($_POST['filterType'])) 
        $_GET['filterType'] = $