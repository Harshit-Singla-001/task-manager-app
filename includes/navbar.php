<?php
// ==========================================
// FILE: includes/navbar_alternative.php
// Alternative Solution: Dynamic Path Detection
// ==========================================

// Detect current file location and calculate base path
$current_path = dirname($_SERVER['SCRIPT_NAME']);
$base_path = '';

// Calculate depth from document root
$depth = substr_count($current_path, '/') - 1;
if ($depth > 0) {
    $base_path = str_repeat('../', $depth);
}

// Ensure base_path ends with /
if ($base_path && substr($base_path, -1) != '/') {
    $base_path .= '/';
}

// Get current page for active highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Function to check if a page is active
function isActive($page, $current_page) {
    return $current_page == $page ? 'active' : '';
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= $base_path ?>user/home.php">
            <i class="fas fa-shield-alt me-2"></i>Crime Reporting System
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= isActive('home.php', $current_page) ?>" 
                       href="<?= $base_path ?>user/home.php">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-gavel"></i> FIR
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= $base_path ?>user/fir/file_fir.php">📝 File New FIR</a></li>
                        <li><a class="dropdown-item" href="<?= $base_path ?>user/fir/my_firs.php">📋 My FIRs</a></li>
                        <li><a class="dropdown-item" href="<?= $base_path ?>user/fir/fir_records.php">📑 All FIR Records</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isActive('safety_tips.php', $current_page) ?>" 
                       href="<?= $base_path ?>user/pages/safety_tips.php">
                        <i class="fas fa-lightbulb"></i> Safety Tips
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isActive('about.php', $current_page) ?>" 
                       href="<?= $base_path ?>user/pages/about.php">
                        <i class="fas fa-info-circle"></i> About
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isActive('contact.php', $current_page) ?>" 
                       href="<?= $base_path ?>user/pages/contact.php">
                        <i class="fas fa-envelope"></i> Contact
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-warning" href="<?= $base_path ?>auth/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<?php
// Debug info (remove in production)
// echo "<!-- Current Path: $current_path | Base Path: $base_path -->";
?>