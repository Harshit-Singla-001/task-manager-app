<?php
// ==========================================
// FILE: user/pages/detailed_tip.php
// Detailed Safety Tip Page with Voting - FIXED
// ==========================================

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
require_once '../../config/db.php';
require_once '../../config/constants.php';

// Get database connection (STATIC METHOD)
$pdo = Database::getConnection();

if (!$pdo) {
    die("Database connection failed");
}

// Check if user is logged in
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['error'] = "Please login to view safety tips.";
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// Get current user ID and check if admin/staff
$current_user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$is_admin_or_staff = false;
if(isset($_SESSION['login_type']) && ($_SESSION['login_type'] == 'staff' || $_SESSION['role'] == 'admin' || $_SESSION['role'] == 'staff')) {
    $is_admin_or_staff = true;
}

// Get tip ID from URL
$tip_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($tip_id <= 0) {
    $_SESSION['error'] = "Invalid safety tip.";
    header("Location: safety_tips.php");
    exit();
}

// ==========================================
// CREATE USER TIP VOTES TABLE IF NOT EXISTS
// ==========================================
$create_votes_table = "
CREATE TABLE IF NOT EXISTS `user_tip_votes` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `tip_id` int(11) NOT NULL,
    `vote_type` enum('helpful','not_helpful') NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_user_tip` (`user_id`, `tip_id`),
    KEY `user_id` (`user_id`),
    KEY `tip_id` (`tip_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

try {
    $pdo->exec($create_votes_table);
} catch(PDOException $e) {
    // Table might already exist
}

// ==========================================
// UPDATE VIEW COUNT (Session-based to prevent multiple counts)
// ==========================================
$session_key = "viewed_tip_" . $tip_id;
if(!isset($_SESSION[$session_key])) {
    $update_view_sql = "UPDATE safety_tips SET views = views + 1 WHERE id = :id AND status = 'active'";
    $update_view_stmt = $pdo->prepare($update_view_sql);
    $update_view_stmt->execute(array('id' => $tip_id));
    $_SESSION[$session_key] = true;
}

// ==========================================
// HANDLE VOTE VIA AJAX (POST request)
// ==========================================
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    // Check if admin/staff
    if($is_admin_or_staff) {
        echo json_encode(array(
            'success' => false,
            'message' => 'Admin/Staff cannot vote'
        ));
        exit();
    }
    
    $vote_type = isset($_POST['vote_type']) ? trim($_POST['vote_type']) : '';
    $tip_id_post = isset($_POST['tip_id']) ? (int)$_POST['tip_id'] : 0;
    
    if($tip_id_post != $tip_id || !in_array($vote_type, array('helpful', 'not-helpful'))) {
        echo json_encode(array(
            'success' => false,
            'message' => 'Invalid vote data'
        ));
        exit();
    }
    
    try {
        // Check if user already voted
        $check_sql = "SELECT vote_type FROM user_tip_votes WHERE user_id = :user_id AND tip_id = :tip_id";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute(array(
            'user_id' => $current_user_id,
            'tip_id' => $tip_id_post
        ));
        $existing_vote = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if($existing_vote) {
            // User already voted - update the vote
            $update_sql = "UPDATE user_tip_votes SET vote_type = :vote_type WHERE user_id = :user_id AND tip_id = :tip_id";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute(array(
                'vote_type' => $vote_type,
                'user_id' => $current_user_id,
                'tip_id' => $tip_id_post
            ));
        } else {
            // First time voting - insert
            $insert_sql = "INSERT INTO user_tip_votes (user_id, tip_id, vote_type) VALUES (:user_id, :tip_id, :vote_type)";
            $insert_stmt = $pdo->prepare($insert_sql);
            $insert_stmt->execute(array(
                'user_id' => $current_user_id,
                'tip_id' => $tip_id_post,
                'vote_type' => $vote_type
            ));
        }
        
        // Recalculate helpful percentage from ALL votes
        $stats_sql = "SELECT 
                        COUNT(*) as total_votes,
                        SUM(CASE WHEN vote_type = 'helpful' THEN 1 ELSE 0 END) as helpful_count
                      FROM user_tip_votes 
                      WHERE tip_id = :tip_id";
        $stats_stmt = $pdo->prepare($stats_sql);
        $stats_stmt->execute(array('tip_id' => $tip_id_post));
        $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
        
        $total_votes = $stats['total_votes'];
        $helpful_count = $stats['helpful_count'];
        $new_percentage = $total_votes > 0 ? round(($helpful_count / $total_votes) * 100) : 0;
        
        // Update safety_tips table
        $update_tip_sql = "UPDATE safety_tips SET helpful_percentage = :percentage, total_votes = :total_votes WHERE id = :id";
        $update_tip_stmt = $pdo->prepare($update_tip_sql);
        $update_tip_stmt->execute(array(
            'percentage' => $new_percentage,
            'total_votes' => $total_votes,
            'id' => $tip_id_post
        ));
        
        echo json_encode(array(
            'success' => true,
            'helpful_percentage' => $new_percentage,
            'total_votes' => $total_votes,
            'user_vote' => $vote_type,
            'message' => 'Vote recorded successfully'
        ));
        
    } catch(PDOException $e) {
        echo json_encode(array(
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ));
    }
    exit();
}

// ==========================================
// CHECK IF USER HAS ALREADY VOTED
// ==========================================
$user_vote = null;
if(!$is_admin_or_staff && $current_user_id > 0) {
    $vote_sql = "SELECT vote_type FROM user_tip_votes WHERE user_id = :user_id AND tip_id = :tip_id";
    $vote_stmt = $pdo->prepare($vote_sql);
    $vote_stmt->execute(array(
        'user_id' => $current_user_id,
        'tip_id' => $tip_id
    ));
    $user_vote = $vote_stmt->fetch(PDO::FETCH_ASSOC);
}

// ==========================================
// FETCH RELATED TIPS (same category, different tip)
// ==========================================
$related_sql = "SELECT id, title, category, helpful_percentage 
                FROM safety_tips 
                WHERE category = :category AND id != :id AND status = 'active' 
                ORDER BY helpful_percentage DESC 
                LIMIT 4";
$related_stmt = $pdo->prepare($related_sql);
$related_stmt->execute(array(
    'category' => $tip['category'],
    'id' => $tip_id
));
$related_tips = $related_stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to get category icon
function getCategoryIcon($category) {
    $icons = array(
        'Scam' => 'fa-triangle-exclamation',
        'Theft' => 'fa-ban',
        'ATM' => 'fa-credit-card',
        'Cyber' => 'fa-laptop-code',
        'Women' => 'fa-venus',
        'Other' => 'fa-circle-info'
    );
    return isset($icons[$category]) ? $icons[$category] : 'fa-shield-haltered';
}

function getCategoryColor($category) {
    $colors = array(
        'Scam' => 'danger',
        'Theft' => 'warning',
        'ATM' => 'info',
        'Cyber' => 'primary',
        'Women' => 'success',
        'Other' => 'secondary'
    );
    return isset($colors[$category]) ? $colors[$category] : 'dark';
}

// Include header
include_once '../../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tip['title']); ?> - Safety Tips</title>
    <style>
        .tip-content {
            line-height: 1.8;
            font-size: 1rem;
        }
        
        .tip-content p {
            margin-bottom: 1.5rem;
        }
        
        /* Reduced icon sizes */
        .vote-button i {
            font-size: 0.9rem;
        }
        
        .percentage-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }
        
        .percentage-text {
            color: white;
            font-size: 1.6rem;
            font-weight: bold;
        }
        
        .related-card {
            transition: all 0.3s ease;
            cursor: pointer;
            border-radius: 10px;
        }
        
        .related-card:hover {
            transform: translateX(5px);
            background: #f8f9fa;
        }
        
        .related-card i {
            font-size: 0.9rem;
        }
        
        .share-btn {
            transition: all 0.3s ease;
        }
        
        .share-btn:hover {
            transform: translateY(-2px);
        }
        
        .share-btn i {
            font-size: 0.8rem;
        }
        
        /* Vote button styles */
        .vote-button {
            transition: all 0.3s ease;
            padding: 10px 20px;
            font-size: 1rem;
            min-width: 150px;
        }
        
        .vote-button.voted-helpful {
            background: #28a745;
            border-color: #28a745;
            color: white;
            opacity: 0.8;
            cursor: default;
        }
        
        .vote-button.voted-not-helpful {
            background: #dc3545;
            border-color: #dc3545;
            color: white;
            opacity: 0.8;
            cursor: default;
        }
        
        .vote-button:disabled {
            cursor: not-allowed;
        }
        
        /* Admin notice */
        .admin-notice-bar {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .badge-sm {
            font-size: 0.7rem;
            padding: 3px 8px;
        }
        
        i.fa, i.far, i.fas, i.fab {
            font-size: 0.85rem;
        }
        
        h1 i, h2 i, h3 i, h4 i, h5 i, h6 i {
            font-size: inherit;
        }
        
        @media (max-width: 768px) {
            .percentage-circle {
                width: 70px;
                height: 70px;
            }
            .percentage-text {
                font-size: 1.2rem;
            }
            .vote-button {
                padding: 8px 16px;
                font-size: 0.9rem;
                min-width: 120px;
            }
        }
    </style>
</head>
<body>

<div class="container py-4">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="safety_tips.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Safety Tips
        </a>
    </div>
    
    <!-- Admin/Staff Notice -->
    <?php if($is_admin_or_staff): ?>
    <div class="admin-notice-bar">
        <i class="fas fa-info-circle"></i> 
        <strong>Admin/Staff View:</strong> You can view this safety tip. Voting is disabled for admin/staff accounts.
    </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Tip Header -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap">
                        <div>
                            <i class="fas <?php echo getCategoryIcon($tip['category']); ?> text-<?php echo getCategoryColor($tip['category']); ?> mb-2 d-block"></i>
                            <span class="badge bg-<?php echo getCategoryColor($tip['category']); ?> badge-sm">
                                <?php echo htmlspecialchars($tip['category']); ?>
                            </span>
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block">
                                <i class="far fa-calendar-alt"></i> Published: <?php echo date('d M Y', strtotime($tip['created_at'])); ?>
                            </small>
                            <small class="text-muted">
                                <i class="far fa-eye"></i> <?php echo number_format($tip['views']); ?> views
                            </small>
                        </div>
                    </div>
                    
                    <h1 class="display-6 fw-bold mb-4"><?php echo htmlspecialchars($tip['title']); ?></h1>
                    
                    <div class="tip-content">
                        <?php 
                        // Display content with paragraphs
                        $paragraphs = explode("\n", $tip['content']);
                        foreach($paragraphs as $paragraph) {
                            $paragraph = trim($paragraph);
                            if(!empty($paragraph)) {
                                echo '<p>' . nl2br(htmlspecialchars($paragraph)) . '</p>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
            
            <!-- Voting Section (Hidden for Admin/Staff) -->
            <?php if(!$is_admin_or_staff): ?>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body text-center">
                    <h5 class="mb-3">Was this safety tip helpful?</h5>
                    
                    <div class="d-flex justify-content-center gap-3 mb-4">
                        <button class="btn btn-outline-success vote-button" data-vote="helpful" id="helpfulBtn">
                            <i class="fas fa-thumbs-up"></i> Yes, Helpful
                        </button>
                        <button class="btn btn-outline-danger vote-button" data-vote="not-helpful" id="notHelpfulBtn">
                            <i class="fas fa-thumbs-down"></i> Not Helpful
                        </button>
                    </div>
                    
                    <div id="voteMessage" class="alert" style="display: none;"></div>
                    
                    <div class="row align-items-center mt-3">
                        <div class="col-md-4">
                            <div class="percentage-circle">
                                <div class="percentage-text" id="helpfulPercentage">
                                    <?php echo round($tip['helpful_percentage']); ?>%
                                </div>
                            </div>
                            <p class="mt-2 text-muted" id="voteCount">
                                <?php echo number_format($tip['total_votes']); ?> people voted
                            </p>
                        </div>
                        <div class="col-md-8">
                            <div class="progress mb-2" style="height: 20px;">
                                <div class="progress-bar bg-success" 
                                     role="progressbar" 
                                     style="width: <?php echo round($tip['helpful_percentage']); ?>%"
                                     id="helpfulProgressBar">
                                    <?php echo round($tip['helpful_percentage']); ?>% Helpful
                                </div>
                            </div>
                            <p class="text-muted small">Your feedback helps others find useful safety information.</p>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- Voting Disabled for Admin/Staff -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body text-center">
                    <h5 class="mb-3">Was this safety tip helpful?</h5>
                    <div class="d-flex justify-content-center gap-3 mb-4">
                        <button class="btn btn-outline-secondary" disabled style="opacity: 0.5;">
                            <i class="fas fa-thumbs-up"></i> Yes, Helpful
                        </button>
                        <button class="btn btn-outline-secondary" disabled style="opacity: 0.5;">
                            <i class="fas fa-thumbs-down"></i> Not Helpful
                        </button>
                    </div>
                    <div class="text-muted">
                        <i class="fas fa-lock"></i> Voting is disabled for admin/staff accounts
                    </div>
                    <div class="row align-items-center mt-3">
                        <div class="col-md-4">
                            <div class="percentage-circle">
                                <div class="percentage-text">
                                    <?php echo round($tip['helpful_percentage']); ?>%
                                </div>
                            </div>
                            <p class="mt-2 text-muted">
                                <?php echo number_format($tip['total_votes']); ?> people voted
                            </p>
                        </div>
                        <div class="col-md-8">
                            <div class="progress mb-2" style="height: 20px;">
                                <div class="progress-bar bg-success" 
                                     role="progressbar" 
                                     style="width: <?php echo round($tip['helpful_percentage']); ?>%">
                                    <?php echo round($tip['helpful_percentage']); ?>% Helpful
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Share Section -->
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="mb-3"><i class="fas fa-share-alt"></i> Share this safety tip</h6>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-success share-btn" onclick="shareOnWhatsApp()">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </button>
                        <button class="btn btn-sm btn-secondary share-btn" onclick="copyToClipboard()">
                            <i class="fas fa-link"></i> Copy Link
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Related Tips -->
            <?php if(count($related_tips) > 0): ?>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="fas fa-link"></i> Related Safety Tips</h5>
                </div>
                <div class="card-body">
                    <?php foreach($related_tips as $related): ?>
                    <div class="related-card p-3 mb-2" onclick="window.location.href='detailed_tip.php?id=<?php echo $related['id']; ?>'">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas <?php echo getCategoryIcon($related['category']); ?> text-<?php echo getCategoryColor($related['category']); ?> me-2"></i>
                                <strong><?php echo htmlspecialchars($related['title']); ?></strong>
                            </div>
                            <span class="badge bg-success badge-sm"><?php echo round($related['helpful_percentage']); ?>%</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Quick Tips -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="fas fa-lightbulb text-warning"></i> Quick Safety Reminders</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Never share OTP or passwords</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Verify caller before sharing info</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Use strong unique passwords</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Report cyber crime at 1930</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i> Save emergency contacts</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ==========================================
// VOTING JAVASCRIPT - FINAL VERSION
// ==========================================
<?php if(!$is_admin_or_staff): ?>
const helpfulBtn = document.getElementById('helpfulBtn');
const notHelpfulBtn = document.getElementById('notHelpfulBtn');
let isProcessing = false;

// Set initial button states based on user's existing vote
<?php if($user_vote): ?>
    <?php if($user_vote['vote_type'] == 'helpful'): ?>
        // User voted helpful before - helpful disabled, not helpful enabled
        helpfulBtn.classList.remove('btn-outline-success');
        helpfulBtn.classList.add('btn-success');
        helpfulBtn.disabled = true;
        notHelpfulBtn.disabled = false;
        notHelpfulBtn.classList.remove('btn-danger');
        notHelpfulBtn.classList.add('btn-outline-danger');
    <?php else: ?>
        // User voted not helpful before - not helpful disabled, helpful enabled
        notHelpfulBtn.classList.remove('btn-outline-danger');
        notHelpfulBtn.classList.add('btn-danger');
        notHelpfulBtn.disabled = true;
        helpfulBtn.disabled = false;
        helpfulBtn.classList.remove('btn-success');
        helpfulBtn.classList.add('btn-outline-success');
    <?php endif; ?>
<?php else: ?>
    // No vote yet - both buttons enabled
    helpfulBtn.disabled = false;
    notHelpfulBtn.disabled = false;
    helpfulBtn.classList.add('btn-outline-success');
    helpfulBtn.classList.remove('btn-success');
    notHelpfulBtn.classList.add('btn-outline-danger');
    notHelpfulBtn.classList.remove('btn-danger');
<?php endif; ?>

// Function to handle voting
function handleVote(voteType, clickedBtn, otherBtn) {
    if(isProcessing) return;
    if(clickedBtn.disabled) return;
    
    isProcessing = true;
    const tipId = <?php echo $tip_id; ?>;
    
    // Show loading state
    const voteMessage = document.getElementById('voteMessage');
    voteMessage.className = 'alert alert-info';
    voteMessage.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing your vote...';
    voteMessage.style.display = 'block';
    
    // Send AJAX request
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'tip_id=' + tipId + '&vote_type=' + voteType
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            // Update percentage display (show 0% if no helpful votes)
            const percentage = data.helpful_percentage;
            document.getElementById('helpfulPercentage').innerText = percentage + '%';
            document.getElementById('voteCount').innerHTML = data.total_votes + ' people voted';
            
            // Update progress bar
            const progressBar = document.getElementById('helpfulProgressBar');
            progressBar.style.width = percentage + '%';
            progressBar.innerHTML = percentage + '% Helpful';
            
            // Update button states: clicked button becomes disabled and solid
            // Other button becomes enabled and outline
            if(voteType === 'helpful') {
                // Helpful was clicked
                clickedBtn.classList.remove('btn-outline-success');
                clickedBtn.classList.add('btn-success');
                clickedBtn.disabled = true;
                otherBtn.disabled = false;
                otherBtn.classList.remove('btn-danger');
                otherBtn.classList.add('btn-outline-danger');
            } else {
                // Not Helpful was clicked
                clickedBtn.classList.remove('btn-outline-danger');
                clickedBtn.classList.add('btn-danger');
                clickedBtn.disabled = true;
                otherBtn.disabled = false;
                otherBtn.classList.remove('btn-success');
                otherBtn.classList.add('btn-outline-success');
            }
            
            // Show success message
            voteMessage.className = 'alert alert-success';
            voteMessage.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
        } else {
            throw new Error(data.message || 'Failed to submit vote');
        }
        
        setTimeout(() => {
            voteMessage.style.display = 'none';
        }, 3000);
        
        isProcessing = false;
    })
    .catch(error => {
        console.error('Error:', error);
        voteMessage.className = 'alert alert-danger';
        voteMessage.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + error.message;
        
        setTimeout(() => {
            voteMessage.style.display = 'none';
        }, 3000);
        
        isProcessing = false;
    });
}

// Add click event listeners
helpfulBtn.addEventListener('click', function() {
    handleVote('helpful', helpfulBtn, notHelpfulBtn);
});

notHelpfulBtn.addEventListener('click', function() {
    handleVote('not-helpful', notHelpfulBtn, helpfulBtn);
});

<?php endif; ?>

// Share functions
function shareOnWhatsApp() {
    const text = encodeURIComponent('Check out this safety tip: <?php echo addslashes($tip['title']); ?> - ');
    const url = encodeURIComponent(window.location.href);
    window.open(`https://wa.me/?text=${text}${url}`, '_blank');
}

function copyToClipboard() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        alert('Link copied to clipboard!');
    });
}
</script>

<?php include_once '../../includes/footer.php'; ?>
</body>
</html>