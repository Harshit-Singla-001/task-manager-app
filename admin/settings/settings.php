<?php
// ==========================================
// FILE: admin/settings/settings.php
// System Settings Page - NO HTML TAGS (navbar handles them)
// ==========================================

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin authentication check
require_once dirname(__DIR__) . '/../includes/admin_auth_check.php';

// Set session if not set (for demo)
if (!isset($_SESSION['admin_name'])) {
    $_SESSION['admin_name'] = 'Rajesh Kumar';
}

// Define ROOT_PATH if not defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)) . '/');
}

require_once ROOT_PATH . 'config/constants.php';

// Include admin navbar (contains all HTML head and body tags)
include_once ROOT_PATH . 'includes/admin_navbar.php';
?>

<style>
    /* Page specific styles */
    .setting-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }
    
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }
    
    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    
    input:checked + .slider {
        background-color: #28a745;
    }
    
    input:checked + .slider:before {
        transform: translateX(26px);
    }
</style>

<div class="main-content">
    <div class="container py-4">
        <h2 class="mb-4">⚙️ System Settings</h2>
        
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card setting-card">
                    <div class="card-body">
                        <h5 class="card-title">System Status</h5>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Site Active / Maintenance Mode</span>
                            <label class="toggle-switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <small class="text-muted">When maintenance mode is ON, only admins can access the site</small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card setting-card">
                    <div class="card-body">
                        <h5 class="card-title">FIR Submission</h5>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Allow users to file FIRs</span>
                            <label class="toggle-switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <small class="text-muted">Users can submit new FIR complaints</small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card setting-card">
                    <div class="card-body">
                        <h5 class="card-title">User Registration</h5>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Allow new user registrations</span>
                            <label class="toggle-switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <small class="text-muted">New users can create accounts on the platform</small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card setting-card">
                    <div class="card-body">
                        <h5 class="card-title">Public FIR Display</h5>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Show approved FIRs publicly</span>
                            <label class="toggle-switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <small class="text-muted">Public can view approved FIR records</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <button class="btn btn-primary btn-lg" onclick="alert('Settings saved successfully!')">
                💾 Save All Settings
            </button>
        </div>
    </div>
</div>

<?php 
// Include admin footer (closes body and html tags)
include_once ROOT_PATH . 'includes/admin_footer.php'; 
?>