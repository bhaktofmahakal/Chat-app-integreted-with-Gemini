<?php
session_start();

// Log logout activity
if (isset($_SESSION['admin_username'])) {
    $username = $_SESSION['admin_username'];
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    error_log("Admin logout: {$username} from {$ip}");
}

// Destroy session
session_destroy();

// Redirect to login
header('Location: login.php');
exit;
?>