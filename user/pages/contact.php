<?php
// ==========================================
// CONTACT PAGE - FINAL FIXED VERSION
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

// ==========================================
// HANDLE FORM SUBMISSION
// ==========================================
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_query'])) {

    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    $user_id = $_SESSION['user_id'];
    $name = $_SESSION['full_name'];
    $email = $_SESSION['email'];

    $errors = [];

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

            $stmt->execute([
                'user_id' => $user_id,
                'name' => $name,
                'email' => $email,
                'subject' => $subject,
                'message' => $message
            ]);

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

    <!-- SUCCESS MESSAGE -->
    <?php if($success): ?>
        <div class="alert alert-success text-center" id="successMsg">
            <?= $success ?>
        </div>
    <?php endif; ?>

    <!-- ERROR MESSAGE -->
    <?php if($error): ?>
        <div class="alert alert-danger text-center">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <!-- Name -->
        <div class="mb-3">
            <label>Name</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($name) ?>" disabled>
        </div>

        <!-- Email -->
        <div class="mb-3">
            <label>Email</label>
            <input type="email" class="form-control" value="<?= htmlspecialchars($email) ?>" disabled>
        </div>

        <!-- Subject -->
        <div class="mb-3">
            <label>Subject</label>
            <select name="subject" class="form-control" required>
                <option value="">Select</option>
                <option <?= $subject=='Feedback'?'selected':'' ?>>Feedback</option>
                <option <?= $subject=='Complaint'?'selected':'' ?>>Complaint</option>
                <option <?= $subject=='Suggestion'?'selected':'' ?>>Suggestion</option>
                <option <?= $subject=='Technical Issue'?'selected':'' ?>>Technical Issue</option>
            </select>
        </div>

        <!-- Message -->
        <div class="mb-3">
            <label>Message</label>
            <textarea name="message" class="form-control" rows="5" required><?= htmlspecialchars($message) ?></textarea>
        </div>

        <button type="submit" name="submit_query" class="btn btn-primary w-100">
            Send Message
        </button>

    </form>
</div>

<script>
// Auto-hide success message after 3 seconds
setTimeout(() => {
    let msg = document.getElementById('successMsg');
    if(msg) {
        msg.style.display = 'none';
    }
}, 3000);
</script>

<?php include_once '../../includes/footer.php'; ?>