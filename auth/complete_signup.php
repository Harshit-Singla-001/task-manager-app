<?php
// ==========================================
// FILE: auth/complete_signup.php
// Complete Signup - Set Password + Recovery Key
// ==========================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Signup - Online Crime Reporting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">

<div class="container">
    <div class="row min-vh-100 align-items-center justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-info text-white text-center rounded-top-4 py-3">
                    <h3><i class="fas fa-check-circle me-2"></i>Final Step</h3>
                    <p>Step 3 of 3: Set Password & Recovery Key</p>
                </div>
                <div class="card-body p-4">
                    <form action="../index.php" method="GET">
                        <div class="mb-3">
                            <label class="form-label">Set Password</label>
                            <input type="password" class="form-control" placeholder="Create a strong password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" placeholder="Confirm password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Recovery Key (Save it safely)</label>
                            <input type="text" class="form-control" value="RECOVERY-KEY-2024-ABCD" readonly>
                            <small class="text-muted">This key will help recover your account.</small>
                        </div>
                        <button type="submit" class="btn btn-info w-100 text-white">Complete Signup → Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>