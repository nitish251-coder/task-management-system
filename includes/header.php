<?php
if (!isset($pdo)) {
    require_once __DIR__ . '/../config.php';
}
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - Task Management System' : 'Task Management System'; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1><a href="<?php echo SITE_URL; ?>">TaskManager</a></h1>
                </div>
                
                <?php if (isLoggedIn()): ?>
                <nav class="main-nav">
                    <?php if (isAdmin()): ?>
                        <a href="<?php echo SITE_URL; ?>/admin/dashboard.php" class="nav-link">Dashboard</a>
                        <a href="<?php echo SITE_URL; ?>/admin/user_management.php" class="nav-link">Users</a>
                    <?php elseif (isUser()): ?>
                        <a href="<?php echo SITE_URL; ?>/user/dashboard.php" class="nav-link">Dashboard</a>
                        <a href="<?php echo SITE_URL; ?>/user/profile.php" class="nav-link">Profile</a>
                        <a href="<?php echo SITE_URL; ?>/user/wallet.php" class="nav-link">Wallet</a>
                    <?php endif; ?>
                </nav>
                
                <div class="user-menu">
                    <span class="user-name">Welcome, <?php echo sanitizeInput($_SESSION['user_name'] ?? 'User'); ?></span>
                    <a href="<?php echo SITE_URL; ?>/logout.php" class="logout-btn">Logout</a>
                </div>
                <?php else: ?>
                <nav class="main-nav">
                    <a href="<?php echo SITE_URL; ?>/login.php" class="nav-link">Login</a>
                    <a href="<?php echo SITE_URL; ?>/signup/register.php" class="nav-link">Signup</a>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </header>
    
    <main class="main-content">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php 
                echo sanitizeInput($_SESSION['success_message']); 
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error">
                <?php 
                echo sanitizeInput($_SESSION['error_message']); 
                unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>
