<?php
// ==========================================
// FILE: user/pages/privacy_policy.php
// Privacy Policy Page
// ==========================================
require_once '../../includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - <?= SITE_NAME ?></title>
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
                    <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                    <h1 class="fw-bold">Privacy Policy</h1>
                    <p class="text-muted">Last Updated: January 2026</p>
                    <div class="divider bg-primary mx-auto" style="width: 60px; height: 3px;"></div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="text-primary mb-3">Information We Collect</h4>
                        <p>We collect the following information when you register and use our platform:</p>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <h6 class="fw-bold">Personal Information:</h6>
                                <ul>
                                    <li>Full Name</li>
                                    <li>Date of Birth</li>
                                    <li>Aadhaar Number (encrypted)</li>
                                    <li>PAN Number (encrypted)</li>
                                    <li>Address</li>
                                    <li>Phone Number</li>
                                    <li>Email Address</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold">FIR Information:</h6>
                                <ul>
                                    <li>Incident details and description</li>
                                    <li>Location information</li>
                                    <li>Uploaded evidence images</li>
                                    <li>Suspect and witness descriptions</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="text-primary mb-3">How We Use Your Information</h4>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item bg-transparent"><i class="fas fa-database text-primary me-2"></i> Process and store FIR complaints</li>
                            <li class="list-group-item bg-transparent"><i class="fas fa-chart-line text-primary me-2"></i> Track case status and updates</li>
                            <li class="list-group-item bg-transparent"><i class="fas fa-envelope text-primary me-2"></i> Respond to your queries and feedback</li>
                            <li class="list-group-item bg-transparent"><i class="fas fa-chart-simple text-primary me-2"></i> Generate statistical reports (anonymized data only)</li>
                            <li class="list-group-item bg-transparent"><i class="fas fa-shield text-primary me-2"></i> Improve system security and performance</li>
                        </ul>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="text-primary mb-3">Data Protection & Security</h4>
                        <div class="alert alert-info">
                            <i class="fas fa-lock me-2"></i>
                            <strong>We prioritize your data security with:</strong>
                        </div>
                        <ul>
                            <li>Password hashing using bcrypt algorithm</li>
                            <li>Encrypted storage of sensitive documents (Aadhaar, PAN)</li>
                            <li>Prepared SQL statements to prevent injection attacks</li>
                            <li>Session-based authentication with timeouts</li>
                            <li>HTTPS-ready configuration</li>
                        </ul>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="text-primary mb-3">What Information Is Public?</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="bg-light p-3 rounded mb-3">
                                    <h6 class="text-success"><i class="fas fa-check-circle me-2"></i>Publicly Visible:</h6>
                                    <ul class="mb-0">
                                        <li>Crime Category</li>
                                        <li>City of Incident</li>
                                        <li>Incident Date</li>
                                        <li>Case Status</li>
                                        <li>Limited Description</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light p-3 rounded">
                                    <h6 class="text-danger"><i class="fas fa-times-circle me-2"></i>Never Public:</h6>
                                    <ul class="mb-0">
                                        <li>Full Address</li>
                                        <li>Identity Proofs</li>
                                        <li>Contact Information</li>
                                        <li>Suspect/Witness Details</li>
                                        <li>Evidence Images</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="text-primary mb-3">Cookies & Tracking</h4>
                        <p>We use session cookies to maintain your login state and preferences. These cookies are essential for platform functionality and do not track your browsing activity outside our site.</p>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="text-primary mb-3">Third-Party Disclosure</h4>
                        <p>We do not sell, trade, or transfer your personally identifiable information to outside parties. This does not include trusted third parties who assist us in operating our platform, as long as they agree to keep this information confidential.</p>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="text-primary mb-3">Your Rights</h4>
                        <p>As a user, you have the right to:</p>
                        <ul>
                            <li>Access your personal data stored in our system</li>
                            <li>Request correction of inaccurate information</li>
                            <li>Request deletion of your account and associated data</li>
                            <li>Opt-out of non-essential communications</li>
                        </ul>
                        <p class="mt-2">To exercise these rights, please contact us through the <a href="<?= BASE_URL ?>user/pages/contact.php">Contact Page</a>.</p>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body p-4 text-center">
                        <h5 class="text-primary">Educational Project Disclaimer</h5>
                        <p class="mb-0 small">This is an academic project demonstration. For real emergencies, always contact your local police station or dial 100.</p>
                        <hr>
                        <small class="text-muted">Last reviewed: January 2026 | Version 1.0</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>
</body>
</html>