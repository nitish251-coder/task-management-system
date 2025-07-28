<?php
// config.php - Central configuration file
session_start();

// Database configuration - UPDATE THESE WITH YOUR ACTUAL CPANEL DATABASE DETAILS
define('DB_HOST', 'localhost');  // Usually 'localhost' for cPanel
define('DB_NAME', 'your_cpanel_username_taskdb');  // Format: cpanel_username_databasename
define('DB_USER', 'your_cpanel_username_dbuser');  // Format: cpanel_username_username
define('DB_PASS', 'your_database_password');       // Your database password

// Application settings - UPDATE WITH YOUR ACTUAL DOMAIN
define('SITE_URL', 'https://swift.herosite.pro/taskmanager');  // Update with your actual path
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB

// Error reporting (set to 0 in production, 1 for development)
error_reporting(E_ALL);
ini_set('display_errors', 1);  // Change to 0 in production

// Database connection with better error handling
$pdo = null;
try {
    // Check if MySQL extension is available
    if (!extension_loaded('pdo_mysql')) {
        throw new Exception('PDO MySQL extension is not loaded');
    }
    
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
} catch (PDOException $e) {
    // Log the actual error for debugging
    error_log("Database PDO Error: " . $e->getMessage());
    
    // Show user-friendly message
    $error_message = "Database connection failed. Please check your database configuration.";
    
    // In development, show more details
    if (ini_get('display_errors')) {
        $error_message .= "<br><br><strong>Debug Info:</strong><br>";
        $error_message .= "Host: " . DB_HOST . "<br>";
        $error_message .= "Database: " . DB_NAME . "<br>";
        $error_message .= "User: " . DB_USER . "<br>";
        $error_message .= "Error: " . $e->getMessage();
    }
    
    die($error_message);
} catch (Exception $e) {
    error_log("Database Extension Error: " . $e->getMessage());
    die("Database extension not available. Please contact your hosting provider.");
}

// Security settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
// Only enable secure cookies if using HTTPS
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

// Create uploads directory if it doesn't exist
if (!is_dir(UPLOAD_DIR)) {
    if (!mkdir(UPLOAD_DIR, 0755, true)) {
        error_log("Failed to create uploads directory: " . UPLOAD_DIR);
    }
}

// Set timezone (adjust as needed)
date_default_timezone_set('Asia/Kolkata');
?>
