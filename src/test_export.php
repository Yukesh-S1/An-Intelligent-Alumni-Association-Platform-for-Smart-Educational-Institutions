<?php
// Simple test export to verify Excel download works
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['logged_account']) || $_SESSION['logged_account']['type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Clear any output buffer
if (ob_get_level()) {
    ob_end_clean();
}

$format = $_GET['format'] ?? 'excel';

if ($format === 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="test_export_' . date('Y-m-d_H-i-s') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo '<html><head><meta charset="UTF-8"></head