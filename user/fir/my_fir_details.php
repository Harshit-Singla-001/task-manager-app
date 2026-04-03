<?php
// ==========================================
// FILE: user/fir/my_fir_details.php
// My FIR Details Page
// ==========================================
include_once '../../includes/header.php';
$fir_id = isset($_GET['id']) ? $_GET['id'] : 1;
?>

<div class="container my-5">
    <div class="card shadow">
        <div class="card-header bg-info text-white">
            <h4><i class="fas fa-file-alt me-2"></i>FIR Details #FIR00<?php echo $fir_id; ?></h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr><th width="40%">FIR Number</th><td>#FIR00<?php echo $fir_id; ?></td></tr>
                        <tr><th>Date of Incident</th><td>2024-01-15</td></tr>
                        <tr><th>Incident Type</th><td>Cyber Crime</td></tr>
                        <tr><th>Location</th><td>Cyber Cell, Electronic City</td></tr>
                        <tr><th>Status</th><td><span class="badge bg-warning">Under Investigation</span></td></tr>
                        <tr><th>Filed On</th><td>2024-01-16</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">Description</div>
                        <div class="card-body">
                            <p>Online fraud where victim lost ₹50,000 to a fake loan app.</p>
                            <hr>
                            <strong>Police Station:</strong> Cyber Crime PS, Sector 62<br>
                            <strong>IO Name:</strong> Inspector Sharma<br>
                            <strong>Contact:</strong> 9876543210
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <a href="my_firs.php" class="btn btn-secondary">← Back to My FIRs</a>
                <a href="../home.php" class="btn btn-primary">Go to Dashboard</a>
            </div>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>