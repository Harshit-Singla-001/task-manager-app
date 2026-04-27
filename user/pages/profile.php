<?php
// ==========================================
// FILE: user/pages/profile.php
// User Profile Page
// ==========================================
require_once '../../includes/auth_check.php';

// Ensure only users can access this page
if($_SESSION['login_type'] !== 'user') {
    header("Location: " . BASE_URL . "admin/dashboard.php");
    exit();
}

// Database connection
require_once '../../config/db.php';
$database = new Database();
$pdo = $database->getConnection();

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch();

// Update profile
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['update_profile'])) {
        $full_name = filter_var($_POST['full_name'], FILTER_SANITIZE_STRING);
        $phone_number = filter_var($_POST['phone_number'], FILTER_SANITIZE_STRING);
        $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
        
        $update = $pdo->prepare("UPDATE users SET full_name = :full_name, phone_number = :phone_number, address = :address, updated_at = NOW() WHERE user_id = :user_id");
        
        if($update->execute([
            'full_name' => $full_name,
            'phone_number' => $phone_number,
            'address' => $address,
            'user_id' => $user_id
        ])) {
            $_SESSION['full_name'] = $full_name;
            $success = "Profile updated successfully!";
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $user_id]);
            $user = $stmt->fetch();
        } else {
            $error = "Failed to update profile.";
        }
    }
    
    // Change password
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
                    if($update->execute(['password_hash' => $new_hash, 'user_id' => $user_id])) {
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>

<?php include_once '../../includes/header.php'; ?>

<div class="main-content">
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <!-- Page Header -->
                <div class="text-center mb-5">
                    <i class="fas fa-user-circle fa-3x text-primary mb-3"></i>
                    <h1 class="fw-bold">My Profile</h1>
                    <p class="text-muted">Manage your account information and security settings</p>
                    <div class="divider bg-primary mx-auto" style="width: 60px; height: 3px;"></div>
                </div>

                <?php if($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i> <?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Full Name</label>
                                        <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Email Address</label>
                                        <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly disabled>
                                        <small class="text-muted">Email cannot be changed</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Phone Number</label>
                                        <input type="tel" class="form-control" name="phone_number" value="<?= htmlspecialchars($user['phone_number']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Date of Birth</label>
                                        <input type="date" class="form-control" value="<?= htmlspecialchars($user['dob']) ?>" readonly disabled>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Address</label>
                                        <textarea class="form-control" name="address" rows="3"><?= htmlspecialchars($user['address']) ?></textarea>
                                    </div>
                                    <button type="submit" name="update_profile" class="btn btn-primary w-100">
                                        <i class="fas fa-save me-2"></i>Update Profile
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Security Settings -->
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
                                
                                <hr>
                                
                                <div class="mt-3">
                                    <h6 class="fw-bold">Account Information</h6>
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td class="text-muted">Member Since:</td>
                                            <td class="fw-bold"><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Last Login:</td>
                                            <td class="fw-bold"><?= date('d M Y, h:i A', strtotime($user['last_login'])) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Status:</td>
                                            <td>
                                                <span class="badge bg-success"><?= ucfirst($user['status']) ?></span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sensitive Information (Read Only) -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>Sensitive Information (Read Only)</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    For security reasons, your Aadhaar and PAN numbers cannot be changed directly. Please contact support for any corrections.
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="fw-bold">Aadhaar Number:</label>
                                        <p class="text-muted">•••• •••• •••• <?= substr($user['aadhaar_number'], -4) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fw-bold">PAN Number:</label>
                                        <p class="text-muted">••••• •••• <?= substr($user['pan_number'], -4) ?></p>
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
</body>
</html>