<?php
// ==========================================
// CONTACT PAGE - WITH ADMIN VIEW-ONLY MODE
// ==========================================

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
require_once '../../config/db.php';
require_once '../../config/constants.php';
require_once '../../includes/auth_check.php'; // Add auth check for view-only mode

// Get DB connection (CORRECT WAY)
$pdo = Database::getConnection();

// Check DB
if (!$pdo) {
    die("Database connection failed");
}

// Check login
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['error'] = "Please login first.";
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// Initialize variables
$success = '';
$error = '';
$subject = '';
$message = '';

$name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : '';
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';

// Check if current user is admin (view-only mode)
$view_only_mode = isAdminUser();

// ==========================================
// HANDLE FORM SUBMISSION (Only for regular users)
// ==========================================
if(!$view_only_mode && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_query'])) {

    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    $user_id = $_SESSION['user_id'];
    $name = $_SESSION['full_name'];
    $email = $_SESSION['email'];

    $errors = array();

    if(empty($subject)) {
        $errors[] = "Subject is required";
    }

    if(empty($message) || strlen($message) < 10) {
        $errors[] = "Message must be at least 10 characters";
    }

    if(empty($errors)) {
        try {

            $stmt = $pdo->prepare("
                INSERT INTO user_queries 
                (user_id, name, email, subject, message, status) 
                VALUES (:user_id, :name, :email, :subject, :message, 'unread')
            ");

            $stmt->execute(array(
                'user_id' => $user_id,
                'name' => $name,
                'email' => $email,
                'subject' => $subject,
                'message' => $message
            ));

            $success = "Message sent successfully!";
            $subject = '';
            $message = '';

        } catch(PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    } else {
        $error = implode("<br>", $errors);
    }
}

include_once '../../includes/header.php';
?>

<div class="container py-5">

    <h2 class="mb-4 text-center">Contact Us</h2>

    <!-- ADMIN VIEW-ONLY WARNING -->
    <?php if($view_only_mode): ?>
        <div class="alert alert-warning text-center">
            <i class="fas fa-eye me-2"></i>
            <strong>View Only Mode:</strong> As an administrator, you can preview this form but cannot send messages.
        </div>
    <?php endif; ?>

    <!-- SUCCESS MESSAGE -->
    <?php if($success): ?>
        <div class="alert alert-success text-center" id="successMsg">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <!-- ERROR MESSAGE -->
    <?php if($error): ?>
        <div class="alert alert-danger text-center">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" <?php echo $view_only_mode ? 'onsubmit="return false;"' : ''; ?>>

        <!-- Name -->
        <div class="mb-3">
            <label>Name</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($name); ?>" disabled>
        </div>

        <!-- Email -->
        <div class="mb-3">
            <label>Email</label>
            <input type="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" disabled>
        </div>

        <!-- Subject -->
        <div class="mb-3">
            <label>Subject</label>
            <select name="subject" class="form-control" <?php echo $view_only_mode ? 'disabled' : ''; ?> required>
                <option value="">Select</option>
                <option <?php echo $subject=='Feedback' ? 'selected' : ''; ?>>Feedback</option>
                <option <?php echo $subject=='Complaint' ? 'selected' : ''; ?>>Complaint</option>
                <option <?php echo $subject=='Suggestion' ? 'selected' : ''; ?>>Suggestion</option>
                <option <?php echo $subject=='Technical Issue' ? 'selected' : ''; ?>>Technical Issue</option>
            </select>
        </div>

        <!-- Message -->
        <div class="mb-3">
            <label>Message</label>
            <textarea name="message" class="form-control" rows="5" <?php echo $view_only_mode ? 'disabled' : ''; ?> required><?php echo htmlspecialchars($message); ?></textarea>
        </div>

        <?php if($view_only_mode): ?>
            <button type="button" class="btn btn-secondary w-100" disabled>
                <i class="fas fa-eye me-2"></i> Send Message (Disabled - View Only)
            </button>
            <div class="text-center mt-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i> 
                    Admin accounts cannot send messages. This is a preview only.
                </small>
            </div>
        <?php else: ?>
            <button type="submit" name="submit_query" class="btn btn-primary w-100">
                <i class="fas fa-paper-plane me-2"></i> Send Message
            </button>
        <?php endif; ?>

    </form>
</div>

<script>
// Auto-hide success message after 3 seconds
setTimeout(function() {
    var msg = document.getElementById('successMsg');
    if(msg) {
        msg.style.display = 'none';
    }
}, 3000);
</script>

<?php include_once '../../includes/footer.php'; ?>