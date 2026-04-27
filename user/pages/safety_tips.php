<?php
// ==========================================
// FILE: user/pages/safety_tips.php
// Safety Tips Page
// ==========================================
include_once '../../includes/header.php';
?>

<div class="main-content">
<div class="container my-5">
    <h2 class="text-center mb-4"><i class="fas fa-shield-virus"></i> Safety Tips & Guidelines</h2>
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow">
                <div class="card-body">
                    <i class="fas fa-laptop-code fa-3x text-primary mb-3"></i>
                    <h5>Cyber Safety</h5>
                    <ul>
                        <li>Use strong passwords (8+ chars, mix of letters/numbers/symbols)</li>
                        <li>Enable two-factor authentication wherever possible</li>
                        <li>Never share OTP or bank details with anyone</li>
                        <li>Keep software and antivirus updated</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow">
                <div class="card-body">
                    <i class="fas fa-person-walking-arrow-right fa-3x text-success mb-3"></i>
                    <h5>Personal Safety</h5>
                    <ul>
                        <li>Be aware of your surroundings when walking alone</li>
                        <li>Share your live location with trusted contacts</li>
                        <li>Keep emergency numbers on speed dial</li>
                        <li>Avoid isolated areas at night</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow">
                <div class="card-body">
                    <i class="fas fa-car-side fa-3x text-warning mb-3"></i>
                    <h5>Road Safety</h5>
                    <ul>
                        <li>Always wear seatbelt and helmet</li>
                        <li>Follow traffic signals and speed limits</li>
                        <li>Never drink and drive</li>
                        <li>Keep vehicle documents handy</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="alert alert-info mt-3">
        <i class="fas fa-phone-alt"></i> <strong>Emergency Helpline:</strong> For any immediate help, dial 100 (Police) or 112 (Emergency Response).
    </div>
</div>
</div>

<?php include_once '../../includes/footer.php'; ?>