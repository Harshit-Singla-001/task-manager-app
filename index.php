<?php
// ==========================================
// ONLINE CRIME REPORTING SYSTEM
// COMPLETE BACKEND WITH CAPTCHA & AUTHENTICATION
// ==========================================
// This file: index.php (Login Page - Entry Point)
// With full backend validation, CAPTCHA, and database integration
// ==========================================

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// IMPORTANT FIX: Only redirect if user is trying to access login page while logged in
// But allow manual logout to work properly
$request_uri = $_SERVER['REQUEST_URI'];
$is_logout_request = (strpos($request_uri, 'logout') !== false);

if (!$is_logout_request && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // If already logged in, redirect to appropriate dashboard
    if (isset($_SESSION['login_type'])) {
        if ($_SESSION['login_type'] == 'user') {
            header("Location: user/home.php");
            exit();
        } elseif ($_SESSION['login_type'] == 'staff') {
            header("Location: admin/dashboard.php");
            exit();
        }
    }
}

// Include database connection
require_once 'config/db.php';
require_once 'config/constants.php';

// Initialize variables
$error_message = '';
$email = '';
$remember_me = '';
$captcha_error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $captcha_input = isset($_POST['captcha']) ? strtoupper(trim($_POST['captcha'])) : '';
    $remember_me = isset($_POST['remember']) ? 'yes' : '';
    
    $errors = array();
    
    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Validate password
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // Validate CAPTCHA
    if (empty($captcha_input)) {
        $captcha_error = "CAPTCHA is required";
        $errors[] = "CAPTCHA is required";
    } elseif (!isset($_SESSION['captcha'])) {
        $captcha_error = "CAPTCHA expired. Please refresh.";
        $errors[] = "CAPTCHA expired. Please refresh.";
    } elseif ($captcha_input !== $_SESSION['captcha']) {
        $captcha_error = "Invalid CAPTCHA code";
        $errors[] = "Invalid CAPTCHA code";
    }
    
    // If no validation errors, proceed with login
    if (empty($errors)) {
        
        $database = new Database();
        $pdo = $database->getConnection();
        
        if ($pdo != null) {
            try {
                // First, check in users table
                $query = "SELECT user_id, full_name, email, password_hash, role, status, failed_attempts 
                          FROM users 
                          WHERE email = :email";
                $stmt = $pdo->prepare($query);
                $stmt->execute(array('email' => $email));
                $user = $stmt->fetch();
                
                if ($user && $user['status'] == 'active') {
                    // Verify password
                    if (password_verify($password, $user['password_hash'])) {
                        // Password correct - login successful
                        
                        // Reset failed attempts
                        $update = $pdo->prepare("UPDATE users SET failed_attempts = 0, last_login = NOW() WHERE user_id = :user_id");
                        $update->execute(array('user_id' => $user['user_id']));
                        
                        // Set session variables
                        $_SESSION['logged_in'] = true;
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['full_name'] = $user['full_name'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['login_type'] = 'user';
                        
                        // Set remember me cookie if checked (7 days)
                        if ($remember_me == 'yes') {
                            setcookie('remember_email', $email, time() + (86400 * 7), "/");
                        }
                        
                        // Clear CAPTCHA from session
                        unset($_SESSION['captcha']);
                        
                        // Redirect to user home
                        header("Location: user/home.php");
                        exit();
                        
                    } else {
                        // Wrong password - increment failed attempts
                        $failed = $user['failed_attempts'] + 1;
                        $update = $pdo->prepare("UPDATE users SET failed_attempts = :failed WHERE user_id = :user_id");
                        $update->execute(array('failed' => $failed, 'user_id' => $user['user_id']));
                        
                        $error_message = "Invalid email or password";
                    }
                } else {
                    // Check in staff table for admin login
                    $query = "SELECT staff_id, full_name, email, password_hash, role, status, failed_attempts 
                              FROM staff 
                              WHERE email = :email";
                    $stmt = $pdo->prepare($query);
                    $stmt->execute(array('email' => $email));
                    $staff = $stmt->fetch();
                    
                    if ($staff && $staff['status'] == 'active') {
                        // Verify password
                        if (password_verify($password, $staff['password_hash'])) {
                            // Password correct - login successful
                            
                            // Reset failed attempts
                            $update = $pdo->prepare("UPDATE staff SET failed_attempts = 0, last_login = NOW() WHERE staff_id = :staff_id");
                            $update->execute(array('staff_id' => $staff['staff_id']));
                            
                            // Set session variables
                            $_SESSION['logged_in'] = true;
                            $_SESSION['staff_id'] = $staff['staff_id'];
                            $_SESSION['full_name'] = $staff['full_name'];
                            $_SESSION['email'] = $staff['email'];
                            $_SESSION['role'] = $staff['role'];
                            $_SESSION['login_type'] = 'staff';
                            
                            // Set remember me cookie if checked
                            if ($remember_me == 'yes') {
                                setcookie('remember_email', $email, time() + (86400 * 7), "/");
                            }
                            
                            // Clear CAPTCHA from session
                            unset($_SESSION['captcha']);
                            
                            // Redirect to admin dashboard
                            header("Location: admin/dashboard.php");
                            exit();
                            
                        } else {
                            // Wrong password
                            $error_message = "Invalid email or password";
                        }
                    } else if ($user && $user['status'] != 'active') {
                        $error_message = "Your account is suspended. Please contact support.";
                    } else {
                        $error_message = "Invalid email or password";
                    }
                }
                
                // Clear CAPTCHA on failed attempt
                unset($_SESSION['captcha']);
                
            } catch (PDOException $e) {
                error_log("Login Error: " . $e->getMessage());
                $error_message = "An error occurred. Please try again later.";
            }
        } else {
            $error_message = "Database connection error.";
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

// Pre-fill email from cookie if exists
if (empty($email) && isset($_COOKIE['remember_email'])) {
    $email = $_COOKIE['remember_email'];
    $remember_me = 'checked';
}

// Clear CAPTCHA for new page load if not set
if (!isset($_SESSION['captcha'])) {
    // CAPTCHA will be generated by captcha.php when requested
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Crime Reporting System | Login</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .captcha-image {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            cursor: pointer;
            background-color: #f8f9fa;
            height: 50px;
            width: 100%;
            object-fit: cover;
        }
        .refresh-captcha {
            cursor: pointer;
            transition: transform 0.3s;
            font-size: 18px !important;
        }
        .refresh-captcha:hover {
            transform: rotate(180deg);
        }
        .password-field {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            font-size: 20px !important;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            z-index: 10;
        }
        .password-toggle:hover {
            color: #0d6efd;
        }
    </style>
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
                    
                    <!-- Error Message Display -->
                    <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="loginForm">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" 
                                   class="form-control" 
                                   name="email" 
                                   id="email"
                                   value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
                                   placeholder="Enter your email" 
                                   required>
                            <div id="emailError" class="invalid-feedback"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="password-field">
                                <input type="password" 
                                       class="form-control" 
                                       name="password" 
                                       id="password"
                                       placeholder="Enter your password" 
                                       required>
                                <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                            </div>
                        </div>
                        
                        <!-- CAPTCHA Section -->
                        <div class="mb-3">
                            <label class="form-label">Security Verification</label>
                            <div class="row g-2 align-items-center">
                                <div class="col-7">
                                    <img src="includes/captcha.php" 
                                         alt="CAPTCHA" 
                                         id="captchaImage" 
                                         class="captcha-image"
                                         onclick="refreshCaptcha()"
                                         title="Click to refresh">
                                </div>
                                <div class="col-5">
                                    <button type="button" 
                                            class="btn btn-outline-secondary w-100" 
                                            onclick="refreshCaptcha()">
                                        <i class="fas fa-sync-alt refresh-captcha"></i> Refresh
                                    </button>
                                </div>
                            </div>
                            <div class="mt-2">
                                <input type="text" 
                                       class="form-control" 
                                       name="captcha" 
                                       id="captcha"
                                       placeholder="Enter the code shown" 
                                       maxlength="5"
                                       style="text-transform: uppercase"
                                       required>
                                <?php if (!empty($captcha_error)): ?>
                                <small class="text-danger"><?php echo $captcha_error; ?></small>
                                <?php endif; ?>
                                <small class="text-muted">Code is case-insensitive</small>
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember" <?php echo ($remember_me == 'checked') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        
                        <button type="submit" name="login" class="btn btn-primary w-100 py-2 fw-bold">
                            <i class="fas fa-sign-in-alt me-2" style="font-size: 16px;"></i>Login →
                        </button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <a href="auth/forgot_password.php" class="text-decoration-none">Forgot Password?</a>
                        <span class="mx-2">|</span>
                        <a href="auth/signup.php" class="text-decoration-none">Create New Account</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Password Show/Hide Toggle
const togglePassword = document.getElementById('togglePassword');
const password = document.getElementById('password');

if (togglePassword) {
    togglePassword.addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
}

// Refresh CAPTCHA Function
function refreshCaptcha() {
    const captchaImage = document.getElementById('captchaImage');
    const timestamp = new Date().getTime();
    captchaImage.src = 'includes/captcha.php?' + timestamp;
    
    const captchaInput = document.getElementById('captcha');
    if (captchaInput) {
        captchaInput.value = '';
        captchaInput.classList.remove('is-invalid');
    }
}

// CAPTCHA Input - Auto uppercase
const captchaInput = document.getElementById('captcha');
if (captchaInput) {
    captchaInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
}

// Email validation
const emailInput = document.getElementById('email');
if (emailInput) {
    emailInput.addEventListener('blur', function() {
        const email = this.value;
        const emailError = document.getElementById('emailError');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            this.classList.add('is-invalid');
            emailError.innerHTML = 'Please enter a valid email address';
        } else {
            this.classList.remove('is-invalid');
            emailError.innerHTML = '';
        }
    });
}

// Form validation
const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
        const email = document.getElementById('email');
        const password = document.getElementById('password');
        const captcha = document.getElementById('captcha');
        let hasError = false;
        
        if (!email.value) {
            email.classList.add('is-invalid');
            document.getElementById('emailError').innerHTML = 'Email is required';
            hasError = true;
        }
        
        if (!password.value) {
            password.classList.add('is-invalid');
            hasError = true;
        } else {
            password.classList.remove('is-invalid');
        }
        
        if (!captcha.value) {
            captcha.classList.add('is-invalid');
            hasError = true;
        } else if (captcha.value.length !== 5) {
            captcha.classList.add('is-invalid');
            hasError = true;
        } else {
            captcha.classList.remove('is-invalid');
        }
        
        if (hasError) {
            e.preventDefault();
        }
    });
}

// Remove invalid class on input
document.querySelectorAll('.form-control').forEach(input => {
    input.addEventListener('input', function() {
        this.classList.remove('is-invalid');
    });
});
</script>
</body>
</html>