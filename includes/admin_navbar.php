<?php
// ==========================================
// FILE: includes/admin_navbar.php
// Admin Navbar - Same design as user header
// ==========================================

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load constants first
require_once dirname(__DIR__) . '/config/constants.php';

// Get admin name (compatible with your login system)
$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : (isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Admin');
$firstLetter = strtoupper(substr($admin_name, 0, 1));
$colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7', '#DDA0DD', '#98D8C8', '#F7DC6F'];
$randomColor = $colors[abs(crc32($firstLetter)) % count($colors)];

// Get current page for active highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> - Admin Panel</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>

<!-- Admin Navigation Bar - Same style as user header -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm fixed-top">
    <div class="container">
        <!-- Logo Left - Shield Icon + Admin Panel -->
        <a class="navbar-brand fw-bold d-flex align-items-center" href="<?= BASE_URL ?>admin/dashboard.php">
            <i class="fas fa-shield-alt me-2"></i>
            <span class="brand-name">
                <span class="brand-full d-none d-lg-inline">Admin Panel</span>
                <span class="brand-medium d-none d-md-inline d-lg-none">Admin Panel</span>
                <span class="brand-short d-inline d-md-none">Admin</span>
            </span>
        </a>
        
        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Center Menu -->
        <div class="collapse navbar-collapse" id="adminNavbar">
            <!-- Center Menu - Admin Navigation Links -->
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/dashboard.php">
                        <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'manage_fir.php' || $current_page == 'fir_details.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/fir/manage_fir.php">
                        <i class="fas fa-gavel me-1"></i> FIRs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'manage_tips.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/tips/manage_tips.php">
                        <i class="fas fa-lightbulb me-1"></i> Tips
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'manage_users.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/users/manage_users.php">
                        <i class="fas fa-users me-1"></i> Users
                    </a>
                </li>
            </ul>
            
            <!-- Profile Dropdown - Same as user header -->
            <div class="dropdown ms-auto ms-lg-0">
                <?php if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                    <div class="dropdown-toggle-no-arrow" data-bs-toggle="dropdown" style="cursor: pointer;">
                        <div class="d-flex align-items-center">
                            <div class="profile-circle" style="width: 40px; height: 40px; background-color: <?= $randomColor ?>; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2rem;">
                                <?= $firstLetter ?>
                            </div>
                            <!-- Show name on all screens including mobile -->
                            <span class="ms-2 text-white"><?= htmlspecialchars($admin_name) ?></span>
                        </div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/profile/profile.php">
                            <i class="fas fa-user-circle me-2"></i> My Profile
                        </a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/settings/settings.php">
                            <i class="fas fa-cog me-2"></i> Settings
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>auth/logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a></li>
                    </ul>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>index.php" class="btn btn-outline-light">
                        <i class="fas fa-sign-in-alt me-1"></i> Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<?php
// Display session messages if any
if(isset($_SESSION['success'])): ?>
    <div class="container mt-3">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if(isset($_SESSION['error'])): ?>
    <div class="container mt-3">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>