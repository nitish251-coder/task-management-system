<?php
// test_connection.php - Database Connection Test
// Delete this file after successful setup for security

// Display all errors for testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Task Management System - Database Connection Test</h2>";
echo "<p><strong>Server Information:</strong></p>";
echo "<ul>";
echo "<li>PHP Version: " . phpversion() . "</li>";
echo "<li>Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "</li>";
echo "<li>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</li>";
echo "<li>Current Directory: " . __DIR__ . "</li>";
echo "</ul>";

echo "<p><strong>Required Extensions:</strong></p>";
echo "<ul>";
echo "<li>PDO: " . (extension_loaded('pdo') ? '✅ Available' : '❌ Not Available') . "</li>";
echo "<li>PDO MySQL: " . (extension_loaded('pdo_mysql') ? '✅ Available' : '❌ Not Available') . "</li>";
echo "<li>MySQLi: " . (extension_loaded('mysqli') ? '✅ Available' : '❌ Not Available') . "</li>";
echo "<li>GD: " . (extension_loaded('gd') ? '✅ Available' : '❌ Not Available') . "</li>";
echo "</ul>";

// Test database configuration
echo "<p><strong>Database Configuration Test:</strong></p>";

// Check if config file exists
if (!file_exists('config.php')) {
    echo "<p style='color: red;'>❌ config.php file not found!</p>";
    exit;
}

// Include config but catch any errors
try {
    // Temporarily disable the die() in config.php for testing
    ob_start();
    include 'config.php';
    $config_output = ob_get_clean();
    
    if (!empty($config_output)) {
        echo "<p style='color: red;'>❌ Config file has output/errors:</p>";
        echo "<pre>" . htmlspecialchars($config_output) . "</pre>";
    }
    
    echo "<ul>";
    echo "<li>Database Host: " . DB_HOST . "</li>";
    echo "<li>Database Name: " . DB_NAME . "</li>";
    echo "<li>Database User: " . DB_USER . "</li>";
    echo "<li>Database Password: " . (DB_PASS ? str_repeat('*', strlen(DB_PASS)) : 'Not Set') . "</li>";
    echo "</ul>";
    
    // Test connection manually
    echo "<p><strong>Connection Test:</strong></p>";
    
    if (!extension_loaded('pdo_mysql')) {
        echo "<p style='color: red;'>❌ PDO MySQL extension not loaded</p>";
        echo "<p><strong>Solution:</strong> Contact your hosting provider to enable PDO MySQL extension.</p>";
        exit;
    }
    
    $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    
    // Test connection to server (without database)
    try {
        $test_pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        echo "<p style='color: green;'>✅ Successfully connected to MySQL server</p>";
        
        // Test database existence
        $dsn_with_db = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        try {
            $test_pdo_db = new PDO($dsn_with_db, DB_USER, DB_PASS, $options);
            echo "<p style='color: green;'>✅ Successfully connected to database: " . DB_NAME . "</p>";
            
            // Test if tables exist
            $stmt = $test_pdo_db->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($tables)) {
                echo "<p style='color: orange;'>⚠️ Database is empty. You need to import sql/schema.sql</p>";
                echo "<p><strong>Steps:</strong></p>";
                echo "<ol>";
                echo "<li>Go to cPanel → phpMyAdmin</li>";
                echo "<li>Select your database: " . DB_NAME . "</li>";
                echo "<li>Click 'Import' tab</li>";
                echo "<li>Upload sql/schema.sql file</li>";
                echo "<li>Click 'Go' to import</li>";
                echo "</ol>";
            } else {
                echo "<p style='color: green;'>✅ Database has " . count($tables) . " tables</p>";
                echo "<p><strong>Tables found:</strong> " . implode(', ', $tables) . "</p>";
                
                // Test admin user
                if (in_array('users', $tables)) {
                    $stmt = $test_pdo_db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
                    $admin_count = $stmt->fetchColumn();
                    
                    if ($admin_count > 0) {
                        echo "<p style='color: green;'>✅ Admin user found in database</p>";
                        echo "<p><strong>You can now access the system!</strong></p>";
                        echo "<p><a href='index.php' style='background: #3182ce; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Main Site</a></p>";
                    } else {
                        echo "<p style='color: orange;'>⚠️ No admin user found. Database may not be properly imported.</p>";
                    }
                }
            }
            
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ Cannot connect to database: " . DB_NAME . "</p>";
            echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
            echo "<p><strong>Possible solutions:</strong></p>";
            echo "<ul>";
            echo "<li>Check if database name is correct (should be: your_username_databasename)</li>";
            echo "<li>Verify database exists in cPanel → MySQL Databases</li>";
            echo "<li>Ensure user has privileges on this database</li>";
            echo "</ul>";
        }
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Cannot connect to MySQL server</p>";
        echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
        echo "<p><strong>Possible solutions:</strong></p>";
        echo "<ul>";
        echo "<li>Check database host (usually 'localhost' for cPanel)</li>";
        echo "<li>Verify database username format (should be: your_username_dbuser)</li>";
        echo "<li>Check database password</li>";
        echo "<li>Ensure database user exists in cPanel → MySQL Databases</li>";
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error loading config.php: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>File Permissions Test:</strong></p>";

$upload_dir = 'uploads/';
if (!is_dir($upload_dir)) {
    if (mkdir($upload_dir, 0755, true)) {
        echo "<p style='color: green;'>✅ Created uploads directory</p>";
    } else {
        echo "<p style='color: red;'>❌ Cannot create uploads directory</p>";
    }
} else {
    echo "<p style='color: green;'>✅ Uploads directory exists</p>";
}

if (is_writable($upload_dir)) {
    echo "<p style='color: green;'>✅ Uploads directory is writable</p>";
} else {
    echo "<p style='color: red;'>❌ Uploads directory is not writable</p>";
    echo "<p><strong>Solution:</strong> Set permissions to 755 for uploads directory</p>";
}

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>If all tests pass, <a href='index.php'>visit your main site</a></li>";
echo "<li>Login with admin credentials: admin@taskmanagement.com / admin123</li>";
echo "<li><strong>Delete this test file (test_connection.php) for security</strong></li>";
echo "</ol>";

echo "<hr>";
echo "<p style='color: #666; font-size: 12px;'>Generated: " . date('Y-m-d H:i:s') . "</p>";
?>
