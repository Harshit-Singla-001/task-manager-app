<?php
// ==========================================
// FILE: includes/footer.php
// Common Footer - Updated with social links
// ==========================================
?>
<!-- Footer -->
<footer class="bg-dark text-white py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4 mb-md-0">
                <h5><i class="fas fa-shield-alt me-2"></i><?= SITE_NAME ?></h5>
                <p class="text-white-50 small">A secure and user-friendly platform for reporting and tracking crime cases digitally. Developed as an educational project.</p>
                <div class="mt-3">
                    <small class="text-white-50">
                        <i class="fas fa-calendar-alt me-1"></i> Last Updated: 2026
                    </small>
                </div>
            </div>
            
            <div class="col-md-4 mb-4 mb-md-0">
                <h5><i class="fas fa-address-card me-2"></i>Contact Info</h5>
                <ul class="list-unstyled small">
                    <li class="mb-2">
                        <i class="fas fa-user text-primary me-2"></i>
                        <span class="text-white-50">Harshit Singla</span>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-envelope text-primary me-2"></i>
                        <a href="mailto:singlaharshit480@gmail.com" class="text-white-50 text-decoration-none">singlaharshit480@gmail.com</a>
                    </li>
                </ul>
            </div>
            
            <div class="col-md-4">
                <h5><i class="fas fa-link me-2"></i>Connect With Me</h5>
                <ul class="list-unstyled small">
                    <li class="mb-2">
                        <i class="fab fa-linkedin text-info me-2"></i>
                        <a href="https://www.linkedin.com/in/harshit-singla001" target="_blank" class="text-white-50 text-decoration-none">LinkedIn</a>
                    </li>
                    <li class="mb-2">
                        <i class="fab fa-github text-light me-2"></i>
                        <a href="https://github.com/Harshit-Singla-001" target="_blank" class="text-white-50 text-decoration-none">GitHub</a>
                    </li>
                    <li class="mb-2">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/1/19/LeetCode_logo_black.png" 
                             alt="LeetCode" 
                             style="width: 18px; height: 18px; margin-right: 8px;">
                        <a href="https://leetcode.com/u/Harshit-Singla-001/" target="_blank" class="text-white-50 text-decoration-none">LeetCode</a>
                    </li>
                </ul>
            </div>
        </div>
        
        <hr class="bg-secondary mt-3">
        
        <div class="text-center">
            <small class="text-white-50">
                <i class="fas fa-graduation-cap me-1"></i> 
                &copy; 2026 <?= SITE_NAME ?> | Educational Project |
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