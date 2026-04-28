<?php
// ==========================================
// FILE: admin/users/manage_users.php
// Manage Users Page - NO HTML TAGS (navbar handles them)
// ==========================================

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin authentication check
require_once dirname(__DIR__) . '/../includes/admin_auth_check.php';

// Set session if not set (for demo)
if (!isset($_SESSION['admin_name'])) {
    $_SESSION['admin_name'] = 'Rajesh Kumar';
}

// Define ROOT_PATH if not defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)) . '/');
}

require_once ROOT_PATH . 'config/constants.php';

// Dummy users data (PHP 5 compatible array syntax)
$users = array(
    array('id' => 1, 'name' => 'Priya Singh', 'email' => 'priya@example.com', 'status' => 'Active', 'registered' => '2024-01-01'),
    array('id' => 2, 'name' => 'Amit Kumar', 'email' => 'amit@example.com', 'status' => 'Active', 'registered' => '2024-01-02'),
    array('id' => 3, 'name' => 'Neha Sharma', 'email' => 'neha@example.com', 'status' => 'Blocked', 'registered' => '2024-01-03'),
    array('id' => 4, 'name' => 'Rahul Verma', 'email' => 'rahul@example.com', 'status' => 'Active', 'registered' => '2024-01-04'),
    array('id' => 5, 'name' => 'Sneha Patel', 'email' => 'sneha@example.com', 'status' => 'Pending', 'registered' => '2024-01-05'),
);

// Include admin navbar (contains all HTML head and body tags)
include_once ROOT_PATH . 'includes/admin_navbar.php';
?>

<style>
    /* Page specific styles */
    .user-table th {
        background: #f8f9fa;
    }
    
    .status-active {
        color: #28a745;
        font-weight: 500;
    }
    
    .status-blocked {
        color: #dc3545;
        font-weight: 500;
    }
    
    .status-pending {
        color: #ffc107;
        font-weight: 500;
    }
    
    .btn-sm {
        border-radius: 20px;
    }
</style>

<div class="main-content">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>👥 Manage Users</h2>
            <button class="btn btn-primary" onclick="alert('Add user form would open here')">
                + Add New User
            </button>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover user-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Registered Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo $user['name']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td><?php echo $user['registered']; ?></td>
                        <td>
                            <span class="status-<?php echo strtolower($user['status']); ?>">
                                <?php echo $user['status']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if($user['status'] == 'Active'): ?>
                                <button class="btn btn-warning btn-sm" onclick="alert('User blocked!')">
                                    Block
                                </button>
                            <?php elseif($user['status'] == 'Blocked'): ?>
                                <button class="btn btn-success btn-sm" onclick="alert('User unblocked!')">
                                    Unblock
                                </button>
                            <?php endif; ?>
                            <button class="btn btn-info btn-sm" onclick="alert('View user details')">
                                View
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
// Include admin footer (closes body and html tags)
include_once ROOT_PATH . 'includes/admin_footer.php'; 
?>