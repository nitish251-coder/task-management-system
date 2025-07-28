-- Task Management System Database Schema
-- Create database first: CREATE DATABASE task_management_db;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    mobile VARCHAR(20),
    referral_code VARCHAR(20) UNIQUE,
    referred_by VARCHAR(20),
    bank_details TEXT,
    upi_id VARCHAR(50),
    interests VARCHAR(255),
    profile_photo VARCHAR(255),
    package_id INT,
    wallet_balance DECIMAL(10,2) DEFAULT 0.00,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_referral_code (referral_code),
    INDEX idx_referred_by (referred_by)
);

-- Packages table
CREATE TABLE packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name ENUM('gold', 'silver', 'diamond') NOT NULL,
    task_limit INT NOT NULL,
    validity_days INT DEFAULT 365,
    price DECIMAL(10,2) DEFAULT 0.00,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tasks table
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    media_link VARCHAR(500),
    media_file VARCHAR(255),
    points DECIMAL(10,2) NOT NULL,
    timer_accept INT NOT NULL, -- seconds to accept task
    timer_submit INT NOT NULL, -- seconds to complete task
    max_users INT DEFAULT 1,
    assigned_users INT DEFAULT 0,
    validity_days INT DEFAULT 365,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_admin_id (admin_id),
    INDEX idx_is_active (is_active)
);

-- Task assignments table
CREATE TABLE task_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'rejected', 'completed', 'approved', 'reassigned') DEFAULT 'pending',
    submission_file VARCHAR(255),
    submission_text TEXT,
    submission_time TIMESTAMP NULL,
    review_notes TEXT,
    approved BOOLEAN DEFAULT FALSE,
    accept_deadline TIMESTAMP NULL,
    submit_deadline TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_task_user (task_id, user_id),
    INDEX idx_task_id (task_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
);

-- Earnings table
CREATE TABLE earnings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    task_id INT NULL,
    amount DECIMAL(10,2) NOT NULL,
    type ENUM('task', 'referral') DEFAULT 'task',
    referral_level INT NULL,
    description VARCHAR(255),
    status ENUM('pending', 'credited', 'withdrawn') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_type (type),
    INDEX idx_status (status)
);

-- Referrals table
CREATE TABLE referrals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    referred_user_id INT NOT NULL,
    level INT NOT NULL,
    commission_percent DECIMAL(5,2) NOT NULL,
    total_earned DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (referred_user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_referral (user_id, referred_user_id),
    INDEX idx_user_id (user_id),
    INDEX idx_referred_user_id (referred_user_id),
    INDEX idx_level (level)
);

-- Withdrawal requests table
CREATE TABLE withdrawal_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    bank_details TEXT,
    upi_id VARCHAR(50),
    status ENUM('pending', 'approved', 'rejected', 'processed') DEFAULT 'pending',
    admin_notes TEXT,
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
);

-- System settings table
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default packages
INSERT INTO packages (name, task_limit, validity_days, description) VALUES
('gold', 1, 365, 'Gold package - 1 task per day'),
('silver', 2, 365, 'Silver package - 2 tasks per day'),
('diamond', 3, 365, 'Diamond package - 3 tasks per day');

-- Insert default admin user (password: admin123)
INSERT INTO users (role, name, email, password, referral_code) VALUES
('admin', 'System Admin', 'admin@taskmanagement.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADMIN001');

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('referral_level_1', '10', 'Level 1 referral commission percentage'),
('referral_level_2', '5', 'Level 2 referral commission percentage'),
('referral_level_3', '4', 'Level 3 referral commission percentage'),
('referral_level_4', '3', 'Level 4 referral commission percentage'),
('referral_level_5', '2', 'Level 5 referral commission percentage'),
('min_withdrawal', '100', 'Minimum withdrawal amount'),
('site_name', 'Task Management System', 'Website name'),
('site_email', 'noreply@taskmanagement.com', 'System email address');
