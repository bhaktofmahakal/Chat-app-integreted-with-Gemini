<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/config.php';
requireLogin();
require_once 'includes/header.php';

$conn = getDBConnection();
$admin = getAdminInfo();

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        
        if (empty($username) || empty($email)) {
            $error = "Username and email are required.";
        } else {
            // Verify current password if new password is provided
            if (!empty($newPassword)) {
                $stmt = $conn->prepare("SELECT password FROM admins WHERE id = ?");
                $stmt->bind_param("i", $admin['id']);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                
                if (!password_verify($currentPassword, $result['password']) && 
                    $currentPassword !== $result['password']) {
                    $error = "Current password is incorrect.";
                } else {
                    // Update with new password
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE admins SET username = ?, email = ?, password = ? WHERE id = ?");
                    $stmt->bind_param("sssi", $username, $email, $hashedPassword, $admin['id']);
                }
            } else {
                // Update without password change
                $stmt = $conn->prepare("UPDATE admins SET username = ?, email = ? WHERE id = ?");
                $stmt->bind_param("ssi", $username, $email, $admin['id']);
            }
            
            if (!isset($error) && $stmt->execute()) {
                $_SESSION['admin_username'] = $username;
                $_SESSION['admin_email'] = $email;
                $success = "Profile updated successfully!";
            } else {
                $error = "Failed to update profile.";
            }
        }
    }
    
    if ($action === 'cleanup_data') {
        $days = intval($_POST['cleanup_days'] ?? 30);
        if ($days < 1) $days = 30;
        
        try {
            $conn->begin_transaction();
            
            // Delete old messages and sessions
            $stmt = $conn->prepare("DELETE cm FROM chat_messages cm 
                                   JOIN chat_sessions cs ON cm.session_id = cs.session_id 
                                   WHERE cs.created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
            $stmt->bind_param("i", $days);
            $stmt->execute();
            $deletedMessages = $stmt->affected_rows;
            
            $stmt = $conn->prepare("DELETE FROM chat_sessions WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
            $stmt->bind_param("i", $days);
            $stmt->execute();
            $deletedSessions = $stmt->affected_rows;
            
            // Delete old API stats
            $stmt = $conn->prepare("DELETE FROM api_stats WHERE date < DATE_SUB(CURDATE(), INTERVAL ? DAY)");
            $stmt->bind_param("i", $days);
            $stmt->execute();
            $deletedStats = $stmt->affected_rows;
            
            $conn->commit();
            $success = "Cleanup completed! Deleted $deletedSessions sessions, $deletedMessages messages, and $deletedStats API stats records.";
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Cleanup failed: " . $e->getMessage();
        }
    }
}

// Get current admin info
$stmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->bind_param("i", $admin['id']);
$stmt->execute();
$adminData = $stmt->get_result()->fetch_assoc();

// Get system statistics
$systemStats = [
    'total_sessions' => $conn->query("SELECT COUNT(*) as count FROM chat_sessions")->fetch_assoc()['count'],
    'total_messages' => $conn->query("SELECT COUNT(*) as count FROM chat_messages")->fetch_assoc()['count'],
    'total_users' => $conn->query("SELECT COUNT(DISTINCT user_ip) as count FROM chat_sessions")->fetch_assoc()['count'],
    'db_size' => 'N/A' // We'll calculate this
];

// Get database size
try {
    $result = $conn->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb 
                           FROM information_schema.tables 
                           WHERE table_schema = 'chat_app'");
    if ($result) {
        $systemStats['db_size'] = $result->fetch_assoc()['size_mb'] . ' MB';
    }
} catch (Exception $e) {
    // Ignore if we can't get DB size
}
?>

<script>
    setPageTitle('Settings', 'System configuration and maintenance');
</script>

<?php if (isset($success)): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        <div class="flex">
            <i class="fas fa-check-circle mr-2 mt-0.5"></i>
            <span><?php echo htmlspecialchars($success); ?></span>
        </div>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        <div class="flex">
            <i class="fas fa-exclamation-circle mr-2 mt-0.5"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
    </div>
<?php endif; ?>

<!-- System Overview -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <i class="fas fa-database text-blue-600 text-2xl mr-4"></i>
            <div>
                <p class="text-sm text-gray-500">Database Size</p>
                <p class="text-2xl font-bold"><?php echo $systemStats['db_size']; ?></p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <i class="fas fa-comments text-green-600 text-2xl mr-4"></i>
            <div>
                <p class="text-sm text-gray-500">Total Sessions</p>
                <p class="text-2xl font-bold"><?php echo number_format($systemStats['total_sessions']); ?></p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <i class="fas fa-envelope text-purple-600 text-2xl mr-4"></i>
            <div>
                <p class="text-sm text-gray-500">Total Messages</p>
                <p class="text-2xl font-bold"><?php echo number_format($systemStats['total_messages']); ?></p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <i class="fas fa-users text-yellow-600 text-2xl mr-4"></i>
            <div>
                <p class="text-sm text-gray-500">Total Users</p>
                <p class="text-2xl font-bold"><?php echo number_format($systemStats['total_users']); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Admin Profile Settings -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-user-cog mr-2"></i>Admin Profile
            </h3>
        </div>
        <div class="p-6">
            <form method="POST" class="space-y-6">
                <input type="hidden" name="action" value="update_profile">
                
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-2"></i>Username
                    </label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo htmlspecialchars($adminData['username']); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           required>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2"></i>Email
                    </label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($adminData['email']); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           required>
                </div>

                <div class="border-t pt-6">
                    <h4 class="text-md font-semibold text-gray-900 mb-4">Change Password (Optional)</h4>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                                Current Password
                            </label>
                            <input type="password" id="current_password" name="current_password"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                                New Password
                            </label>
                            <input type="password" id="new_password" name="new_password"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                    <i class="fas fa-save mr-2"></i>Update Profile
                </button>
            </form>
        </div>
    </div>

    <!-- System Maintenance -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-tools mr-2"></i>System Maintenance
            </h3>
        </div>
        <div class="p-6 space-y-6">
            <!-- Data Cleanup -->
            <div class="border border-gray-200 rounded-lg p-4">
                <h4 class="text-md font-semibold text-gray-900 mb-3">
                    <i class="fas fa-broom mr-2"></i>Data Cleanup
                </h4>
                <p class="text-sm text-gray-600 mb-4">
                    Remove old chat data to free up database space. This will permanently delete sessions and messages older than the specified days.
                </p>
                
                <form method="POST" onsubmit="return confirmCleanup()">
                    <input type="hidden" name="action" value="cleanup_data">
                    <div class="flex items-center space-x-4">
                        <div>
                            <label for="cleanup_days" class="block text-sm font-medium text-gray-700 mb-1">
                                Delete data older than
                            </label>
                            <select name="cleanup_days" id="cleanup_days" 
                                    class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="30">30 days</option>
                                <option value="60">60 days</option>
                                <option value="90">90 days</option>
                                <option value="180">6 months</option>
                                <option value="365">1 year</option>
                            </select>
                        </div>
                        <div class="pt-6">
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                <i class="fas fa-trash-alt mr-2"></i>Clean Up
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Export All Data -->
            <div class="border border-gray-200 rounded-lg p-4">
                <h4 class="text-md font-semibold text-gray-900 mb-3">
                    <i class="fas fa-download mr-2"></i>Data Export
                </h4>
                <p class="text-sm text-gray-600 mb-4">
                    Export all system data for backup purposes.
                </p>
                <div class="flex items-center space-x-2">
                    <button onclick="exportAllData('json')" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        <i class="fas fa-database mr-2"></i>Export JSON
                    </button>
                    <button onclick="exportAllData('csv')" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        <i class="fas fa-file-csv mr-2"></i>Export CSV
                    </button>
                </div>
            </div>

            <!-- System Information -->
            <div class="border border-gray-200 rounded-lg p-4">
                <h4 class="text-md font-semibold text-gray-900 mb-3">
                    <i class="fas fa-info-circle mr-2"></i>System Information
                </h4>
                <div class="text-sm space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">PHP Version:</span>
                        <span class="font-mono"><?php echo PHP_VERSION; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Admin Panel Version:</span>
                        <span class="font-mono"><?php echo ADMIN_VERSION; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Last Login:</span>
                        <span><?php echo date('M j, Y H:i'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmCleanup() {
        const days = document.getElementById('cleanup_days').value;
        return confirm(`Are you sure you want to delete all data older than ${days} days? This action cannot be undone.`);
    }

    function exportAllData(format) {
        if (confirm('This will export all system data. Continue?')) {
            window.location.href = 'pages/export-all-data.php?format=' + format;
        }
    }
</script>

<?php require_once 'includes/footer.php'; ?>