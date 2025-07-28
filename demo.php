<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management System - Demo</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1><a href="#">TaskManager</a></h1>
                </div>
                <nav class="main-nav">
                    <a href="#" class="nav-link">Login</a>
                    <a href="#" class="nav-link">Signup</a>
                </nav>
            </div>
        </div>
    </header>
    
    <main class="main-content">
        <div class="container">
            <div class="text-center mb-5">
                <h1>Task Management System</h1>
                <p class="text-lg text-gray-600">Efficient task management with referral rewards system</p>
            </div>
            
            <div class="login-options">
                <div class="login-option">
                    <h3>Admin Login</h3>
                    <p>Access admin dashboard to manage tasks, users, and system settings.</p>
                    <a href="#admin-demo" class="btn btn-large" onclick="showDemo('admin')">Admin Demo</a>
                </div>
                
                <div class="login-option">
                    <h3>User Login</h3>
                    <p>Access your dashboard to view and complete assigned tasks.</p>
                    <a href="#user-demo" class="btn btn-large" onclick="showDemo('user')">User Demo</a>
                </div>
                
                <div class="login-option">
                    <h3>New User Signup</h3>
                    <p>Create a new account and start earning by completing tasks.</p>
                    <a href="#signup-demo" class="btn btn-large btn-success" onclick="showDemo('signup')">Signup Demo</a>
                </div>
            </div>
            
            <!-- Demo Sections -->
            <div id="admin-demo" class="demo-section hidden">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Admin Dashboard Demo</h2>
                    </div>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <span class="stat-number">156</span>
                            <div class="stat-label">Total Users</div>
                        </div>
                        <div class="stat-card">
                            <span class="stat-number">23</span>
                            <div class="stat-label">Active Tasks</div>
                        </div>
                        <div class="stat-card">
                            <span class="stat-number">₹45,230</span>
                            <div class="stat-label">Total Earnings</div>
                        </div>
                        <div class="stat-card">
                            <span class="stat-number text-orange-600">8</span>
                            <div class="stat-label">Pending Reviews</div>
                        </div>
                    </div>
                    
                    <div class="grid grid-2">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Create New Task</h3>
                            </div>
                            <form>
                                <div class="form-group">
                                    <label class="form-label">Task Title</label>
                                    <input type="text" class="form-input" placeholder="Enter task title">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Points (₹)</label>
                                    <input type="number" class="form-input" placeholder="50">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-input form-textarea" placeholder="Task description..."></textarea>
                                </div>
                                <button type="button" class="btn btn-success">Create Task</button>
                            </form>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Recent Activity</h3>
                            </div>
                            <div class="text-sm">
                                <div class="mb-2 p-2 bg-gray-50 rounded">
                                    <strong>John Doe</strong> completed "Data Entry Task"
                                    <div class="text-gray-600">2 hours ago</div>
                                </div>
                                <div class="mb-2 p-2 bg-gray-50 rounded">
                                    <strong>Jane Smith</strong> accepted "Content Writing"
                                    <div class="text-gray-600">4 hours ago</div>
                                </div>
                                <div class="mb-2 p-2 bg-gray-50 rounded">
                                    <strong>Mike Johnson</strong> rejected "Survey Task"
                                    <div class="text-gray-600">6 hours ago</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="user-demo" class="demo-section hidden">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">User Dashboard Demo</h2>
                    </div>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <span class="stat-number">₹1,250</span>
                            <div class="stat-label">Wallet Balance</div>
                        </div>
                        <div class="stat-card">
                            <span class="stat-number">₹3,450</span>
                            <div class="stat-label">Total Earnings</div>
                        </div>
                        <div class="stat-card">
                            <span class="stat-number">2/3</span>
                            <div class="stat-label">Today's Tasks</div>
                        </div>
                        <div class="stat-card">
                            <span class="stat-number">12</span>
                            <div class="stat-label">Total Referrals</div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Active Tasks</h3>
                        </div>
                        
                        <div class="task-card pending">
                            <div class="task-header">
                                <div>
                                    <div class="task-title">Social Media Content Creation</div>
                                    <div class="task-points">₹150</div>
                                </div>
                            </div>
                            <div class="task-description">
                                Create engaging social media posts for our brand. Include relevant hashtags and compelling captions.
                            </div>
                            <div class="task-timer">
                                Time to accept: 2h 45m 30s
                            </div>
                            <div class="task-actions">
                                <button class="btn btn-success">Accept Task</button>
                                <button class="btn btn-danger">Reject Task</button>
                            </div>
                        </div>
                        
                        <div class="task-card accepted">
                            <div class="task-header">
                                <div>
                                    <div class="task-title">Data Entry Project</div>
                                    <div class="task-points">₹75</div>
                                </div>
                            </div>
                            <div class="task-description">
                                Enter customer data from provided spreadsheets into our system. Accuracy is important.
                            </div>
                            <div class="task-timer warning">
                                Time to submit: 4h 12m 15s
                            </div>
                            <div class="task-actions">
                                <button class="btn btn-success">Submit Task</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-3">
                        <div class="card text-center">
                            <h4>Package: Diamond</h4>
                            <p class="text-gray-600">Daily Limit: 3 tasks</p>
                        </div>
                        
                        <div class="card text-center">
                            <h4>Referral Code</h4>
                            <div class="flex items-center justify-center gap-2">
                                <code class="bg-gray-100 px-2 py-1 rounded">ABC12345</code>
                                <button class="btn btn-small">Copy</button>
                            </div>
                        </div>
                        
                        <div class="card text-center">
                            <h4>Referral Link</h4>
                            <button class="btn btn-small btn-success">Copy Link</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="signup-demo" class="demo-section hidden">
                <div class="card max-w-lg mx-auto">
                    <div class="card-header text-center">
                        <h2 class="card-title">Create Your Account</h2>
                        <p class="text-gray-600">Step 1 of 2 - Basic Information</p>
                        
                        <div class="flex justify-center mt-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-bold">1</div>
                                <div class="w-16 h-1 bg-gray-300 mx-2"></div>
                                <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-bold">2</div>
                            </div>
                        </div>
                    </div>
                    
                    <form>
                        <div class="form-group">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-input" placeholder="Enter your full name">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Email Address *</label>
                            <input type="email" class="form-input" placeholder="Enter your email address">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Mobile Number *</label>
                            <input type="tel" class="form-input" placeholder="Enter 10-digit mobile number">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Referral Code (Optional)</label>
                            <input type="text" class="form-input" placeholder="Enter referral code if you have one">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Password *</label>
                            <input type="password" class="form-input" placeholder="Enter password (minimum 6 characters)">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Confirm Password *</label>
                            <input type="password" class="form-input" placeholder="Re-enter your password">
                        </div>
                        
                        <div class="form-group">
                            <button type="button" class="btn btn-large w-full btn-success">
                                Continue to Step 2 →
                            </button>
                        </div>
                    </form>
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
            
            <div class="alert alert-info mt-5">
                <strong>Demo Mode:</strong> This is a demonstration of the Task Management System interface. 
                The actual system requires database setup as described in the README.md file.
            </div>
        </div>
    </main>
    
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Task Management System</h3>
                    <p>Efficient task management with referral rewards system.</p>
                </div>
                
                <div class="footer-section">
                    <h4>Features</h4>
                    <ul>
                        <li>Admin Dashboard</li>
                        <li>User Management</li>
                        <li>Task Assignment</li>
                        <li>Referral System</li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>System Info</h4>
                    <p class="system-info">
                        Demo Version - <?php echo date('Y-m-d H:i:s'); ?>
                    </p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Task Management System. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <script>
        function showDemo(type) {
            // Hide all demo sections
            document.querySelectorAll('.demo-section').forEach(section => {
                section.classList.add('hidden');
            });
            
            // Show selected demo
            const demoSection = document.getElementById(type + '-demo');
            if (demoSection) {
                demoSection.classList.remove('hidden');
                demoSection.scrollIntoView({ behavior: 'smooth' });
            }
        }
        
        // Add some interactivity
        document.addEventListener('DOMContentLoaded', function() {
            // Simulate timer countdown
            const timers = document.querySelectorAll('.task-timer');
            timers.forEach(timer => {
                if (timer.textContent.includes('Time to')) {
                    let seconds = Math.floor(Math.random() * 10000) + 3600; // Random time
                    
                    setInterval(() => {
                        const hours = Math.floor(seconds / 3600);
                        const minutes = Math.floor((seconds % 3600) / 60);
                        const secs = seconds % 60;
                        
                        const timeString = `${hours}h ${minutes}m ${secs}s`;
                        if (timer.textContent.includes('accept')) {
                            timer.textContent = `Time to accept: ${timeString}`;
                        } else {
                            timer.textContent = `Time to submit: ${timeString}`;
                        }
                        
                        seconds--;
                        if (seconds < 0) seconds = 0;
                    }, 1000);
                }
            });
        });
    </script>
    
    <style>
        .demo-section {
            margin-top: 2rem;
        }
        
        .max-w-lg {
            max-width: 32rem;
        }
        
        .mx-auto {
            margin-left: auto;
            margin-right: auto;
        }
        
        .grid-5 {
            grid-template-columns: repeat(5, 1fr);
        }
        
        @media (max-width: 768px) {
            .grid-5 {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</body>
</html>
