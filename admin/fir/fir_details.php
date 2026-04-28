<?php
// ==========================================
// FILE: admin/fir/fir_details.php
// FIR Details Page - NO HTML TAGS (navbar handles them)
// ==========================================

// Start session if not started
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

$fir_id = isset($_GET['id']) ? $_GET['id'] : 1;

// Dummy FIR Details
$fir_details = array(
    'id' => $fir_id,
    'category' => 'Theft',
    'city' => 'Mumbai',
    'status' => 'Under Investigation',
    'date' => '2024-01-15',
    'complainant' => 'Amit Sharma',
    'phone' => '9876543210',
    'email' => 'amit@example.com',
    'description' => 'Mobile phone and wallet stolen from railway station. The incident occurred on platform no. 3 between 6-7 PM.',
    'address' => 'Mumbai Central Railway Station, Mumbai',
    'officer' => 'Inspector Rajesh Kumar'
);

// Include admin navbar (contains all HTML head and body tags)
include_once ROOT_PATH . 'includes/admin_navbar.php';
?>

<style>
    /* Page specific styles */
    .detail-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 0 20px rgba(0,0,0,0.08);
    }
    
    .detail-label {
        font-weight: 600;
        color: #555;
        width: 150px;
        display: inline-block;
    }
</style>

<div class="main-content">
    <div class="container py-4">
        <div class="mb-3">
            <a href="manage_fir.php" class="btn btn-secondary">
                ← Back to FIRs
            </a>
        </div>
        
        <div class="card detail-card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">FIR Details #<?php echo $fir_details['id']; ?></h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <span class="detail-label">Category:</span>
                            <span class="detail-value"><?php echo $fir_details['category']; ?></span>
                        </div>
                        <div class="mb-3">
                            <span class="detail-label">City:</span>
                            <span class="detail-value"><?php echo $fir_details['city']; ?></span>
                        </div>
                        <div class="mb-3">
                            <span class="detail-label">Status:</span>
                            <span class="badge bg-info"><?php echo $fir_details['status']; ?></span>
                        </div>
                        <div class="mb-3">
                            <span class="detail-label">Date of Incident:</span>
                            <span class="detail-value"><?php echo $fir_details['date']; ?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <span class="detail-label">Complainant:</span>
                            <span class="detail-value"><?php echo $fir_details['complainant']; ?></span>
                        </div>
                        <div class="mb-3">
                            <span class="detail-label">Phone:</span>
                            <span class="detail-value"><?php echo $fir_details['phone']; ?></span>
                        </div>
                        <div class="mb-3">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value"><?php echo $fir_details['email']; ?></span>
                        </div>
                        <div class="mb-3">
                            <span class="detail-label">Officer:</span>
                            <span class="detail-value"><?php echo $fir_details['officer']; ?></span>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="mb-3">
                    <h6>Address of Incident:</h6>
                    <p><?php echo $fir_details['address']; ?></p>
                </div>
                
                <div class="mb-3">
                    <h6>Description:</h6>
                    <p><?php echo $fir_details['description']; ?></p>
                </div>
            </div>
            <div class="card-footer">
                <button class="btn btn-success" onclick="alert('Status updated successfully!')">Update Status</button>
                <button class="btn btn-primary" onclick="alert('PDF downloaded!')">Download PDF</button>
            </div>
        </div>
    </div>
</div>

<?php 
// Include admin footer (closes body and html tags)
include_once ROOT_PATH . 'includes/admin_footer.php'; 
?>