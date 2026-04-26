<?php
// ==========================================
// FILE: auth/signup.php
// Signup Step 1 - Basic Details with Age Detection
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
// IMPORTANT: Initialize variables with defaults FIRST
// ==========================================
$full_name = '';
$dob = '';
$aadhaar = '';
$pan = '';
$address = '';
$phone = '';
$email = '';
$show_pan = false;
$error_message = '';
$success_message = '';

// ==========================================
// THEN check for session data to pre-fill (OVERWRITES defaults)
// ==========================================

// Check if returning from OTP page with preserved data
if (isset($_SESSION['return_from_otp']) && $_SESSION['return_from_otp'] === true) {
    // Data is already pre-filled from session
    // Clear the flag
    unset($_SESSION['return_from_otp']);
}

// Pre-fill form data from session if available (this OVERWRITES the empty defaults)
if (isset($_SESSION['signup_data'])) {
    $full_name = $_SESSION['signup_data']['full_name'];
    $dob = $_SESSION['signup_data']['dob'];
    $aadhaar = $_SESSION['signup_data']['aadhaar'];
    $pan = isset($_SESSION['signup_data']['pan']) ? $_SESSION['signup_data']['pan'] : '';
    $address = $_SESSION['signup_data']['address'];
    $phone = $_SESSION['signup_data']['phone'];
    $email = $_SESSION['signup_data']['email'];
    $age = isset($_SESSION['signup_data']['age']) ? $_SESSION['signup_data']['age'] : 0;
    $show_pan = ($age >= 18);
}

// Handle Email Verification AJAX request
if (isset($_POST['verify_email']) && isset($_POST['email'])) {
    header('Content-Type: application/json');
    
    $email_to_verify = trim($_POST['email']);
    $response = array();
    
    // Validate email format
    if (!filter_var($email_to_verify, FILTER_VALIDATE_EMAIL)) {
        $response['success'] = false;
        $response['message'] = 'Invalid email format';
        echo json_encode($response);
        exit();
    }
    
    // Check if email already exists in database
    $database = new Database();
    $pdo = $database->getConnection();
    
    if ($pdo != null) {
        $query = "SELECT COUNT(*) as count FROM users WHERE email = :email";
        $stmt = $pdo->prepare($query);
        $stmt->execute(array('email' => $email_to_verify));
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            $response['success'] = false;
            $response['message'] = 'Email already registered! Please use a different email or login.';
            echo json_encode($response);
            exit();
        }
    }
    
    // Email is available - Generate OTP
    $otp = rand(100000, 999999);
    $_SESSION['signup_temp']['email'] = $email_to_verify;
    $_SESSION['signup_temp']['otp'] = $otp;
    $_SESSION['signup_temp']['otp_expiry'] = time() + (OTP_EXPIRY_MINUTES * 60);
    
    $response['success'] = true;
    $response['message'] = 'OTP sent successfully! OTP: ' . $otp . ' (Demo Only)';
    echo json_encode($response);
    exit();
}

// Handle form submission (Proceed to OTP Verification)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify_and_continue'])) {
    
    // Get form data
    $full_name = trim($_POST['full_name']);
    $dob = trim($_POST['dob']);
    $aadhaar = trim($_POST['aadhaar']);
    $pan = isset($_POST['pan']) ? trim($_POST['pan']) : '';
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $email_verified = isset($_POST['email_verified']) ? $_POST['email_verified'] : '0';
    
    // Calculate age
    $age = 0;
    if (!empty($dob)) {
        $dob_timestamp = strtotime($dob);
        $today = time();
        $age = floor(($today - $dob_timestamp) / (365.25 * 24 * 60 * 60));
    }
    
    $show_pan = ($age >= 18);
    
    // Validation errors array
    $errors = array();
    
    // Validate Full Name
    if (empty($full_name)) {
        $errors[] = "Full Name is required";
    } elseif (strlen($full_name) < 3) {
        $errors[] = "Full Name must be at least 3 characters";
    }
    
    // Validate DOB
    if (empty($dob)) {
        $errors[] = "Date of Birth is required";
    } elseif ($age < 13) {
        $errors[] = "You must be at least 13 years old to register";
    }
    
    // Validate Aadhaar
    if (empty($aadhaar)) {
        $errors[] = "Aadhaar Number is required";
    } elseif (!preg_match('/^[0-9]{12}$/', $aadhaar)) {
        $errors[] = "Aadhaar Number must be 12 digits";
    }
    
    // Validate PAN (if age >= 18 and user filled it - NOT compulsory)
    if (!empty($pan) && !preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $pan)) {
        $errors[] = "Invalid PAN Number format (Example: ABCDE1234F)";
    }
    
    // Validate Address
    if (empty($address)) {
        $errors[] = "Address is required";
    } elseif (strlen($address) < 10) {
        $errors[] = "Please enter a complete address";
    }
    
    // Validate Phone
    if (empty($phone)) {
        $errors[] = "Phone Number is required";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $errors[] = "Phone Number must be 10 digits";
    }
    
    // Validate Email
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // If no errors, store data in session and proceed to OTP verification page
    if (empty($errors)) {

        // Step 1: Generate OTP
        $otp = rand(100000, 999999);

        // Step 2: Store OTP in session
        $_SESSION['signup_temp'] = array(
            'email' => $email,
            'otp' => $otp,
            'otp_expiry' => time() + (OTP_EXPIRY_MINUTES * 60)
        );

        // Step 3: Store form data
        $_SESSION['signup_data'] = array(
            'full_name' => $full_name,
            'dob' => $dob,
            'aadhaar' => $aadhaar,
            'pan' => $pan,
            'address' => $address,
            'phone' => $phone,
            'email' => $email,
            'age' => $age
        );

        // (Optional - for testing only)
        echo "Your OTP is: " . $otp;

        // Step 4: Redirect
        header("Location: verify_otp.php");
        exit();
    } else {
        $error_message = implode("<br>", $errors);
        $show_pan = ($age >= 18);
    }
}

// Check if returning from OTP verification with error
if (isset($_SESSION['signup_error'])) {
    $error_message = $_SESSION['signup_error'];
    unset($_SESSION['signup_error']);
}

// Get stored email for OTP resend
$stored_email = isset($_SESSION['signup_temp']['email']) ? $_SESSION['signup_temp']['email'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup Step 1 - Online Crime Reporting</title>
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
        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
        .btn-verify {
            background-color: #17a2b8;
            color: white;
            min-width: 100px;
        }
        .btn-verify:hover {
            background-color: #138496;
        }
        .email-verified-badge {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            display: inline-block;
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
        .alert-custom {
            border-radius: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step active">1</div>
                <div class="step-line"></div>
                <div class="step">2</div>
                <div class="step-line"></div>
                <div class="step">3</div>
            </div>
            
            <div class="card shadow-lg border-0">
                <div class="card-header bg-success text-white text-center py-4">
                    <h3 class="mb-0"><i class="fas fa-user-plus me-2"></i>Create Account</h3>
                    <p class="mb-0 mt-2">Step 1 of 3: Personal Information</p>
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
                    
                    <form method="POST" action="" id="signupForm">
                        <!-- Full Name -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-user me-1 text-success"></i>Full Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   name="full_name" 
                                   id="full_name"
                                   value="<?php echo htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'); ?>"
                                   placeholder="Enter your full name" 
                                   required>
                            <small class="text-muted">Enter your full name as per government ID</small>
                        </div>
                        
                        <!-- Date of Birth -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar-alt me-1 text-success"></i>Date of Birth <span class="text-danger">*</span>
                            </label>
                            <input type="date" 
                                   class="form-control" 
                                   name="dob" 
                                   id="dob"
                                   value="<?php echo htmlspecialchars($dob, ENT_QUOTES, 'UTF-8'); ?>"
                                   required>
                            <small class="text-muted">You must be at least 13 years old to register</small>
                            <div id="ageDisplay" class="mt-2"></div>
                        </div>
                        
                        <!-- Aadhaar Number -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-id-card me-1 text-success"></i>Aadhaar Number <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   name="aadhaar" 
                                   id="aadhaar"
                                   value="<?php echo htmlspecialchars($aadhaar, ENT_QUOTES, 'UTF-8'); ?>"
                                   placeholder="12 digit Aadhaar number" 
                                   maxlength="12"
                                   pattern="[0-9]{12}"
                                   required>
                            <small class="text-muted">Enter 12 digit Aadhaar number</small>
                        </div>
                        
                        <!-- PAN Number (Dynamic - shown/hidden based on age) -->
                        <div class="mb-3" id="panContainer" style="display: <?php echo ($show_pan) ? 'block' : 'none'; ?>;">
                            <label class="form-label fw-bold">
                                <i class="fas fa-credit-card me-1 text-info"></i>PAN Number <span class="text-muted">(Optional)</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   name="pan" 
                                   id="pan"
                                   value="<?php echo htmlspecialchars($pan, ENT_QUOTES, 'UTF-8'); ?>"
                                   placeholder="ABCDE1234F" 
                                   maxlength="10"
                                   style="text-transform: uppercase">
                            <small class="text-muted">Format: 5 letters, 4 digits, 1 letter (Optional)</small>
                        </div>
                        
                        <!-- Address -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-map-marker-alt me-1 text-success"></i>Address <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" 
                                      name="address" 
                                      id="address"
                                      rows="3" 
                                      placeholder="Enter your complete address"
                                      required><?php echo htmlspecialchars($address, ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>
                        
                        <!-- Phone Number -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-phone me-1 text-success"></i>Phone Number <span class="text-danger">*</span>
                            </label>
                            <input type="tel" 
                                   class="form-control" 
                                   name="phone" 
                                   id="phone"
                                   value="<?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?>"
                                   placeholder="10 digit mobile number" 
                                   maxlength="10"
                                   pattern="[0-9]{10}"
                                   required>
                        </div>
                        
                        <!-- Email with Verification Button -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-envelope me-1 text-success"></i>Email Address <span class="text-danger">*</span>
                            </label>
                            <input type="email" 
                                class="form-control" 
                                name="email" 
                                id="email"
                                value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
                                placeholder="Enter email address" 
                                required>
                            <div id="emailStatus" class="mt-2"></div>
                            <small class="text-muted">Click Verify to check email availability and send OTP</small>
                        </div>
                        
                        <!-- Hidden field to track email verification -->
                        <input type="hidden" name="email_verified" id="email_verified" value="0">
                        
                        <button type="submit" name="verify_and_continue" class="btn btn-success w-100 py-2">
                            <i class="fas fa-envelope"></i> Verify Email & Continue
                        </button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <a href="../index.php" class="text-decoration-none">
                            <i class="fas fa-arrow-left me-1"></i>Back to Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Age calculation and PAN field visibility
    const dobInput = document.getElementById('dob');
    const ageDisplay = document.getElementById('ageDisplay');
    const panContainer = document.getElementById('panContainer');
    const panInput = document.getElementById('pan');
    const emailInput = document.getElementById('email');
    let isEmailVerified = false;
    
    function calculateAge() {
        const dob = dobInput.value;
        if (!dob) {
            ageDisplay.innerHTML = '';
            panContainer.style.display = 'none';
            panInput.removeAttribute('required');
            return;
        }
        
        const today = new Date();
        const birthDate = new Date(dob);
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        if (age > 0) {
            ageDisplay.innerHTML = '<span class="badge bg-info">Age: ' + age + ' years</span>';
            
            // Show/hide PAN based on age
            if (age >= 18) {
                panContainer.style.display = 'block';
                panInput.removeAttribute('required');
            } else {
                panContainer.style.display = 'none';
                panInput.value = '';
                panInput.removeAttribute('required');
            }
        } else {
            ageDisplay.innerHTML = '<span class="badge bg-danger">Please enter valid date of birth</span>';
        }
    }
    
    dobInput.addEventListener('change', calculateAge);
    
    // Format Aadhaar to allow only numbers
    const aadhaarInput = document.getElementById('aadhaar');
    aadhaarInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 12);
    });
    
    // Format Phone to allow only numbers
    const phoneInput = document.getElementById('phone');
    phoneInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
    });
    
    // Format PAN to uppercase
    if (panInput) {
        panInput.addEventListener('input', function(e) {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 10);
        });
    }
    
    function showEmailStatus(message, type) {
        const statusDiv = document.getElementById('emailStatus');
        statusDiv.innerHTML = '<div class="alert alert-' + type + ' alert-sm p-2">' + message + '</div>';
        setTimeout(function() {
            if (statusDiv.innerHTML) {
                statusDiv.innerHTML = '';
            }
        }, 5000);
    }
    
    // Initial age calculation if DOB is pre-filled
    if (dobInput.value) {
        calculateAge();
    }
    
    // Check if email was already verified from previous step
    <?php if (!empty($stored_email)): ?>
    emailInput.value = '<?php echo htmlspecialchars($stored_email, ENT_QUOTES, 'UTF-8'); ?>';
    emailInput.readOnly = true;
    isEmailVerified = true;
    emailVerifiedHidden.value = '1';
    proceedBtn.disabled = false;
    document.getElementById('verifyEmailBtn').innerHTML = '<i class="fas fa-check"></i> Verified!';
    document.getElementById('verifyEmailBtn').classList.remove('btn-verify');
    document.getElementById('verifyEmailBtn').classList.add('btn-success');
    document.getElementById('verifyEmailBtn').disabled = true;
    <?php endif; ?>
</script>
</body>
</html>