<?php
$page_title = 'Admin Dashboard';
require_once '../includes/header.php';

requireLogin();
requireAdmin();

// Get dashboard statistics
try {
    // Total users
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'user'");
    $stmt->execute();
    $totalUsers = $stmt->fetchColumn();
    
    // Active tasks
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE is_active = 1");
    $stmt->execute();
    $activeTasks = $stmt->fetchColumn();
    
    // Total earnings
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM earnings WHERE status = 'credited'");
    $stmt->execute();
    $totalEarnings = $stmt->fetchColumn();
    
    // Pending task submissions
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM task_assignments WHERE status = 'completed'");
    $stmt->execute();
    $pendingReviews = $stmt->fetchColumn();
    
    // Recent task assignments
    $stmt = $pdo->prepare("
        SELECT ta.*, t.title, u.name as user_name, t.points
        FROM task_assignments ta
        JOIN tasks t ON ta.task_id = t.id
        JOIN users u ON ta.user_id = u.id
        ORDER BY ta.created_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    $recentAssignments = $stmt->fetchAll();
    
    // Tasks needing review
    $stmt = $pdo->prepare("
        SELECT ta.*, t.title, u.name as user_name, t.points
        FROM task_assignments ta
        JOIN tasks t ON ta.task_id = t.id
        JOIN users u ON ta.user_id = u.id
        WHERE ta.status = 'completed'
        ORDER BY ta.submission_time DESC
        LIMIT 5
    ");
    $stmt->execute();
    $tasksForReview = $stmt->fetchAll();
    
} catch (Exception $e) {
    logError("Admin dashboard error: " . $e->getMessage());
    $totalUsers = $activeTasks = $totalEarnings = $pendingReviews = 0;
    $recentAssignments = $tasksForReview = [];
}

// Handle task creation
$taskError = '';
$taskSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_task'])) {
    $title = sanitizeInput($_POST['title'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $media_link = sanitizeInput($_POST['media_link'] ?? '');
    $points = floatval($_POST['points'] ?? 0);
    $timer_accept = intval($_POST['timer_accept'] ?? 3600); // 1 hour default
    $timer_submit = intval($_POST['timer_submit'] ?? 86400); // 24 hours default
    $max_users = intval($_POST['max_users'] ?? 1);
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        $taskError = 'Invalid security token.';
    } elseif (empty($title) || empty($description) || $points <= 0) {
        $taskError = 'Please fill in all required fields with valid values.';
    } else {
        try {
            $media_file = '';
            
            // Handle file upload
            if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = secureFileUpload($_FILES['media_file'], $_SESSION['user_id'], 'tasks');
                if ($uploadResult['success']) {
                    $media_file = $uploadResult['path'];
                } else {
                    $taskError = $uploadResult['message'];
                }
            }
            
            if (!$taskError) {
                $stmt = $pdo->prepare("
                    INSERT INTO tasks (admin_id, title, description, media_link, media_file, points, timer_accept, timer_submit, max_users)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $_SESSION['user_id'], $title, $description, $media_link, $media_file, 
                    $points, $timer_accept, $timer_submit, $max_users
                ]);
                
                $taskSuccess = 'Task created successfully!';
                
                // Clear form data
                $_POST = [];
            }
        } catch (Exception $e) {
            logError("Task creation error: " . $e->getMessage());
            $taskError = 'An error occurred while creating the task.';
        }
    }
}
?>

<div class="container">
    <div class="flex justify-between items-center mb-6">
        <h1>Admin Dashboard</h1>
        <div class="text-sm text-gray-600">
            Welcome, <?php echo sanitizeInput($_SESSION['user_name']); ?>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-number"><?php echo number_format($totalUsers); ?></span>
            <div class="stat-label">Total Users</div>
        </div>
        
        <div class="stat-card">
            <span class="stat-number"><?php echo number_format($activeTasks); ?></span>
            <div class="stat-label">Active Tasks</div>
        </div>
        
        <div class="stat-card">
            <span class="stat-number"><?php echo formatCurrency($totalEarnings); ?></span>
            <div class="stat-label">Total Earnings</div>
        </div>
        
        <div class="stat-card">
            <span class="stat-number text-orange-600"><?php echo number_format($pendingReviews); ?></span>
            <div class="stat-label">Pending Reviews</div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="grid grid-2 mb-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="grid grid-2 gap-3">
                <a href="user_management.php" class="btn">Manage Users</a>
                <a href="#create-task" class="btn btn-success" onclick="toggleSection('create-task-form')">Create Task</a>
                <a href="#packages" class="btn btn-secondary" onclick="toggleSection('package-management')">Manage Packages</a>
                <a href="#earnings" class="btn" onclick="toggleSection('earnings-report')">View Earnings</a>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">System Status</h3>
            </div>
            <div class="text-sm text-gray-600">
                <p><strong>Server Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
                <p><strong>Active Sessions:</strong> <?php echo session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive'; ?></p>
                <p><strong>Database:</strong> Connected</p>
                <p><strong>Upload Directory:</strong> <?php echo is_writable(UPLOAD_DIR) ? 'Writable' : 'Not Writable'; ?></p>
            </div>
        </div>
    </div>
    
    <!-- Create Task Form (Hidden by default) -->
    <div id="create-task-form" class="card hidden">
        <div class="card-header">
            <h3 class="card-title">Create New Task</h3>
        </div>
        
        <?php if ($taskError): ?>
            <div class="alert alert-error"><?php echo $taskError; ?></div>
        <?php endif; ?>
        
        <?php if ($taskSuccess): ?>
            <div class="alert alert-success"><?php echo $taskSuccess; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" data-validate>
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="create_task" value="1">
            
            <div class="grid grid-2">
                <div class="form-group">
                    <label for="title" class="form-label">Task Title *</label>
                    <input type="text" id="title" name="title" class="form-input" required 
                           value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="points" class="form-label">Points (₹) *</label>
                    <input type="number" id="points" name="points" class="form-input" min="1" step="0.01" required
                           value="<?php echo htmlspecialchars($_POST['points'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="description" class="form-label">Task Description *</label>
                <textarea id="description" name="description" class="form-input form-textarea" rows="4" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>
            
            <div class="grid grid-2">
                <div class="form-group">
                    <label for="media_link" class="form-label">Media Link (Optional)</label>
                    <input type="url" id="media_link" name="media_link" class="form-input" 
                           placeholder="https://example.com/resource"
                           value="<?php echo htmlspecialchars($_POST['media_link'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="media_file" class="form-label">Upload File (Optional)</label>
                    <input type="file" id="media_file" name="media_file" class="form-input">
                </div>
            </div>
            
            <div class="grid grid-3">
                <div class="form-group">
                    <label for="timer_accept" class="form-label">Accept Timer (seconds)</label>
                    <select id="timer_accept" name="timer_accept" class="form-input form-select">
                        <option value="3600">1 Hour</option>
                        <option value="7200">2 Hours</option>
                        <option value="21600">6 Hours</option>
                        <option value="43200">12 Hours</option>
                        <option value="86400">24 Hours</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="timer_submit" class="form-label">Submit Timer (seconds)</label>
                    <select id="timer_submit" name="timer_submit" class="form-input form-select">
                        <option value="3600">1 Hour</option>
                        <option value="7200">2 Hours</option>
                        <option value="21600">6 Hours</option>
                        <option value="43200">12 Hours</option>
                        <option value="86400" selected>24 Hours</option>
                        <option value="172800">48 Hours</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="max_users" class="form-label">Max Users</label>
                    <input type="number" id="max_users" name="max_users" class="form-input" min="1" max="100" value="1">
                </div>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-success">Create Task</button>
                <button type="button" class="btn btn-secondary" onclick="toggleSection('create-task-form')">Cancel</button>
            </div>
        </form>
    </div>
    
    <!-- Tasks for Review -->
    <?php if (!empty($tasksForReview)): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Tasks Pending Review</h3>
        </div>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>User</th>
                        <th>Points</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasksForReview as $task): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($task['title']); ?></strong>
                        </td>
                        <td><?php echo htmlspecialchars($task['user_name']); ?></td>
                        <td><?php echo formatCurrency($task['points']); ?></td>
                        <td><?php echo timeAgo($task['submission_time']); ?></td>
                        <td>
                            <a href="review_task.php?id=<?php echo $task['id']; ?>" class="btn btn-small">Review</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Recent Activity -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Task Activity</h3>
        </div>
        
        <?php if (!empty($recentAssignments)): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>User</th>
                        <th>Status</th>
                        <th>Points</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentAssignments as $assignment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                        <td><?php echo htmlspecialchars($assignment['user_name']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $assignment['status']; ?>">
                                <?php echo ucfirst($assignment['status']); ?>
                            </span>
                        </td>
                        <td><?php echo formatCurrency($assignment['points']); ?></td>
                        <td><?php echo timeAgo($assignment['created_at']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="text-gray-600 text-center py-4">No recent activity found.</p>
        <?php endif; ?>
    </div>
</div>

<style>
.flex {
    display: flex;
}

.justify-between {
    justify-content: space-between;
}

.items-center {
    align-items: center;
}

.mb-6 {
    margin-bottom: 1.5rem;
}

.gap-3 {
    gap: 0.75rem;
}

.table-responsive {
    overflow-x: auto;
}

.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
}

.badge-pending {
    background: #fef3c7;
    color: #92400e;
}

.badge-accepted {
    background: #d1fae5;
    color: #065f46;
}

.badge-rejected {
    background: #fee2e2;
    color: #991b1b;
}

.badge-completed {
    background: #e0e7ff;
    color: #3730a3;
}

.badge-approved {
    background: #dcfce7;
    color: #166534;
}
</style>

<script>
function toggleSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.classList.toggle('hidden');
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>
