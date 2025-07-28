<?php
$page_title = 'Login';
require_once 'includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: user/dashboard.php');
    }
    exit();
}

$role = isset($_GET['role']) ? sanitizeInput($_GET['role']) : 'user';
if (!in_array($role, ['admin', 'user'])) {
    $role = 'user';
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Invalid security token. Please try again.';
    } elseif (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, name, email, password, role, is_active FROM users WHERE email = ? AND role = ?");
            $stmt->execute([$email, $role]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                if (!$user['is_active']) {
                    $error = 'Your account has been deactivated. Please contact support.';
                } else {
                    // Regenerate session ID for security
                    session_regenerate_id(true);
                    
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['login_time'] = time();
                    
                    // Redirect to appropriate dashboard
                    if ($user['role'] === 'admin') {
                        header('Location: admin/dashboard.php');
                    } else {
                        header('Location: user/dashboard.php');
                    }
                    exit();
                }
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (Exception $e) {
            logError("Login error: " . $e->getMessage());
            $error = 'An error occurred. Please try again.';
        }
    }
}
?>

<div class="container">
    <div class="max-w-md mx-auto">
        <div class="card">
            <div class="card-header text-center">
                <h2 class="card-title">
                    <?php echo ucfirst($role); ?> Login
                </h2>
                <p class="text-gray-600">
                    <?php if ($role === 'admin'): ?>
                        Access admin dashboard
                    <?php else: ?>
                        Access your user dashboard
                    <?php endif; ?>
                </p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" data-validate>
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        value="<?php echo htmlspecialchars($email); ?>"
                        required
                        autocomplete="email"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        required
                        autocomplete="current-password"
                    >
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-large w-full">
                        Login as <?php echo ucfirst($role); ?>
                    </button>
                </div>
            </form>
            
            <div class="text-center mt-4">
                <p class="text-gray-600">
                    <?php if ($role === 'user'): ?>
                        Don't have an account? 
                        <a href="signup/register.php" class="text-blue-600 hover:text-blue-800">Sign up here</a>
                    <?php endif; ?>
                </p>
                
                <div class="mt-3">
                    <a href="index.php" class="text-gray-600 hover:text-gray-800">
                        ← Back to Home
                    </a>
                </div>
                
                <?php if ($role === 'user'): ?>
                    <div class="mt-2">
                        <a href="login.php?role=admin" class="text-gray-600 hover:text-gray-800">
                            Admin Login
                        </a>
                    </div>
                <?php else: ?>
                    <div class="mt-2">
                        <a href="login.php?role=user" class="text-gray-600 hover:text-gray-800">
                            User Login
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($role === 'admin'): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h4 class="card-title">Demo Admin Credentials</h4>
                </div>
                <div class="text-sm text-gray-600">
                    <p><strong>Email:</strong> admin@taskmanagement.com</p>
                    <p><strong>Password:</strong> admin123</p>
                    <p class="text-red-600 mt-2">
                        <strong>Note:</strong> Change these credentials after first login!
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.max-w-md {
    max-width: 28rem;
}

.mx-auto {
    margin-left: auto;
    margin-right: auto;
}
</style>

<?php require_once 'includes/footer.php'; ?>
