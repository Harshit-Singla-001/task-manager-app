<?php
// ==========================================
// FILE: config/constants.php
// Project Constants - Updated with BASE_URL
// ==========================================

// Base URL - Update this based on your server setup
// For localhost: 
// define('BASE_URL', 'http://localhost/online-crime-reporting-system/');
// For custom port or different path, adjust accordingly

// Auto-detect base URL (Recommended for flexibility)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$script_name = $_SERVER['SCRIPT_NAME'];
$path = dirname($script_name);

// Remove 'online-crime-reporting-system' from path if it exists
// This ensures it works even if folder name changes
$base_path = str_replace('\\', '/', $path);
if ($base_path == '/') {
    $base_path = '';
}

define('BASE_URL', $protocol . $host . $base_path . '/');
define('SITE_NAME', 'Online Crime Reporting System');
define('ADMIN_EMAIL', 'admin@crimereporting.local');
define('DEMO_MODE', true); // Phase 1: Demo mode active

// For debugging (optional - comment in production)
// echo "<!-- BASE_URL: " . BASE_URL . " -->";
?>