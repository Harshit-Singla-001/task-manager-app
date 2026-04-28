<?php
// ==========================================
// FILE: user/fir/file_fir.php
// File FIR Page - With Admin View-Only Mode
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

// Check if current user is admin (view-only mode)
$view_only_mode = isAdminUser();

include_once '../../includes/header.php';
?>

<div class="main-content">
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            
            <!-- ADMIN VIEW-ONLY WARNING -->
            <?php if($view_only_mode): ?>
                <div class="alert alert-warning text-center mb-4">
                    <i class="fas fa-eye me-2"></i>
                    <strong>View Only Mode:</strong> As an administrator, you can preview this form but cannot submit FIRs.
                </div>
            <?php endif; ?>
            
            <div class="card shadow">
                <div class="card-header bg-danger text-white">
                    <h4><i class="fas fa-gavel me-2"></i>File New FIR</h4>
                </div>
                <div class="card-body">
                    <form action="my_firs.php" method="GET" <?php echo $view_only_mode ? 'onsubmit="return false;"' : ''; ?>>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Incident Type <span class="text-danger">*</span></label>
                                <select class="form-select" <?php echo $view_only_mode ? 'disabled' : ''; ?> required>
                                    <option value="">Select Type</option>
                                    <option>Theft</option>
                                    <option>Cyber Crime</option>
                                    <option>Assault</option>
                                    <option>Fraud</option>
                                    <option>Harassment</option>
                                    <option>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Incident Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" <?php echo $view_only_mode ? 'disabled' : ''; ?> required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location of Incident <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" placeholder="Full address with landmark" <?php echo $view_only_mode ? 'disabled' : ''; ?> required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" rows="5" placeholder="Describe the incident in detail..." <?php echo $view_only_mode ? 'disabled' : ''; ?> required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Upload Evidence (if any)</label>
                            <input type="file" class="form-control" <?php echo $view_only_mode ? 'disabled' : ''; ?>>
                            <small class="text-muted">Supported: Images, PDF, DOC (Max 5MB)</small>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" <?php echo $view_only_mode ? 'disabled' : ''; ?> required>
                            <label class="form-check-label">I declare that the information provided is true to the best of my knowledge.</label>
                        </div>
                        
                        <?php if($view_only_mode): ?>
                            <button type="button" class="btn btn-secondary" disabled>
                                <i class="fas fa-eye me-2"></i> Submit FIR (Disabled - View Only)
                            </button>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i> 
                                    Admin accounts cannot submit FIRs. This is a preview only.
                                </small>
                            </div>
                        <?php else: ?>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-paper-plane me-2"></i> Submit FIR →
                            </button>
                        <?php endif; ?>
                        
                        <a href="../home.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<?php include_once '../../includes/footer.php'; ?>