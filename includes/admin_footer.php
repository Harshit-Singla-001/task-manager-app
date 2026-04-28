<?php
// ==========================================
// FILE: includes/admin_footer.php
// Admin Footer - Same design as user footer
// ==========================================

// Get admin name
$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : (isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Admin');
?>
<!-- Admin Footer -->
<footer class="bg-dark text-white py-4">
    <div class="container">
        <div class="row">
            <!-- Left Section -->
            <div class="col-md-4 mb-4 mb-md-0">
                <h5><i class="fas fa-shield-alt me-2"></i><?= SITE_NAME ?></h5>
                <p class="text-white-50 small">Admin Panel - Manage FIRs, Users, and System Settings</p>
                <div class="mt-3">
                    <small class="text-white-50">
                        <i class="fas fa-calendar-alt me-1"></i> Last Updated: 2026
                    </small>
                </div>
            </div>
            
            <!-- Center Section -->
            <div class="col-md-4 mb-4 mb-md-0 text-center">
                <h5><i class="fas fa-link me-2"></i>Quick Links</h5>
                <ul class="list-unstyled small">
                    <li class="mb-2">
                        <a href="<?= BASE_URL ?>admin/profile/help.php" class="text-white-50 text-decoration-none">Help</a>
                    </li>
                    <li class="mb-2">
                        <span class="text-white-50">
                            <span class="system-status" style="display: inline-block; width: 8px; height: 8px; background-color: #2ecc71; border-radius: 50%; margin-right: 5px;"></span>
                            System Active
                        </span>
                    </li>
                    <li class="mb-2">
                        <a href="<?= BASE_URL ?>admin/settings/settings.php" class="text-white-50 text-decoration-none">Settings</a>
                    </li>
                </ul>
            </div>
            
            <!-- Right Section -->
            <div class="col-md-4 text-md-end text-center">
                <h5><i class="fas fa-user-shield me-2"></i>Administrator</h5>
                <ul class="list-unstyled small">
                    <li class="mb-2">
                        <i class="fas fa-user text-primary me-2"></i>
                        <span class="text-white-50"><?= htmlspecialchars($admin_name) ?></span>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-envelope text-primary me-2"></i>
                        <a href="mailto:<?= ADMIN_EMAIL ?>" class="text-white-50 text-decoration-none"><?= ADMIN_EMAIL ?></a>
                    </li>
                </ul>
            </div>
        </div>
        
        <hr class="bg-secondary mt-3">
        
        <div class="text-center">
            <small class="text-white-50">
                <i class="fas fa-graduation-cap me-1"></i> 
                &copy; 2026 <?= SITE_NAME ?> | Admin Panel | Educational Project
            </small>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Add active class to current menu item in mobile view
    document.addEventListener('DOMContentLoaded', function() {
        var currentPage = window.location.pathname.split('/').pop();
        var navLinks = document.querySelectorAll('.navbar-nav .nav-link');
        
        navLinks.forEach(function(link) {
            var linkHref = link.getAttribute('href').split('/').pop();
            if(linkHref === currentPage) {
                link.classList.add('active');
            }
        });
    });
</script>
</body>
</html>