<?php
// ==========================================
// FILE: auth/verify_otp.php
// Verify OTP Page (Dummy OTP: 123456)
// ==========================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Online Crime Reporting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">

<div class="container">
    <div class="row min-vh-100 align-items-center justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-warning text-dark text-center rounded-top-4 py-3">
                    <h3><i class="fas fa-key me-2"></i>Verify OTP</h3>
                    <p>Step 2 of 3: OTP Verification</p>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Demo OTP: <strong>123456</strong> (No actual SMS sent)
                    </div>
                    <form action="complete_signup.php" method="GET">
                        <div class="mb-3">
                            <label class="form-label">Enter OTP</label>
                            <input type="text" class="form-control" placeholder="Enter 6-digit OTP" maxlength="6" required>
                        </div>
                        <button type="submit" class="btn btn-warning w-100">Verify & Continue →</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="signup.php" class="text-decoration-none">← Back to Signup</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>