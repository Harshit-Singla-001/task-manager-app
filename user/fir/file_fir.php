<?php
// ==========================================
// FILE: user/fir/file_fir.php
// File FIR Page (Dummy Data)
// ==========================================
include_once '../../includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-danger text-white">
                    <h4><i class="fas fa-gavel me-2"></i>File New FIR</h4>
                </div>
                <div class="card-body">
                    <form action="my_firs.php" method="GET">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Incident Type <span class="text-danger">*</span></label>
                                <select class="form-select" required>
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
                                <input type="date" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location of Incident <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" placeholder="Full address with landmark" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" rows="5" placeholder="Describe the incident in detail..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Upload Evidence (if any)</label>
                            <input type="file" class="form-control">
                            <small class="text-muted">Supported: Images, PDF, DOC (Max 5MB)</small>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" required>
                            <label class="form-check-label">I declare that the information provided is true to the best of my knowledge.</label>
                        </div>
                        <button type="submit" class="btn btn-danger">Submit FIR →</button>
                        <a href="../home.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>