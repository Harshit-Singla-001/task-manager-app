<?php
// ==========================================
// FILE: config/constants.php
// Project Constants - Phase 2 & 3
// PHP 5.x Compatible Version
// ==========================================

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// BASE URL & PATH DEFINITIONS (Manually Set)
// ==========================================

// Define BASE_URL manually (stable & reliable)
define('BASE_URL', 'http://localhost/crime_reporting_system/');

// Define ROOT_PATH manually
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . '/crime_reporting_system/');

// ==========================================
// SITE CONSTANTS
// ==========================================

// Site-wide constants
define('SITE_NAME', 'Online Crime Reporting System');
define('SITE_TAGLINE', 'Report Crime, Stay Safe');
define('ADMIN_EMAIL', 'admin@crimereporting.local');
define('CONTACT_EMAIL', 'support@crimereporting.local');

// Demo mode (false for production)
define('DEMO_MODE', false);

// Debug mode (set to false in production)
define('DEBUG_MODE', false);

// File upload settings
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', 'jpg,jpeg,png,gif,pdf');
define('UPLOAD_PATH', ROOT_PATH . 'uploads/fir_evidence/');

// Pagination settings
define('ITEMS_PER_PAGE', 10);
define('ADMIN_ITEMS_PER_PAGE', 15);

// Session timeout (30 minutes)
define('SESSION_TIMEOUT', 1800);

// OTP settings (for demo, can use static OTP)
define('DEMO_OTP', '123456'); // Static OTP for testing
define('OTP_EXPIRY_MINUTES', 10);

// Password requirements
define('MIN_PASSWORD_LENGTH', 8);
define('MAX_FAILED_ATTEMPTS', 5);

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Error reporting based on debug mode
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ==========================================
// HELPER FUNCTIONS FOR SESSION MANAGEMENT
// (PHP 5.x Compatible - No ?? operator)
// ==========================================

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Check if user is admin/staff
function isAdmin() {
    return isset($_SESSION['login_type']) && $_SESSION['login_type'] === 'staff';
}

// Check if logged in as regular user
function isUser() {
    return isset($_SESSION['login_type']) && $_SESSION['login_type'] === 'user';
}

// Get current user's role
function getUserRole() {
    if (isset($_SESSION['role'])) {
        return $_SESSION['role'];
    }
    return null;
}

// Get current user ID
function getCurrentUserId() {
    if (isUser()) {
        if (isset($_SESSION['user_id'])) {
            return $_SESSION['user_id'];
        }
    } elseif (isAdmin()) {
        if (isset($_SESSION['staff_id'])) {
            return $_SESSION['staff_id'];
        }
    }
    return null;
}

// Get current user name
function getCurrentUserName() {
    if (isset($_SESSION['full_name'])) {
        return $_SESSION['full_name'];
    }
    return 'Guest';
}

// Generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return $_SESSION['csrf_token'] === $token;
}

// Display CSRF token field (for forms)
function csrf_field() {
    $token = generateCSRFToken();
    echo '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

// Redirect with message
function redirect($url, $message = '', $type = 'success') {
    if ($message != '') {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    // Remove leading slash if present
    $url = ltrim($url, '/');
    header("Location: " . BASE_URL . $url);
    exit();
}

// Display flash message
function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = isset($_SESSION['flash_type']) ? $_SESSION['flash_type'] : 'info';
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
                ' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}

// Sanitize input
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Escape output (alias for sanitize)
function e($input) {
    return sanitize($input);
}

// Log activity (PHP 5.x compatible)
function logActivity($action, $details = '') {
    $log_file = ROOT_PATH . 'logs/activity.log';
    $log_dir = dirname($log_file);
    
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0777, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $user_id = getCurrentUserId();
    if ($user_id == null) {
        $user_id = 'Guest';
    }
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'Unknown';
    $log_entry = "[$timestamp] User: $user_id | IP: $ip | Action: $action | Details: $details" . PHP_EOL;
    
    error_log($log_entry, 3, $log_file);
}
?>