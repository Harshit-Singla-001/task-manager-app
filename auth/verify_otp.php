<?php
// ==========================================
// FILE: auth/verify_otp.php
// OTP Verification Page
// PHP 5.x Compatible
// ==========================================

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once '../config/db.php';
require_once '../config/constants.php';

// ==========================================
// 3. Page Access Protection
// ==========================================
if (!isset($_SESSION['signup_temp']) || !isset($_SESSION['signup_data'])) {
    header("Location: signup.php");
    exit();
}

// Include PHPMailer files
require_once '../vendor/PHPMailer/PHPMailer.php';
require_once '../vendor/PHPMailer/SMTP.php';
require_once '../vendor/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// ==========================================
// 4. Function to Send OTP Email
// ==========================================
function sendOTPEmail($to_email, $otp, $full_name) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to_email, $full_name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Email Verification OTP - Online Crime Reporting System';
        
        // Email body HTML
        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
                .container { max-width: 500px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; }
                .content { padding: 30px; text-align: center; }
                .otp-code { font-size: 36px; font-weight: bold; color: #28a745; letter-spacing: 5px; margin: 20px 0; }
                .expiry { color: #dc3545; font-size: 12px; margin-top: 20px; }
                .footer { background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #6c757d; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Email Verification</h2>
                </div>
                <div class="content">
                    <p>Dear <strong>' . htmlspecialchars($full_name) . '</strong>,</p>
                    <p>Thank you for registering with the Online Crime Reporting System.</p>
                    <p>Please use the following One-Time Password (OTP) to verify your email address:</p>
                    <div class="otp-code">' . $otp . '</div>
                    <p>This OTP is valid for <strong>5 minutes</strong>.</p>
                    <div class="expiry">
                        <strong>⚠️ Security Notice:</strong> Never share this OTP with anyone.
                    </div>
                </div>
                <div class="footer">
                    <p>This is an automated message. Please do not reply to this email.</p>
                    <p>&copy; 2024 Online Crime Reporting System. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        // Plain text alternative
        $mail->AltBody = "Dear " . $full_name . ",\n\n"
                        . "Thank you for registering with the Online Crime Reporting System.\n\n"
                        . "Your OTP for email verification is: " . $otp . "\n\n"
                        . "This OTP is valid for 5 minutes.\n\n"
                        . "⚠️ Security Notice: Never share this OTP with anyone.\n\n"
                        . "This is an automated message. Please do not reply to this email.";
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

// ==========================================
// 4. Send OTP Email on First Load
// ==========================================
$email_sent = false;
$error_message = '';
$success_message = '';

if (!isset($_SESSION['signup_temp']['otp_sent'])) {
    $to_email = $_SESSION['signup_temp']['email'];
    $otp = $_SESSION['signup_temp']['otp'];
    $full_name = $_SESSION['signup_data']['full_name'];
    
    if (sendOTPEmail($to_email, $otp, $full_name)) {
        $_SESSION['signup_temp']['otp_sent'] = true;
        $success_message = "OTP has been sent to your email address!";
        $email_sent = true;
    } else {
        $error_message = "Failed to send OTP email. Please try again or contact support.";
    }
} else {
    $email_sent = true;
}

// ==========================================
// Handle Form Actions
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    // ==========================================
    // Verify OTP Logic
    // ==========================================
    if ($action == 'verify_otp') {
        
        $entered_otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';
        
        // Check if OTP is empty
        if (empty($entered_otp)) {
            $error_message = "Please enter the OTP";
        }
        // Check OTP expiry first
        elseif (time() > $_SESSION['signup_temp']['otp_expiry']) {
            $error_message = "OTP has expired. Please go back and signup again.";
        }
        // Verify OTP
        elseif ($entered_otp == $_SESSION['signup_temp']['otp']) {
            // OTP valid and not expired
            $_SESSION['email_verified'] = true;
            
            // Clear OTP data for security but keep signup_data
            unset($_SESSION['signup_temp']['otp']);
            unset($_SESSION['signup_temp']['otp_expiry']);
            unset($_SESSION['signup_temp']['otp_sent']);
            
            // Redirect to complete_signup.php
            header("Location: complete_signup.php");
            exit();
        } 
        else {
            $error_message = "Invalid OTP. Please try again.";
        }
    }
    
    // ==========================================
    // Change Email Logic (PRESERVE ALL DATA)
    // ==========================================
    elseif ($action == 'change_email') {
        // IMPORTANT: Keep signup_data in session, only clear signup_temp
        // This way all entered data remains pre-filled on signup.php
        
        // Store signup_data before clearing temp
        $preserved_signup_data = $_SESSION['signup_data'];
        
        // Clear only signup_temp (OTP related data)
        unset($_SESSION['signup_temp']);
        
        // Restore signup_data so form gets pre-filled
        $_SESSION['signup_data'] = $preserved_signup_data;
        
        // Add a flag to indicate returning from OTP page
        $_SESSION['return_from_otp'] = true;
        
        // Redirect back to signup page with data preserved
        header("Location: signup.php");
        exit();
    }
}

// Get user email for display
$user_email = $_SESSION['signup_temp']['email'];
$user_name = $_SESSION['signup_data']['full_name'];

// Calculate initial time values for JavaScript
$otp_expiry_time = $_SESSION['signup_temp']['otp_expiry'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Online Crime Reporting</title>
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
        .otp-input {
            font-size: 24px;
            letter-spacing: 10px;
            text-align: center;
            font-weight: bold;
        }
        .timer {
            font-size: 24px;
            font-weight: bold;
            font-family: monospace;
        }
        .timer-expired {
            color: #dc3545;
        }
        .timer-warning {
            color: #ffc107;
        }
        .email-display {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            border-left: 4px solid #28a745;
        }
        .alert-custom {
            border-radius: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step completed">1</div>
                <div class="step-line"></div>
                <div class="step active">2</div>
                <div class="step-line"></div>
                <div class="step">3</div>
            </div>
            
            <div class="card shadow-lg border-0">
                <div class="card-header bg-success text-white text-center py-4">
                    <h3 class="mb-0"><i class="fas fa-envelope me-2"></i>Verify Email</h3>
                    <p class="mb-0 mt-2">Step 2 of 3: OTP Verification</p>
                </div>
                <div class="card-body p-4">
                    
                    <!-- Error Message Display -->
                    <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Success Message Display -->
                    <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Email Display -->
                    <div class="email-display mb-4">
                        <i class="fas fa-envelope text-success me-2"></i>
                        <strong>Verifying email:</strong> 
                        <?php echo htmlspecialchars($user_email, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    
                    <!-- OTP Timer Display -->
                    <div class="text-center mb-3">
                        <div class="small text-muted">OTP expires in:</div>
                        <div id="otpTimer" class="timer">05:00</div>
                    </div>
                    
                    <!-- OTP Form -->
                    <form method="POST" action="" id="otpForm">
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-key me-1 text-success"></i>Enter OTP
                            </label>
                            <input type="text" 
                                   class="form-control otp-input" 
                                   name="otp" 
                                   id="otp"
                                   placeholder="000000" 
                                   maxlength="6"
                                   pattern="[0-9]{6}"
                                   autocomplete="off"
                                   required>
                            <small class="text-muted">Enter the 6-digit OTP sent to your email</small>
                        </div>
                        
                        <input type="hidden" name="action" value="verify_otp">
                        
                        <button type="submit" class="btn btn-success w-100 py-2" id="verifyBtn">
                            <i class="fas fa-check-circle me-2"></i>Verify OTP
                        </button>
                    </form>
                    
                    <hr class="my-4">
                    
                    <!-- Change Email Button Only (No Resend OTP) -->
                    <div class="text-center">
                        <form method="POST" action="" id="changeEmailForm">
                            <input type="hidden" name="action" value="change_email">
                            <button type="submit" class="btn btn-outline-secondary">
                                <i class="fas fa-envelope me-1"></i>Change Email Address
                            </button>
                        </form>
                    </div>
                    
                    <div class="text-center mt-4">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt me-1"></i>
                            OTP is valid for 5 minutes for security reasons
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ==========================================
// Timer Functionality
// ==========================================

// Get expiry time from PHP
var otpExpiryTime = <?php echo $otp_expiry_time; ?> * 1000; // Convert to milliseconds

// Function to format time as MM:SS
function formatTime(seconds) {
    var mins = Math.floor(seconds / 60);
    var secs = seconds % 60;
    return (mins < 10 ? '0' + mins : mins) + ':' + (secs < 10 ? '0' + secs : secs);
}

// OTP Expiry Timer
function updateOtpTimer() {
    var now = new Date().getTime();
    var remaining = otpExpiryTime - now;
    
    if (remaining <= 0) {
        document.getElementById('otpTimer').innerHTML = 'Expired!';
        document.getElementById('otpTimer').classList.add('timer-expired');
        document.getElementById('verifyBtn').disabled = true;
        document.getElementById('otp').disabled = true;
        document.getElementById('otp').placeholder = 'OTP Expired';
        return;
    }
    
    var seconds = Math.floor(remaining / 1000);
    document.getElementById('otpTimer').innerHTML = formatTime(seconds);
    
    // Add warning class when less than 1 minute remaining
    if (seconds < 60) {
        document.getElementById('otpTimer').classList.add('timer-warning');
    } else {
        document.getElementById('otpTimer').classList.remove('timer-warning');
    }
}

// Update OTP timer every second
setInterval(updateOtpTimer, 1000);

// Initial call to set timer
updateOtpTimer();

// OTP Input - Allow only numbers
var otpInput = document.getElementById('otp');
if (otpInput) {
    otpInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
    });
}

// Auto-submit when OTP reaches 6 digits (optional)
if (otpInput) {
    otpInput.addEventListener('keyup', function(e) {
        if (this.value.length === 6) {
            document.getElementById('otpForm').submit();
        }
    });
}

// Prevent form resubmission on page refresh
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}
</script>
</body>
</html>