<?php
// ==========================================
// FILE: admin/dashboard.php
// Admin Dashboard - Simple version
// ==========================================

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin authentication check
require_once dirname(__DIR__) . '/includes/admin_auth_check.php';

// Check if admin/staff is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../index.php");
    exit();
}

// Check if logged in as staff/admin
if (!isset($_SESSION['login_type']) || $_SESSION['login_type'] !== 'staff') {
    header("Location: ../user/home.php");
    exit();
}

// Include admin navbar
include_once '../includes/admin_navbar.php';
?>

<!-- Main Content -->
<div class="main-content">
    <div class="container my-5">
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-success">
                    <h4><i class="fas fa-user-shield"></i> Welcome back, <?= isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin' ?>!</h4>
                    <p>This is your admin dashboard. Use the navigation menu to manage the system.</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card text-center h-100 shadow">
                    <div class="card-body">
                        <i class="fas fa-gavel fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Manage FIRs</h5>
                        <p class="card-text">View and process all FIR complaints</p>
                        <a href="fir/manage_fir.php" class="btn btn-primary">Manage →</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card text-center h-100 shadow">
                    <div class="card-body">
                        <i class="fas fa-users fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Manage Users</h5>
                        <p class="card-text">View and manage registered users</p>
                        <a href="users/manage_users.php" class="btn btn-success">Manage →</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card text-center h-100 shadow">
                    <div class="card-body">
                        <i class="fas fa-lightbulb fa-3x text-warning mb-3"></i>
                        <h5 class="card-title">Safety Tips</h5>
                        <p class="card-text">Manage safety tips for users</p>
                        <a href="tips/manage_tips.php" class="btn btn-warning">Manage →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/admin_footer.php'; ?>