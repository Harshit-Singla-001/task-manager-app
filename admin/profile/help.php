<?php
// ==========================================
// FILE: admin/profile/help.php
// Admin Help Page (in profile folder)
// ==========================================

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin authentication check
require_once dirname(__DIR__) . '/../includes/admin_auth_check.php';

// Check if admin/staff is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../../index.php");
    exit();
}

// Check if logged in as staff/admin
if (!isset($_SESSION['login_type']) || $_SESSION['login_type'] !== 'staff') {
    header("Location: ../../user/home.php");
    exit();
}

// Include admin navbar (adjust path)
include_once '../../includes/admin_navbar.php';
?>

<!-- Main Content -->
<div class="main-content">
    <div class="container my-5">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-question-circle me-2"></i>Admin Help Center</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5><i class="fas fa-gavel text-primary me-2"></i> Managing FIRs</h5>
                                        <p class="text-muted">How to manage FIR complaints:</p>
                                        <ul>
                                            <li>View all FIRs from the FIRs page</li>
                                            <li>Click on any FIR to view details</li>
                                            <li>Update status (Pending/Under Investigation/Resolved)</li>
                                            <li>Download FIR as PDF</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5><i class="fas fa-users text-success me-2"></i> Managing Users</h5>
                                        <p class="text-muted">How to manage user accounts:</p>
                                        <ul>
                                            <li>View all registered users</li>
                                            <li>Block/Unblock user accounts</li>
                                            <li>View user activity and FIR history</li>
                                            <li>Reset user passwords if needed</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5><i class="fas fa-lightbulb text-warning me-2"></i> Managing Safety Tips</h5>
                                        <p class="text-muted">How to manage safety tips:</p>
                                        <ul>
                                            <li>Add new safety tips with title and content</li>
                                            <li>Edit or update existing tips</li>
                                            <li>Delete outdated tips</li>
                                            <li>Tips are displayed to users on their dashboard</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5><i class="fas fa-cog text-info me-2"></i> System Settings</h5>
                                        <p class="text-muted">How to configure system settings:</p>
                                        <ul>
                                            <li>Toggle maintenance mode ON/OFF</li>
                                            <li>Enable/Disable user registration</li>
                                            <li>Enable/Disable FIR submission</li>
                                            <li>Save settings after changes</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h5><i class="fas fa-user-shield me-2"></i> Admin Profile Management</h5>
                                <p>How to manage your admin profile:</p>
                                <ul>
                                    <li>View your profile information</li>
                                    <li>Update your personal details</li>
                                    <li>Change your password</li>
                                    <li>Update email address</li>
                                </ul>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h5><i class="fas fa-envelope me-2"></i> Need More Help?</h5>
                                <p>If you need additional assistance, please contact:</p>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-user text-primary me-2"></i> System Administrator</li>
                                    <li><i class="fas fa-envelope text-primary me-2"></i> <a href="mailto:<?= ADMIN_EMAIL ?>"><?= ADMIN_EMAIL ?></a></li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="<?= BASE_URL ?>admin/profile/profile.php" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-2"></i> Back to Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../../includes/admin_footer.php'; ?>