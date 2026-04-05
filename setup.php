<?php
// ==========================================
// FILE: setup.php
// Database Setup & Installation Script
// Online Crime Reporting System
// ==========================================

// Error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_implicit_flush(true);
ob_end_flush();

// Lock mechanism to prevent accidental re-run
$lockFile = 'setup.lock';

// Check if setup is already completed
if (file_exists($lockFile) && !isset($_GET['reset'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Setup Already Completed</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; }
            .card { border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-warning text-dark text-center">
                            <h3><i class="fas fa-lock"></i> Setup Already Completed</h3>
                        </div>
                        <div class="card-body text-center">
                            <p>The database setup has already been completed.</p>
                            <p>To reinstall, use the <strong>Reset & Reinstall</strong> option below.</p>
                            <hr>
                            <a href="?reset=1" class="btn btn-danger">Reset & Reinstall</a>
                            <a href="index.php" class="btn btn-primary">Go to Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Database configuration
$host = "localhost";
$user = "root";
$password = "";
$db_name = "online_crime_reporting";

// Check if reset option is selected
$isReset = isset($_GET['reset']) && $_GET['reset'] == 1;

// Process setup when form is submitted
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'first_time') {
        runSetup($host, $user, $password, $db_name, false);
    } elseif ($action === 'reset') {
        runSetup($host, $user, $password, $db_name, true);
    }
}

/**
 * Main setup function
 */
function runSetup($host, $user, $password, $db_name, $reset = false) {
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Setup Progress</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            body { background: #f0f2f5; padding: 20px; }
            .progress-container { max-width: 800px; margin: 50px auto; }
            .log { background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 13px; max-height: 400px; overflow-y: auto; }
            .success { color: #4caf50; }
            .error { color: #f44336; }
            .info { color: #2196f3; }
        </style>
    </head>
    <body>
        <div class="container progress-container">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4><i class="fas fa-database"></i> Setup Progress</h4>
                </div>
                <div class="card-body">
                    <div class="log" id="log">';
    
    flush();
    
    // Log function
    function logMessage($message, $type = 'info') {
        $icon = $type === 'success' ? '✅' : ($type === 'error' ? '❌' : 'ℹ️');
        echo "<div class='$type'><i class='fas fa-" . ($type === 'success' ? 'check-circle' : ($type === 'error' ? 'times-circle' : 'info-circle')) . "'></i> $message</div>";
        flush();
    }
    
    try {
        // Step 1: Connect without database
        logMessage("Connecting to MySQL server...", "info");
        $conn = new mysqli($host, $user, $password);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        logMessage("Connected successfully!", "success");
        
        // Step 2: Reset if requested
        if ($reset) {
            logMessage("DROP DATABASE IF EXISTS $db_name...", "info");
            if ($conn->query("DROP DATABASE IF EXISTS $db_name")) {
                logMessage("Existing database dropped successfully!", "success");
            } else {
                throw new Exception("Error dropping database: " . $conn->error);
            }
        }
        
        // Step 3: Create database
        logMessage("Creating database $db_name...", "info");
        $sql = "CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        if ($conn->query($sql)) {
            logMessage("Database created successfully!", "success");
        } else {
            throw new Exception("Error creating database: " . $conn->error);
        }
        
        // Step 4: Select database
        logMessage("Selecting database $db_name...", "info");
        if ($conn->select_db($db_name)) {
            logMessage("Database selected!", "success");
        } else {
            throw new Exception("Error selecting database: " . $conn->error);
        }
        
        // Step 5: Create tables
        logMessage("Creating tables...", "info");
        createTables($conn);
        logMessage("All tables created successfully!", "success");
        
        // Step 6: Insert sample data
        logMessage("Inserting sample data...", "info");
        insertSampleData($conn);
        logMessage("Sample data inserted successfully!", "success");
        
        // Step 7: Create lock file
        file_put_contents('setup.lock', date('Y-m-d H:i:s') . " - Setup completed\n");
        logMessage("Setup lock file created!", "success");
        
        logMessage("🎉 SETUP COMPLETED SUCCESSFULLY!", "success");
        
        echo '<hr>
        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-success"><i class="fas fa-sign-in-alt"></i> Go to Login Page</a>
        </div>';
        
    } catch (Exception $e) {
        logMessage("Error: " . $e->getMessage(), "error");
        echo '<hr>
        <div class="text-center mt-3">
            <a href="setup.php" class="btn btn-primary"><i class="fas fa-redo"></i> Try Again</a>
        </div>';
    }
    
    echo '          </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>';
    exit();
}

/**
 * Create all tables in correct order
 */
function createTables($conn) {
    $tables = [
        // Users Table
        "CREATE TABLE IF NOT EXISTS users (
            user_id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(100) NOT NULL,
            dob DATE NOT NULL,
            aadhaar_number VARCHAR(12) NOT NULL UNIQUE,
            pan_number VARCHAR(10) NULL,
            address TEXT NOT NULL,
            phone_number VARCHAR(15) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            recovery_key_hash TEXT NOT NULL,
            role ENUM('user','admin','staff') DEFAULT 'user',
            status ENUM('active','suspended','blocked') DEFAULT 'active',
            failed_attempts INT DEFAULT 0,
            last_login DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_phone (phone_number)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // Staff Table
        "CREATE TABLE IF NOT EXISTS staff (
            staff_id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(100) NOT NULL,
            dob DATE NOT NULL,
            phone_number VARCHAR(15) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            recovery_key_hash VARCHAR(255) NOT NULL,
            role ENUM('admin','staff','officer','moderator') NOT NULL,
            access_level INT DEFAULT 1,
            status ENUM('active','suspended','blocked') DEFAULT 'active',
            failed_attempts INT DEFAULT 0,
            last_login DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_role (role)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // FIR Records Table
        "CREATE TABLE IF NOT EXISTS fir_records (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            category VARCHAR(100) NOT NULL,
            priority ENUM('Low','Medium','High') NOT NULL,
            description TEXT NOT NULL,
            incident_date DATE NOT NULL,
            incident_time TIME NOT NULL,
            city VARCHAR(100) NOT NULL,
            full_address TEXT NOT NULL,
            current_address TEXT NOT NULL,
            suspect_description TEXT NULL,
            witness_description TEXT NULL,
            image1 VARCHAR(255) NULL,
            image2 VARCHAR(255) NULL,
            image3 VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_category (category),
            INDEX idx_city (city),
            CONSTRAINT fk_fir_user 
            FOREIGN KEY (user_id) REFERENCES users(user_id)
            ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // FIR Public Records Table
        "CREATE TABLE IF NOT EXISTS fir_public_records (
            id INT AUTO_INCREMENT PRIMARY KEY,
            fir_id INT NOT NULL,
            city VARCHAR(100) NOT NULL,
            category VARCHAR(100) NOT NULL,
            incident_datetime DATETIME NOT NULL,
            status ENUM('Pending','Approved','Solved') DEFAULT 'Pending',
            description TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_fir_id (fir_id),
            INDEX idx_category (category),
            INDEX idx_city (city),
            INDEX idx_status (status),
            CONSTRAINT fk_public_fir 
            FOREIGN KEY (fir_id) REFERENCES fir_records(id)
            ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // User Feedback Table
        "CREATE TABLE IF NOT EXISTS user_feedback (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            message TEXT NOT NULL,
            type ENUM('positive','neutral','suggestion') NOT NULL,
            rating INT NULL,
            is_approved BOOLEAN DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_type (type),
            INDEX idx_approved (is_approved),
            CONSTRAINT fk_feedback_user 
            FOREIGN KEY (user_id) REFERENCES users(user_id)
            ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // User Queries Table
        "CREATE TABLE IF NOT EXISTS user_queries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            status ENUM('unread','read','replied') DEFAULT 'unread',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_status (status),
            CONSTRAINT fk_query_user 
            FOREIGN KEY (user_id) REFERENCES users(user_id)
            ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // Safety Tips Table
        "CREATE TABLE IF NOT EXISTS safety_tips (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            category VARCHAR(100) NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_category (category)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];
    
    foreach ($tables as $sql) {
        if (!$conn->query($sql)) {
            throw new Exception("Error creating table: " . $conn->error);
        }
    }
}

/**
 * Insert sample data into all tables
 */
function insertSampleData($conn) {
    // Sample Users (password_hash = password123 hashed)
    $users = [
        ['John Doe', '1990-05-15', '123456789012', 'ABCDE1234F', '123 Main St, City', '9876543210', 'john@example.com', password_hash('password123', PASSWORD_DEFAULT), hash('sha256', 'recovery-key-1'), 'user', 'active'],
        ['Jane Smith', '1985-08-20', '234567890123', 'FGHIJ5678K', '456 Oak Ave, Town', '9876543211', 'jane@example.com', password_hash('password123', PASSWORD_DEFAULT), hash('sha256', 'recovery-key-2'), 'user', 'active'],
        ['Admin User', '1980-03-10', '345678901234', 'LMNOP9012Q', '789 Pine Rd, Village', '9876543212', 'admin@example.com', password_hash('admin123', PASSWORD_DEFAULT), hash('sha256', 'admin-recovery'), 'admin', 'active'],
        ['Robert Johnson', '1995-12-01', '456789012345', 'RSTUV3456W', '321 Elm St, City', '9876543213', 'robert@example.com', password_hash('password123', PASSWORD_DEFAULT), hash('sha256', 'recovery-key-4'), 'user', 'active'],
        ['Maria Garcia', '1988-07-25', '567890123456', 'XYZAB7890C', '654 Birch Ln, Town', '9876543214', 'maria@example.com', password_hash('password123', PASSWORD_DEFAULT), hash('sha256', 'recovery-key-5'), 'staff', 'active']
    ];
    
    $userIds = [];
    $stmt = $conn->prepare("INSERT INTO users (full_name, dob, aadhaar_number, pan_number, address, phone_number, email, password_hash, recovery_key_hash, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($users as $user) {
        $stmt->bind_param("sssssssssss", $user[0], $user[1], $user[2], $user[3], $user[4], $user[5], $user[6], $user[7], $user[8], $user[9], $user[10]);
        $stmt->execute();
        $userIds[] = $conn->insert_id;
    }
    $stmt->close();
    
    // Sample Staff
    $staffData = [
        ['Officer Smith', '1985-03-15', '9988776655', 'officer@police.gov', password_hash('officer123', PASSWORD_DEFAULT), hash('sha256', 'officer-key'), 'officer', 3, 'active'],
        ['Moderator Jones', '1990-06-20', '9988776654', 'moderator@police.gov', password_hash('moderator123', PASSWORD_DEFAULT), hash('sha256', 'moderator-key'), 'moderator', 2, 'active']
    ];
    
    $stmt = $conn->prepare("INSERT INTO staff (full_name, dob, phone_number, email, password_hash, recovery_key_hash, role, access_level, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($staffData as $staff) {
        $stmt->bind_param("sssssssis", $staff[0], $staff[1], $staff[2], $staff[3], $staff[4], $staff[5], $staff[6], $staff[7], $staff[8]);
        $stmt->execute();
    }
    $stmt->close();
    
    // Sample FIR Records
    $firs = [
        [$userIds[0], 'Mobile Phone Theft', 'Theft', 'High', 'My phone was stolen from the bus stop', '2024-01-15', '18:30:00', 'Mumbai', 'Andheri Bus Stop, Mumbai', '123 Main St, City', 'Tall man in black hoodie', 'One witness saw the incident', NULL, NULL, NULL],
        [$userIds[1], 'Online Banking Fraud', 'Cyber Crime', 'High', 'Received fake SMS and lost money', '2024-02-10', '14:20:00', 'Delhi', 'Connaught Place, Delhi', '456 Oak Ave, Town', 'Unknown caller', NULL, NULL, NULL, NULL],
        [$userIds[0], 'House Break-in', 'Theft', 'Medium', 'Someone broke into my house', '2024-03-05', '22:00:00', 'Bangalore', 'Indiranagar, Bangalore', '123 Main St, City', 'Two masked persons', 'Neighbor saw suspicious activity', NULL, NULL, NULL],
        [$userIds[2], 'Cyber Harassment', 'Harassment', 'High', 'Receiving threatening messages online', '2024-03-20', '09:00:00', 'Chennai', 'T Nagar, Chennai', '789 Pine Rd, Village', 'Unknown social media account', NULL, NULL, NULL, NULL],
        [$userIds[3], 'Road Accident', 'Accident', 'Medium', 'Hit and run case near mall', '2024-04-01', '20:15:00', 'Kolkata', 'Park Street, Kolkata', '321 Elm St, City', 'White sedan car', 'Multiple witnesses', NULL, NULL, NULL]
    ];
    
    $firIds = [];
    $stmt = $conn->prepare("INSERT INTO fir_records (user_id, title, category, priority, description, incident_date, incident_time, city, full_address, current_address, suspect_description, witness_description, image1, image2, image3) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($firs as $fir) {
        $stmt->bind_param("issssssssssssss", $fir[0], $fir[1], $fir[2], $fir[3], $fir[4], $fir[5], $fir[6], $fir[7], $fir[8], $fir[9], $fir[10], $fir[11], $fir[12], $fir[13], $fir[14]);
        $stmt->execute();
        $firIds[] = $conn->insert_id;
    }
    $stmt->close();
    
    // Sample FIR Public Records
    $publicRecords = [
        [$firIds[0], 'Mumbai', 'Theft', '2024-01-15 18:30:00', 'Pending', 'Mobile phone theft reported at bus stop'],
        [$firIds[1], 'Delhi', 'Cyber Crime', '2024-02-10 14:20:00', 'Approved', 'Online banking fraud case under investigation'],
        [$firIds[2], 'Bangalore', 'Theft', '2024-03-05 22:00:00', 'Solved', 'House break-in - Suspects arrested'],
        [$firIds[3], 'Chennai', 'Harassment', '2024-03-20 09:00:00', 'Pending', 'Cyber harassment case being tracked'],
        [$firIds[4], 'Kolkata', 'Accident', '2024-04-01 20:15:00', 'Approved', 'Hit and run case - Seeking witnesses']
    ];
    
    $stmt = $conn->prepare("INSERT INTO fir_public_records (fir_id, city, category, incident_datetime, status, description) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($publicRecords as $record) {
        $stmt->bind_param("isssss", $record[0], $record[1], $record[2], $record[3], $record[4], $record[5]);
        $stmt->execute();
    }
    $stmt->close();
    
    // Sample User Feedback
    $feedbacks = [
        [$userIds[0], 'Great system, very helpful!', 'positive', 5, 1],
        [$userIds[1], 'Good platform but needs improvement in response time', 'neutral', 3, 1],
        [$userIds[2], 'Excellent service! My complaint was resolved quickly', 'positive', 5, 1],
        [$userIds[3], 'Suggestion: Add more categories for FIR types', 'suggestion', NULL, 0]
    ];
    
    $stmt = $conn->prepare("INSERT INTO user_feedback (user_id, message, type, rating, is_approved) VALUES (?, ?, ?, ?, ?)");
    foreach ($feedbacks as $feedback) {
        $stmt->bind_param("issii", $feedback[0], $feedback[1], $feedback[2], $feedback[3], $feedback[4]);
        $stmt->execute();
    }
    $stmt->close();
    
    // Sample User Queries
    $queries = [
        [NULL, 'Guest User', 'guest@example.com', 'How to file FIR?', 'Can you guide me through the FIR filing process?', 'unread'],
        [$userIds[0], 'John Doe', 'john@example.com', 'Update on FIR #1', 'Any updates on my stolen phone case?', 'replied'],
        [$userIds[1], 'Jane Smith', 'jane@example.com', 'Document upload issue', 'Unable to upload PDF files for evidence', 'read'],
        [NULL, 'Test User', 'test@example.com', 'Website feedback', 'The website is user-friendly and easy to navigate', 'read']
    ];
    
    $stmt = $conn->prepare("INSERT INTO user_queries (user_id, name, email, subject, message, status) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($queries as $query) {
        $stmt->bind_param("isssss", $query[0], $query[1], $query[2], $query[3], $query[4], $query[5]);
        $stmt->execute();
    }
    $stmt->close();
    
    // Sample Safety Tips
    $safetyTips = [
        ['Cyber Safety Tips', 'Cyber Crime', '1. Use strong passwords (8+ characters with mix of letters, numbers, symbols)\n2. Enable two-factor authentication\n3. Never share OTP or bank details\n4. Keep software and antivirus updated\n5. Avoid clicking on suspicious links'],
        ['Personal Safety Guidelines', 'Personal Safety', '1. Be aware of your surroundings\n2. Share your location with trusted contacts\n3. Keep emergency numbers handy\n4. Avoid isolated areas at night\n5. Trust your instincts'],
        ['Road Safety Rules', 'Road Safety', '1. Always wear seatbelt and helmet\n2. Follow traffic signals\n3. Never drink and drive\n4. Keep vehicle documents ready\n5. Maintain safe distance'],
        ['Women Safety Tips', 'Women Safety', '1. Use safety apps on phone\n2. Learn basic self-defense\n3. Avoid walking alone late at night\n4. Keep pepper spray if possible\n5. Share taxi details with family'],
        ['Online Fraud Prevention', 'Cyber Crime', '1. Verify websites before entering details\n2. Don\'t respond to unknown lottery/prize messages\n3. Check bank statements regularly\n4. Use secure payment gateways\n5. Report suspicious activities immediately']
    ];
    
    $stmt = $conn->prepare("INSERT INTO safety_tips (title, category, content) VALUES (?, ?, ?)");
    foreach ($safetyTips as $tip) {
        $stmt->bind_param("sss", $tip[0], $tip[1], $tip[2]);
        $stmt->execute();
    }
    $stmt->close();
}

// Display setup interface if not processing
if (!isset($_POST['action'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Setup - Online Crime Reporting System</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
            .setup-card {
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                overflow: hidden;
            }
            .warning-box {
                background: #fff3cd;
                border-left: 4px solid #ffc107;
                padding: 15px;
                border-radius: 8px;
                margin: 20px 0;
            }
            .btn-setup {
                padding: 12px 30px;
                font-size: 16px;
                font-weight: bold;
                border-radius: 50px;
                transition: all 0.3s;
            }
            .btn-setup:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            }
            .db-details {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 10px;
                font-family: monospace;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card setup-card">
                        <div class="card-header bg-danger text-white text-center py-4">
                            <i class="fas fa-exclamation-triangle fa-3x mb-2"></i>
                            <h2 class="mb-0">Database Setup Wizard</h2>
                            <p class="mb-0 mt-2">Online Crime Reporting System</p>
                        </div>
                        <div class="card-body p-5">
                            <div class="warning-box">
                                <i class="fas fa-info-circle"></i>
                                <strong>⚠️ IMPORTANT WARNING:</strong>
                                <p class="mb-0 mt-2">This setup will create or reset the database. Running this again may <strong class="text-danger">DELETE all existing data</strong>. Please proceed with caution.</p>
                            </div>
                            
                            <h5><i class="fas fa-database"></i> Database Configuration</h5>
                            <div class="db-details mb-4">
                                <div><strong>Host:</strong> localhost</div>
                                <div><strong>Username:</strong> root</div>
                                <div><strong>Password:</strong> (empty)</div>
                                <div><strong>Database:</strong> online_crime_reporting</div>
                            </div>
                            
                            <h5><i class="fas fa-table"></i> Tables to be Created:</h5>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check-circle text-success"></i> Users Table</li>
                                        <li><i class="fas fa-check-circle text-success"></i> Staff Table</li>
                                        <li><i class="fas fa-check-circle text-success"></i> FIR Records Table</li>
                                        <li><i class="fas fa-check-circle text-success"></i> FIR Public Records</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check-circle text-success"></i> User Feedback Table</li>
                                        <li><i class="fas fa-check-circle text-success"></i> User Queries Table</li>
                                        <li><i class="fas fa-check-circle text-success"></i> Safety Tips Table</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <form method="POST" action="" onsubmit="return confirmSetup(this);">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <button type="submit" name="action" value="first_time" class="btn btn-success btn-setup w-100">
                                            <i class="fas fa-play"></i> Run Setup (First Time)
                                        </button>
                                        <small class="text-muted d-block text-center mt-2">Create database & tables</small>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="submit" name="action" value="reset" class="btn btn-danger btn-setup w-100">
                                            <i class="fas fa-trash-alt"></i> Reset & Reinstall
                                        </button>
                                        <small class="text-muted d-block text-center mt-2">Delete all data & fresh install</small>
                                    </div>
                                </div>
                            </form>
                            
                            <hr class="my-4">
                            
                            <div class="text-center">
                                <a href="index.php" class="text-decoration-none">
                                    <i class="fas fa-arrow-left"></i> Back to Login Page
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
            function confirmSetup(form) {
                let action = form.querySelector('button[type="submit"]:focus').value;
                let message = '';
                
                if (action === 'first_time') {
                    message = 'Are you sure you want to run the FIRST TIME setup?\n\nThis will create the database and tables.';
                } else {
                    message = '⚠️ DANGER: This will DELETE ALL EXISTING DATA!\n\nAre you absolutely sure you want to RESET and REINSTALL the database?';
                }
                
                return confirm(message);
            }
        </script>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
}
?>