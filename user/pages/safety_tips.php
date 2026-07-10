<?php
// ==========================================
// FILE: user/pages/safety_tips.php
// Safety & Awareness Tips Page - WITH PERSISTENT LIKES
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
    $_SESSION['error'] = "Please login to access safety tips.";
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// Get current user ID
$current_user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

// Check if user is admin or staff - disable voting/likes for them
$is_admin_or_staff = false;
if(isset($_SESSION['login_type']) && ($_SESSION['login_type'] == 'staff' || $_SESSION['role'] == 'admin' || $_SESSION['role'] == 'staff')) {
    $is_admin_or_staff = true;
}

// ==========================================
// CREATE EMERGENCY ALERTS TABLE IF NOT EXISTS
// ==========================================
$create_alerts_table = "
CREATE TABLE IF NOT EXISTS `emergency_alerts` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `description` text NOT NULL,
    `alert_type` enum('danger','warning','info') DEFAULT 'info',
    `icon` varchar(100) DEFAULT 'fa-bell',
    `is_active` tinyint(1) DEFAULT 1,
    `likes` int(11) DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

try {
    $pdo->exec($create_alerts_table);
    
    // Insert sample emergency alerts if table is empty
    $check_sql = "SELECT COUNT(*) as count FROM emergency_alerts";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute();
    $count = $check_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if($count == 0) {
        $insert_alerts = "INSERT INTO `emergency_alerts` (`title`, `description`, `alert_type`, `icon`, `likes`) VALUES
            ('New Cyber Scam Alert', 'Fake RBI KYC update scam targeting bank customers. Never share OTP.', 'danger', 'fa-triangle-exclamation', 45),
            ('Heavy Rainfall Warning', 'Multiple districts on alert. Avoid unnecessary travel.', 'warning', 'fa-cloud-rain', 32),
            ('Road Accident Alert', 'NH-48 traffic diversion due to accident. Use alternate routes.', 'info', 'fa-car-crash', 28),
            ('Digital Arrest Scam', 'Fraudsters posing as police. Real police never call for digital arrest.', 'danger', 'fa-mobile-alt', 56)";
        $pdo->exec($insert_alerts);
    }
} catch(PDOException $e) {
    // Table might already exist
}

// ==========================================
// CREATE USER ALERT LIKES TABLE IF NOT EXISTS
// ==========================================
$create_likes_table = "
CREATE TABLE IF NOT EXISTS `user_alert_likes` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `alert_id` int(11) NOT NULL,
    `liked_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_user_alert` (`user_id`, `alert_id`),
    KEY `user_id` (`user_id`),
    KEY `alert_id` (`alert_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

try {
    $pdo->exec($create_likes_table);
} catch(PDOException $e) {
    // Table might already exist
}

// ==========================================
// HANDLE EMERGENCY ALERT LIKES (TOGGLE - ADD/REMOVE)
// USING DATABASE FOR PERSISTENT STORAGE
// DISABLED FOR ADMIN/STAFF
// ==========================================
if(!$is_admin_or_staff && $current_user_id > 0 && isset($_POST['toggle_like']) && isset($_POST['alert_id'])) {
    $alert_id = (int)$_POST['alert_id'];
    
    // Check if user already liked this alert
    $check_sql = "SELECT id FROM user_alert_likes WHERE user_id = :user_id AND alert_id = :alert_id";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute(array(
        'user_id' => $current_user_id,
        'alert_id' => $alert_id
    ));
    $existing_like = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if($existing_like) {
        // User already liked - remove the like
        $delete_sql = "DELETE FROM user_alert_likes WHERE user_id = :user_id AND alert_id = :alert_id";
        $delete_stmt = $pdo->prepare($delete_sql);
        $delete_stmt->execute(array(
            'user_id' => $current_user_id,
            'alert_id' => $alert_id
        ));
        
        // Decrease like count in emergency_alerts table
        $update_sql = "UPDATE emergency_alerts SET likes = likes - 1 WHERE id = :id AND likes > 0";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute(array('id' => $alert_id));
    } else {
        // User hasn't liked - add like
        $insert_sql = "INSERT INTO user_alert_likes (user_id, alert_id) VALUES (:user_id, :alert_id)";
        $insert_stmt = $pdo->prepare($insert_sql);
        $insert_stmt->execute(array(
            'user_id' => $current_user_id,
            'alert_id' => $alert_id
        ));
        
        // Increase like count in emergency_alerts table
        $update_sql = "UPDATE emergency_alerts SET likes = likes + 1 WHERE id = :id";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute(array('id' => $alert_id));
    }
    
    // Redirect to remove POST data and prevent resubmission
    header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
    exit();
}

// ==========================================
// GET FILTER PARAMETERS
// ==========================================
$selected_category = isset($_GET['category']) ? trim($_GET['category']) : 'all';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_by = isset($_GET['sort']) ? trim($_GET['sort']) : 'recent';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 6;
$offset = ($page - 1) * $items_per_page;

// ==========================================
// BUILD WHERE CLAUSE FOR ACTIVE TIPS ONLY
// ==========================================
$where_conditions = array();
$params = array();

$where_conditions[] = "status = 'active'";

// Category filter
if($selected_category !== 'all' && !empty($selected_category)) {
    $where_conditions[] = "category = :category";
    $params['category'] = $selected_category;
}

// Search filter
if(!empty($search_query)) {
    $where_conditions[] = "(title LIKE :search OR content LIKE :search)";
    $params['search'] = "%$search_query%";
}

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// ==========================================
// BUILD ORDER BY CLAUSE
// ==========================================
$order_by = "";
switch($sort_by) {
    case 'helpful':
        $order_by = "ORDER BY helpful_percentage DESC, total_votes DESC";
        break;
    case 'views':
        $order_by = "ORDER BY views DESC";
        break;
    case 'recent':
    default:
        $order_by = "ORDER BY created_at DESC";
        break;
}

// ==========================================
// GET FEATURED TIPS (TOP 6 BY HELPFUL PERCENTAGE)
// ==========================================
$featured_sql = "SELECT id, title, category, content, helpful_percentage 
                 FROM safety_tips 
                 WHERE status = 'active' 
                 ORDER BY helpful_percentage DESC, total_votes DESC 
                 LIMIT 6";
$featured_stmt = $pdo->prepare($featured_sql);
$featured_stmt->execute();
$featured_tips = $featured_stmt->fetchAll(PDO::FETCH_ASSOC);

// ==========================================
// GET EMERGENCY ALERTS (Most liked, active)
// ==========================================
$alerts_sql = "SELECT id, title, description, alert_type, icon, likes, created_at 
               FROM emergency_alerts 
               WHERE is_active = 1 
               ORDER BY likes DESC 
               LIMIT 6";
$alerts_stmt = $pdo->prepare($alerts_sql);
$alerts_stmt->execute();
$emergency_alerts = $alerts_stmt->fetchAll(PDO::FETCH_ASSOC);

// ==========================================
// GET USER'S LIKED ALERTS (for persistent like status)
// ==========================================
$user_liked_alerts = array();
if(!$is_admin_or_staff && $current_user_id > 0) {
    $liked_sql = "SELECT alert_id FROM user_alert_likes WHERE user_id = :user_id";
    $liked_stmt = $pdo->prepare($liked_sql);
    $liked_stmt->execute(array('user_id' => $current_user_id));
    while($row = $liked_stmt->fetch(PDO::FETCH_ASSOC)) {
        $user_liked_alerts[] = $row['alert_id'];
    }
}

// ==========================================
// GET TOTAL COUNT FOR PAGINATION
// ==========================================
$count_sql = "SELECT COUNT(*) as total FROM safety_tips $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_items = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_items / $items_per_page);

// ==========================================
// GET SAFETY TIPS FOR CURRENT PAGE
// ==========================================
$sql = "SELECT id, title, category, content, helpful_percentage, total_votes, 
               views, created_at 
        FROM safety_tips 
        $where_clause 
        $order_by 
        LIMIT :offset, :items_per_page";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':items_per_page', $items_per_page, PDO::PARAM_INT);

// Bind other parameters
foreach($params as $key => $value) {
    $stmt->bindValue(":$key", $value);
}

$stmt->execute();
$safety_tips = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get category icon mapping
function getCategoryIcon($category) {
    $icons = array(
        'Scam' => 'fa-triangle-exclamation',
        'Theft' => 'fa-ban',
        'ATM' => 'fa-credit-card',
        'Cyber' => 'fa-laptop-code',
        'Women' => 'fa-venus',
        'Other' => 'fa-circle-info'
    );
    $icon = isset($icons[$category]) ? $icons[$category] : 'fa-shield-haltered';
    return $icon;
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
    <title>Safety & Awareness Tips - Online Crime Reporting System</title>
    <style>
        /* Custom CSS for Safety Tips Page */
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
            border-radius: 0 0 30px 30px;
        }
        
        /* Logo and title in one line */
        .page-header-title {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .page-header-title i {
            font-size: 3rem;
        }
        
        /* Featured tip full width */
        .featured-carousel .carousel-item {
            padding: 20px;
        }
        
        .featured-card {
            background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
            border-radius: 20px;
            padding: 40px 30px;
            margin: 10px auto;
            cursor: pointer;
            transition: all 0.3s ease;
            max-width: 900px;
            width: 90%;
        }
        
        .featured-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        /* Reduce all icon sizes by half */
        .featured-card i {
            font-size: 2rem !important;
        }
        
        .safety-card .card-icon {
            font-size: 1rem !important;
        }
        
        .stat-icon {
            font-size: 0.6rem !important;
        }
        
        .emergency-card i {
            font-size: 0.8rem !important;
        }
        
        .helpline-card i {
            font-size: 1.5rem !important;
        }
        
        .category-pill i {
            font-size: 0.8rem !important;
        }
        
        .helpful-badge i {
            font-size: 0.7rem !important;
        }
        
        .carousel-control-prev-icon, 
        .carousel-control-next-icon {
            width: 30px;
            height: 30px;
        }
        
        .category-pill {
            display: inline-block;
            padding: 8px 20px;
            margin: 5px;
            border-radius: 50px;
            background: #f0f0f0;
            color: #333;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }
        
        .category-pill:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .category-pill.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .category-label {
            font-weight: 600;
            margin-right: 15px;
            display: inline-flex;
            align-items: center;
        }
        
        .safety-card {
            transition: all 0.3s ease;
            cursor: pointer;
            height: 100%;
            border-radius: 15px;
            overflow: hidden;
        }
        
        .safety-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .card-preview {
            color: #666;
            line-height: 1.6;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .helpful-badge {
            background: #28a745;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        /* Emergency alerts container */
        .emergency-alerts-container {
            scrollbar-width: thin;
            scrollbar-color: #c1c1c1 #f1f1f1;
        }
        
        .emergency-alerts-container::-webkit-scrollbar {
            width: 5px;
        }
        
        .emergency-alerts-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .emergency-alerts-container::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }
        
        .emergency-alerts-container::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }
        
        .emergency-card {
            border-left: 4px solid;
            transition: all 0.3s ease;
            margin-bottom: 15px;
            break-inside: avoid;
            page-break-inside: avoid;
        }
        
        .emergency-card:hover {
            transform: translateX(5px);
        }
        
        .emergency-danger { border-left-color: #dc3545; background: #fff5f5; }
        .emergency-warning { border-left-color: #ffc107; background: #fffbf0; }
        .emergency-info { border-left-color: #0dcaf0; background: #f0f9ff; }
        
        /* Like button styling */
        .like-btn {
            cursor: pointer;
            transition: all 0.3s ease;
            background: none;
            border: none;
            padding: 5px;
        }
        
        .like-btn:hover {
            transform: scale(1.05);
            background: rgba(220, 53, 69, 0.1);
        }
        
        .like-btn.disabled {
            cursor: not-allowed;
            opacity: 0.5;
        }
        
        .like-btn.disabled:hover {
            transform: none;
            background: none;
        }
        
        .helpline-card {
            text-align: center;
            padding: 20px;
            border-radius: 15px;
            background: white;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .helpline-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .helpline-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }
        
        .pagination .page-link {
            border-radius: 50px;
            margin: 0 5px;
            color: #667eea;
        }
        
        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
        }
        
        /* Admin/Staff notice banner */
        .admin-notice {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        @media (max-width: 768px) {
            .hero-section {
                padding: 40px 0;
            }
            .page-header-title i {
                font-size: 2rem;
            }
            .page-header-title h1 {
                font-size: 1.5rem;
            }
            .category-pill {
                padding: 5px 12px;
                font-size: 0.8rem;
            }
            .featured-card {
                padding: 25px 20px;
                width: 95%;
            }
            .featured-card i {
                font-size: 1.5rem !important;
            }
        }
    </style>
</head>
<body>

<!-- ========================================== -->
<!-- PAGE HEADER - Logo and title in one line -->
<!-- ========================================== -->
<div class="hero-section">
    <div class="container text-center">
        <div class="page-header-title">
            <i class="fas fa-lightbulb"></i>
            <h1 class="display-4 fw-bold mb-0">Safety & Awareness Tips</h1>
        </div>
        <p class="lead mt-3">Stay informed and protect yourself from scams, cyber crimes, thefts, frauds, and emergencies.</p>
    </div>
</div>

<div class="container mb-5">
    
    <!-- Admin/Staff Notice Banner -->
    <?php if($is_admin_or_staff): ?>
    <div class="admin-notice">
        <i class="fas fa-info-circle"></i> 
        <strong>Admin/Staff View:</strong> You can view all safety tips. Liking emergency alerts is disabled for admin/staff accounts.
    </div>
    <?php endif; ?>
    
    <!-- ========================================== -->
    <!-- FEATURED TIPS CAROUSEL - Full width, single item -->
    <!-- ========================================== -->
    <?php if(count($featured_tips) > 0): ?>
    <div class="row mb-5">
        <div class="col-12">
            <h3 class="mb-4"><i class="fas fa-star text-warning"></i> Featured Safety Tips</h3>
            <div id="featuredTipsCarousel" class="carousel slide featured-carousel" data-bs-ride="carousel" data-bs-interval="5000">
                <div class="carousel-inner">
                    <?php foreach($featured_tips as $index => $tip): ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <div class="row justify-content-center">
                            <div class="col-12 d-flex justify-content-center">
                                <div class="featured-card text-center" onclick="window.location.href='detailed_tip.php?id=<?php echo $tip['id']; ?>'">
                                    <i class="fas <?php echo getCategoryIcon($tip['category']); ?> text-primary mb-3 d-inline-block"></i>
                                    <h4><?php echo htmlspecialchars($tip['title']); ?></h4>
                                    <span class="badge bg-<?php echo getCategoryColor($tip['category']); ?> mb-3"><?php echo htmlspecialchars($tip['category']); ?></span>
                                    <p class="mb-3"><?php echo htmlspecialchars(substr(strip_tags($tip['content']), 0, 150)) . '...'; ?></p>
                                    <button class="btn btn-primary">Read More <i class="fas fa-arrow-right" style="font-size: 0.7rem; vertical-align: middle;"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Carousel Controls -->
                <?php if(count($featured_tips) > 1): ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#featuredTipsCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon bg-dark rounded-circle p-3" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#featuredTipsCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon bg-dark rounded-circle p-3" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
                
                <!-- Carousel Indicators -->
                <div class="carousel-indicators position-static mt-3">
                    <?php foreach($featured_tips as $index => $tip): ?>
                    <button type="button" data-bs-target="#featuredTipsCarousel" data-bs-slide-to="<?php echo $index; ?>" 
                            class="<?php echo $index === 0 ? 'active' : ''; ?> mx-1" 
                            style="width: 10px; height: 10px; border-radius: 50%; background-color: #667eea;"></button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- ========================================== -->
    <!-- CATEGORY FILTERS WITH "Category" Label -->
    <!-- ========================================== -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap align-items-center justify-content-center">
                <span class="category-label"><i class="fas fa-filter me-1"></i> Category:</span>
                <div class="category-filters">
                    <?php 
                    $categories = array('all', 'Scam', 'Theft', 'ATM', 'Cyber', 'Women', 'Other');
                    foreach($categories as $cat):
                        $is_active = ($selected_category === strtolower($cat) || ($selected_category === 'all' && $cat === 'all')) ? 'active' : '';
                    ?>
                    <a href="?category=<?php echo strtolower($cat); ?>&search=<?php echo urlencode($search_query); ?>&sort=<?php echo $sort_by; ?>" 
                       class="category-pill <?php echo $is_active; ?>" 
                       data-cat="<?php echo $cat; ?>">
                        <?php 
                        if($cat === 'all') echo '<i class="fas fa-list me-1"></i> All';
                        elseif($cat === 'Scam') echo '<i class="fas fa-triangle-exclamation me-1"></i> Scam';
                        elseif($cat === 'Theft') echo '<i class="fas fa-ban me-1"></i> Theft';
                        elseif($cat === 'ATM') echo '<i class="fas fa-credit-card me-1"></i> ATM';
                        elseif($cat === 'Cyber') echo '<i class="fas fa-laptop-code me-1"></i> Cyber';
                        elseif($cat === 'Women') echo '<i class="fas fa-venus me-1"></i> Women';
                        elseif($cat === 'Other') echo '<i class="fas fa-circle-info me-1"></i> Other';
                        ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ========================================== -->
    <!-- MAIN CONTENT SECTION (2 COLUMN LAYOUT) -->
    <!-- ========================================== -->
    <div class="row">
        
        <!-- LEFT SIDEBAR - EMERGENCY ALERTS (SCROLLABLE FIXED HEIGHT) -->
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="fas fa-bell text-warning"></i> Emergency Alerts</h5>
                    <small class="text-muted"><?php echo $is_admin_or_staff ? 'Liking disabled for admin/staff' : 'Click heart to like/unlike alerts (saved permanently)'; ?></small>
                </div>
                <div class="card-body emergency-alerts-container" style="height: 600px; overflow-y: auto;">
                    <?php if(count($emergency_alerts) > 0): ?>
                        <?php foreach($emergency_alerts as $alert): ?>
                        <?php 
                        $is_liked = in_array($alert['id'], $user_liked_alerts);
                        ?>
                        <div class="emergency-card emergency-<?php echo $alert['alert_type']; ?> p-3 rounded mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <i class="fas <?php echo $alert['icon']; ?> me-2"></i>
                                    <strong><?php echo htmlspecialchars($alert['title']); ?></strong>
                                    <p class="small mb-1 mt-2"><?php echo htmlspecialchars($alert['description']); ?></p>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <small class="text-muted">
                                            <i class="far fa-clock"></i> <?php echo date('d M', strtotime($alert['created_at'])); ?>
                                        </small>
                                        <?php if($is_admin_or_staff): ?>
                                            <span class="like-btn disabled" title="Liking disabled for admin/staff">
                                                <i class="far fa-heart text-muted" style="font-size: 0.9rem;"></i>
                                                <span class="like-count ms-1" style="font-size: 0.8rem;"><?php echo $alert['likes']; ?></span>
                                            </span>
                                        <?php else: ?>
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="hidden" name="alert_id" value="<?php echo $alert['id']; ?>">
                                                <button type="submit" name="toggle_like" class="like-btn btn btn-sm btn-link text-decoration-none p-0">
                                                    <?php if($is_liked): ?>
                                                        <i class="fas fa-heart text-danger" style="font-size: 0.9rem;"></i>
                                                    <?php else: ?>
                                                        <i class="far fa-heart text-danger" style="font-size: 0.9rem;"></i>
                                                    <?php endif; ?>
                                                    <span class="like-count ms-1" style="font-size: 0.8rem;"><?php echo $alert['likes']; ?></span>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center">No emergency alerts at this time.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- ========================================== -->
        <!-- RIGHT SIDE (SEARCH, SORT & SAFETY CARDS) -->
        <!-- ========================================== -->
        <div class="col-lg-9">
            <!-- Search and Sort Bar -->
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <!-- Search with icon INSIDE button -->
                        <div class="col-md-7">
                            <form method="GET" action="" class="search-wrapper">
                                <input type="hidden" name="category" value="<?php echo $selected_category; ?>">
                                <input type="hidden" name="sort" value="<?php echo $sort_by; ?>">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Search safety tips..." value="<?php echo htmlspecialchars($search_query); ?>">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search" style="font-size: 0.8rem;"></i> Search
                                    </button>
                                </div>
                                <?php if(!empty($search_query)): ?>
                                <a href="?category=<?php echo $selected_category; ?>&sort=<?php echo $sort_by; ?>" class="btn btn-link btn-sm mt-1">
                                    <i class="fas fa-times" style="font-size: 0.7rem;"></i> Clear search
                                </a>
                                <?php endif; ?>
                            </form>
                        </div>
                        <div class="col-md-5">
                            <!-- Filters label beside dropdown -->
                            <div class="d-flex align-items-center gap-2">
                                <label class="fw-bold mb-0" style="font-size: 0.85rem;">
                                    <i class="fas fa-sliders-h" style="font-size: 0.8rem;"></i> Filters:
                                </label>
                                <form method="GET" action="" class="flex-grow-1">
                                    <input type="hidden" name="category" value="<?php echo $selected_category; ?>">
                                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_query); ?>">
                                    <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()" style="font-size: 0.85rem;">
                                        <option value="recent" <?php echo $sort_by == 'recent' ? 'selected' : ''; ?>>📅 Recently Added</option>
                                        <option value="helpful" <?php echo $sort_by == 'helpful' ? 'selected' : ''; ?>>👍 Most Helpful</option>
                                        <option value="views" <?php echo $sort_by == 'views' ? 'selected' : ''; ?>>👁️ Most Viewed</option>
                                    </select>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Safety Tips Cards Grid -->
            <?php if(count($safety_tips) > 0): ?>
            <div class="row">
                <?php foreach($safety_tips as $tip): 
                    $preview = strip_tags($tip['content']);
                    $preview = strlen($preview) > 100 ? substr($preview, 0, 100) . '...' : $preview;
                ?>
                <div class="col-md-6 col-lg-6 mb-4">
                    <div class="card safety-card shadow-sm border-0 h-100" onclick="window.location.href='detailed_tip.php?id=<?php echo $tip['id']; ?>'">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <i class="fas <?php echo getCategoryIcon($tip['category']); ?> card-icon text-<?php echo getCategoryColor($tip['category']); ?>"></i>
                                <span class="helpful-badge">
                                    <i class="fas fa-thumbs-up stat-icon"></i> <?php echo round($tip['helpful_percentage']); ?>% Helpful
                                </span>
                            </div>
                            <h5 class="card-title mt-2"><?php echo htmlspecialchars($tip['title']); ?></h5>
                            <span class="badge bg-<?php echo getCategoryColor($tip['category']); ?> mb-3">
                                <?php echo htmlspecialchars($tip['category']); ?>
                            </span>
                            <p class="card-preview"><?php echo htmlspecialchars($preview); ?></p>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <small class="text-muted">
                                    <i class="far fa-calendar-alt stat-icon"></i> <?php echo date('d M Y', strtotime($tip['created_at'])); ?>
                                </small>
                                <small class="text-muted">
                                    <i class="far fa-eye stat-icon"></i> <?php echo number_format($tip['views']); ?> views
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo ($page-1); ?>&category=<?php echo $selected_category; ?>&search=<?php echo urlencode($search_query); ?>&sort=<?php echo $sort_by; ?>">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php 
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    if($start_page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?page=1&category=<?php echo $selected_category; ?>&search=<?php echo urlencode($search_query); ?>&sort=<?php echo $sort_by; ?>">1</a></li>
                        <?php if($start_page > 2): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for($i = $start_page; $i <= $end_page; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&category=<?php echo $selected_category; ?>&search=<?php echo urlencode($search_query); ?>&sort=<?php echo $sort_by; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if($end_page < $total_pages): ?>
                        <?php if($end_page < $total_pages - 1): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                        <li class="page-item"><a class="page-link" href="?page=<?php echo $total_pages; ?>&category=<?php echo $selected_category; ?>&search=<?php echo urlencode($search_query); ?>&sort=<?php echo $sort_by; ?>"><?php echo $total_pages; ?></a></li>
                    <?php endif; ?>
                    
                    <?php if($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo ($page+1); ?>&category=<?php echo $selected_category; ?>&search=<?php echo urlencode($search_query); ?>&sort=<?php echo $sort_by; ?>">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                <h5>No safety tips found</h5>
                <p>Please try different search terms or check back later for new tips.</p>
                <a href="safety_tips.php" class="btn btn-primary">View All Tips</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- ========================================== -->
    <!-- EMERGENCY HELPLINE SECTION -->
    <!-- ========================================== -->
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="text-center mb-4"><i class="fas fa-heartbeat text-danger"></i> Emergency Helpline Numbers</h3>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="helpline-card shadow-sm">
                <i class="fas fa-shield-alt text-danger mb-2"></i>
                <h6>Police</h6>
                <div class="helpline-number">100</div>
                <small class="text-muted">24/7 Emergency</small>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="helpline-card shadow-sm">
                <i class="fas fa-female text-pink mb-2"></i>
                <h6>Women Helpline</h6>
                <div class="helpline-number">1091</div>
                <small class="text-muted">Women in Distress</small>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="helpline-card shadow-sm">
                <i class="fas fa-laptop-code text-info mb-2"></i>
                <h6>Cyber Crime</h6>
                <div class="helpline-number">1930</div>
                <small class="text-muted">Cyber Fraud Help</small>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="helpline-card shadow-sm">
                <i class="fas fa-truck-medical text-success mb-2"></i>
                <h6>Ambulance</h6>
                <div class="helpline-number">108</div>
                <small class="text-muted">Medical Emergency</small>
            </div>
        </div>
    </div>
</div>

<script>
// Card click handling
document.querySelectorAll('.safety-card').forEach(card => {
    card.addEventListener('click', function(e) {
        if(e.target.tagName === 'A' || e.target.tagName === 'BUTTON' || e.target.closest('a') || e.target.closest('button')) {
            e.stopPropagation();
        }
    });
});
</script>

<?php include_once '../../includes/footer.php'; ?>
</body>
</html>