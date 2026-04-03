<?php
// ==========================================
// FILE: config/constants.php
// Project Constants - Auto-detect BASE_URL
// ==========================================

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Detect protocol (http or https)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';

// Get host and script path
$host = $_SERVER['HTTP_HOST'];
$script_name = $_SERVER['SCRIPT_NAME'];

// Get folder path of project
$project_folder = 'crime_reporting_system'; // CHANGE if your folder name differs
$base_path = '/' . $project_folder . '/';

// Define BASE_URL (works from any page depth)
define('BASE_URL', $protocol . $host . $base_path);

// Optional: ROOT_PATH for server-side includes
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . $base_path);

// Site-wide constants
define('SITE_NAME', 'Online Crime Reporting System');
define('ADMIN_EMAIL', 'admin@crimereporting.local');
define('DEMO_MODE', true); // true for demo mode, false for production

// Optional: Debugging (comment in production)
// echo "<!-- BASE_URL: " . BASE_URL . " -->";
?>