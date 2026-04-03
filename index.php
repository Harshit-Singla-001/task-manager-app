<?php
// ==========================================
// ONLINE CRIME REPORTING SYSTEM
// PHASE 1: UI STRUCTURE & NAVIGATION FLOW
// ==========================================
// This file: index.php (Login Page - Entry Point)
// No backend logic, only page structure and redirections
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Crime Reporting System | Login</title>
    <!-- Bootstrap 5 CSS (for clean, simple layout) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

<div class="container">
    <div class="row min-vh-100 align-items-center justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-primary text-white text-center rounded-top-4 py-4">
                    <h3 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Online Crime Reporting</h3>
                    <p class="mb-0 mt-2">Login to your account</p>
                </div>
                <div class="card-body p-5">
                    <!-- Dummy Login Form - No backend validation -->
                    <form action="user/home.php" method="GET">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" placeholder="Enter your email" value="demo@example.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" placeholder="Enter password" value="password" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Login →</button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <a href="auth/forgot_password.php" class="text-decoration-none">Forgot Password?</a>
                        <span class="mx-2">|</span>
                        <a href="auth/signup.php" class="text-decoration-none">Create New Account</a>
                    </div>
                    
                    <!-- Demo notice for phase 1 -->
                    <div class="alert alert-info mt-4 small">
                        <i class="fas fa-info-circle"></i> Demo Mode: Click Login to access Home Page (No validation)
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>