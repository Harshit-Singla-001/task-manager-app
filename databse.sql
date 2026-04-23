-- ==========================================
-- DATABASE: Online Crime Reporting System
-- FINAL VERSION (Fully Synced with setup.php)
-- ==========================================

CREATE DATABASE IF NOT EXISTS online_crime_reporting;
USE online_crime_reporting;

-- ==========================================
-- 1. USERS TABLE
-- ==========================================
CREATE TABLE IF NOT EXISTS users (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- 2. STAFF TABLE
-- ==========================================
CREATE TABLE IF NOT EXISTS staff (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- 3. FIR RECORDS (MAIN TABLE)
-- ==========================================
CREATE TABLE IF NOT EXISTS fir_records (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- 4. FIR PUBLIC RECORDS (SAFE TABLE)
-- ==========================================
CREATE TABLE IF NOT EXISTS fir_public_records (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- 5. USER FEEDBACK
-- ==========================================
CREATE TABLE IF NOT EXISTS user_feedback (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- 6. USER QUERIES
-- ==========================================
CREATE TABLE IF NOT EXISTS user_queries (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- 7. SAFETY TIPS
-- ==========================================
CREATE TABLE IF NOT EXISTS safety_tips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- SAMPLE DATA (CLEAN & CONSISTENT)
-- ==========================================


-- USERS (3 entries with hashed password for 'Abcd1234@')
-- Password hash for 'Abcd1234@' using PHP's password_hash()
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

INSERT INTO users (full_name, dob, aadhaar_number, pan_number, address, phone_number, email, password_hash, recovery_key_hash, role) VALUES
('Harshit Singla', '2006-12-09', '111122223333', 'ABCDE1234F', '123 Main Street, Phase 2, Chandigarh, India', '9780567543', 'harshit.singla@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'recovery_key_hash_1', 'user'),
('Priya Sharma', '1995-08-15', '444455556666', 'PQRS5678G', '45 Lake View Apartments, Andheri East, Mumbai, Maharashtra - 400069', '9876543210', 'priya.sharma@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'recovery_key_hash_2', 'user'),
('Amit Verma', '1998-03-22', '777788889999', 'XYZO9012H', '789 Green Park Colony, Sector 62, Noida, Uttar Pradesh - 201301', '9988776655', 'amit.verma@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'recovery_key_hash_3', 'user');

-- STAFF (2 entries - one admin, one staff member)
INSERT INTO staff (full_name, dob, phone_number, email, password_hash, recovery_key_hash, role, access_level) VALUES
('Rajesh Kumar Singh', '1980-05-10', '9999999999', 'rajesh.kumar@police.gov.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff_recovery_key_1', 'admin', 5),
('Neha Gupta', '1990-11-25', '8888887777', 'neha.gupta@police.gov.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff_recovery_key_2', 'officer', 3);

-- FIR RECORDS (2 entries with valid data)
INSERT INTO fir_records (user_id, title, category, priority, description, incident_date, incident_time, city, full_address, current_address, suspect_description, witness_description) VALUES
(1, 'Mobile Phone Theft at Metro Station', 'Theft', 'High', 'My iPhone 14 was stolen from my pocket while boarding the metro at Rajiv Chowk station. The incident happened during peak hours. I immediately realized but the train doors closed.', '2024-01-15', '18:30:00', 'Delhi', 'Rajiv Chowk Metro Station, Gate No. 3, Connaught Place, New Delhi - 110001', 'Sector 62, Noida, Uttar Pradesh - 201301', 'Suspect was a young male, approximately 25-30 years old, wearing a black hoodie and jeans. He bumped into me intentionally.', 'A fellow passenger named Mr. Ramesh (9876543210) saw the incident and tried to catch the thief.'),
(2, 'Online Banking Fraud - UPI Scam', 'Cyber Crime', 'High', 'Received a call claiming to be from my bank asking to verify my account. They sent a Google Meet link and shared screen. Within minutes, Rs. 50,000 was debited from my account.', '2024-02-10', '14:15:00', 'Mumbai', 'Home, Lake View Apartments, Andheri East, Mumbai', 'Lake View Apartments, Andheri East, Mumbai', 'Unknown caller from number +91-9876543210. They used fake bank employee identity.', 'None'),
(3, 'Chain Snatching Incident', 'Robbery', 'High', 'Two men on a bike snatched my gold chain while I was walking back home from the market. They were wearing helmets.', '2024-03-05', '20:45:00', 'Noida', 'Near Pillar No. 45, Sector 18 Market, Noida', 'Green Park Colony, Sector 62, Noida', 'Two unidentified men on a black Pulsar bike. One was wearing a red t-shirt, both had black helmets.', 'Local shopkeeper at corner store saw the incident and noted the bike number partially (UP 32 AB 12xx)');

-- FIR PUBLIC RECORDS (All FIRs must be in public records)
INSERT INTO fir_public_records (fir_id, city, category, incident_datetime, status, description) VALUES
(1, 'Delhi', 'Theft', '2024-01-15 18:30:00', 'Approved', 'Mobile theft case at Rajiv Chowk Metro Station. Under investigation by cyber cell. CCTV footage being reviewed.'),
(2, 'Mumbai', 'Cyber Crime', '2024-02-10 14:15:00', 'Solved', 'UPI fraud case solved. Bank account frozen and amount recovered partially. Accused traced to another state.'),
(3, 'Noida', 'Robbery', '2024-03-05 20:45:00', 'Pending', 'Chain snatching incident. Police are reviewing CCTV footage from nearby cameras. Suspect bike description shared with all checkposts.');

-- USER FEEDBACK (3 entries)
INSERT INTO user_feedback (user_id, message, type, rating, is_approved) VALUES
(1, 'Excellent system! The FIR filing process was smooth and I received acknowledgement immediately. Very helpful platform.', 'positive', 5, 1),
(2, 'My cyber crime complaint was resolved quickly. The team followed up regularly. Thank you for your support.', 'positive', 4, 1),
(3, 'The interface is good but response time could be faster. However, overall experience is satisfactory.', 'neutral', 3, 1);

-- USER QUERIES (3 entries)
INSERT INTO user_queries (user_id, name, email, subject, message, status) VALUES
(1, 'Harshit Singla', 'harshit.singla@example.com', 'How to add evidence documents?', 'I have some screenshots and call recordings related to my FIR. How can I upload additional evidence after filing the FIR?', 'replied'),
(2, 'Priya Sharma', 'priya.sharma@example.com', 'Status update on FIR #2', 'My FIR number is #2. It has been 2 weeks but I haven''t received any update. Can someone please check the status?', 'read'),
(3, 'Amit Verma', 'amit.verma@example.com', 'Need help with OTP verification', 'I am not receiving the OTP on my registered mobile number during signup. Please help me resolve this issue.', 'unread');

-- SAFETY TIPS (6 tips in 3 categories - 2 tips per category)
INSERT INTO safety_tips (title, category, content) VALUES

-- Cyber Crime Category (2 tips)
('Protect Your Online Banking', 'Cyber Crime', 'Never share your OTP, PIN, or password with anyone. Banks never ask for these details over phone or email. Always verify any call claiming to be from your bank by calling official customer care.'),
('Safe Social Media Practices', 'Cyber Crime', 'Don''t accept friend requests from strangers. Never share your location, travel plans, or personal documents on social media. Enable two-factor authentication on all your accounts and use strong, unique passwords.'),

-- Women Safety Category (2 tips)
('Emergency SOS Features', 'Women Safety', 'Most smartphones have an SOS feature. Set up emergency contacts and learn how to trigger it quickly (usually by pressing power button 3 times). Share your live location with trusted family members when traveling alone.'),
('Safe Public Transport Tips', 'Women Safety', 'While using public transport, sit near the driver or conductor. Share your vehicle number with family. Use women-only coaches when available. Keep pepper spray handy and learn basic self-defense techniques.'),

-- General Safety Category (2 tips)
('Home Security Measures', 'General Safety', 'Install good quality locks and CCTV cameras. Don''t share your absence on social media. Inform trusted neighbors when going out of town. Keep emergency numbers on speed dial and install a door peephole.'),
('Road Safety Guidelines', 'General Safety', 'Always wear seatbelt and helmet. Never drink and drive. Follow traffic signals and speed limits. Keep your vehicle documents handy. In case of accident, call 100 and 108 immediately.');


-- ==========================================
-- END OF FILE
-- ==========================================