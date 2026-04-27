<?php
// ==========================================
// FILE: user/pages/contact.php
// Contact Page
// ==========================================
include_once '../../includes/header.php';
?>

<div class="main-content">
<div class="container my-5">
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h4><i class="fas fa-envelope"></i> Send us a Message</h4>
                </div>
                <div class="card-body">
                    <form action="#" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Your Name</label>
                            <input type="text" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea class="form-control" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning">Send Message</button>
                    </form>
                    <div class="mt-3 small text-muted">* This is a demo form. No actual email will be sent.</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h4>Contact Information</h4>
                </div>
                <div class="card-body">
                    <p><i class="fas fa-map-marker-alt"></i> <strong>Head Office:</strong> Cyber Crime Unit, Electronic City, Bangalore - 560100</p>
                    <p><i class="fas fa-phone-alt"></i> <strong>Phone:</strong> +91-80-12345678</p>
                    <p><i class="fas fa-envelope"></i> <strong>Email:</strong> helpdesk@crimereporting.gov</p>
                    <p><i class="fas fa-clock"></i> <strong>Working Hours:</strong> 9:00 AM - 6:00 PM (Monday to Friday)</p>
                    <hr>
                    <p><strong>Emergency:</strong> Dial 100 for immediate police assistance (24x7).</p>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<?php include_once '../../includes/footer.php'; ?>