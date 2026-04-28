<?php
// ==========================================
// FILE: admin/profile/profile.php
// Admin Profile Page - With Admin Navbar/Footer
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
    $_SESSION['admin_email'] = 'rajesh@police.gov.in';
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
    .profile-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 30px;
        color: white;
        text-align: center;
    }
    
    .profile-avatar {
        width: 120px;
        height: 120px;
        margin: 0 auto 20px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        font-weight: bold;
        color: #667eea;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    }
    
    .info-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    
    .info-label {
        font-weight: 600;
        color: #555;
        width: 130px;
        display: inline-block;
    }
</style>

<div class="main-content">
    <div class="container py-4">
        <div class="profile-header mb-4">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($_SESSION['admin_name'], 0, 1)); ?>
            </div>
            <h3><?php echo htmlspecialchars($_SESSION['admin_name']); ?></h3>
            <p class="mb-0">System Administrator</p>
        </div>
        
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="card info-card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Personal Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <span class="info-label">Full Name:</span>
                            <span><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                        </div>
                        <div class="mb-3">
                            <span class="info-label">Email Address:</span>
                            <span><?php echo htmlspecialchars($_SESSION['admin_email']); ?></span>
                        </div>
                        <div class="mb-3">
                            <span class="info-label">Role:</span>
                            <span class="badge bg-primary">Administrator</span>
                        </div>
                        <div class="mb-3">
                            <span class="info-label">Member Since:</span>
                            <span>January 2024</span>
                        </div>
                        <div class="mb-3">
                            <span class="info-label">Last Login:</span>
                            <span><?php echo date('F j, Y, g:i a'); ?></span>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <a href="<?php echo BASE_URL; ?>admin/profile/edit_profile.php" class="btn btn-primary">
                            ✏️ Edit Profile
                        </a>
                        <a href="<?php echo BASE_URL; ?>admin/profile/change_password.php" class="btn btn-secondary">
                            🔒 Change Password
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// Include admin footer (closes body and html tags)
include_once ROOT_PATH . 'includes/admin_footer.php'; 
?>