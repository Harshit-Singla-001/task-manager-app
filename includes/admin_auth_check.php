<?php
// ==========================================
// FILE: includes/admin_auth_check.php
// Admin Authentication Check - Reusable for ALL admin pages
// ==========================================

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define BASE_URL if not defined (for redirection)
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/crime_reporting_system/');
}

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['error'] = "Please login first to access admin panel.";
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// Check if logged in as staff/admin (not regular user)
if (!isset($_SESSION['login_type']) || $_SESSION['login_type'] !== 'staff') {
    $_SESSION['error'] = "Access denied. Admin only.";
    
    // If logged in as regular user, redirect to user home
    if (isset($_SESSION['login_type']) && $_SESSION['login_type'] === 'user') {
        header("Location: " . BASE_URL . "user/home.php");
    } else {
        header("Location: " . BASE_URL . "index.php");
    }
    exit();
}

// Check if admin status is active (if status is set in session)
if (isset($_SESSION['status']) && $_SESSION['status'] !== 'active') {
    $_SESSION['error'] = "Your admin account is suspended. Contact support.";
    header("Location: " . BASE_URL . "auth/logout.php");
    exit();
}

// Optional: Check if admin has proper role (admin, staff, officer, moderator)
$allowed_roles = array('admin', 'staff', 'officer', 'moderator');
if (isset($_SESSION['role']) && !in_array($_SESSION['role'], $allowed_roles)) {
    $_SESSION['error'] = "Insufficient permissions to access admin area.";
    header("Location: " . BASE_URL . "user/home.php");
    exit();
}

// Set admin session variables if not set (for compatibility with navbar)
if (!isset($_SESSION['admin_name']) && isset($_SESSION['full_name'])) {
    $_SESSION['admin_name'] = $_SESSION['full_name'];
}
if (!isset($_SESSION['admin_email']) && isset($_SESSION['email'])) {
    $_SESSION['admin_email'] = $_SESSION['email'];
}

// Optional: Set demo session for development (Remove in production)
if (!isset($_SESSION['admin_name'])) {
    $_SESSION['admin_name'] = 'Admin User';
}
if (!isset($_SESSION['admin_email'])) {
    $_SESSION['admin_email'] = 'admin@example.com';
}
?>