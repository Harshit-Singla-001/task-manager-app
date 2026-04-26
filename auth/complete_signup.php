<?php
// ==========================================
// FILE: auth/complete_signup.php
// Step 3 of 3: Set Password + Recovery Key
// PHP 5.x Compatible
// ==========================================

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database and constants
require_once '../config/db.php';
require_once '../config/constants.php';

// ==========================================
// 2. Page Access Control (STRICT)
// ==========================================
if (!isset($_SESSION['signup_data']) || !isset($_SESSION['email_verified']) || $_SESSION['email_verified'] !== true) {
    header("Location: signup.php");
    exit();
}

// ==========================================
// 6. Recovery Key Generation (on page load - first time only)
// ==========================================
if (!isset($_SESSION['recovery_key'])) {
    // Fixed words list
    $recovery_words = array('apple', 'tiger', 'moon', 'glass', 'river', 'stone', 'light', 'paper', 'green', 'chair');
    
    // Shuffle the words randomly
    shuffle($recovery_words);
    
    // Convert to space-separated string
    $_SESSION['recovery_key'] = implode(' ', $recovery_words);
}

// Get data from session for display
$full_name = $_SESSION['signup_data']['full_name'];
$email = $_SESSION['signup_data']['email'];
$recovery_key = $_SESSION['recovery_key'];

// Initialize variables
$error_message = '';
$success_message = '';
$password = '';
$confirm_password = '';

// ==========================================
// 8. Final Submission Logic
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complete_signup'])) {
    
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $saved_recovery_key = isset($_POST['saved_recovery_key']) ? $_POST['saved_recovery_key'] : '';
    
    $errors = array();
    
    // ==========================================
    // Validation Step 1: Password Rules
    // ==========================================
    
    // Check if password is empty
    if (empty($password)) {
        $errors[] = "Password is required";
    } 
    // Check minimum length (8 characters)
    elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    // Check for uppercase letter
    elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least 1 uppercase letter";
    }
    // Check for lowercase letter
    elseif (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least 1 lowercase letter";
    }
    // Check for number
    elseif (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least 1 number";
    }
    // Check for special character
    elseif (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $errors[] = "Password must contain at least 1 special character (!@#$%^&* etc.)";
    }
    
    // Check if passwords match
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // Check if recovery key checkbox is checked
    if ($saved_recovery_key != 'yes') {
        $errors[] = "You must confirm that you have saved your recovery key";
    }
    
    // ==========================================
    // If no errors, proceed with registration
    // ==========================================
    if (empty($errors)) {
        
        // Get signup data from session
        $signup_data = $_SESSION['signup_data'];
        
        // Hash the password using BCRYPT
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Hash the recovery key using SHA256
        $recovery_key_hash = hash('sha256', $_SESSION['recovery_key']);
        
        // Handle PAN (if not provided, set NULL)
        $pan_number = null;
        if (isset($signup_data['pan']) && !empty($signup_data['pan'])) {
            $pan_number = $signup_data['pan'];
        }
        
        // ==========================================
        // Database Insertion with Prepared Statement
        // ==========================================
        $database = new Database();
        $pdo = $database->getConnection();
        
        if ($pdo != null) {
            try {
                $query = "INSERT INTO users (full_name, dob, aadhaar_number, pan_number, address, phone_number, email, password_hash, recovery_key_hash, role, created_at) 
                          VALUES (:full_name, :dob, :aadhaar_number, :pan_number, :address, :phone_number, :email, :password_hash, :recovery_key_hash, 'user', NOW())";
                
                $stmt = $pdo->prepare($query);
                
                $stmt->execute(array(
                    'full_name' => $signup_data['full_name'],
                    'dob' => $signup_data['dob'],
                    'aadhaar_number' => $signup_data['aadhaar'],
                    'pan_number' => $pan_number,
                    'address' => $signup_data['address'],
                    'phone_number' => $signup_data['phone'],
                    'email' => $signup_data['email'],
                    'password_hash' => $password_hash,
                    'recovery_key_hash' => $recovery_key_hash
                ));
                
                // Get the last inserted ID
                $user_id = $pdo->lastInsertId();
                
                // ==========================================
                // 9. Auto Login - Set All Session Variables
                // ==========================================
                $_SESSION['user_id'] = $user_id;
                $_SESSION['email'] = $signup_data['email'];
                $_SESSION['role'] = 'user';
                $_SESSION['full_name'] = $signup_data['full_name'];
                $_SESSION['logged_in'] = true;
                $_SESSION['login_type'] = 'user';
                
                // ==========================================
                // 10. Cleanup - Remove Temporary Session Data
                // ==========================================
                unset($_SESSION['signup_data']);
                unset($_SESSION['signup_temp']);
                unset($_SESSION['recovery_key']);
                unset($_SESSION['email_verified']);
                
                // Redirect to user dashboard
                header("Location: " . BASE_URL . "user/home.php");
                exit();
                
            } catch (PDOException $e) {
                error_log("Signup Error: " . $e->getMessage());
                $error_message = "Registration failed. Please try again later.";
            }
        } else {
            $error_message = "Database connection error. Please try again later.";
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

// Function to generate strong random password
function generateStrongPassword() {
    $uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    $lowercase = 'abcdefghijkmnopqrstuvwxyz';
    $numbers = '23456789';
    $special = '!@#$%&*?';
    
    $password = '';
    
    // Ensure at least one of each required type
    $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
    $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
    $password .= $numbers[rand(0, strlen($numbers) - 1)];
    $password .= $special[rand(0, strlen($special) - 1)];
    
    // Fill remaining characters (total length 10-12)
    $all_chars = $uppercase . $lowercase . $numbers . $special;
    $remaining = rand(6, 8);
    
    for ($i = 0; $i < $remaining; $i++) {
        $password .= $all_chars[rand(0, strlen($all_chars) - 1)];
    }
    
    // Shuffle the password
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
    <title>Complete Signup - Online Crime Reporting</title>
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
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #dee2e6;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin: 0 10px;
            position: relative;
        }
        .step.active {
            background-color: #28a745;
            color: white;
        }
        .step.completed {
            background-color: #28a745;
            color: white;
        }
        .step-line {
            width: 50px;
            height: 2px;
            background-color: #dee2e6;
            margin-top: 20px;
        }
        .password-strength {
            font-size: 12px;
            margin-top: 5px;
        }
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }
        .recovery-key-box {
            background-color: #f8f9fa;
            border: 2px dashed #17a2b8;
            border-radius: 10px;
            padding: 15px;
            font-family: monospace;
            font-size: 16px;
            word-break: break-all;
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
            font-size: 14px;
            padding: 5px 10px;
        }
        .btn-generate:hover {
            background-color: #5a6268;
            color: white;
        }
        .alert-custom {
            border-radius: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step completed">1</div>
                <div class="step-line"></div>
                <div class="step completed">2</div>
                <div class="step-line"></div>
                <div class="step active">3</div>
            </div>
            
            <div class="card shadow-lg border-0">
                <div class="card-header bg-success text-white text-center py-4">
                    <h3 class="mb-0"><i class="fas fa-check-circle me-2"></i>Final Step</h3>
                    <p class="mb-0 mt-2">Step 3 of 3: Set Password & Recovery Key</p>
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
                    <?php endif; ?>
                    
                    <!-- User Info Display -->
                    <div class="alert alert-info">
                        <i class="fas fa-user me-2"></i>
                        <strong><?php echo htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'); ?></strong><br>
                        <i class="fas fa-envelope me-2"></i>
                        <?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    
                    <form method="POST" action="" id="signupForm">
                        <!-- Password Section -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-lock me-1 text-success"></i>Set Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       name="password" 
                                       id="password"
                                       placeholder="Create a strong password" 
                                       required>
                                <button type="button" class="btn btn-outline-secondary btn-generate" id="generatePasswordBtn" title="Generate Strong Password">
                                    <i class="fas fa-magic"></i> Generate
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="copyPasswordBtn" title="Copy Password">
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                            </div>
                            <div class="password-strength mt-2">
                                <small id="passwordStrength"></small>
                            </div>
                            <ul class="requirement-list" id="passwordRequirements">
                                <li id="req-length"><i class="fas fa-circle me-1"></i> Minimum 8 characters</li>
                                <li id="req-upper"><i class="fas fa-circle me-1"></i> At least 1 uppercase letter</li>
                                <li id="req-lower"><i class="fas fa-circle me-1"></i> At least 1 lowercase letter</li>
                                <li id="req-number"><i class="fas fa-circle me-1"></i> At least 1 number</li>
                                <li id="req-special"><i class="fas fa-circle me-1"></i> At least 1 special character (!@#$%^&*)</li>
                            </ul>
                        </div>
                        
                        <!-- Confirm Password -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-lock me-1 text-success"></i>Confirm Password <span class="text-danger">*</span>
                            </label>
                            <div class="password-field">
                                <input type="password" 
                                       class="form-control" 
                                       name="confirm_password" 
                                       id="confirm_password"
                                       placeholder="Confirm your password" 
                                       required>
                                <i class="fas fa-eye password-toggle" id="toggleConfirmPassword"></i>
                            </div>
                            <small id="passwordMatchMsg" class="text-muted"></small>
                        </div>
                        
                        <!-- Recovery Key Section -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-key me-1 text-warning"></i>Recovery Key
                            </label>
                            <div class="recovery-key-box mb-2" id="recoveryKeyBox">
                                <?php echo htmlspecialchars($recovery_key, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-info mb-2" id="copyRecoveryKeyBtn">
                                <i class="fas fa-copy me-1"></i> Copy Recovery Key
                            </button>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="saved_recovery_key" id="savedRecoveryKey" value="yes">
                                <label class="form-check-label" for="savedRecoveryKey">
                                    <strong>I have saved this recovery key in a safe place</strong>
                                </label>
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-exclamation-triangle me-1 text-warning"></i>
                                This key is required to recover your account if you forget your password.
                            </small>
                        </div>
                        
                        <button type="submit" name="complete_signup" class="btn btn-success w-100 py-2" id="submitBtn">
                            <i class="fas fa-user-check me-2"></i>Complete Signup & Continue
                        </button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt me-1"></i>
                            Your information is secure with us
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Password toggle functionality
const passwordInput = document.getElementById('password');
const confirmPasswordInput = document.getElementById('confirm_password');
const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');

if (toggleConfirmPassword) {
    toggleConfirmPassword.addEventListener('click', function() {
        const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmPasswordInput.setAttribute('type', type);
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
}

// Password strength validation
function validatePassword() {
    const password = passwordInput.value;
    
    // Requirements
    const lengthValid = password.length >= 8;
    const upperValid = /[A-Z]/.test(password);
    const lowerValid = /[a-z]/.test(password);
    const numberValid = /[0-9]/.test(password);
    const specialValid = /[!@#$%^&*(),.?":{}|<>]/.test(password);
    
    // Update requirement list with icons
    updateRequirement('req-length', lengthValid);
    updateRequirement('req-upper', upperValid);
    updateRequirement('req-lower', lowerValid);
    updateRequirement('req-number', numberValid);
    updateRequirement('req-special', specialValid);
    
    // Calculate strength
    let strength = 0;
    if (lengthValid) strength++;
    if (upperValid) strength++;
    if (lowerValid) strength++;
    if (numberValid) strength++;
    if (specialValid) strength++;
    
    const strengthText = document.getElementById('passwordStrength');
    if (password.length === 0) {
        strengthText.innerHTML = '';
        strengthText.className = '';
    } else if (strength <= 2) {
        strengthText.innerHTML = '⚠️ Weak password';
        strengthText.className = 'strength-weak';
    } else if (strength <= 4) {
        strengthText.innerHTML = '⚡ Medium password';
        strengthText.className = 'strength-medium';
    } else {
        strengthText.innerHTML = '✅ Strong password';
        strengthText.className = 'strength-strong';
    }
    
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

// Check password match
function checkPasswordMatch() {
    const password = passwordInput.value;
    const confirmPassword = confirmPasswordInput.value;
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
    
    // Ensure at least one of each
    password += uppercase.charAt(Math.floor(Math.random() * uppercase.length));
    password += lowercase.charAt(Math.floor(Math.random() * lowercase.length));
    password += numbers.charAt(Math.floor(Math.random() * numbers.length));
    password += special.charAt(Math.floor(Math.random() * special.length));
    
    // Fill remaining (total 10-12 chars)
    const allChars = uppercase + lowercase + numbers + special;
    const remaining = Math.floor(Math.random() * 3) + 6; // 6-8 more chars
    
    for (let i = 0; i < remaining; i++) {
        password += allChars.charAt(Math.floor(Math.random() * allChars.length));
    }
    
    // Shuffle the password
    password = password.split('').sort(function() { return 0.5 - Math.random(); }).join('');
    
    return password;
}

// Event listeners
if (passwordInput) {
    passwordInput.addEventListener('keyup', function() {
        validatePassword();
        checkPasswordMatch();
    });
}

if (confirmPasswordInput) {
    confirmPasswordInput.addEventListener('keyup', checkPasswordMatch);
}

// Generate strong password button
const generateBtn = document.getElementById('generatePasswordBtn');
if (generateBtn) {
    generateBtn.addEventListener('click', function() {
        const strongPassword = generateStrongPassword();
        passwordInput.value = strongPassword;
        confirmPasswordInput.value = strongPassword;
        validatePassword();
        checkPasswordMatch();
        
        // Flash the copy button to indicate user should copy
        const copyBtn = document.getElementById('copyPasswordBtn');
        if (copyBtn) {
            copyBtn.style.backgroundColor = '#ffc107';
            copyBtn.style.borderColor = '#ffc107';
            setTimeout(function() {
                copyBtn.style.backgroundColor = '';
                copyBtn.style.borderColor = '';
            }, 1000);
        }
        
        // Show notification
        const notification = document.createElement('div');
        notification.innerHTML = '<small class="text-success"><i class="fas fa-info-circle"></i> Click "Copy" to save your password</small>';
        notification.style.position = 'absolute';
        notification.style.right = '10px';
        notification.style.top = '-25px';
        notification.style.fontSize = '12px';
        notification.style.backgroundColor = '#d4edda';
        notification.style.padding = '2px 8px';
        notification.style.borderRadius = '4px';
        notification.style.whiteSpace = 'nowrap';
        
        const inputGroup = document.querySelector('.input-group');
        const existingNotification = inputGroup.querySelector('.copy-notification');
        if (existingNotification) {
            existingNotification.remove();
        }
        notification.className = 'copy-notification';
        inputGroup.style.position = 'relative';
        inputGroup.appendChild(notification);
        
        setTimeout(function() {
            notification.remove();
        }, 3000);
    });
}

// ==========================================
// COPY PASSWORD BUTTON FUNCTIONALITY
// ==========================================
const copyPasswordBtn = document.getElementById('copyPasswordBtn');
if (copyPasswordBtn) {
    copyPasswordBtn.addEventListener('click', function() {
        const password = passwordInput.value;
        
        // Check if password is empty
        if (password === '') {
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Empty!';
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
        
        this.innerHTML = '<i class="fas fa-check"></i> Copied!';
        this.style.backgroundColor = '#28a745';
        this.style.borderColor = '#28a745';
        this.style.color = 'white';
        
        // Highlight the password field briefly
        const originalBorder = passwordInput.style.borderColor;
        passwordInput.style.borderColor = '#28a745';
        passwordInput.style.boxShadow = '0 0 0 0.2rem rgba(40, 167, 69, 0.25)';
        
        // Reset after 2 seconds
        setTimeout(() => {
            this.innerHTML = originalText;
            this.style.backgroundColor = originalBgColor;
            this.style.borderColor = originalBorderColor;
            this.style.color = '';
            passwordInput.style.borderColor = originalBorder;
            passwordInput.style.boxShadow = '';
        }, 2000);
    });
}

// Copy recovery key
const copyRecoveryBtn = document.getElementById('copyRecoveryKeyBtn');
if (copyRecoveryBtn) {
    copyRecoveryBtn.addEventListener('click', function() {
        const recoveryKeyBox = document.getElementById('recoveryKeyBox');
        const recoveryKey = recoveryKeyBox.innerText;
        
        // Create temporary textarea for copying
        const textarea = document.createElement('textarea');
        textarea.value = recoveryKey;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        
        // Show success message
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fas fa-check me-1"></i> Copied!';
        setTimeout(() => {
            this.innerHTML = originalText;
        }, 2000);
    });
}

// Form validation before submit
const signupForm = document.getElementById('signupForm');
if (signupForm) {
    signupForm.addEventListener('submit', function(e) {
        const isPasswordValid = validatePassword();
        const isMatch = checkPasswordMatch();
        const isRecoverySaved = document.getElementById('savedRecoveryKey').checked;
        
        if (!isPasswordValid) {
            e.preventDefault();
            alert('Please ensure your password meets all requirements');
            return false;
        }
        
        if (!isMatch) {
            e.preventDefault();
            alert('Passwords do not match');
            return false;
        }
        
        if (!isRecoverySaved) {
            e.preventDefault();
            alert('Please confirm that you have saved your recovery key');
            return false;
        }
        
        return true;
    });
}

// Initial validation
validatePassword();
</script>
</body>
</html>