<?php
/**
 * Authentication Guard for Barangay Zone 12-A Admin Panel
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if user session is not active
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
?>
