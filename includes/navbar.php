<?php
// ==========================================
// FILE: includes/navbar.php
// Reusable Navbar using BASE_URL from constants.php
// ==========================================

// Ensure constants.php is loaded
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__) . '/config/constants.php';
}

// Get current script for active highlighting
$current_script = $_SERVER['SCRIPT_NAME'];

// Function to check active page (supports array or single page)
function isActive($pages, $current_script) {
    if (!is_array($pages)) {
        $pages = [$pages];
    }
    foreach ($pages as $page) {
        // strpos ensures even nested pages in a folder are detected
        if (strpos($current_script, $page) !== false) {
            return 'active';
        }
    }
    return '';
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>user/home.php">
            <i class="fas fa-shield-alt me-2"></i><?= SITE_NAME ?>
        </a>

        <!-- Navbar Toggler -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <!-- Home -->
                <li class="nav-item">
                    <a class="nav-link <?= isActive('home.php', $current_script) ?>" href="<?= BASE_URL ?>user/home.php">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>

                <!-- FIR Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= isActive(['file_fir.php', 'my_firs.php', 'fir_records.php'], $current_script) ?>"
                       href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-gavel"></i> FIR
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item <?= isActive('file_fir.php', $current_script) ?>" href="<?= BASE_URL ?>user/fir/file_fir.php">📝 File New FIR</a></li>
                        <li><a class="dropdown-item <?= isActive('my_firs.php', $current_script) ?>" href="<?= BASE_URL ?>user/fir/my_firs.php">📋 My FIRs</a></li>
                        <li><a class="dropdown-item <?= isActive('fir_records.php', $current_script) ?>" href="<?= BASE_URL ?>user/fir/fir_records.php">📑 All FIR Records</a></li>
                    </ul>
                </li>

                <!-- Safety Tips -->
                <li class="nav-item">
                    <a class="nav-link <?= isActive('safety_tips.php', $current_script) ?>" href="<?= BASE_URL ?>user/pages/safety_tips.php">
                        <i class="fas fa-lightbulb"></i> Safety Tips
                    </a>
                </li>

                <!-- About -->
                <li class="nav-item">
                    <a class="nav-link <?= isActive('about.php', $current_script) ?>" href="<?= BASE_URL ?>user/pages/about.php">
                        <i class="fas fa-info-circle"></i> About
                    </a>
                </li>

                <!-- Contact -->
                <li class="nav-item">
                    <a class="nav-link <?= isActive('contact.php', $current_script) ?>" href="<?= BASE_URL ?>user/pages/contact.php">
                        <i class="fas fa-envelope"></i> Contact
                    </a>
                </li>

                <!-- Logout -->
                <li class="nav-item">
                    <a class="nav-link text-warning" href="<?= BASE_URL ?>auth/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Optional: Make BASE_URL available to JavaScript -->
<script>
    var BASE_URL = '<?= BASE_URL ?>';
</script>