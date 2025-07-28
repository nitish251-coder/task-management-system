</main>
    
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Task Management System</h3>
                    <p>Efficient task management with referral rewards system.</p>
                </div>
                
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <?php if (!isLoggedIn()): ?>
                            <li><a href="<?php echo SITE_URL; ?>/login.php">Login</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/signup/register.php">Sign Up</a></li>
                        <?php else: ?>
                            <?php if (isAdmin()): ?>
                                <li><a href="<?php echo SITE_URL; ?>/admin/dashboard.php">Admin Dashboard</a></li>
                            <?php else: ?>
                                <li><a href="<?php echo SITE_URL; ?>/user/dashboard.php">User Dashboard</a></li>
                            <?php endif; ?>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="mailto:support@taskmanagement.com">Contact Support</a></li>
                        <li><a href="#" onclick="return false;">Help Center</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>System Info</h4>
                    <p class="system-info">
                        <?php if (isAdmin()): ?>
                            Server Time: <?php echo date('Y-m-d H:i:s'); ?>
                        <?php else: ?>
                            &copy; <?php echo date('Y'); ?> Task Management System
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Task Management System. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js_file): ?>
            <script src="<?php echo SITE_URL . $js_file; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <script>
        // Global JavaScript variables
        const SITE_URL = '<?php echo SITE_URL; ?>';
        const USER_ROLE = '<?php echo $_SESSION['role'] ?? ''; ?>';
        const USER_ID = '<?php echo $_SESSION['user_id'] ?? ''; ?>';
        const CSRF_TOKEN = '<?php echo generateCSRFToken(); ?>';
    </script>
</body>
</html>
