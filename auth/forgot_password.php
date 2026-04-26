<?php
// ==========================================
// FILE: auth/forgot_password.php
// Forgot Password - Complete Flow (FIXED VERSION)
// PHP 5.x Compatible
// ==========================================

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once '../config/db.php';
require_once '../config/constants.php';
require_once '../vendor/PHPMailer/PHPMailer.php';
require_once '../vendor/PHPMailer/SMTP.php';
require_once '../vendor/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Fixed 10 words for ALL users
$FIXED_WORDS = array('apple', 'tiger', 'moon', 'glass', 'river', 'stone', 'light', 'paper', 'green', 'chair');

// Initialize variables
$error_message = '';
$success_message = '';
$step = 1; // 1: Email, 2: Method Selection, 3: Recovery Key, 4: OTP
$email = '';
$selected_method = '';
$show_captcha = false;
$captcha_error = '';

// Function to send OTP email
function sendResetOTP($to_email, $otp, $full_name) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;
        
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to_email, $full_name);
        
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset OTP - Online Crime Reporting System';
        
        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 500px; margin: 0 auto; background: #f8f9fa; border-radius: 10px; overflow: hidden; }
                .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
                .content { padding: 30px; text-align: center; }
                .otp-code { font-size: 32px; font-weight: bold; color: #28a745; letter-spacing: 5px; margin: 20px 0; }
                .footer { background: #e9ecef; padding: 15px; text-align: center; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Password Reset Request</h2>
                </div>
                <div class="content">
                    <p>Dear <strong>' . htmlspecialchars($full_name) . '</strong>,</p>
                    <p>We received a request to reset your password.</p>
                    <p>Use the following OTP to verify your identity:</p>
                    <div class="otp-code">' . $otp . '</div>
                    <p>This OTP is valid for <strong>5 minutes</strong>.</p>
                    <p>If you did not request this, please ignore this email.</p>
                </div>
                <div class="footer">
                    <p>&copy; 2024 Online Crime Reporting System</p>
                </div>
            </div>
        </body>
        </html>';
        
        $mail->AltBody = "Dear " . $full_name . ",\n\nPassword Reset OTP: " . $otp . "\n\nValid for 5 minutes.";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("OTP Email failed: " . $mail->ErrorInfo);
        return false;
    }
}

// Check if blocked
$is_blocked = false;
if (isset($_SESSION['reset_block_until']) && time() < $_SESSION['reset_block_until']) {
    $is_blocked = true;
    $remaining_minutes = ceil(($_SESSION['reset_block_until'] - time()) / 60);
    $error_message = "Too many failed attempts. Please try again after {$remaining_minutes} minutes.";
}

// Initialize failed attempts counter
if (!isset($_SESSION['reset_failed_attempts'])) {
    $_SESSION['reset_failed_attempts'] = 0;
}

// Show CAPTCHA after 3 failed attempts
if ($_SESSION['reset_failed_attempts'] >= 3) {
    $show_captcha = true;
}

// ==========================================
// STEP 1: Handle Email Submission
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_email']) && !$is_blocked) {
    
    $email = trim($_POST['email']);
    $captcha_input = isset($_POST['captcha']) ? strtoupper(trim($_POST['captcha'])) : '';
    
    // Validate CAPTCHA if needed
    if ($show_captcha) {
        if (empty($captcha_input) || !isset($_SESSION['captcha']) || $captcha_input !== $_SESSION['captcha']) {
            $captcha_error = "Invalid CAPTCHA code";
            $error_message = "Invalid CAPTCHA code";
        } else {
            // Reset CAPTCHA after successful validation
            unset($_SESSION['captcha']);
        }
    }
    
    if (empty($error_message)) {
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Invalid email or credentials";
        } else {
            // Check if email exists in database
            $database = new Database();
            $pdo = $database->getConnection();
            
            if ($pdo != null) {
                try {
                    // Check in users table
                    $query = "SELECT user_id, full_name, email, recovery_key_hash, status FROM users WHERE email = :email AND status = 'active'";
                    $stmt = $pdo->prepare($query);
                    $stmt->execute(array('email' => $email));
                    $user = $stmt->fetch();
                    
                    if ($user) {
                        // Email exists - store in session
                        $_SESSION['reset_email'] = $email;
                        $_SESSION['reset_user_id'] = $user['user_id'];
                        $_SESSION['reset_full_name'] = $user['full_name'];
                        $_SESSION['reset_recovery_hash'] = $user['recovery_key_hash'];
                        
                        // Reset failed attempts for this session
                        $_SESSION['reset_failed_attempts'] = 0;
                        
                        // Move to step 2 (Method Selection)
                        $step = 2;
                        $success_message = "Please select a recovery method.";
                    } else {
                        // Email not found - generic error
                        $_SESSION['reset_failed_attempts']++;
                        $error_message = "Invalid email or credentials";
                    }
                } catch (PDOException $e) {
                    $error_message = "An error occurred. Please try again.";
                }
            } else {
                $error_message = "Database connection error.";
            }
        }
    }
}

// ==========================================
// STEP 2: Handle Method Selection
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['select_method']) && isset($_SESSION['reset_email'])) {
    
    $selected_method = $_POST['recovery_method'];
    
    if ($selected_method == 'recovery_key') {
        // Use fixed words, shuffle them
        global $FIXED_WORDS;
        $shuffled_words = $FIXED_WORDS;
        shuffle($shuffled_words);
        
        $_SESSION['reset_shuffled_words'] = $shuffled_words;
        $_SESSION['reset_correct_order'] = $FIXED_WORDS; // Store correct order for reference
        
        $step = 3; // Recovery Key Drag & Drop
        
    } elseif ($selected_method == 'otp') {
        // Generate OTP
        $otp = rand(100000, 999999);
        $_SESSION['reset_otp'] = $otp;
        $_SESSION['reset_otp_expiry'] = time() + 300; // 5 minutes
        $_SESSION['reset_otp_last_sent'] = time();
        
        // Send OTP immediately
        $email = $_SESSION['reset_email'];
        $full_name = $_SESSION['reset_full_name'];
        
        if (sendResetOTP($email, $otp, $full_name)) {
            $step = 4; // OTP Verification
            $success_message = "OTP has been sent to your email address!";
        } else {
            $error_message = "Failed to send OTP. Please try again.";
            $step = 2;
        }
    } else {
        $error_message = "Please select a valid recovery method";
        $step = 2;
    }
}

// ==========================================
// STEP 3: Handle Recovery Key Verification
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify_recovery_key']) && isset($_SESSION['reset_email'])) {
    
    $user_order = isset($_POST['word_order']) ? $_POST['word_order'] : '';
    $recovery_key_string = '';
    
    if (!empty($user_order)) {
        $order_array = explode(',', $user_order);
        $selected_words = array();
        
        foreach ($order_array as $index) {
            $index = intval($index);
            if (isset($_SESSION['reset_shuffled_words'][$index])) {
                $selected_words[] = $_SESSION['reset_shuffled_words'][$index];
            }
        }
        
        // Normalize EXACT same way as signup
        $recovery_key_string = trim(strtolower(implode(' ', $selected_words)));
    }
    
    // Hash and compare
    $user_recovery_hash = hash('sha256', $recovery_key_string);
    
    if ($user_recovery_hash === $_SESSION['reset_recovery_hash']) {
        $_SESSION['password_reset_allowed'] = true;
        header("Location: reset_password.php");
        exit();
    } else {
        $_SESSION['reset_failed_attempts']++;
        
        if ($_SESSION['reset_failed_attempts'] >= 5) {
            $_SESSION['reset_block_until'] = time() + 900;
            $error_message = "Too many failed attempts. Please try again after 15 minutes.";
        } else {
            $remaining = 5 - $_SESSION['reset_failed_attempts'];
            $error_message = "Invalid recovery key order. Attempts left: {$remaining}";
        }
        
        // reshuffle again
        $shuffled = $FIXED_WORDS;
        shuffle($shuffled);
        $_SESSION['reset_shuffled_words'] = $shuffled;
        $step = 3;
    }
}

// ==========================================
// STEP 4: Handle OTP Verification
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify_otp']) && isset($_SESSION['reset_email'])) {
    
    $entered_otp = trim($_POST['otp']);
    
    if (empty($entered_otp)) {
        $error_message = "Please enter the OTP";
        $step = 4;
    } elseif (time() > $_SESSION['reset_otp_expiry']) {
        $error_message = "OTP has expired. Please request a new one.";
        $step = 4;
    } elseif ($entered_otp == $_SESSION['reset_otp']) {
        // Success - allow password reset
        $_SESSION['password_reset_allowed'] = true;
        header("Location: reset_password.php");
        exit();
    } else {
        $_SESSION['reset_failed_attempts']++;
        
        if ($_SESSION['reset_failed_attempts'] >= 5) {
            $_SESSION['reset_block_until'] = time() + 900;
            $error_message = "Too many failed attempts. Please try again after 15 minutes.";
        } else {
            $remaining = 5 - $_SESSION['reset_failed_attempts'];
            $error_message = "Invalid OTP. Please try again. Attempts remaining: {$remaining}";
        }
        $step = 4;
    }
}

// ==========================================
// Handle Resend OTP (AJAX)
// ==========================================
if (isset($_GET['action']) && $_GET['action'] == 'resend_otp' && isset($_SESSION['reset_email'])) {
    header('Content-Type: application/json');
    
    $last_sent = isset($_SESSION['reset_otp_last_sent']) ? $_SESSION['reset_otp_last_sent'] : 0;
    $time_since = time() - $last_sent;
    
    if ($time_since < 60) {
        echo json_encode(array('success' => false, 'message' => 'Please wait ' . (60 - $time_since) . ' seconds before requesting again.'));
        exit();
    }
    
    $otp = rand(100000, 999999);
    $_SESSION['reset_otp'] = $otp;
    $_SESSION['reset_otp_expiry'] = time() + 300;
    $_SESSION['reset_otp_last_sent'] = time();
    
    $email = $_SESSION['reset_email'];
    $full_name = $_SESSION['reset_full_name'];
    
    if (sendResetOTP($email, $otp, $full_name)) {
        echo json_encode(array('success' => true, 'message' => 'OTP resent successfully!'));
    } else {
        echo json_encode(array('success' => false, 'message' => 'Failed to send OTP. Please try again.'));
    }
    exit();
}

// Refresh CAPTCHA
if (isset($_GET['action']) && $_GET['action'] == 'refresh_captcha') {
    header('Content-Type: application/json');
    echo json_encode(array('success' => true));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Online Crime Reporting</title>
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
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            padding: 25px;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        .step-circle {
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
        .step-circle.active {
            background-color: #dc3545;
            color: white;
        }
        .step-circle.completed {
            background-color: #28a745;
            color: white;
        }
        .step-line {
            width: 50px;
            height: 2px;
            background-color: #dee2e6;
            margin-top: 20px;
        }
        .captcha-image {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            cursor: pointer;
            height: 50px;
            width: 100%;
            object-fit: cover;
        }
        .refresh-captcha {
            cursor: pointer;
            transition: transform 0.3s;
            font-size: 12px;
        }
        .refresh-captcha:hover {
            transform: rotate(180deg);
        }
        .method-card {
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid #dee2e6;
        }
        .method-card:hover {
            border-color: #dc3545;
            transform: translateY(-5px);
        }
        .method-card.selected {
            border-color: #dc3545;
            background-color: #fff5f5;
        }
        /* Drag & Drop Styles - FIXED */
        .words-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
            min-height: 120px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .draggable-word {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: move;
            user-select: none;
            transition: all 0.3s;
            display: inline-block;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .draggable-word:active {
            cursor: grabbing;
        }
        .draggable-word.dragging {
            opacity: 0.5;
        }
        .slots-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 20px 0;
            min-height: 120px;
        }
        .slot {
            background-color: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 10px 15px;
            min-width: 90px;
            text-align: center;
            transition: all 0.3s;
            position: relative;
        }
        .slot.filled {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-color: #28a745;
            color: white;
        }
        .slot.drag-over {
            border-color: #dc3545;
            background-color: #fff5f5;
        }
        .slot .slot-number {
            font-size: 10px;
            display: block;
            margin-bottom: 5px;
            opacity: 0.7;
        }
        .slot .slot-word {
            font-weight: bold;
        }
        .slot .remove-btn {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .slot .remove-btn:hover {
            background: #c82333;
        }
        .otp-input {
            font-size: 24px;
            letter-spacing: 10px;
            text-align: center;
            font-weight: bold;
        }
        .timer {
            font-size: 20px;
            font-weight: bold;
            font-family: monospace;
        }
        .timer-expired {
            color: #dc3545;
        }
        .btn-icon {
            font-size: 13px;
        }
        .form-label i {
            font-size: 13px;
        }
        .start-over {
            margin-top: 15px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step-circle <?php echo ($step > 1) ? 'completed' : ($step == 1 ? 'active' : ''); ?>">1</div>
                <div class="step-line"></div>
                <div class="step-circle <?php echo ($step > 2) ? 'completed' : ($step == 2 ? 'active' : ''); ?>">2</div>
                <div class="step-line"></div>
                <div class="step-circle <?php echo ($step == 3 || $step == 4) ? 'active' : ''; ?>">3</div>
            </div>
            
            <div class="card shadow-lg">
                <div class="card-header text-white text-center">
                    <h3 class="mb-0"><i class="fas fa-lock me-2"></i>Forgot Password</h3>
                    <p class="mb-0 mt-2">
                        <?php
                        if ($step == 1) echo "Step 1: Enter your email address";
                        elseif ($step == 2) echo "Step 2: Choose recovery method";
                        elseif ($step == 3) echo "Step 3: Arrange recovery words in correct order";
                        elseif ($step == 4) echo "Step 3: Enter OTP sent to your email";
                        ?>
                    </p>
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
                    
                    <?php if ($is_blocked): ?>
                    <div class="alert alert-danger text-center">
                        <i class="fas fa-hourglass-half me-2"></i>
                        <?php echo $error_message; ?>
                        <div class="mt-3">
                            <a href="../index.php" class="btn btn-primary">Back to Login</a>
                        </div>
                    </div>
                    
                    <?php elseif ($step == 1): ?>
                    <!-- STEP 1: Email Input -->
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-envelope me-1 text-danger"></i>Registered Email Address
                            </label>
                            <input type="email" 
                                   class="form-control" 
                                   name="email" 
                                   value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
                                   placeholder="Enter your registered email" 
                                   required>
                        </div>
                        
                        <?php if ($show_captcha): ?>
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-shield-alt me-1 text-danger"></i>Security Verification
                            </label>
                            <div class="row g-2 align-items-center">
                                <div class="col-7">
                                    <img src="../includes/captcha.php" 
                                         alt="CAPTCHA" 
                                         id="captchaImage" 
                                         class="captcha-image"
                                         onclick="refreshCaptcha()">
                                </div>
                                <div class="col-5">
                                    <button type="button" class="btn btn-outline-secondary w-100" onclick="refreshCaptcha()">
                                        <i class="fas fa-sync-alt refresh-captcha"></i> Refresh
                                    </button>
                                </div>
                            </div>
                            <div class="mt-2">
                                <input type="text" 
                                       class="form-control" 
                                       name="captcha" 
                                       id="captcha"
                                       placeholder="Enter CAPTCHA code" 
                                       maxlength="5"
                                       style="text-transform: uppercase"
                                       required>
                                <?php if (!empty($captcha_error)): ?>
                                <small class="text-danger"><?php echo $captcha_error; ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <button type="submit" name="submit_email" class="btn btn-danger w-100 py-2">
                            <i class="fas fa-arrow-right btn-icon me-2"></i>Continue
                        </button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="../index.php" class="text-decoration-none">
                            <i class="fas fa-arrow-left btn-icon me-1"></i>Back to Login
                        </a>
                    </div>
                    
                    <?php elseif ($step == 2): ?>
                    <!-- STEP 2: Method Selection -->
                    <form method="POST" action="" id="methodForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card method-card h-100" data-method="recovery_key">
                                    <div class="card-body text-center">
                                        <i class="fas fa-key fa-3x text-danger mb-3"></i>
                                        <h5>Recovery Key</h5>
                                        <p class="small text-muted">Use your 10-word recovery key</p>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="recovery_method" value="recovery_key" id="method_recovery">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card method-card h-100" data-method="otp">
                                    <div class="card-body text-center">
                                        <i class="fas fa-envelope fa-3x text-danger mb-3"></i>
                                        <h5>Email OTP</h5>
                                        <p class="small text-muted">Receive OTP on registered email</p>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="recovery_method" value="otp" id="method_otp">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <input type="hidden" name="select_method" value="1">
                        <button type="submit" class="btn btn-danger w-100 py-2 mt-4" id="continueBtn" disabled>
                            <i class="fas fa-check-circle btn-icon me-2"></i>Continue
                        </button>
                    </form>
                    
                    <?php elseif ($step == 3): ?>
                    <!-- STEP 3: Recovery Key Drag & Drop (FIXED) -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Instructions:</strong> Drag and drop the words below into the correct order as you set during signup.<br>
                        <small>All 10 slots must be filled to verify.</small>
                    </div>
                    
                    <form method="POST" action="" id="recoveryForm">
                        <label class="form-label fw-bold">
                            <i class="fas fa-arrows-alt me-1 text-danger"></i>Available Words (Drag these)
                        </label>
                        <div class="words-container" id="wordsContainer">
                            <?php 
                            $shuffled_words = $_SESSION['reset_shuffled_words'];
                            foreach ($shuffled_words as $index => $word): 
                            ?>
                            <div class="draggable-word" draggable="true" data-word="<?php echo htmlspecialchars($word, ENT_QUOTES, 'UTF-8'); ?>" data-index="<?php echo $index; ?>">
                                <?php echo htmlspecialchars($word, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <label class="form-label fw-bold mt-3">
                            <i class="fas fa-layer-group me-1 text-success"></i>Drop Area (Arrange in correct order - Slots 1 to 10)
                        </label>
                        <div class="slots-container" id="slotsContainer">
                            <?php for ($i = 0; $i < 10; $i++): ?>
                            <div class="slot" data-slot="<?php echo $i; ?>">
                                <span class="slot-number"><?php echo $i + 1; ?></span>
                                <span class="slot-word"></span>
                                <span class="remove-btn" onclick="removeFromSlot(<?php echo $i; ?>)" style="display: none;">×</span>
                            </div>
                            <?php endfor; ?>
                        </div>
                        
                        <input type="hidden" name="word_order" id="wordOrder" value="">
                        <input type="hidden" name="verify_recovery_key" value="1">
                        
                        <button type="submit" class="btn btn-danger w-100 py-2 mt-3" id="verifyRecoveryBtn" disabled>
                            <i class="fas fa-check-circle btn-icon me-2"></i>Verify Recovery Key
                        </button>
                    </form>
                    
                    <!-- Start Over Option (ADDED) -->
                    <div class="start-over">
                        <a href="forgot_password.php" class="text-decoration-none">
                            <i class="fas fa-arrow-left btn-icon me-1"></i>Start Over
                        </a>
                    </div>
                    
                    <?php elseif ($step == 4): ?>
                    <!-- STEP 4: OTP Verification -->
                    <div class="alert alert-info">
                        <i class="fas fa-envelope me-2"></i>
                        OTP has been sent to <strong><?php echo htmlspecialchars($_SESSION['reset_email'], ENT_QUOTES, 'UTF-8'); ?></strong>
                    </div>
                    
                    <div class="text-center mb-3">
                        <div class="small text-muted">OTP expires in:</div>
                        <div id="otpTimer" class="timer">05:00</div>
                    </div>
                    
                    <form method="POST" action="" id="otpForm">
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-key me-1 text-danger"></i>Enter OTP
                            </label>
                            <input type="text" 
                                   class="form-control otp-input" 
                                   name="otp" 
                                   id="otp"
                                   placeholder="000000" 
                                   maxlength="6"
                                   autocomplete="off"
                                   required>
                        </div>
                        
                        <input type="hidden" name="verify_otp" value="1">
                        
                        <button type="submit" class="btn btn-danger w-100 py-2" id="verifyOtpBtn">
                            <i class="fas fa-check-circle btn-icon me-2"></i>Verify OTP
                        </button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-link" id="resendOtpBtn">
                            <i class="fas fa-redo-alt btn-icon me-1"></i>Resend OTP
                        </button>
                        <span class="text-muted mx-2">|</span>
                        <a href="forgot_password.php" class="text-decoration-none">
                            <i class="fas fa-arrow-left btn-icon me-1"></i>Start Over
                        </a>
                    </div>
                    <div id="resendMessage" class="text-center mt-2 small"></div>
                    
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ==========================================
// Method Selection
// ==========================================
const methodCards = document.querySelectorAll('.method-card');
const continueBtn = document.getElementById('continueBtn');

if (methodCards.length) {
    methodCards.forEach(card => {
        card.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;
            
            methodCards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            
            if (continueBtn) continueBtn.disabled = false;
        });
    });
}

// ==========================================
// Drag & Drop Functionality (COMPLETELY REWRITTEN)
// ==========================================
let currentSlots = new Array(10).fill(null);

// Render slots
function renderSlots() {
    const slots = document.querySelectorAll('.slot');
    let filled = 0;

    slots.forEach((slot, i) => {
        const wordSpan = slot.querySelector('.slot-word');
        const removeBtn = slot.querySelector('.remove-btn');

        if (currentSlots[i] !== null) {
            wordSpan.innerText = currentSlots[i].word;
            slot.classList.add('filled');
            removeBtn.style.display = 'flex';
            filled++;
        } else {
            wordSpan.innerText = '';
            slot.classList.remove('filled');
            removeBtn.style.display = 'none';
        }
    });

    document.getElementById('verifyRecoveryBtn').disabled = (filled !== 10);

    updateWordOrder();
}

// Update hidden input
function updateWordOrder() {
    let order = [];

    currentSlots.forEach(item => {
        if (item !== null) {
            order.push(item.index); // ✅ PURE INDEX
        }
    });

    document.getElementById('wordOrder').value = order.join(',');
}

// Remove word
function removeFromSlot(i) {
    if (currentSlots[i] !== null) {
        const item = currentSlots[i];

        // recreate word div
        const div = document.createElement('div');
        div.className = 'draggable-word';
        div.setAttribute('draggable', true);
        div.setAttribute('data-word', item.word);
        div.setAttribute('data-index', item.index);
        div.innerText = item.word;

        document.getElementById('wordsContainer').appendChild(div);

        currentSlots[i] = null;
        addDragEvents();
        renderSlots();
    }
}

// Drag events
function addDragEvents() {
    document.querySelectorAll('.draggable-word').forEach(el => {
        el.ondragstart = (e) => {
            e.dataTransfer.setData('word', el.dataset.word);
            e.dataTransfer.setData('index', el.dataset.index);
        };
    });

    document.querySelectorAll('.slot').forEach(slot => {
        slot.ondragover = (e) => e.preventDefault();

        slot.ondrop = (e) => {
            e.preventDefault();

            const index = parseInt(slot.dataset.slot);

            if (currentSlots[index] !== null) return;

            const word = e.dataTransfer.getData('word');
            const wordIndex = e.dataTransfer.getData('index');

            currentSlots[index] = {
                word: word,
                index: wordIndex
            };

            // remove from list
            document.querySelectorAll('.draggable-word').forEach(el => {
                if (el.dataset.index === wordIndex) {
                    el.remove();
                }
            });

            renderSlots();
        };
    });
}

// init
if (document.getElementById('wordsContainer')) {
    addDragEvents();
    renderSlots();
}
// ==========================================
// OTP Timer & Resend Functionality
// ==========================================
let otpExpiryTime = <?php echo isset($_SESSION['reset_otp_expiry']) ? $_SESSION['reset_otp_expiry'] : time() + 300; ?> * 1000;

function formatTime(seconds) {
    var mins = Math.floor(seconds / 60);
    var secs = seconds % 60;
    return (mins < 10 ? '0' + mins : mins) + ':' + (secs < 10 ? '0' + secs : secs);
}

function updateOtpTimer() {
    var timerDiv = document.getElementById('otpTimer');
    if (!timerDiv) return;
    
    var now = new Date().getTime();
    var remaining = otpExpiryTime - now;
    
    if (remaining <= 0) {
        timerDiv.innerHTML = 'Expired!';
        timerDiv.classList.add('timer-expired');
        var verifyBtn = document.getElementById('verifyOtpBtn');
        var otpField = document.getElementById('otp');
        if (verifyBtn) verifyBtn.disabled = true;
        if (otpField) otpField.disabled = true;
        return;
    }
    
    var seconds = Math.floor(remaining / 1000);
    timerDiv.innerHTML = formatTime(seconds);
}

if (document.getElementById('otpTimer')) {
    setInterval(updateOtpTimer, 1000);
    updateOtpTimer();
}

// OTP Input - Allow only numbers
var otpInput = document.getElementById('otp');
if (otpInput) {
    otpInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
    });
}

// Resend OTP (AJAX)
var resendBtn = document.getElementById('resendOtpBtn');
if (resendBtn) {
    resendBtn.addEventListener('click', function() {
        this.disabled = true;
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'forgot_password.php?action=resend_otp', true);
        xhr.onload = function() {
            var response = JSON.parse(this.responseText);
            var msgDiv = document.getElementById('resendMessage');
            if (response.success) {
                msgDiv.innerHTML = '<span class="text-success">' + response.message + '</span>';
                otpExpiryTime = new Date().getTime() + 300000;
                updateOtpTimer();
                var verifyBtn = document.getElementById('verifyOtpBtn');
                var otpField = document.getElementById('otp');
                if (verifyBtn) verifyBtn.disabled = false;
                if (otpField) otpField.disabled = false;
            } else {
                msgDiv.innerHTML = '<span class="text-danger">' + response.message + '</span>';
            }
            setTimeout(function() {
                resendBtn.disabled = false;
                setTimeout(function() { msgDiv.innerHTML = ''; }, 3000);
            }, 2000);
        };
        xhr.send();
    });
}

// Refresh CAPTCHA
function refreshCaptcha() {
    var captchaImage = document.getElementById('captchaImage');
    if (captchaImage) {
        captchaImage.src = '../includes/captcha.php?' + new Date().getTime();
    }
    var captchaInput = document.getElementById('captcha');
    if (captchaInput) captchaInput.value = '';
}

// Prevent form resubmission on page refresh
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}
</script>
</body>
</html>