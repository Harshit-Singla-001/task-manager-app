<?php
// ==========================================
// FILE: user/pages/profile.php
// User Profile Page - Shows admin data with user theme when admin is logged in
// ==========================================

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once '../../config/constants.php';
require_once '../../includes/auth_check.php';

// ✅ REMOVED the restriction that blocked admin
// Now admin can also view this page (with user theme)

// Database connection
require_once '../../config/db.php';
$pdo = Database::getConnection(); // Fixed: Use static method

$success = '';
$error = '';

// ✅ Check if user is admin or regular user
$is_admin = isAdminUser();

if($is_admin) {
    // Admin is viewing - show admin data
    $user_id = isset($_SESSION['staff_id']) ? $_SESSION['staff_id'] : 1;
    $table = 'staff';
    $id_field = 'staff_id';
    
    // Fetch admin data
    $stmt = $pdo->prepare("SELECT * FROM staff WHERE staff_id = :user_id");
    $stmt->execute(array('user_id' => $user_id));
    $user = $stmt->fetch();
    
    if(!$user) {
        // Fallback to session data if no DB record
        $user = array(
            'full_name' => $_SESSION['admin_name'],
            'email' => $_SESSION['admin_email'],
            'phone_number' => 'Not available',
            'dob' => 'Not available',
            'address' => 'Not available',
            'created_at' => date('Y-m-d H:i:s'),
            'last_login' => date('Y-m-d H:i:s'),
            'status' => 'active'
        );
    }
} else {
    // Regular user - show user data
    $user_id = $_SESSION['user_id'];
    $table = 'users';
    $id_field = 'user_id';
    
    // Fetch user data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->execute(array('user_id' => $user_id));
    $user = $stmt->fetch();
}

// Update profile (for regular users only)
if(!$is_admin && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['update_profile'])) {
        $full_name = filter_var($_POST['full_name'], FILTER_SANITIZE_STRING);
        $phone_number = filter_var($_POST['phone_number'], FILTER_SANITIZE_STRING);
        $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
        
        $update = $pdo->prepare("UPDATE users SET full_name = :full_name, phone_number = :phone_number, address = :address WHERE user_id = :user_id");
        
        if($update->execute(array(
            'full_name' => $full_name,
            'phone_number' => $phone_number,
            'address' => $address,
            'user_id' => $user_id
        ))) {
            $_SESSION['full_name'] = $full_name;
            $success = "Profile updated successfully!";
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :user_id");
            $stmt->execute(array('user_id' => $user_id));
            $user = $stmt->fetch();
        } else {
            $error = "Failed to update profile.";
        }
    }
    
    // Change password (for regular users only)
    if(isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        if(password_verify($current_password, $user['password_hash'])) {
            if($new_password === $confirm_password) {
                if(strlen($new_password) >= 6) {
                    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $update = $pdo->prepare("UPDATE users SET password_hash = :password_hash WHERE user_id = :user_id");
                    if($update->execute(array('password_hash' => $new_hash, 'user_id' => $user_id))) {
                        $success = "Password changed successfully!";
                    } else {
                        $error = "Failed to change password.";
                    }
                } else {
                    $error = "New password must be at least 6 characters long.";
                }
            } else {
                $error = "New passwords do not match.";
            }
        } else {
            $error = "Current password is incorrect.";
        }
    }
}

include_once '../../includes/header.php';
?>

<div class="main-content">
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <!-- Page Header -->
                <div class="text-center mb-5">
                    <i class="fas fa-user-circle fa-3x text-primary mb-3"></i>
                    <h1 class="fw-bold">My Profile</h1>
                    <p class="text-muted"><?php echo $is_admin ? 'Administrator Account Information' : 'Manage your account information and security settings'; ?></p>
                    <div class="divider bg-primary mx-auto" style="width: 60px; height: 3px;"></div>
                </div>

                <?php if($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if($is_admin): ?>
                    <!-- Admin View Only Mode Warning -->
                    <div class="alert alert-info text-center mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Admin View:</strong> You are viewing your admin profile with user theme.
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Profile Information -->
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Profile Information</h5>
                            </div>
                            <div class="card-body">
                                <?php if($is_admin): ?>
                                    <!-- Admin View Only - No edit -->
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Full Name</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly disabled>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Email Address</label>
                                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly disabled>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Phone Number</label>
                                        <input type="tel" class="form-control" value="<?php echo htmlspecialchars($user['phone_number']); ?>" readonly disabled>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Role</label>
                                        <input type="text" class="form-control" value="<?php echo isset($_SESSION['role']) ? $_SESSION['role'] : 'Administrator'; ?>" readonly disabled>
                                    </div>
                                    <div class="alert alert-warning mb-0">
                                        <i class="fas fa-lock me-2"></i>
                                        Admin profiles cannot be edited from user side. Please use admin panel to edit.
                                    </div>
                                <?php else: ?>
                                    <!-- Regular User Form - Editable -->
                                    <form method="POST" action="">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Full Name</label>
                                            <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Email Address</label>
                                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly disabled>
                                            <small class="text-muted">Email cannot be changed</small>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Phone Number</label>
                                            <input type="tel" class="form-control" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Date of Birth</label>
                                            <input type="date" class="form-control" value="<?php echo htmlspecialchars($user['dob']); ?>" readonly disabled>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Address</label>
                                            <textarea class="form-control" name="address" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                                        </div>
                                        <button type="submit" name="update_profile" class="btn btn-primary w-100">
                                            <i class="fas fa-save me-2"></i>Update Profile
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Security Settings - Only for regular users -->
                    <?php if(!$is_admin): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Security Settings</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Current Password</label>
                                        <input type="password" class="form-control" name="current_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">New Password</label>
                                        <input type="password" class="form-control" name="new_password" required>
                                        <small class="text-muted">Minimum 6 characters</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Confirm New Password</label>
                                        <input type="password" class="form-control" name="confirm_password" required>
                                    </div>
                                    <button type="submit" name="change_password" class="btn btn-warning w-100">
                                        <i class="fas fa-key me-2"></i>Change Password
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Account Information -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Account Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="fw-bold">Account Type:</label>
                                        <p><?php echo $is_admin ? '<span class="badge bg-danger">Administrator</span>' : '<span class="badge bg-info">Regular User</span>'; ?></p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="fw-bold">Member Since:</label>
                                        <p><?php echo date('d M Y', strtotime($user['created_at'])); ?></p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="fw-bold">Status:</label>
                                        <p><span class="badge bg-success"><?php echo ucfirst($user['status']); ?></span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>