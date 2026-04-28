<?php
// ==========================================
// FILE: includes/auth_check.php
// Authentication Check for Protected Pages
// PHP 5.x Compatible
// ==========================================

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// Optional: Check for specific role if passed
if (isset($required_role) && !empty($required_role)) {
    if ($_SESSION['role'] !== $required_role) {
        // Redirect based on login type
        if ($_SESSION['login_type'] == 'user') {
            header("Location: " . BASE_URL . "user/home.php");
        } elseif ($_SESSION['login_type'] == 'staff') {
            header("Location: " . BASE_URL . "admin/dashboard.php");
        } else {
            header("Location: " . BASE_URL . "index.php");
        }
        exit();
    }
}

// ✅ NEW: Function to check if current user is admin (staff)
function isAdminUser() {
    return isset($_SESSION['login_type']) && $_SESSION['login_type'] === 'staff';
}

// ✅ NEW: Function to check if current user is regular user
function isRegularUser() {
    return isset($_SESSION['login_type']) && $_SESSION['login_type'] === 'user';
}

// ✅ NEW: Set global variable for view-only mode (for admin viewing user pages)
$view_only_mode = isAdminUser();
?>