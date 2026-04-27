<?php
// ==========================================
// FILE: includes/header.php
// Common Header - Updated with dynamic profile menu
// ==========================================

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load constants first
require_once dirname(__DIR__) . '/config/constants.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm fixed-top">
    <div class="container">
        <!-- Logo Left - Responsive Site Name -->
        <a class="navbar-brand fw-bold d-flex align-items-center" href="<?= BASE_URL . (isset($_SESSION['login_type']) && $_SESSION['login_type'] == 'user' ? 'user/home.php' : 'index.php') ?>">
            <i class="fas fa-shield-alt me-2"></i>
            <span class="brand-name">
                <span class="brand-full d-none d-lg-inline"><?= SITE_NAME ?></span>
                <span class="brand-medium d-none d-md-inline d-lg-none">Crime Reporting System</span>
                <span class="brand-short d-inline d-md-none">Crime Reporting</span>
            </span>
        </a>
        
        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Center Menu -->
        <div class="collapse navbar-collapse" id="mainNavbar">
    <!-- Center Menu - Left aligned on mobile -->
    <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
        <li class="nav-item">
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'home.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>user/home.php">
                <i class="fas fa-home me-1"></i> Home
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'fir_records.php') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>user/fir/fir_records.php">
                <i class="fas fa-chart-line me-1"></i> Public FIR
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'safety_tips.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>user/pages/safety_tips.php">
                <i class="fas fa-lightbulb me-1"></i> Safety Tips
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>user/pages/about.php">
                <i class="fas fa-info-circle me-1"></i> About
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>user/pages/contact.php">
                <i class="fas fa-envelope me-1"></i> Contact Us
            </a>
        </li>
    </ul>
    
    <!-- Profile Right - Centered on mobile with name -->
    <!-- Profile Right - No Arrow -->
<div class="dropdown ms-auto ms-lg-0">
    <?php if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
        <?php
            $firstLetter = strtoupper(substr($_SESSION['full_name'], 0, 1));
            $colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7', '#DDA0DD', '#98D8C8', '#F7DC6F'];
            $randomColor = $colors[abs(crc32($firstLetter)) % count($colors)];
        ?>
        <div class="dropdown-toggle-no-arrow" data-bs-toggle="dropdown" style="cursor: pointer;">
            <div class="d-flex align-items-center">
                <div class="profile-circle" style="width: 40px; height: 40px; background-color: <?= $randomColor ?>; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2rem;">
                    <?= $firstLetter ?>
                </div>
                <!-- Show name on all screens including mobile -->
                <span class="ms-2 text-white"><?= $_SESSION['full_name'] ?></span>
            </div>
        </div>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="<?= BASE_URL ?>user/profile.php">
                <i class="fas fa-user-circle me-2"></i> My Profile
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