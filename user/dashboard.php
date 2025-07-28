<?php
$page_title = 'User Dashboard';
require_once '../includes/header.php';

requireLogin();

// Ensure user is not admin
if (isAdmin()) {
    header('Location: ../admin/dashboard.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Get user statistics
try {
    // User info with package details
    $stmt = $pdo->prepare("
        SELECT u.*, p.name as package_name, p.task_limit 
        FROM users u 
        LEFT JOIN packages p ON u.package_id = p.id 
        WHERE u.id = ?
    ");
    $stmt->execute([$userId]);
    $userInfo = $stmt->fetch();
    
    // Today's task count
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM task_assignments 
        WHERE user_id = ? AND DATE(created_at) = CURDATE() AND status = 'accepted'
    ");
    $stmt->execute([$userId]);
    $todayTasks = $stmt->fetchColumn();
    
    // Total earnings
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) FROM earnings 
        WHERE user_id = ? AND status = 'credited'
    ");
    $stmt->execute([$userId]);
    $totalEarnings = $stmt->fetchColumn();
    
    // Pending earnings
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) FROM earnings 
        WHERE user_id = ? AND status = 'pending'
    ");
    $stmt->execute([$userId]);
    $pendingEarnings = $stmt->fetchColumn();
    
    // Active tasks (pending and accepted)
    $stmt = $pdo->prepare("
        SELECT ta.*, t.title, t.description, t.points, t.media_link, t.media_file,
               ta.accept_deadline, ta.submit_deadline
        FROM task_assignments ta
        JOIN tasks t ON ta.task_id = t.id
        WHERE ta.user_id = ? AND ta.status IN ('pending', 'accepted')
        ORDER BY ta.created_at DESC
    ");
    $stmt->execute([$userId]);
    $activeTasks = $stmt->fetchAll();
    
    // Completed tasks
    $stmt = $pdo->prepare("
        SELECT ta.*, t.title, t.points
        FROM task_assignments ta
        JOIN tasks t ON ta.task_id = t.id
        WHERE ta.user_id = ? AND ta.status IN ('completed', 'approved')
        ORDER BY ta.updated_at DESC
        LIMIT 10
    ");
    $stmt->execute([$userId]);
    $completedTasks = $stmt->fetchAll();
    
    // Rejected/Missed tasks
    $stmt = $pdo->prepare("
        SELECT ta.*, t.title, t.points
        FROM task_assignments ta
        JOIN tasks t ON ta.task_id = t.id
        WHERE ta.user_id = ? AND ta.status IN ('rejected', 'reassigned')
        ORDER BY ta.updated_at DESC
        LIMIT 10
    ");
    $stmt->execute([$userId]);
    $rejectedTasks = $stmt->fetchAll();
    
    // Referral statistics
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM referrals WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $totalReferrals = $stmt->fetchColumn();
    
    // Referral earnings
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) FROM earnings 
        WHERE user_id = ? AND type = 'referral' AND status = 'credited'
    ");
    $stmt->execute([$userId]);
    $referralEarnings = $stmt->fetchColumn();
    
} catch (Exception $e) {
    logError("User dashboard error: " . $e->getMessage());
    $userInfo = null;
    $todayTasks = $totalEarnings = $pendingEarnings = $totalReferrals = $referralEarnings = 0;
    $activeTasks = $completedTasks = $rejectedTasks = [];
}

// Handle task actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $taskAssignmentId = intval($_POST['task_assignment_id'] ?? 0);
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        $_SESSION['error_message'] = 'Invalid security token.';
    } elseif ($action === 'accept' && $taskAssignmentId) {
        try {
            // Check if task is still pending and within deadline
            $stmt = $pdo->prepare("
                SELECT ta.*, t.timer_submit 
                FROM task_assignments ta 
                JOIN tasks t ON ta.task_id = t.id 
                WHERE ta.id = ? AND ta.user_id = ? AND ta.status = 'pending' 
                AND ta.accept_deadline > NOW()
            ");
            $stmt->execute([$taskAssignmentId, $userId]);
            $taskAssignment = $stmt->fetch();
            
            if ($taskAssignment) {
                // Check daily task limit
                if (validatePackageAccess($userId)) {
                    // Calculate new submit deadline
                    $submitDeadline = date('Y-m-d H:i:s', time() + $taskAssignment['timer_submit']);
                    
                    $stmt = $pdo->prepare("
                        UPDATE task_assignments 
                        SET status = 'accepted', submit_deadline = ?, updated_at = NOW() 
                        WHERE id = ?
                    ");
                    $stmt->execute([$submitDeadline, $taskAssignmentId]);
                    
                    $_SESSION['success_message'] = 'Task accepted successfully!';
                } else {
                    $_SESSION['error_message'] = 'You have reached your daily task limit.';
                }
            } else {
                $_SESSION['error_message'] = 'Task is no longer available or has expired.';
            }
        } catch (Exception $e) {
            logError("Task accept error: " . $e->getMessage());
            $_SESSION['error_message'] = 'An error occurred while accepting the task.';
        }
        
    } elseif ($action === 'reject' && $taskAssignmentId) {
        try {
            $stmt = $pdo->prepare("
                UPDATE task_assignments 
                SET status = 'rejected', updated_at = NOW() 
                WHERE id = ? AND user_id = ? AND status = 'pending'
            ");
            $stmt->execute([$taskAssignmentId, $userId]);
            
            if ($stmt->rowCount() > 0) {
                // Get task ID for redistribution
                $stmt = $pdo->prepare("SELECT task_id FROM task_assignments WHERE id = ?");
                $stmt->execute([$taskAssignmentId]);
                $taskId = $stmt->fetchColumn();
                
                // Redistribute task
                redistributeTask($taskId);
                
                $_SESSION['success_message'] = 'Task rejected successfully.';
            } else {
                $_SESSION['error_message'] = 'Task is no longer available.';
            }
        } catch (Exception $e) {
            logError("Task reject error: " . $e->getMessage());
            $_SESSION['error_message'] = 'An error occurred while rejecting the task.';
        }
    }
    
    header('Location: dashboard.php');
    exit();
}
?>

<div class="container">
    <div class="flex justify-between items-center mb-6">
        <h1>User Dashboard</h1>
        <div class="text-sm text-gray-600">
            Welcome, <?php echo sanitizeInput($_SESSION['user_name']); ?>
        </div>
    </div>
    
    <!-- User Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-number"><?php echo formatCurrency($userInfo['wallet_balance'] ?? 0); ?></span>
            <div class="stat-label">Wallet Balance</div>
        </div>
        
        <div class="stat-card">
            <span class="stat-number"><?php echo formatCurrency($totalEarnings); ?></span>
            <div class="stat-label">Total Earnings</div>
        </div>
        
        <div class="stat-card">
            <span class="stat-number"><?php echo $todayTasks; ?>/<?php echo $userInfo['task_limit'] ?? 0; ?></span>
            <div class="stat-label">Today's Tasks</div>
        </div>
        
        <div class="stat-card">
            <span class="stat-number"><?php echo $totalReferrals; ?></span>
            <div class="stat-label">Total Referrals</div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="grid grid-3 mb-6">
        <div class="card text-center">
            <h4>Package: <?php echo ucfirst($userInfo['package_name'] ?? 'None'); ?></h4>
            <p class="text-gray-600">Daily Limit: <?php echo $userInfo['task_limit'] ?? 0; ?> tasks</p>
        </div>
        
        <div class="card text-center">
            <h4>Referral Code</h4>
            <div class="flex items-center justify-center gap-2">
                <code class="bg-gray-100 px-2 py-1 rounded"><?php echo $userInfo['referral_code'] ?? 'N/A'; ?></code>
                <button onclick="copyToClipboard('<?php echo $userInfo['referral_code'] ?? ''; ?>', this)" class="btn btn-small">Copy</button>
            </div>
        </div>
        
        <div class="card text-center">
            <h4>Referral Link</h4>
            <button onclick="copyToClipboard('<?php echo SITE_URL; ?>/signup/register.php?ref=<?php echo $userInfo['referral_code'] ?? ''; ?>', this)" class="btn btn-small btn-success">Copy Link</button>
        </div>
    </div>
    
    <!-- Active Tasks -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Active Tasks</h3>
        </div>
        
        <?php if (!empty($activeTasks)): ?>
            <?php foreach ($activeTasks as $task): ?>
            <div class="task-card <?php echo $task['status']; ?>">
                <div class="task-header">
                    <div>
                        <div class="task-title"><?php echo htmlspecialchars($task['title']); ?></div>
                        <div class="task-points"><?php echo formatCurrency($task['points']); ?></div>
                    </div>
                </div>
                
                <div class="task-description">
                    <?php echo nl2br(htmlspecialchars($task['description'])); ?>
                </div>
                
                <?php if ($task['media_link'] || $task['media_file']): ?>
                <div class="mb-3">
                    <?php if ($task['media_link']): ?>
                        <a href="<?php echo htmlspecialchars($task['media_link']); ?>" target="_blank" class="btn btn-small">View Resource</a>
                    <?php endif; ?>
                    <?php if ($task['media_file']): ?>
                        <a href="../<?php echo htmlspecialchars($task['media_file']); ?>" target="_blank" class="btn btn-small">Download File</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($task['status'] === 'pending'): ?>
                    <div class="task-timer" data-timer="<?php echo $task['accept_deadline']; ?>">
                        Time to accept: Calculating...
                    </div>
                    
                    <div class="task-actions">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="action" value="accept">
                            <input type="hidden" name="task_assignment_id" value="<?php echo $task['id']; ?>">
                            <button type="submit" class="btn btn-success">Accept Task</button>
                        </form>
                        
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="task_assignment_id" value="<?php echo $task['id']; ?>">
                            <button type="submit" class="btn btn-danger" data-confirm="Are you sure you want to reject this task?">Reject Task</button>
                        </form>
                    </div>
                    
                <?php elseif ($task['status'] === 'accepted'): ?>
                    <div class="task-timer warning" data-timer="<?php echo $task['submit_deadline']; ?>">
                        Time to submit: Calculating...
                    </div>
                    
                    <div class="task-actions">
                        <a href="submit_task.php?id=<?php echo $task['id']; ?>" class="btn btn-success">Submit Task</a>
                    </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-gray-600 text-center py-4">No active tasks available.</p>
        <?php endif; ?>
    </div>
    
    <!-- Completed Tasks -->
    <?php if (!empty($completedTasks)): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Completed Tasks</h3>
        </div>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Points</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($completedTasks as $task): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($task['title']); ?></td>
                        <td><?php echo formatCurrency($task['points']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $task['status']; ?>">
                                <?php echo ucfirst($task['status']); ?>
                            </span>
                        </td>
                        <td><?php echo timeAgo($task['updated_at']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Rejected/Missed Tasks -->
    <?php if (!empty($rejectedTasks)): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Rejected/Missed Tasks</h3>
        </div>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Points</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rejectedTasks as $task): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($task['title']); ?></td>
                        <td><?php echo formatCurrency($task['points']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $task['status']; ?>">
                                <?php echo ucfirst($task['status']); ?>
                            </span>
                        </td>
                        <td><?php echo timeAgo($task['updated_at']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Referral Information -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Referral Information</h3>
        </div>
        
        <div class="grid grid-2">
            <div>
                <h4>Your Referral Statistics</h4>
                <p><strong>Total Referrals:</strong> <?php echo $totalReferrals; ?></p>
                <p><strong>Referral Earnings:</strong> <?php echo formatCurrency($referralEarnings); ?></p>
                <p><strong>Your Referral Code:</strong> <code><?php echo $userInfo['referral_code'] ?? 'N/A'; ?></code></p>
            </div>
            
            <div>
                <h4>Commission Structure</h4>
                <div class="text-sm text-gray-600">
                    <p>Level 1: 10% commission</p>
                    <p>Level 2: 5% commission</p>
                    <p>Level 3: 4% commission</p>
                    <p>Level 4: 3% commission</p>
                    <p>Level 5: 2% commission</p>
                </div>
            </div>
        </div>
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

.justify-center {
    justify-content: center;
}

.mb-6 {
    margin-bottom: 1.5rem;
}

.mb-3 {
    margin-bottom: 0.75rem;
}

.gap-2 {
    gap: 0.5rem;
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

.badge-reassigned {
    background: #fef3c7;
    color: #92400e;
}

code {
    font-family: 'Courier New', monospace;
    font-size: 0.875rem;
}
</style>

<?php require_once '../includes/footer.php'; ?>
