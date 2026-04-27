<?php
// ==========================================
// FILE: user/pages/terms_of_service.php
// Terms of Service Page
// ==========================================
require_once '../../includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>

<?php include_once '../../includes/header.php'; ?>

<div class="main-content">
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <!-- Page Header -->
                <div class="text-center mb-5">
                    <i class="fas fa-gavel fa-3x text-primary mb-3"></i>
                    <h1 class="fw-bold">Terms of Service</h1>
                    <p class="text-muted">Last Updated: January 2026</p>
                    <div class="divider bg-primary mx-auto" style="width: 60px; height: 3px;"></div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="text-primary mb-3">1. Acceptance of Terms</h4>
                        <p>By accessing and using the Online Crime Reporting System, you agree to be bound by these Terms of Service. If you do not agree, please do not use this platform.</p>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="text-primary mb-3">2. Educational Purpose Disclaimer</h4>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>IMPORTANT:</strong> This project is developed for <strong>educational purposes only</strong>. It is NOT connected to any official law enforcement authority. All FIR records and case data are fictional.
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="text-primary mb-3">3. User Responsibilities</h4>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i> Provide accurate and truthful information when filing FIRs</li>
                            <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i> Keep your login credentials confidential</li>
                            <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i> Not use the system for fraudulent or malicious purposes</li>
                            <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i> Respect the privacy of other users</li>
                            <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i> Not upload offensive or illegal content</li>
                        </ul>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="text-primary mb-3">4. Account Termination</h4>
                        <p>We reserve the right to suspend or terminate accounts that violate these terms, including but not limited to:</p>
                        <ul>
                            <li>Submitting false information</li>
                            <li>Attempting to hack or breach system security</li>
                            <li>Harassing other users or staff</li>
                            <li>Using the system for illegal activities</li>
                        </ul>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="text-primary mb-3">5. Data Privacy</h4>
                        <p>Your personal data is handled according to our <a href="<?= BASE_URL ?>user/pages/privacy_policy.php">Privacy Policy</a>. We do not share your sensitive information with third parties.</p>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="text-primary mb-3">6. Limitation of Liability</h4>
                        <p>This system is provided "as is" without any warranties. We are not responsible for any damages arising from the use of this platform. For real emergencies, please contact local law enforcement immediately.</p>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="text-primary mb-3">7. Changes to Terms</h4>
                        <p>We may update these terms periodically. Continued use of the platform after changes constitutes acceptance of the new terms.</p>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body p-4 text-center">
                        <h5 class="text-primary">Contact Us</h5>
                        <p>For questions about these Terms, please contact us through the <a href="<?= BASE_URL ?>user/pages/contact.php">Contact Page</a>.</p>
                        <small class="text-muted">By using this platform, you acknowledge that you have read and understood these terms.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>
</body>
</html>