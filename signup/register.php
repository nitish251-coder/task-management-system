<?php
$page_title = 'Sign Up - Step 1';
require_once '../includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

$error = '';
$success = '';
$formData = [
    'name' => '',
    'email' => '',
    'mobile' => '',
    'referral_code' => ''
];

// Get referral code from URL if present
if (isset($_GET['ref'])) {
    $formData['referral_code'] = sanitizeInput($_GET['ref']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['name'] = sanitizeInput($_POST['name'] ?? '');
    $formData['email'] = sanitizeInput($_POST['email'] ?? '');
    $formData['mobile'] = sanitizeInput($_POST['mobile'] ?? '');
    $formData['referral_code'] = sanitizeInput($_POST['referral_code'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Validation
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Invalid security token. Please try again.';
    } elseif (empty($formData['name']) || empty($formData['email']) || empty($formData['mobile']) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!preg_match('/^[6-9]\d{9}$/', preg_replace('/\D/', '', $formData['mobile']))) {
        $error = 'Please enter a valid 10-digit mobile number.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$formData['email']]);
            if ($stmt->fetch()) {
                $error = 'Email address is already registered.';
            } else {
                // Validate referral code if provided
                $referrer_id = null;
                if (!empty($formData['referral_code'])) {
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE referral_code = ? AND role = 'user'");
                    $stmt->execute([$formData['referral_code']]);
                    $referrer = $stmt->fetch();
                    if (!$referrer) {
                        $error = 'Invalid referral code.';
                    } else {
                        $referrer_id = $referrer['id'];
                    }
                }
                
                if (!$error) {
                    // Store data in session for step 2
                    $_SESSION['signup_step1'] = [
                        'name' => $formData['name'],
                        'email' => $formData['email'],
                        'mobile' => $formData['mobile'],
                        'password' => password_hash($password, PASSWORD_DEFAULT),
                        'referral_code' => $formData['referral_code'],
                        'referrer_id' => $referrer_id
                    ];
                    
                    header('Location: register_details.php');
                    exit();
                }
            }
        } catch (Exception $e) {
            logError("Registration step 1 error: " . $e->getMessage());
            $error = 'An error occurred. Please try again.';
        }
    }
}
?>

<div class="container">
    <div class="max-w-lg mx-auto">
        <div class="card">
            <div class="card-header text-center">
                <h2 class="card-title">Create Your Account</h2>
                <p class="text-gray-600">Step 1 of 2 - Basic Information</p>
                
                <!-- Progress indicator -->
                <div class="flex justify-center mt-4">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-bold">1</div>
                        <div class="w-16 h-1 bg-gray-300 mx-2"></div>
                        <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-bold">2</div>
                    </div>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" data-validate>
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="name" class="form-label">Full Name *</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        class="form-input" 
                        value="<?php echo htmlspecialchars($formData['name']); ?>"
                        required
                        autocomplete="name"
                        placeholder="Enter your full name"
                    >
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email Address *</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        value="<?php echo htmlspecialchars($formData['email']); ?>"
                        required
                        autocomplete="email"
                        placeholder="Enter your email address"
                    >
                </div>
                
                <div class="form-group">
                    <label for="mobile" class="form-label">Mobile Number *</label>
                    <input 
                        type="tel" 
                        id="mobile" 
                        name="mobile" 
                        class="form-input" 
                        value="<?php echo htmlspecialchars($formData['mobile']); ?>"
                        required
                        autocomplete="tel"
                        placeholder="Enter 10-digit mobile number"
                        pattern="[6-9][0-9]{9}"
                    >
                </div>
                
                <div class="form-group">
                    <label for="referral_code" class="form-label">Referral Code (Optional)</label>
                    <input 
                        type="text" 
                        id="referral_code" 
                        name="referral_code" 
                        class="form-input" 
                        value="<?php echo htmlspecialchars($formData['referral_code']); ?>"
                        placeholder="Enter referral code if you have one"
                        style="text-transform: uppercase;"
                    >
                    <small class="text-gray-600">
                        Enter a referral code to earn your referrer commission on your earnings.
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password *</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        required
                        autocomplete="new-password"
                        placeholder="Enter password (minimum 6 characters)"
                        minlength="6"
                    >
                </div>
                
                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm Password *</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        class="form-input" 
                        required
                        autocomplete="new-password"
                        placeholder="Re-enter your password"
                        minlength="6"
                    >
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-large w-full btn-success">
                        Continue to Step 2 →
                    </button>
                </div>
            </form>
            
            <div class="text-center mt-4">
                <p class="text-gray-600">
                    Already have an account? 
                    <a href="../login.php?role=user" class="text-blue-600 hover:text-blue-800">Login here</a>
                </p>
                
                <div class="mt-3">
                    <a href="../index.php" class="text-gray-600 hover:text-gray-800">
                        ← Back to Home
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h4 class="card-title">Why Join Us?</h4>
            </div>
            <ul class="text-sm text-gray-600 space-y-2">
                <li>• Earn money by completing simple tasks</li>
                <li>• Get referral commissions up to 5 levels deep</li>
                <li>• Flexible work - complete tasks at your own pace</li>
                <li>• Secure payment system with wallet management</li>
                <li>• 24/7 support for all your queries</li>
            </ul>
        </div>
    </div>
</div>

<style>
.max-w-lg {
    max-width: 32rem;
}

.mx-auto {
    margin-left: auto;
    margin-right: auto;
}

.space-y-2 > * + * {
    margin-top: 0.5rem;
}

.justify-center {
    justify-content: center;
}

.items-center {
    align-items: center;
}

input[style*="text-transform: uppercase"] {
    text-transform: uppercase;
}
</style>

<script>
// Auto-format mobile number
document.getElementById('mobile').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 10) {
        value = value.substring(0, 10);
    }
    e.target.value = value;
});

// Auto-uppercase referral code
document.getElementById('referral_code').addEventListener('input', function(e) {
    e.target.value = e.target.value.toUpperCase();
});
</script>

<?php require_once '../includes/footer.php'; ?>
