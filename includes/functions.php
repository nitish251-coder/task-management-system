<?php
// includes/functions.php - Common helper functions

// Sanitize input data
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Check if user is regular user
function isUser() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'user';
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit();
    }
}

// Redirect if not admin
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /index.php');
        exit();
    }
}

// Generate unique referral code
function generateReferralCode() {
    return strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
}

// Validate file upload
function validateFileUpload($file) {
    $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    $maxSize = MAX_FILE_SIZE; // 5MB
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error occurred.'];
    }
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Only PDF, JPG, PNG, DOC, DOCX files are allowed.'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File size too large. Maximum 5MB allowed.'];
    }
    
    return ['success' => true, 'message' => 'File is valid.'];
}

// Secure file upload
function secureFileUpload($file, $user_id, $subfolder = '') {
    $validation = validateFileUpload($file);
    if (!$validation['success']) {
        return $validation;
    }
    
    $uploadDir = UPLOAD_DIR . $user_id . '/';
    if ($subfolder) {
        $uploadDir .= $subfolder . '/';
    }
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
    $uploadPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => true, 'path' => $uploadPath, 'filename' => $fileName];
    } else {
        return ['success' => false, 'message' => 'Failed to upload file.'];
    }
}

// Process referral commission
function processReferralCommission($user_id, $amount) {
    global $pdo;
    
    try {
        // Get referral commission rates from settings
        $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM system_settings WHERE setting_key LIKE 'referral_level_%'");
        $stmt->execute();
        $rates = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $commissionRates = [
            1 => floatval($rates['referral_level_1'] ?? 10),
            2 => floatval($rates['referral_level_2'] ?? 5),
            3 => floatval($rates['referral_level_3'] ?? 4),
            4 => floatval($rates['referral_level_4'] ?? 3),
            5 => floatval($rates['referral_level_5'] ?? 2)
        ];
        
        // Get the user's referrer
        $stmt = $pdo->prepare("SELECT referred_by FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $referrerCode = $stmt->fetchColumn();
        
        if (!$referrerCode) return;
        
        $level = 1;
        $currentReferrer = $referrerCode;
        
        while ($currentReferrer && $level <= 5) {
            // Get referrer user ID
            $stmt = $pdo->prepare("SELECT id FROM users WHERE referral_code = ?");
            $stmt->execute([$currentReferrer]);
            $referrerId = $stmt->fetchColumn();
            
            if ($referrerId) {
                $commission = ($amount * $commissionRates[$level]) / 100;
                
                // Insert earning record
                $stmt = $pdo->prepare("INSERT INTO earnings (user_id, amount, type, referral_level, description, status) VALUES (?, ?, 'referral', ?, ?, 'credited')");
                $stmt->execute([$referrerId, $commission, $level, "Level $level referral commission from user ID: $user_id"]);
                
                // Update wallet balance
                $stmt = $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
                $stmt->execute([$commission, $referrerId]);
                
                // Update referral total earned
                $stmt = $pdo->prepare("UPDATE referrals SET total_earned = total_earned + ? WHERE user_id = ? AND referred_user_id = ?");
                $stmt->execute([$commission, $referrerId, $user_id]);
                
                // Get next level referrer
                $stmt = $pdo->prepare("SELECT referred_by FROM users WHERE id = ?");
                $stmt->execute([$referrerId]);
                $currentReferrer = $stmt->fetchColumn();
            } else {
                break;
            }
            
            $level++;
        }
    } catch (Exception $e) {
        error_log("Referral commission processing error: " . $e->getMessage());
    }
}

// Redistribute rejected task
function redistributeTask($task_id) {
    global $pdo;
    
    try {
        // Get task details
        $stmt = $pdo->prepare("SELECT max_users, assigned_users FROM tasks WHERE id = ?");
        $stmt->execute([$task_id]);
        $task = $stmt->fetch();
        
        if (!$task || $task['assigned_users'] >= $task['max_users']) {
            return false;
        }
        
        // Find users who haven't reached their daily task limit and haven't been assigned this task
        $stmt = $pdo->prepare("
            SELECT u.id, p.task_limit 
            FROM users u 
            JOIN packages p ON u.package_id = p.id 
            WHERE u.role = 'user' 
            AND u.is_active = 1
            AND u.id NOT IN (
                SELECT user_id FROM task_assignments WHERE task_id = ?
            )
            AND (
                SELECT COUNT(*) FROM task_assignments ta 
                WHERE ta.user_id = u.id 
                AND DATE(ta.created_at) = CURDATE() 
                AND ta.status = 'accepted'
            ) < p.task_limit
            ORDER BY RAND() 
            LIMIT 1
        ");
        $stmt->execute([$task_id]);
        $availableUser = $stmt->fetch();
        
        if ($availableUser) {
            // Calculate deadlines
            $stmt = $pdo->prepare("SELECT timer_accept, timer_submit FROM tasks WHERE id = ?");
            $stmt->execute([$task_id]);
            $timers = $stmt->fetch();
            
            $acceptDeadline = date('Y-m-d H:i:s', time() + $timers['timer_accept']);
            $submitDeadline = date('Y-m-d H:i:s', time() + $timers['timer_accept'] + $timers['timer_submit']);
            
            // Assign task to available user
            $stmt = $pdo->prepare("INSERT INTO task_assignments (task_id, user_id, status, accept_deadline, submit_deadline) VALUES (?, ?, 'pending', ?, ?)");
            $stmt->execute([$task_id, $availableUser['id'], $acceptDeadline, $submitDeadline]);
            
            // Update assigned users count
            $stmt = $pdo->prepare("UPDATE tasks SET assigned_users = assigned_users + 1 WHERE id = ?");
            $stmt->execute([$task_id]);
            
            return true;
        }
        
        return false;
    } catch (Exception $e) {
        error_log("Task redistribution error: " . $e->getMessage());
        return false;
    }
}

// Validate package access
function validatePackageAccess($user_id) {
    global $pdo;
    
    try {
        // Get user's package and today's accepted tasks
        $stmt = $pdo->prepare("
            SELECT p.task_limit,
                   (SELECT COUNT(*) FROM task_assignments ta 
                    WHERE ta.user_id = ? 
                    AND DATE(ta.created_at) = CURDATE() 
                    AND ta.status = 'accepted') as today_tasks
            FROM users u 
            JOIN packages p ON u.package_id = p.id 
            WHERE u.id = ?
        ");
        $stmt->execute([$user_id, $user_id]);
        $result = $stmt->fetch();
        
        if (!$result) return false;
        
        return $result['today_tasks'] < $result['task_limit'];
    } catch (Exception $e) {
        error_log("Package access validation error: " . $e->getMessage());
        return false;
    }
}

// Send email (basic PHP mail function)
function sendEmail($to, $subject, $body, $from = null) {
    if (!$from) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'site_email'");
        $stmt->execute();
        $from = $stmt->fetchColumn() ?: 'noreply@taskmanagement.com';
    }
    
    $headers = "From: $from\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    return mail($to, $subject, $body, $headers);
}

// Log system errors
function logError($message, $file = '', $line = '') {
    $logMessage = date('Y-m-d H:i:s') . " - Error: $message";
    if ($file) $logMessage .= " in $file";
    if ($line) $logMessage .= " on line $line";
    
    if (!is_dir('logs')) {
        mkdir('logs', 0755, true);
    }
    
    error_log($logMessage . "\n", 3, 'logs/error.log');
}

// Format currency
function formatCurrency($amount) {
    return '₹' . number_format($amount, 2);
}

// Time ago function
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31536000) return floor($time/2592000) . ' months ago';
    
    return floor($time/31536000) . ' years ago';
}

// Generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
