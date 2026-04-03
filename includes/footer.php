<?php
// ==========================================
// FILE: includes/footer.php
// Common Footer - Updated with BASE_URL
// ==========================================
?>
<!-- Footer -->
<footer class="bg-dark text-white mt-5 py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5><i class="fas fa-shield-alt me-2"></i><?= SITE_NAME ?></h5>
                <p>Safe & Secure platform to report crimes anonymously.</p>
            </div>
            <div class="col-md-4">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="<?= BASE_URL ?>user/home.php" class="text-white-50 text-decoration-none">Home</a></li>
                    <li><a href="<?= BASE_URL ?>user/fir/file_fir.php" class="text-white-50 text-decoration-none">File FIR</a></li>
                    <li><a href="<?= BASE_URL ?>user/pages/safety_tips.php" class="text-white-50 text-decoration-none">Safety Tips</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Emergency Numbers</h5>
                <p><i class="fas fa-phone-alt"></i> Police: 100<br>
                <i class="fas fa-ambulance"></i> Ambulance: 102<br>
                <i class="fas fa-fire-extinguisher"></i> Fire: 101</p>
            </div>
        </div>
        <hr class="bg-secondary">
        <div class="text-center">
            <small>&copy; 2024 <?= SITE_NAME ?>. All rights reserved.</small>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>