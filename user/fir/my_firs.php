<?php
// ==========================================
// FILE: user/fir/my_firs.php
// My FIRs Page - Redirect Admin to Home
// ==========================================

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once '../../config/constants.php';
require_once '../../includes/auth_check.php';

// Check if user is logged in
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['error'] = "Please login first.";
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// ✅ CHECK IF ADMIN - REDIRECT TO HOME PAGE
if(isAdminUser()) {
    $_SESSION['error'] = "Admin accounts cannot view My FIRs Page. Users only.";
    header("Location: " . BASE_URL . "user/home.php");
    exit();
}

include_once '../../includes/header.php';
?>

<div class="main-content">
<div class="container my-5">
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h4><i class="fas fa-list me-2"></i>My FIRs</h4>
        </div>
        <div class="card-body">
            <?php if(true): // Dummy condition to show FIRs exist ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>FIR No.</th>
                                <th>Date Filed</th>
                                <th>Incident Type</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#FIR001</td>
                                <td>2024-01-15</td>
                                <td>Cyber Crime</td>
                                <td><span class="badge bg-warning">Under Investigation</span></td>
                                <td><a href="my_fir_details.php?id=1" class="btn btn-sm btn-info">View Details</a></td>
                            </tr>
                            <tr>
                                <td>#FIR002</td>
                                <td>2024-02-20</td>
                                <td>Theft</td>
                                <td><span class="badge bg-success">Resolved</span></td>
                                <td><a href="my_fir_details.php?id=2" class="btn btn-sm btn-info">View Details</a></td>
                            </tr>
                            <tr>
                                <td>#FIR003</td>
                                <td>2024-03-10</td>
                                <td>Harassment</td>
                                <td><span class="badge bg-danger">Rejected</span></td>
                                <td><a href="my_fir_details.php?id=3" class="btn btn-sm btn-info">View Details</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No FIRs filed yet. <a href="file_fir.php">Click here to file your first FIR</a></div>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>

<?php include_once '../../includes/footer.php'; ?>