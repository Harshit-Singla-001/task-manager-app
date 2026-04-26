<?php
// ==========================================
// FILE: user/home.php
// Home Page (Dashboard after Login)
// ==========================================
include_once '../includes/header.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../index.php");
    exit();
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-success">
                <h4><i class="fas fa-user-check"></i> Welcome back, Demo User!</h4>
                <p>This is your dashboard. Use the navigation menu to access all features.</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card text-center h-100 shadow">
                <div class="card-body">
                    <i class="fas fa-file-alt fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">File New FIR</h5>
                    <p class="card-text">Report a new crime incident online</p>
                    <a href="fir/file_fir.php" class="btn btn-primary">File FIR →</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card text-center h-100 shadow">
                <div class="card-body">
                    <i class="fas fa-list-alt fa-3x text-success mb-3"></i>
                    <h5 class="card-title">My FIRs</h5>
                    <p class="card-text">View status of your filed FIRs</p>
                    <a href="fir/my_firs.php" class="btn btn-success">View Status →</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card text-center h-100 shadow">
                <div class="card-body">
                    <i class="fas fa-chart-line fa-3x text-info mb-3"></i>
                    <h5 class="card-title">Statistics</h5>
                    <p class="card-text">Total FIRs: 24 | Resolved: 18</p>
                    <button class="btn btn-info text-white">View Reports</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5><i class="fas fa-bell"></i> Recent Updates</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">✅ FIR #FIR001 status updated to "Under Investigation"</li>
                        <li class="list-group-item">📢 New safety guidelines issued for cyber crime</li>
                        <li class="list-group-item">👮‍♀️ Police camp on 15th December at Central Park</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h5><i class="fas fa-tips"></i> Quick Safety Tip</h5>
                </div>
                <div class="card-body">
                    <p><i class="fas fa-quote-left me-2"></i> Always save emergency numbers in your phone. In case of cyber crime, report immediately on cybercrime.gov.in</p>
                    <a href="pages/safety_tips.php" class="btn btn-sm btn-warning">More Tips →</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>