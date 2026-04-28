<?php
// ==========================================
// FILE: admin/fir/manage_fir.php
// Manage FIRs Page - NO HTML TAGS (navbar handles them)
// ==========================================

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin authentication check
require_once dirname(__DIR__) . '/../includes/admin_auth_check.php';

// Set dummy session if not set (for demo)
if (!isset($_SESSION['admin_name'])) {
    $_SESSION['admin_name'] = 'Rajesh Kumar';
    $_SESSION['admin_email'] = 'rajesh@police.gov.in';
}

// Define root path if not defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)) . '/');
}

require_once ROOT_PATH . 'config/constants.php';

// Dummy FIR Data
$firs = array(
    array('id' => 1, 'category' => 'Theft', 'city' => 'Mumbai', 'status' => 'Pending', 'date' => '2024-01-15'),
    array('id' => 2, 'category' => 'Cyber Crime', 'city' => 'Delhi', 'status' => 'Under Investigation', 'date' => '2024-01-14'),
    array('id' => 3, 'category' => 'Assault', 'city' => 'Bangalore', 'status' => 'Resolved', 'date' => '2024-01-13'),
    array('id' => 4, 'category' => 'Fraud', 'city' => 'Chennai', 'status' => 'Pending', 'date' => '2024-01-12'),
    array('id' => 5, 'category' => 'Robbery', 'city' => 'Kolkata', 'status' => 'Under Investigation', 'date' => '2024-01-11'),
);

// Include admin navbar (contains all HTML head and body tags)
include_once ROOT_PATH . 'includes/admin_navbar.php';
?>

<style>
    /* Page specific styles */
    .fir-card {
        border: none;
        border-radius: 12px;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .fir-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .status-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .status-Pending { background: #ffc107; color: #000; }
    .status-Under { background: #17a2b8; color: #fff; }
    .status-Resolved { background: #28a745; color: #fff; }
    
    .category-icon {
        font-size: 2rem;
        margin-bottom: 10px;
    }
</style>

<div class="main-content">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>📋 Manage FIRs</h2>
            <div class="input-group w-25">
                <input type="text" class="form-control" placeholder="Search FIRs...">
                <button class="btn btn-primary">Search</button>
            </div>
        </div>
        
        <div class="row">
            <?php foreach($firs as $fir): ?>
            <div class="col-md-4 mb-4">
                <div class="card fir-card h-100" onclick="window.location.href='fir_details.php?id=<?php echo $fir['id']; ?>'">
                    <div class="card-body">
                        <div class="category-icon">
                            <?php 
                                $icons = array(
                                    'Theft' => '💰',
                                    'Cyber Crime' => '💻',
                                    'Assault' => '👊',
                                    'Fraud' => '🎭',
                                    'Robbery' => '🔫'
                                );
                                // PHP 5 compatible icon selection
                                $icon = isset($icons[$fir['category']]) ? $icons[$fir['category']] : '📄';
                                echo $icon;
                            ?>
                        </div>
                        <h5 class="card-title"><?php echo $fir['category']; ?></h5>
                        <p class="card-text">
                            <strong>City:</strong> <?php echo $fir['city']; ?><br>
                            <strong>Date:</strong> <?php echo $fir['date']; ?>
                        </p>
                        <span class="status-badge status-<?php echo str_replace(' ', '', $fir['status']); ?>">
                            <?php echo $fir['status']; ?>
                        </span>
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