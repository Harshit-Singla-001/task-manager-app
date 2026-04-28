<?php
// ==========================================
// FILE: admin/tips/manage_tips.php
// Manage Safety Tips Page - NO HTML TAGS (navbar handles them)
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

// Dummy tips data (PHP 5 compatible array syntax)
$tips = array(
    array('id' => 1, 'title' => 'Always use licensed cabs', 'description' => 'Use only authorized taxi services, especially at night. Note down the vehicle number before starting your journey.'),
    array('id' => 2, 'title' => 'Keep emergency contacts handy', 'description' => 'Save important numbers like police (100), women helpline (1091), and ambulance (108) on speed dial.'),
    array('id' => 3, 'title' => 'Share your location with family', 'description' => 'Use location sharing features on your phone to let family members know your whereabouts.'),
    array('id' => 4, 'title' => 'Be aware of your surroundings', 'description' => 'Avoid using earphones while walking alone, especially in isolated areas. Stay alert and aware.'),
);

// Include admin navbar (contains all HTML head and body tags)
include_once ROOT_PATH . 'includes/admin_navbar.php';
?>

<style>
    /* Page specific styles */
    .tip-card {
        border-left: 4px solid #007bff;
        transition: all 0.3s ease;
    }
    
    .tip-card:hover {
        transform: translateX(5px);
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
</style>

<div class="main-content">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>💡 Manage Safety Tips</h2>
            <button class="btn btn-primary" onclick="alert('Add tip form would open here')">
                + Add New Tip
            </button>
        </div>
        
        <div class="row">
            <?php foreach($tips as $tip): ?>
            <div class="col-md-6 mb-4">
                <div class="card tip-card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $tip['title']; ?></h5>
                        <p class="card-text"><?php echo $tip['description']; ?></p>
                        <div class="mt-2">
                            <button class="btn btn-sm btn-warning" onclick="alert('Edit tip: <?php echo $tip['title']; ?>')">
                                Edit
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="alert('Deleted tip: <?php echo $tip['title']; ?>')">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php 
// Include admin footer (closes body and html tags)
include_once ROOT_PATH . 'includes/admin_footer.php'; 
?>