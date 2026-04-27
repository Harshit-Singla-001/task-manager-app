<?php
// ==========================================
// FILE: user/pages/about.php
// About Page - Complete Redesign with Logo & New Future Scope
// ==========================================
include_once '../../includes/header.php';
?>

<!-- Page Title Section with Logo -->
<section class="page-title-section bg-light py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <!-- Logo at the beginning -->
                <div class="about-logo mb-4">
                    <i class="fas fa-shield-alt fa-4x text-primary"></i>
                    <h1 class="display-4 fw-bold text-primary mt-3">About Online Crime Reporting System</h1>
                </div>
                <p class="lead mt-3">A secure and user-friendly platform for reporting and tracking crime cases digitally.</p>
            </div>
        </div>
    </div>
</section>

<!-- Disclaimer Section - Always Visible (Not a Popup) -->
<section class="disclaimer-section py-4">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="alert alert-warning border border-warning shadow-sm" style="background-color: #fff3cd;">
                    <div class="d-flex">
                        <div class="me-3">
                            <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                        </div>
                        <div>
                            <h5 class="alert-heading fw-bold mb-2">⚠️ EDUCATIONAL DISCLAIMER</h5>
                            <p class="mb-0">This project is developed for educational purposes only. All FIR records and case data shown on this platform are fictional and do not represent real incidents. This system is not connected to any official law enforcement authority.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Project Description Section -->
<section class="project-description py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="section-header text-center mb-5">
                    <h2 class="fw-bold text-primary">Project Description</h2>
                    <div class="divider bg-primary mx-auto" style="width: 60px; height: 3px;"></div>
                </div>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="text-primary mb-3">🎯 Purpose of the System</h4>
                        <p>The Online Crime Reporting System is designed to modernize the traditional method of filing crime complaints. It provides a secure, transparent, and efficient digital platform where citizens can report crimes, upload evidence, and track the status of their complaints in real-time.</p>
                    </div>
                </div>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="text-primary mb-3">💡 Why It Was Created (Academic Project)</h4>
                        <p>This project was developed as part of an academic curriculum to demonstrate the practical application of web development, database management, and security concepts learned during the BTech program. It serves as a proof-of-concept for digitizing citizen-police interactions.</p>
                    </div>
                </div>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="text-primary mb-3">🚨 Problems It Solves</h4>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <i class="fas fa-file-alt fa-2x text-danger mb-2"></i>
                                    <h6>Manual FIR Issues</h6>
                                    <small class="text-muted">No more long queues and paperwork delays</small>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <i class="fas fa-eye-slash fa-2x text-danger mb-2"></i>
                                    <h6>Lack of Transparency</h6>
                                    <small class="text-muted">Real-time case tracking and updates</small>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <i class="fas fa-chart-line fa-2x text-danger mb-2"></i>
                                    <h6>Difficulty in Tracking</h6>
                                    <small class="text-muted">Digital record keeping with searchable history</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How the System Works Section -->
<section class="how-it-works py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="section-header text-center mb-5">
                    <h2 class="fw-bold text-primary">How the System Works</h2>
                    <div class="divider bg-primary mx-auto" style="width: 60px; height: 3px;"></div>
                    <p class="mt-3">A simple 5-step process to file and track your complaint</p>
                </div>
                
                <div class="steps-wrapper">
                    <div class="step-item d-flex mb-4 p-3 bg-white rounded shadow-sm">
                        <div class="step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; min-width: 50px;">
                            <span class="fw-bold h5 mb-0">1</span>
                        </div>
                        <div>
                            <h5 class="text-primary mb-2">User Signs Up & Verifies Account</h5>
                            <p class="mb-0 text-muted">Citizens create an account using their email and mobile number (OTP verification). One-time verification ensures authentic registration.</p>
                        </div>
                    </div>
                    
                    <div class="step-item d-flex mb-4 p-3 bg-white rounded shadow-sm">
                        <div class="step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; min-width: 50px;">
                            <span class="fw-bold h5 mb-0">2</span>
                        </div>
                        <div>
                            <h5 class="text-primary mb-2">Logs Into the System</h5>
                            <p class="mb-0 text-muted">Secure login with password encryption protects user accounts from unauthorized access. Day/Night mode available for comfortable viewing.</p>
                        </div>
                    </div>
                    
                    <div class="step-item d-flex mb-4 p-3 bg-white rounded shadow-sm">
                        <div class="step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; min-width: 50px;">
                            <span class="fw-bold h5 mb-0">3</span>
                        </div>
                        <div>
                            <h5 class="text-primary mb-2">Files FIR Using Form</h5>
                            <p class="mb-0 text-muted">Users fill out a comprehensive FIR form with incident details, location, description, and can upload evidence images. Language switch option available.</p>
                        </div>
                    </div>
                    
                    <div class="step-item d-flex mb-4 p-3 bg-white rounded shadow-sm">
                        <div class="step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; min-width: 50px;">
                            <span class="fw-bold h5 mb-0">4</span>
                        </div>
                        <div>
                            <h5 class="text-primary mb-2">Can Track FIR Status</h5>
                            <p class="mb-0 text-muted">After filing, users can monitor their complaint status in "My FIRs" section. Users can also save interesting public FIRs to their "Saved Records" section.</p>
                        </div>
                    </div>
                    
                    <div class="step-item d-flex mb-4 p-3 bg-white rounded shadow-sm">
                        <div class="step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; min-width: 50px;">
                            <span class="fw-bold h5 mb-0">5</span>
                        </div>
                        <div>
                            <h5 class="text-primary mb-2">Can View Public FIR Records</h5>
                            <p class="mb-0 text-muted">Limited case information (category, city, date, status) is made available publicly while protecting victim identity and sensitive details. Graphical crime representation helps understand crime patterns.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Key Features Section -->
<section class="key-features py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="section-header text-center mb-5">
                    <h2 class="fw-bold text-primary">Key Features</h2>
                    <div class="divider bg-primary mx-auto" style="width: 60px; height: 3px;"></div>
                </div>
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="feature-card p-3 border rounded shadow-sm h-100">
                            <i class="fas fa-lock fa-2x text-primary mb-2"></i>
                            <h5>Secure Login & Mobile Verification</h5>
                            <p class="text-muted small">Password hashing, session management, and mobile number OTP verification protect user accounts.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="feature-card p-3 border rounded shadow-sm h-100">
                            <i class="fas fa-file-upload fa-2x text-primary mb-2"></i>
                            <h5>FIR Filing System</h5>
                            <p class="text-muted small">User-friendly form with evidence upload capability and automatic case ID generation.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="feature-card p-3 border rounded shadow-sm h-100">
                            <i class="fas fa-chart-line fa-2x text-primary mb-2"></i>
                            <h5>FIR Status Tracking</h5>
                            <p class="text-muted small">Real-time updates on complaint progress from submission to resolution.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="feature-card p-3 border rounded shadow-sm h-100">
                            <i class="fas fa-globe fa-2x text-primary mb-2"></i>
                            <h5>Public FIR Records & Graphs</h5>
                            <p class="text-muted small">Privacy-protected case information with graphical crime representation across states/areas.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="feature-card p-3 border rounded shadow-sm h-100">
                            <i class="fas fa-envelope fa-2x text-primary mb-2"></i>
                            <h5>Contact & Query System</h5>
                            <p class="text-muted small">Users can submit queries and feedback directly through the platform.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="feature-card p-3 border rounded shadow-sm h-100">
                            <i class="fas fa-bookmark fa-2x text-primary mb-2"></i>
                            <h5>Saved Records Section</h5>
                            <p class="text-muted small">Users can save interesting public FIRs for future reference.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="feature-card p-3 border rounded shadow-sm h-100">
                            <i class="fas fa-users fa-2x text-primary mb-2"></i>
                            <h5>Multiple Staff Roles</h5>
                            <p class="text-muted small">Different staff roles (Admin, Officer, Clerk) with role-specific tasks and permissions.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="feature-card p-3 border rounded shadow-sm h-100">
                            <i class="fas fa-palette fa-2x text-primary mb-2"></i>
                            <h5>Day/Night Mode & Language Switch</h5>
                            <p class="text-muted small">User preference-based theme toggle and multi-language support for better accessibility.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Privacy & Data Handling Section -->
<section class="privacy-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="section-header text-center mb-5">
                    <h2 class="fw-bold text-primary">Privacy & Data Handling</h2>
                    <div class="divider bg-primary mx-auto" style="width: 60px; height: 3px;"></div>
                </div>
                
                <div class="card border-success shadow-sm">
                    <div class="card-body p-4">
                        <div class="alert alert-success mb-4">
                            <i class="fas fa-shield-alt fa-2x float-end"></i>
                            <h5 class="fw-bold">Your Privacy Matters</h5>
                            <p class="mb-0">We are committed to protecting your sensitive information and maintaining transparency in data handling.</p>
                        </div>
                        
                        <h5 class="text-primary mt-4 mb-3">🔒 What is NOT Publicly Visible:</h5>
                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item bg-transparent"><i class="fas fa-times-circle text-danger me-2"></i> Full Address (Residential/Incident)</li>
                            <li class="list-group-item bg-transparent"><i class="fas fa-times-circle text-danger me-2"></i> Identity Proofs (Aadhaar, PAN)</li>
                            <li class="list-group-item bg-transparent"><i class="fas fa-times-circle text-danger me-2"></i> Contact Information (Phone, Email)</li>
                            <li class="list-group-item bg-transparent"><i class="fas fa-times-circle text-danger me-2"></i> Suspect/Witness Details</li>
                            <li class="list-group-item bg-transparent"><i class="fas fa-times-circle text-danger me-2"></i> Evidence Images</li>
                        </ul>
                        
                        <h5 class="text-primary mt-4 mb-3">👁️ What is Publicly Visible (Limited Info):</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i> Crime Category</li>
                            <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i> City of Incident</li>
                            <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i> Incident Date</li>
                            <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i> Case Status</li>
                            <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i> General Description (Limited)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Developer Section with Toggle (i icon controlled) -->
<section class="developer-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="section-header text-center mb-5">
                    <i class="fas fa-user-tie fa-2x text-primary mb-2"></i>
                    <h2 class="fw-bold text-primary">About the Developer</h2>
                    <div class="divider bg-primary mx-auto" style="width: 60px; height: 3px;"></div>
                </div>
                
                <!-- Info Icon (controls the toggle) -->
                <div class="text-end mb-2">
                    <i class="fas fa-info-circle text-primary" id="toggleDeveloperCard" style="font-size: 1.2rem; cursor: pointer;" title="Click to view detailed information"></i>
                </div>
                
                <!-- Simple Card (shown by default) -->
                <div id="simpleCard" class="developer-simple-card">
                    <div class="card shadow-sm">
                        <div class="card-body p-4 text-center">
                            <div class="developer-photo mb-3">
                                <img src="https://iili.io/f1ZAiss.jpg" alt="Harshit Singla" class="profile-img-portrait rounded-circle" style="width: 180px; height: 180px; object-fit: cover; border: 4px solid #0d6efd; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                            </div>
                            <h3 class="fw-bold mb-2">Harshit Singla</h3>
                            <p class="text-primary fw-bold mb-1">BTech AI & Data Science</p>
                            <p class="text-muted">2024 - 2028</p>
                        </div>
                    </div>
                </div>
                
                <!-- Detailed Card (hidden by default, shows when i icon clicked) -->
                <div id="detailedCard" class="developer-detailed-card" style="display: none;">
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <div class="developer-info">
                                <h3 class="fw-bold mb-2">Harshit Singla</h3>
                                <p class="developer-role text-primary fw-bold mb-3">Student</p>
                                <p style="text-align: justify;">Currently pursuing <strong>BTech in Artificial Intelligence & Data Science (4th Sem)</strong> at CGC College of Engineering.</p>
                                <p style="text-align: justify;">Passionate about creating beautiful, functional web applications that provide great user experiences. This project combines my interest in design with my technical skills in frontend and backend development.</p>
                                <div class="education-info mt-3">
                                    <p><i class="fas fa-graduation-cap text-primary me-2"></i> CGC College of Engineering</p>
                                    <p><i class="fas fa-book text-primary me-2"></i> BTech AI & Data Science (2024-2028)</p>
                                </div>
                                <p class="mt-3 text-muted fst-italic" style="text-align: justify;">Developed as part of an academic project to demonstrate web development and database management skills.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // JavaScript to toggle between simple and detailed card
    document.getElementById('toggleDeveloperCard').addEventListener('click', function() {
        var simpleCard = document.getElementById('simpleCard');
        var detailedCard = document.getElementById('detailedCard');
        
        if (simpleCard.style.display === 'none') {
            simpleCard.style.display = 'block';
            detailedCard.style.display = 'none';
        } else {
            simpleCard.style.display = 'none';
            detailedCard.style.display = 'block';
        }
    });
</script>

<!-- Future Scope Section - Prioritized List -->
<section class="future-scope py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="section-header text-center mb-5">
                    <h2 class="fw-bold text-primary">Future Scope</h2>
                    <div class="divider bg-primary mx-auto" style="width: 60px; height: 3px;"></div>
                    <p class="mt-3">Planned enhancements for the next versions (Listed by priority)</p>
                </div>
                
                <div class="row g-3">
                    <!-- Priority 1 -->
                    <div class="col-md-6">
                        <div class="future-card p-3 bg-white rounded shadow-sm h-100">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-danger me-2">Priority 1</span>
                                <i class="fas fa-chart-line fa-2x text-primary"></i>
                            </div>
                            <h6>Graph representation of crimes across states/areas</h6>
                            <p class="small text-muted">Visual analytics dashboard showing crime patterns and statistics</p>
                        </div>
                    </div>
                    
                    <!-- Priority 2 -->
                    <div class="col-md-6">
                        <div class="future-card p-3 bg-white rounded shadow-sm h-100">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-warning text-dark me-2">Priority 2</span>
                                <i class="fas fa-bell fa-2x text-primary"></i>
                            </div>
                            <h6>Admin dashboard notifications</h6>
                            <p class="small text-muted">Real-time alerts to admin on pending queries and FIRs awaiting approval</p>
                        </div>
                    </div>
                    
                    <!-- Priority 3 -->
                    <div class="col-md-6">
                        <div class="future-card p-3 bg-white rounded shadow-sm h-100">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-warning text-dark me-2">Priority 3</span>
                                <i class="fas fa-phone-alt fa-2x text-primary"></i>
                            </div>
                            <h6>Mobile number verification during signup</h6>
                            <p class="small text-muted">OTP-based mobile verification for enhanced account security</p>
                        </div>
                    </div>
                    
                    <!-- Priority 4 -->
                    <div class="col-md-6">
                        <div class="future-card p-3 bg-white rounded shadow-sm h-100">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-warning text-dark me-2">Priority 4</span>
                                <i class="fas fa-users-cog fa-2x text-primary"></i>
                            </div>
                            <h6>Multiple staff roles with role-specific tasks</h6>
                            <p class="small text-muted">Different permission levels for Admin, Officer, and Clerk roles</p>
                        </div>
                    </div>
                    
                    <!-- Priority 5 -->
                    <div class="col-md-6">
                        <div class="future-card p-3 bg-white rounded shadow-sm h-100">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-info text-dark me-2">Priority 5</span>
                                <i class="fas fa-history fa-2x text-primary"></i>
                            </div>
                            <h6>Admin log to track actions</h6>
                            <p class="small text-muted">Complete audit trail of all administrative activities</p>
                        </div>
                    </div>
                    
                    <!-- Priority 6 -->
                    <div class="col-md-6">
                        <div class="future-card p-3 bg-white rounded shadow-sm h-100">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-info text-dark me-2">Priority 6</span>
                                <i class="fas fa-bookmark fa-2x text-primary"></i>
                            </div>
                            <h6>"Saved Records" section for users</h6>
                            <p class="small text-muted">Users can save interesting public FIRs for future reference</p>
                        </div>
                    </div>
                    
                    <!-- Priority 7 -->
                    <div class="col-md-6">
                        <div class="future-card p-3 bg-white rounded shadow-sm h-100">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-info text-dark me-2">Priority 7</span>
                                <i class="fas fa-user-circle fa-2x text-primary"></i>
                            </div>
                            <h6>Profile picture upload via free image host</h6>
                            <p class="small text-muted">Users can upload and manage profile pictures using external image hosting</p>
                        </div>
                    </div>
                    
                    <!-- Priority 8 -->
                    <div class="col-md-6">
                        <div class="future-card p-3 bg-white rounded shadow-sm h-100">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-info text-dark me-2">Priority 8</span>
                                <i class="fas fa-language fa-2x text-primary"></i>
                            </div>
                            <h6>Language switch option for users</h6>
                            <p class="small text-muted">Multi-language support for better accessibility</p>
                        </div>
                    </div>
                    
                    <!-- Priority 9 -->
                    <div class="col-md-6">
                        <div class="future-card p-3 bg-white rounded shadow-sm h-100">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-secondary me-2">Priority 9</span>
                                <i class="fas fa-adjust fa-2x text-primary"></i>
                            </div>
                            <h6>Day/Night mode toggle based on user preference</h6>
                            <p class="small text-muted">Theme switcher for comfortable viewing experience</p>
                        </div>
                    </div>
                    
                    <!-- Priority 10 -->
                    <div class="col-md-6">
                        <div class="future-card p-3 bg-white rounded shadow-sm h-100">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-secondary me-2">Priority 10</span>
                                <i class="fas fa-clock fa-2x text-primary"></i>
                            </div>
                            <h6>Track last updated time of the site</h6>
                            <p class="small text-muted">Display recent changes and site update timestamps</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Legal Links Section (Terms & Privacy Policy) -->
<section class="legal-links py-4 bg-white">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <div class="legal-info p-3 border-top border-bottom">
                    <p class="mb-0 small text-muted">
                        <i class="fas fa-gavel me-1"></i> 
                        <a href="terms_of_service.php" class="text-decoration-none me-3">Terms of Service</a> 
                        <span class="text-muted">|</span> 
                        <a href="privacy_policy.php" class="text-decoration-none ms-3">Privacy Policy</a>
                    </p>
                    <p class="mt-2 small text-muted mb-0">
                        <i class="fas fa-info-circle me-1"></i> These legal documents outline your rights and responsibilities when using this platform.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include_once '../../includes/footer.php'; ?>