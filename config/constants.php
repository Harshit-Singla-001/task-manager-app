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

// Prevent multiple definitions - CHECK IF CONSTANTS ARE ALREADY DEFINED
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/crime_reporting_system/');
}

if (!defined('ROOT_PATH')) {
    // Get the absolute path to the project root
    define('ROOT_PATH', dirname(__DIR__) . '/');
}

// ==========================================
// SITE CONSTANTS
// ==========================================

if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'Online Crime Reporting System');
}

if (!defined('SITE_TAGLINE')) {
    define('SITE_TAGLINE', 'Report Crime, Stay Safe');
}

if (!defined('ADMIN_EMAIL')) {
    define('ADMIN_EMAIL', 'admin@crimereporting.local');
}

if (!defined('CONTACT_EMAIL')) {
    define('CONTACT_EMAIL', 'support@crimereporting.local');
}

if (!defined('DEMO_MODE')) {
    define('DEMO_MODE', false);
}

if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', false);
}

// SMTP Configuration for PHPMailer
if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', 'smtp.gmail.com');
}

if (!defined('SMTP_USERNAME')) {
    define('SMTP_USERNAME', 'singlaharshit480@gmail.com');
}

if (!defined('SMTP_PASSWORD')) {
    define('SMTP_PASSWORD', 'fujiiqszdfojjarh');
}

if (!defined('SMTP_SECURE')) {
    define('SMTP_SECURE', 'tls');
}

if (!defined('SMTP_PORT')) {
    define('SMTP_PORT', 587);
}

if (!defined('SMTP_FROM_EMAIL')) {
    define('SMTP_FROM_EMAIL', 'singlaharshit480@gmail.com');
}

if (!defined('SMTP_FROM_NAME')) {
    define('SMTP_FROM_NAME', 'Online Crime Reporting System');
}

// OTP Configuration
if (!defined('OTP_EXPIRY_MINUTES')) {
    define('OTP_EXPIRY_MINUTES', 5);
}

// File upload settings
if (!defined('MAX_FILE_SIZE')) {
    define('MAX_FILE_SIZE', 5242880);
}

if (!defined('ALLOWED_EXTENSIONS')) {
    define('ALLOWED_EXTENSIONS', 'jpg,jpeg,png,gif,pdf');
}

if (!defined('UPLOAD_PATH')) {
    define('UPLOAD_PATH', ROOT_PATH . 'uploads/fir_evidence/');
}

// Pagination settings
if (!defined('ITEMS_PER_PAGE')) {
    define('ITEMS_PER_PAGE', 10);
}

if (!defined('ADMIN_ITEMS_PER_PAGE')) {
    define('ADMIN_ITEMS_PER_PAGE', 15);
}

// Session timeout (30 minutes)
if (!defined('SESSION_TIMEOUT')) {
    define('SESSION_TIMEOUT', 1800);
}

// Password requirements
if (!defined('MIN_PASSWORD_LENGTH')) {
    define('MIN_PASSWORD_LENGTH', 8);
}

if (!defined('MAX_FAILED_ATTEMPTS')) {
    define('MAX_FAILED_ATTEMPTS', 5);
}

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

// Prevent function redeclaration
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin() {
        return isset($_SESSION['login_type']) && $_SESSION['login_type'] === 'staff';
    }
}

if (!function_exists('isUser')) {
    function isUser() {
        return isset($_SESSION['login_type']) && $_SESSION['login_type'] === 'user';
    }
}

if (!function_exists('getUserRole')) {
    function getUserRole() {
        if (isset($_SESSION['role'])) {
            return $_SESSION['role'];
        }
        return null;
    }
}

if (!function_exists('getCurrentUserId')) {
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
}

if (!function_exists('getCurrentUserName')) {
    function getCurrentUserName() {
        if (isset($_SESSION['full_name'])) {
            return $_SESSION['full_name'];
        } elseif (isset($_SESSION['admin_name'])) {
            return $_SESSION['admin_name'];
        }
        return 'Guest';
    }
}

if (!function_exists('generateCSRFToken')) {
    function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verifyCSRFToken')) {
    function verifyCSRFToken($token) {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        return $_SESSION['csrf_token'] === $token;
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field() {
        $token = generateCSRFToken();
        echo '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }
}

if (!function_exists('redirect')) {
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
}

if (!function_exists('displayFlashMessage')) {
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
}

if (!function_exists('sanitize')) {
    function sanitize($input) {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('e')) {
    function e($input) {
        return sanitize($input);
    }
}

if (!function_exists('logActivity')) {
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
}
?>