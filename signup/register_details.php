<?php
$page_title = 'Sign Up - Step 2';
require_once '../includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

// Check if step 1 is completed
if (!isset($_SESSION['signup_step1'])) {
    header('Location: register.php');
    exit();
}

$step1Data = $_SESSION['signup_step1'];
$error = '';
$success = '';
$formData = [
    'bank_name' => '',
    'account_number' => '',
    'ifsc_code' => '',
    'account_holder_name' => '',
    'upi_id' => '',
    'interests' => '',
    'package_id' => '1' // Default to Gold package
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['bank_name'] = sanitizeInput($_POST['bank_name'] ?? '');
    $formData['account_number'] = sanitizeInput($_POST['account_number'] ?? '');
    $formData['ifsc_code'] = sanitizeInput($_POST['ifsc_code'] ?? '');
    $formData['account_holder_name'] = sanitizeInput($_POST['account_holder_name'] ?? '');
    $formData['upi_id'] = sanitizeInput($_POST['upi_id'] ?? '');
    $formData['interests'] = sanitizeInput($_POST['interests'] ?? '');
    $formData['package_id'] = sanitizeInput($_POST['package_id'] ?? '1');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Validation
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Invalid security token. Please try again.';
    } elseif (empty($formData['bank_name']) || empty($formData['account_number']) || 
              empty($formData['ifsc_code']) || empty($formData['account_holder_name'])) {
        $error = 'Please fill in all bank details.';
    } elseif (!preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', $formData['ifsc_code'])) {
        $error = 'Please enter a valid IFSC code.';
    } elseif (!empty($formData['upi_id']) && !preg_match('/^[\w\.\-_]{2,256}@[a-zA-Z]{2,64}$/', $formData['upi_id'])) {
        $error = 'Please enter a valid UPI ID.';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Generate unique referral code
            $referralCode = generateReferralCode();
            
            // Ensure referral code is unique
            $stmt = $pdo->prepare("SELECT id FROM users WHERE referral_code = ?");
            $stmt->execute([$referralCode]);
            while ($stmt->fetch()) {
                $referralCode = generateReferralCode();
                $stmt->execute([$referralCode]);
            }
            
            // Prepare bank details JSON
            $bankDetails = json_encode([
                'bank_name' => $formData['bank_name'],
                'account_number' => $formData['account_number'],
                'ifsc_code' => $formData['ifsc_code'],
                'account_holder_name' => $formData['account_holder_name']
            ]);
            
            // Insert user
            $stmt = $pdo->prepare("
                INSERT INTO users (
                    role, name, email, password, mobile, referral_code, referred_by, 
                    bank_details, upi_id, interests, package_id, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                'user',
                $step1Data['name'],
                $step1Data['email'],
                $step1Data['password'],
                $step1Data['mobile'],
                $referralCode,
                $step1Data['referral_code'],
                $bankDetails,
                $formData['upi_id'],
                $formData['interests'],
                $formData['package_id']
            ]);
            
            $userId = $pdo->lastInsertId();
            
            // Create referral relationships if referred by someone
            if ($step1Data['referrer_id']) {
                $currentReferrer = $step1Data['referrer_id'];
                $level = 1;
                
                // Get commission rates
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
                
                while ($currentReferrer && $level <= 5) {
                    // Insert referral relationship
                    $stmt = $pdo->prepare("
                        INSERT INTO referrals (user_id, referred_user_id, level, commission_percent) 
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([$currentReferrer, $userId, $level, $commissionRates[$level]]);
                    
                    // Get next level referrer
                    $stmt = $pdo->prepare("SELECT id, referred_by FROM users WHERE id = ?");
                    $stmt->execute([$currentReferrer]);
                    $referrerData = $stmt->fetch();
                    
                    if ($referrerData && $referrerData['referred_by']) {
                        $stmt = $pdo->prepare("SELECT id FROM users WHERE referral_code = ?");
                        $stmt->execute([$referrerData['referred_by']]);
                        $nextReferrer = $stmt->fetch();
                        $currentReferrer = $nextReferrer ? $nextReferrer['id'] : null;
                    } else {
                        $currentReferrer = null;
                    }
                    
                    $level++;
                }
            }
            
            $pdo->commit();
            
            // Clear signup session data
            unset($_SESSION['signup_step1']);
            
            // Send welcome email (optional)
            $subject = "Welcome to Task Management System!";
            $body = "
                <h2>Welcome {$step1Data['name']}!</h2>
                <p>Your account has been successfully created.</p>
                <p><strong>Your Referral Code:</strong> {$referralCode}</p>
                <p>You can now login and start completing tasks to earn money.</p>
                <p><a href='" . SITE_URL . "/login.php?role=user'>Login Now</a></p>
            ";
            sendEmail($step1Data['email'], $subject, $body);
            
            $success = "Account created successfully! You can now login with your credentials.";
            
        } catch (Exception $e) {
            $pdo->rollBack();
            logError("Registration step 2 error: " . $e->getMessage());
            $error = 'An error occurred while creating your account. Please try again.';
        }
    }
}

// Get available packages
try {
    $stmt = $pdo->prepare("SELECT id, name, task_limit, description FROM packages WHERE is_active = 1 ORDER BY task_limit");
    $stmt->execute();
    $packages = $stmt->fetchAll();
} catch (Exception $e) {
    $packages = [];
}
?>

<div class="container">
    <div class="max-w-lg mx-auto">
        <div class="card">
            <div class="card-header text-center">
                <h2 class="card-title">Complete Your Profile</h2>
                <p class="text-gray-600">Step 2 of 2 - Additional Details</p>
                
                <!-- Progress indicator -->
                <div class="flex justify-center mt-4">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-bold">✓</div>
                        <div class="w-16 h-1 bg-blue-600 mx-2"></div>
                        <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-bold">2</div>
                    </div>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                    <div class="mt-3">
                        <a href="../login.php?role=user" class="btn btn-success">Login Now</a>
                    </div>
                </div>
            <?php else: ?>
            
            <form method="POST" data-validate>
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="card-header">
                    <h4 class="card-title">Bank Details</h4>
                    <p class="text-sm text-gray-600">Required for withdrawal processing</p>
                </div>
                
                <div class="form-group">
                    <label for="bank_name" class="form-label">Bank Name *</label>
                    <input 
                        type="text" 
                        id="bank_name" 
                        name="bank_name" 
                        class="form-input" 
                        value="<?php echo htmlspecialchars($formData['bank_name']); ?>"
                        required
                        placeholder="e.g., State Bank of India"
                    >
                </div>
                
                <div class="form-group">
                    <label for="account_holder_name" class="form-label">Account Holder Name *</label>
                    <input 
                        type="text" 
                        id="account_holder_name" 
                        name="account_holder_name" 
                        class="form-input" 
                        value="<?php echo htmlspecialchars($formData['account_holder_name']); ?>"
                        required
                        placeholder="Name as per bank account"
                    >
                </div>
                
                <div class="form-group">
                    <label for="account_number" class="form-label">Account Number *</label>
                    <input 
                        type="text" 
                        id="account_number" 
                        name="account_number" 
                        class="form-input" 
                        value="<?php echo htmlspecialchars($formData['account_number']); ?>"
                        required
                        placeholder="Enter bank account number"
                    >
                </div>
                
                <div class="form-group">
                    <label for="ifsc_code" class="form-label">IFSC Code *</label>
                    <input 
                        type="text" 
                        id="ifsc_code" 
                        name="ifsc_code" 
                        class="form-input" 
                        value="<?php echo htmlspecialchars($formData['ifsc_code']); ?>"
                        required
                        placeholder="e.g., SBIN0001234"
                        style="text-transform: uppercase;"
                        pattern="[A-Z]{4}0[A-Z0-9]{6}"
                    >
                </div>
                
                <div class="card-header mt-4">
                    <h4 class="card-title">Payment Details</h4>
                </div>
                
                <div class="form-group">
                    <label for="upi_id" class="form-label">UPI ID (Optional)</label>
                    <input 
                        type="text" 
                        id="upi_id" 
                        name="upi_id" 
                        class="form-input" 
                        value="<?php echo htmlspecialchars($formData['upi_id']); ?>"
                        placeholder="e.g., yourname@paytm"
                    >
                    <small class="text-gray-600">
                        UPI ID for faster payments (optional)
                    </small>
                </div>
                
                <div class="card-header mt-4">
                    <h4 class="card-title">Package Selection</h4>
                </div>
                
                <div class="form-group">
                    <label for="package_id" class="form-label">Choose Package *</label>
                    <select id="package_id" name="package_id" class="form-input form-select" required>
                        <?php foreach ($packages as $package): ?>
                            <option value="<?php echo $package['id']; ?>" 
                                    <?php echo $formData['package_id'] == $package['id'] ? 'selected' : ''; ?>>
                                <?php echo ucfirst($package['name']); ?> - 
                                <?php echo $package['task_limit']; ?> task(s) per day
                                <?php if ($package['description']): ?>
                                    (<?php echo $package['description']; ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="interests" class="form-label">Areas of Interest (Optional)</label>
                    <textarea 
                        id="interests" 
                        name="interests" 
                        class="form-input form-textarea" 
                        placeholder="e.g., Data entry, Content writing, Social media, etc."
                        rows="3"
                    ><?php echo htmlspecialchars($formData['interests']); ?></textarea>
                    <small class="text-gray-600">
                        Help us assign relevant tasks to you
                    </small>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-large w-full btn-success">
                        Create Account
                    </button>
                </div>
            </form>
            
            <?php endif; ?>
            
            <div class="text-center mt-4">
                <?php if (!$success): ?>
                <p class="text-gray-600">
                    <a href="register.php" class="text-blue-600 hover:text-blue-800">← Back to Step 1</a>
                </p>
                <?php endif; ?>
                
                <div class="mt-3">
                    <a href="../index.php" class="text-gray-600 hover:text-gray-800">
                        ← Back to Home
                    </a>
                </div>
            </div>
        </div>
        
        <?php if (!$success): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h4 class="card-title">Account Summary</h4>
            </div>
            <div class="text-sm text-gray-600">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($step1Data['name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($step1Data['email']); ?></p>
                <p><strong>Mobile:</strong> <?php echo htmlspecialchars($step1Data['mobile']); ?></p>
                <?php if ($step1Data['referral_code']): ?>
                    <p><strong>Referred by:</strong> <?php echo htmlspecialchars($step1Data['referral_code']); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
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
// Auto-uppercase IFSC code
document.getElementById('ifsc_code').addEventListener('input', function(e) {
    e.target.value = e.target.value.toUpperCase();
});

// Format account number (remove spaces and special characters)
document.getElementById('account_number').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/\D/g, '');
});
</script>

<?php require_once '../includes/footer.php'; ?>
