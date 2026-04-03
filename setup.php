<?php
// setup.php - Database Setup Script for Online Crime Reporting System
// Run this file first when setting up the project on a new system

session_start();

// Database configuration for XAMPP
$servername = "localhost";
$username = "root";
$password = ""; // Default XAMPP password is empty

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create connection without database selected first
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("<div style='color:red; padding:20px; font-family:Arial;'>Connection failed: " . $conn->connect_error . "</div>");
}

// Create database if not exists
$dbname = "crime_reporting_system";
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "<div style='color:green; padding:10px; font-family:Arial;'>✓ Database '$dbname' created successfully or already exists.</div>";
} else {
    die("<div style='color:red; padding:20px; font-family:Arial;'>Error creating database: " . $conn->error . "</div>");
}

// Select the database
$conn->select_db($dbname);

// Create users table
$users_table = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    role ENUM('user', 'admin') DEFAULT 'user',
    email_verified BOOLEAN DEFAULT FALSE,
    verification_token VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($users_table) === TRUE) {
    echo "<div style='color:green; padding:10px; font-family:Arial;'>✓ Users table created successfully.</div>";
} else {
    echo "<div style='color:red; padding:10px; font-family:Arial;'>Error creating users table: " . $conn->error . "</div>";
}

// Create fir_reports table
$fir_reports_table = "CREATE TABLE IF NOT EXISTS fir_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fir_number VARCHAR(20) UNIQUE,
    user_id INT NOT NULL,
    incident_type VARCHAR(100) NOT NULL,
    incident_date DATE NOT NULL,
    incident_time TIME NOT NULL,
    location VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    accused_name VARCHAR(255),
    accused_description TEXT,
    witness_names TEXT,
    documents_path VARCHAR(255),
    status ENUM('pending', 'under_investigation', 'resolved', 'rejected') DEFAULT 'pending',
    officer_assigned VARCHAR(100),
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($fir_reports_table) === TRUE) {
    echo "<div style='color:green; padding:10px; font-family:Arial;'>✓ FIR Reports table created successfully.</div>";
} else {
    echo "<div style='color:red; padding:10px; font-family:Arial;'>Error creating fir_reports table: " . $conn->error . "</div>";
}

// Create safety_tips table
$safety_tips_table = "CREATE TABLE IF NOT EXISTS safety_tips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    description TEXT NOT NULL,
    tips_content TEXT NOT NULL,
    priority INT DEFAULT 1,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
)";

if ($conn->query($safety_tips_table) === TRUE) {
    echo "<div style='color:green; padding:10px; font-family:Arial;'>✓ Safety Tips table created successfully.</div>";
} else {
    echo "<div style='color:red; padding:10px; font-family:Arial;'>Error creating safety_tips table: " . $conn->error . "</div>";
}

// Create crime_records table
$crime_records_table = "CREATE TABLE IF NOT EXISTS crime_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    crime_type VARCHAR(100) NOT NULL,
    cases_registered INT DEFAULT 0,
    cases_solved INT DEFAULT 0,
    year INT NOT NULL,
    month VARCHAR(20),
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
)";

if ($conn->query($crime_records_table) === TRUE) {
    echo "<div style='color:green; padding:10px; font-family:Arial;'>✓ Crime Records table created successfully.</div>";
} else {
    echo "<div style='color:red; padding:10px; font-family:Arial;'>Error creating crime_records table: " . $conn->error . "</div>";
}

// Create contact_messages table for user inquiries
$contact_messages_table = "CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)";

if ($conn->query($contact_messages_table) === TRUE) {
    echo "<div style='color:green; padding:10px; font-family:Arial;'>✓ Contact Messages table created successfully.</div>";
} else {
    echo "<div style='color:red; padding:10px; font-family:Arial;'>Error creating contact_messages table: " . $conn->error . "</div>";
}

// Create admin_logs table for tracking admin activities
$admin_logs_table = "CREATE TABLE IF NOT EXISTS admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($admin_logs_table) === TRUE) {
    echo "<div style='color:green; padding:10px; font-family:Arial;'>✓ Admin Logs table created successfully.</div>";
} else {
    echo "<div style='color:red; padding:10px; font-family:Arial;'>Error creating admin_logs table: " . $conn->error . "</div>";
}

// Insert default admin user if not exists
$admin_email = "admin@crimesystem.com";
$admin_password = password_hash("Admin@123", PASSWORD_DEFAULT); // Default password: Admin@123
$admin_name = "System Administrator";

// Check if admin already exists
$check_admin = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check_admin->bind_param("s", $admin_email);
$check_admin->execute();
$result = $check_admin->get_result();

if ($result->num_rows == 0) {
    $insert_admin = $conn->prepare("INSERT INTO users (name, email, password, role, email_verified) VALUES (?, ?, ?, 'admin', TRUE)");
    $insert_admin->bind_param("sss", $admin_name, $admin_email, $admin_password);
    
    if ($insert_admin->execute()) {
        echo "<div style='color:green; padding:10px; font-family:Arial;'>✓ Default admin user created successfully.</div>";
        echo "<div style='padding:10px; font-family:Arial; background:#f0f0f0; margin-top:10px;'>";
        echo "<strong>Admin Login Credentials:</strong><br>";
        echo "Email: admin@crimesystem.com<br>";
        echo "Password: Admin@123<br>";
        echo "<span style='color:orange;'>⚠️ Please change this password after first login!</span>";
        echo "</div>";
    } else {
        echo "<div style='color:red; padding:10px; font-family:Arial;'>Error creating admin user: " . $insert_admin->error . "</div>";
    }
    $insert_admin->close();
} else {
    echo "<div style='color:blue; padding:10px; font-family:Arial;'>✓ Admin user already exists.</div>";
}
$check_admin->close();

// Insert some sample safety tips
$sample_tips = [
    [
        'title' => 'Personal Safety Tips for Night Travel',
        'category' => 'Personal Safety',
        'description' => 'Essential safety measures when traveling at night',
        'tips_content' => '1. Always inform someone about your whereabouts\n2. Keep emergency contacts handy\n3. Stay in well-lit areas\n4. Keep your phone charged\n5. Trust your instincts'
    ],
    [
        'title' => 'Cyber Crime Prevention',
        'category' => 'Cyber Safety',
        'description' => 'Protect yourself from online fraud and cyber crimes',
        'tips_content' => '1. Use strong passwords\n2. Enable two-factor authentication\n3. Don\'t share OTP with anyone\n4. Be cautious of phishing emails\n5. Keep software updated'
    ],
    [
        'title' => 'Women Safety Guidelines',
        'category' => 'Women Safety',
        'description' => 'Important safety tips for women',
        'tips_content' => '1. Carry pepper spray or safety alarm\n2. Learn self-defense techniques\n3. Share live location with family\n4. Use women safety apps\n5. Avoid isolated areas'
    ]
];

$check_tips = $conn->query("SELECT COUNT(*) as count FROM safety_tips");
$tips_count = $check_tips->fetch_assoc()['count'];

if ($tips_count == 0) {
    $stmt = $conn->prepare("INSERT INTO safety_tips (title, category, description, tips_content, priority) VALUES (?, ?, ?, ?, ?)");
    $priority = 1;
    
    foreach ($sample_tips as $tip) {
        $stmt->bind_param("ssssi", $tip['title'], $tip['category'], $tip['description'], $tip['tips_content'], $priority);
        $stmt->execute();
    }
    $stmt->close();
    echo "<div style='color:green; padding:10px; font-family:Arial;'>✓ Sample safety tips added successfully.</div>";
} else {
    echo "<div style='color:blue; padding:10px; font-family:Arial;'>✓ Safety tips already exist.</div>";
}

// Insert sample crime records
$sample_crime_records = [
    ['Mumbai', 'Maharashtra', 'Theft', 145, 98, 2024, 'January', 'Decreased by 15% compared to last year'],
    ['Delhi', 'Delhi', 'Cyber Crime', 89, 45, 2024, 'January', 'Increase in online fraud cases'],
    ['Bangalore', 'Karnataka', 'Robbery', 67, 52, 2024, 'January', 'Solved rate improved'],
    ['Chennai', 'Tamil Nadu', 'Assault', 112, 78, 2024, 'January', 'Patrolling increased'],
    ['Kolkata', 'West Bengal', 'Fraud', 56, 34, 2024, 'January', 'Financial fraud cases under investigation']
];

$check_crime = $conn->query("SELECT COUNT(*) as count FROM crime_records");
$crime_count = $check_crime->fetch_assoc()['count'];

if ($crime_count == 0) {
    $stmt = $conn->prepare("INSERT INTO crime_records (city, state, crime_type, cases_registered, cases_solved, year, month, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($sample_crime_records as $record) {
        $stmt->bind_param("sssiiiss", $record[0], $record[1], $record[2], $record[3], $record[4], $record[5], $record[6], $record[7]);
        $stmt->execute();
    }
    $stmt->close();
    echo "<div style='color:green; padding:10px; font-family:Arial;'>✓ Sample crime records added successfully.</div>";
} else {
    echo "<div style='color:blue; padding:10px; font-family:Arial;'>✓ Crime records already exist.</div>";
}

// Create a configuration file
$config_content = "<?php
// Database configuration for Online Crime Reporting System
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'crime_reporting_system');

// Create connection
\$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if (\$conn->connect_error) {
    die('Connection failed: ' . \$conn->connect_error);
}

// Set charset to UTF-8
\$conn->set_charset('utf8');

// Site configuration
define('SITE_NAME', 'Online Crime Reporting System');
define('SITE_URL', 'http://localhost/crime_reporting_system/');
?>";

$config_file = fopen("config.php", "w") or die("<div style='color:red; padding:20px; font-family:Arial;'>Unable to create config.php file!</div>");
fwrite($config_file, $config_content);
fclose($config_file);
echo "<div style='color:green; padding:10px; font-family:Arial;'>✓ Configuration file (config.php) created successfully.</div>";

// Create .htaccess file for security (optional)
$htaccess_content = "# Security settings for Online Crime Reporting System
Options -Indexes
RewriteEngine On

# Prevent access to sensitive directories
RedirectMatch 403 ^/config\.php$
RedirectMatch 403 ^/setup\.php$

# Protect against SQL injection and XSS
<IfModule mod_headers.c>
    Header set X-XSS-Protection \"1; mode=block\"
    Header set X-Content-Type-Options \"nosniff\"
    Header set X-Frame-Options \"SAMEORIGIN\"
</IfModule>

# PHP settings
<IfModule mod_php7.c>
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
    php_value max_execution_time 300
</IfModule>";

$htaccess_file = fopen(".htaccess", "w") or die("<div style='color:red; padding:20px; font-family:Arial;'>Unable to create .htaccess file!</div>");
fwrite($htaccess_file, $htaccess_content);
fclose($htaccess_file);
echo "<div style='color:green; padding:10px; font-family:Arial;'>✓ Security file (.htaccess) created successfully.</div>";

// Close connection
$conn->close();

// Display completion message
echo "<div style='background:#d4edda; border:1px solid #c3e6cb; border-radius:5px; padding:20px; margin-top:20px; font-family:Arial;'>";
echo "<h2 style='color:#155724;'>✓ Setup Completed Successfully! ✓</h2>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Delete the <strong>setup.php</strong> file for security reasons</li>";
echo "<li>Navigate to <strong>login.php</strong> to access the system</li>";
echo "<li>Use the admin credentials provided above to login</li>";
echo "<li>Change the default admin password after first login</li>";
echo "<li>Start using the Online Crime Reporting System</li>";
echo "</ol>";
echo "<p><strong>Project URL:</strong> <a href='login.php' style='color:#007bff;'>http://localhost/crime-reporting-system/login.php</a></p>";
echo "<div style='background:#fff3cd; border-left:4px solid #ffeeba; padding:10px; margin-top:15px;'>";
echo "<strong>⚠️ Important Security Notes:</strong><br>";
echo "- Delete setup.php after successful setup<br>";
echo "- Change the default admin password immediately<br>";
echo "- Configure email settings for email verification feature<br>";
echo "- Update database credentials in config.php if needed<br>";
echo "</div>";
echo "</div>";

// Add a button to proceed to login
echo "<div style='text-align:center; margin-top:20px; font-family:Arial;'>";
echo "<a href='login.php' style='background:#28a745; color:white; padding:12px 30px; text-decoration:none; border-radius:5px; display:inline-block;'>Proceed to Login →</a>";
echo "</div>";
?>