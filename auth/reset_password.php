<?php
// ==========================================
// FILE: auth/reset_password.php
// Reset Password - Set New Password
// PHP 5.x Compatible
// ==========================================

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if password reset is allowed
if (!isset($_SESSION['password_reset_allowed']) || $_SESSION['password_reset_allowed'] !== true) {
    header("Location: forgot_password.php");
    exit();
}

// Include database
require_once '../config/db.php';
require_once '../config/constants.php';

$error_message = '';
$success_message = '';
$password = '';
$confirm_password = '';

// Handle password reset submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])) {
    
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $errors = array();
    
    // Validate password strength
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least 1 uppercase letter";
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least 1 lowercase letter";
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least 1 number";
    } elseif (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $errors[] = "Password must contain at least 1 special character";
    }
    
    // Check if passwords match
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($errors)) {
        // Hash the new password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $email = $_SESSION['reset_email'];
        
        $database = new Database();
        $pdo = $database->getConnection();
        
        if ($pdo != null) {
            try {
                // Update password in database
                $query = "UPDATE users SET password_hash = :password_hash, updated_at = NOW() WHERE email = :email";
                $stmt = $pdo->prepare($query);
                $stmt->execute(array(
                    'password_hash' => $password_hash,
                    'email' => $email
                ));
                
                // Clear all reset sessions
                unset($_SESSION['password_reset_allowed']);
                unset($_SESSION['reset_email']);
                unset($_SESSION['reset_user_id']);
                unset($_SESSION['reset_full_name']);
                unset($_SESSION['reset_recovery_hash']);
                unset($_SESSION['reset_otp']);
                unset($_SESSION['reset_otp_expiry']);
                unset($_SESSION['reset_failed_attempts']);
                unset($_SESSION['reset_block_until']);
                
                $success_message = "Password has been reset successfully!";
                
                // Redirect to login after 2 seconds
                header("refresh:2;url=../index.php");
                
            } catch (PDOException $e) {
                $error_message = "An error occurred. Please try again.";
            }
        } else {
            $error_message = "Database connection error.";
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

// Function to generate strong password
function generateStrongPassword() {
    $uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    $lowercase = 'abcdefghijkmnopqrstuvwxyz';
    $numbers = '23456789';
    $special = '!@#$%&*?';
    
    $password = '';
    $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
    $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
    $password .= $numbers[rand(0, strlen($numbers) - 1)];
    $password .= $special[rand(0, strlen($special) - 1)];
    
    $all_chars = $uppercase . $lowercase . $numbers . $special;
    $remaining = rand(6, 8);
    
    for ($i = 0; $i < $remaining; $i++) {
        $password .= $all_chars[rand(0, strlen($all_chars) - 1)];
    }
    
    $password_array = str_split($password);
    shuffle($password_array);
    
    return implode('', $password_array);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Online Crime Reporting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .card {
            border-radius: 20px;
            overflow: hidden;
            border: none;
        }
        .card-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            padding: 25px;
        }
        .password-field {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            z-index: 10;
            font-size: 14px;
        }
        .password-toggle:hover {
            color: #28a745;
        }
        .requirement-list {
            list-style: none;
            padding-left: 0;
            font-size: 12px;
            margin-top: 5px;
        }
        .requirement-list li {
            margin-bottom: 3px;
        }
        .requirement-list li.valid {
            color: #28a745;
        }
        .requirement-list li.invalid {
            color: #dc3545;
        }
        .btn-generate {
            background-color: #6c757d;
            color: white;
            font-size: 13px;
            padding: 5px 10px;
        }
        .btn-generate:hover {
            background-color: #5a6268;
            color: white;
        }
        .btn-icon {
            font-size: 13px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg">
                <div class="card-header text-white text-center">
                    <h3 class="mb-0"><i class="fas fa-key me-2"></i>Reset Password</h3>
                    <p class="mb-0 mt-2">Create a new secure password</p>
                </div>
                <div class="card-body p-4">
                    
                    <!-- Error Message Display -->
                    <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Success Message Display -->
                    <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <div class="text-center mt-3">
                        <a href="../index.php" class="btn btn-primary">Go to Login</a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (empty($success_message)): ?>
                    <form method="POST" action="" id="resetForm">
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-lock me-1 text-success"></i>New Password
                            </label>
                            <div class="input-group">
                                <div class="password-field flex-grow-1">
                                    <input type="password" 
                                           class="form-control" 
                                           name="password" 
                                           id="password"
                                           placeholder="Create a strong password" 
                                           required>
                                </div>
                                <button type="button" class="btn btn-outline-secondary" id="copyPasswordBtn" title="Copy Password">
                                    <i class="fas fa-copy btn-icon"></i> 
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-generate" id="generatePasswordBtn">
                                    <i class="fas fa-magic btn-icon"></i> Generate
                                </button>
                            </div>
                            <ul class="requirement-list" id="passwordRequirements">
                                <li id="req-length"><i class="fas fa-circle me-1"></i> Minimum 8 characters</li>
                                <li id="req-upper"><i class="fas fa-circle me-1"></i> At least 1 uppercase letter</li>
                                <li id="req-lower"><i class="fas fa-circle me-1"></i> At least 1 lowercase letter</li>
                                <li id="req-number"><i class="fas fa-circle me-1"></i> At least 1 number</li>
                                <li id="req-special"><i class="fas fa-circle me-1"></i> At least 1 special character</li>
                            </ul>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-lock me-1 text-success"></i>Confirm Password
                            </label>
                            <div class="password-field">
                                <input type="password" 
                                       class="form-control" 
                                       name="confirm_password" 
                                       id="confirm_password"
                                       placeholder="Confirm your password" 
                                       required>
                                <i class="btn-outline-secondary fas fa-eye password-toggle" id="toggleConfirmPassword"></i>
                            </div>
                            <small id="passwordMatchMsg" class="text-muted"></small>
                        </div>
                        
                        <button type="submit" name="reset_password" class="btn btn-success w-100 py-2">
                            <i class="fas fa-save btn-icon me-2"></i>Reset Password
                        </button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="forgot_password.php" class="text-decoration-none">
                            <i class="fas fa-arrow-left btn-icon me-1"></i>Back to Forgot Password
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Password toggle functionality
const togglePassword = document.getElementById('togglePassword');
const password = document.getElementById('password');
const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
const confirmPassword = document.getElementById('confirm_password');

if (togglePassword) {
    togglePassword.addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
}

if (toggleConfirmPassword) {
    toggleConfirmPassword.addEventListener('click', function() {
        const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmPassword.setAttribute('type', type);
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
}

// Copy Password Button Functionality
const copyPasswordBtn = document.getElementById('copyPasswordBtn');
if (copyPasswordBtn) {
    copyPasswordBtn.addEventListener('click', function() {
        const password = document.getElementById('password').value;
        
        // Check if password is empty
        if (password === '') {
            // Show temporary notification
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-exclamation-triangle btn-icon"></i> Empty!';
            this.style.backgroundColor = '#dc3545';
            this.style.borderColor = '#dc3545';
            this.style.color = 'white';
            
            setTimeout(() => {
                this.innerHTML = originalText;
                this.style.backgroundColor = '';
                this.style.borderColor = '';
                this.style.color = '';
            }, 2000);
            return;
        }
        
        // Create temporary textarea for copying
        const textarea = document.createElement('textarea');
        textarea.value = password;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        
        // Change button appearance to show success
        const originalText = this.innerHTML;
        const originalBgColor = this.style.backgroundColor;
        const originalBorderColor = this.style.borderColor;
        
        this.innerHTML = '<i class="fas fa-check btn-icon"></i> Copied!';
        this.style.backgroundColor = '#28a745';
        this.style.borderColor = '#28a745';
        this.style.color = 'white';
        
        // Highlight the password field briefly
        const passwordField = document.getElementById('password');
        const originalBorder = passwordField.style.borderColor;
        passwordField.style.borderColor = '#28a745';
        passwordField.style.boxShadow = '0 0 0 0.2rem rgba(40, 167, 69, 0.25)';
        
        // Reset after 2 seconds
        setTimeout(() => {
            this.innerHTML = originalText;
            this.style.backgroundColor = originalBgColor;
            this.style.borderColor = originalBorderColor;
            this.style.color = '';
            passwordField.style.borderColor = originalBorder;
            passwordField.style.boxShadow = '';
        }, 2000);
    });
}

// Password validation
function validatePassword() {
    const password = document.getElementById('password').value;
    
    const lengthValid = password.length >= 8;
    const upperValid = /[A-Z]/.test(password);
    const lowerValid = /[a-z]/.test(password);
    const numberValid = /[0-9]/.test(password);
    const specialValid = /[!@#$%^&*(),.?":{}|<>]/.test(password);
    
    updateRequirement('req-length', lengthValid);
    updateRequirement('req-upper', upperValid);
    updateRequirement('req-lower', lowerValid);
    updateRequirement('req-number', numberValid);
    updateRequirement('req-special', specialValid);
    
    return (lengthValid && upperValid && lowerValid && numberValid && specialValid);
}

function updateRequirement(elementId, isValid) {
    const element = document.getElementById(elementId);
    if (element) {
        const icon = element.querySelector('i');
        if (isValid) {
            element.className = 'valid';
            icon.className = 'fas fa-check-circle me-1';
        } else {
            element.className = 'invalid';
            icon.className = 'fas fa-circle me-1';
        }
    }
}

function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const matchMsg = document.getElementById('passwordMatchMsg');
    
    if (confirmPassword.length > 0) {
        if (password === confirmPassword) {
            matchMsg.innerHTML = '<i class="fas fa-check-circle text-success"></i> Passwords match';
            matchMsg.className = 'text-success';
            return true;
        } else {
            matchMsg.innerHTML = '<i class="fas fa-times-circle text-danger"></i> Passwords do not match';
            matchMsg.className = 'text-danger';
            return false;
        }
    } else {
        matchMsg.innerHTML = '';
        return false;
    }
}

// Generate strong password
function generateStrongPassword() {
    const uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    const lowercase = 'abcdefghijkmnopqrstuvwxyz';
    const numbers = '23456789';
    const special = '!@#$%&*?';
    
    let password = '';
    password += uppercase.charAt(Math.floor(Math.random() * uppercase.length));
    password += lowercase.charAt(Math.floor(Math.random() * lowercase.length));
    password += numbers.charAt(Math.floor(Math.random() * numbers.length));
    password += special.charAt(Math.floor(Math.random() * special.length));
    
    const allChars = uppercase + lowercase + numbers + special;
    const remaining = Math.floor(Math.random() * 3) + 6;
    
    for (let i = 0; i < remaining; i++) {
        password += allChars.charAt(Math.floor(Math.random() * allChars.length));
    }
    
    password = password.split('').sort(function() { return 0.5 - Math.random(); }).join('');
    return password;
}

// Event listeners
password.addEventListener('keyup', function() {
    validatePassword();
    checkPasswordMatch();
});

confirmPassword.addEventListener('keyup', checkPasswordMatch);

document.getElementById('generatePasswordBtn').addEventListener('click', function() {
    const strongPassword = generateStrongPassword();
    password.value = strongPassword;
    confirmPassword.value = strongPassword;
    validatePassword();
    checkPasswordMatch();
});

// Form validation
document.getElementById('resetForm').addEventListener('submit', function(e) {
    if (!validatePassword()) {
        e.preventDefault();
        alert('Please ensure your password meets all requirements');
        return false;
    }
    if (!checkPasswordMatch()) {
        e.preventDefault();
        alert('Passwords do not match');
        return false;
    }
    return true;
});

validatePassword();
</script>
</body>
</html>