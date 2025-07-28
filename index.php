<?php
$page_title = 'Welcome';
require_once 'includes/header.php';
?>

<div class="container">
    <div class="text-center mb-5">
        <h1>Task Management System</h1>
        <p class="text-lg text-gray-600">Efficient task management with referral rewards system</p>
    </div>
    
    <div class="login-options">
        <div class="login-option">
            <h3>Admin Login</h3>
            <p>Access admin dashboard to manage tasks, users, and system settings.</p>
            <a href="login.php?role=admin" class="btn btn-large">Admin Login</a>
        </div>
        
        <div class="login-option">
            <h3>User Login</h3>
            <p>Access your dashboard to view and complete assigned tasks.</p>
            <a href="login.php?role=user" class="btn btn-large">User Login</a>
        </div>
        
        <div class="login-option">
            <h3>New User Signup</h3>
            <p>Create a new account and start earning by completing tasks.</p>
            <a href="signup/register.php" class="btn btn-large btn-success">Sign Up Now</a>
        </div>
    </div>
    
    <div class="card mt-5">
        <div class="card-header">
            <h3 class="card-title">How It Works</h3>
        </div>
        
        <div class="grid grid-3">
            <div class="text-center">
                <h4>1. Sign Up</h4>
                <p>Create your account with referral code and complete your profile.</p>
            </div>
            
            <div class="text-center">
                <h4>2. Complete Tasks</h4>
                <p>Accept and complete tasks assigned by admin to earn points.</p>
            </div>
            
            <div class="text-center">
                <h4>3. Earn & Refer</h4>
                <p>Earn money and get referral commissions up to 5 levels deep.</p>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Package Options</h3>
        </div>
        
        <div class="grid grid-3">
            <div class="text-center p-4 bg-yellow-50 rounded">
                <h4 class="text-yellow-600">Gold Package</h4>
                <p class="text-2xl font-bold">1 Task/Day</p>
                <p class="text-sm text-gray-600">Perfect for beginners</p>
            </div>
            
            <div class="text-center p-4 bg-gray-50 rounded">
                <h4 class="text-gray-600">Silver Package</h4>
                <p class="text-2xl font-bold">2 Tasks/Day</p>
                <p class="text-sm text-gray-600">For regular users</p>
            </div>
            
            <div class="text-center p-4 bg-blue-50 rounded">
                <h4 class="text-blue-600">Diamond Package</h4>
                <p class="text-2xl font-bold">3 Tasks/Day</p>
                <p class="text-sm text-gray-600">Maximum earning potential</p>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Referral Commission Structure</h3>
        </div>
        
        <div class="grid grid-5">
            <div class="text-center p-3 bg-green-50 rounded">
                <div class="text-2xl font-bold text-green-600">10%</div>
                <div class="text-sm">Level 1</div>
            </div>
            <div class="text-center p-3 bg-green-50 rounded">
                <div class="text-2xl font-bold text-green-600">5%</div>
                <div class="text-sm">Level 2</div>
            </div>
            <div class="text-center p-3 bg-green-50 rounded">
                <div class="text-2xl font-bold text-green-600">4%</div>
                <div class="text-sm">Level 3</div>
            </div>
            <div class="text-center p-3 bg-green-50 rounded">
                <div class="text-2xl font-bold text-green-600">3%</div>
                <div class="text-sm">Level 4</div>
            </div>
            <div class="text-center p-3 bg-green-50 rounded">
                <div class="text-2xl font-bold text-green-600">2%</div>
                <div class="text-sm">Level 5</div>
            </div>
        </div>
        
        <p class="text-center mt-3 text-gray-600">
            Earn commission from your referrals' task completions up to 5 levels deep!
        </p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
