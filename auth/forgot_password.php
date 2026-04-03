<?php
// ==========================================
// FILE: auth/forgot_password.php
// Forgot Password Page
// ==========================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Online Crime Reporting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">

<div class="container">
    <div class="row min-vh-100 align-items-center justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-danger text-white text-center rounded-top-4 py-3">
                    <h3><i class="fas fa-lock me-2"></i>Forgot Password</h3>
                    <p>Enter your email to reset password</p>
                </div>
                <div class="card-body p-4">
                    <form action="reset_password.php" method="GET">
                        <div class="mb-3">
                            <label class="form-label">Registered Email</label>
                            <input type="email" class="form-control" placeholder="Enter your email address" required>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">Send Reset Link →</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="../index.php">← Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>