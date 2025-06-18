<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/config.php';
requireLogin();
require_once 'includes/header.php';

// Get user statistics
$conn = getDBConnection();
$usersQuery = "SELECT cs.user_ip, 
                      COUNT(DISTINCT cs.session_id) as total_sessions,
                      COUNT(cm.id) as total_messages,
                      MAX(cs.updated_at) as last_activity,
                      MIN(cs.created_at) as first_activity
               FROM chat_sessions cs
               LEFT JOIN chat_messages cm ON cs.session_id = cm.session_id
               GROUP BY cs.user_ip
               ORDER BY total_messages DESC";

$users = $conn->query($usersQuery)->fetch_all(MYSQLI_ASSOC);
?>

<script>
    setPageTitle('Users', 'User activity and statistics');
</script>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <i class="fas fa-users text-blue-600 text-2xl mr-4"></i>
            <div>
                <p class="text-sm text-gray-500">Total Users</p>
                <p class="text-2xl font-bold"><?php echo count($users); ?></p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <i class="fas fa-user-clock text-green-600 text-2xl mr-4"></i>
            <div>
                <p class="text-sm text-gray-500">Active Today</p>
                <p class="text-2xl font-bold">
                    <?php 
                    $activeToday = array_filter($users, function($user) {
                        return date('Y-m-d', strtotime($user['last_activity'])) === date('Y-m-d');
                    });
                    echo count($activeToday);
                    ?>
                </p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <i class="fas fa-chart-line text-purple-600 text-2xl mr-4"></i>
            <div>
                <p class="text-sm text-gray-500">Avg Messages/User</p>
                <p class="text-2xl font-bold">
                    <?php 
                    $totalMessages = array_sum(array_column($users, 'total_messages'));
                    $avgMessages = count($users) > 0 ? round($totalMessages / count($users)) : 0;
                    echo $avgMessages;
                    ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Export Button -->
<div class="mb-6">
    <button onclick="exportUsers()" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
        <i class="fas fa-download mr-2"></i>Export Users Data
    </button>
</div>

<!-- Users Table -->
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <h3 class="text-lg font-semibold text-gray-900">User Activity</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User IP</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sessions</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Messages</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">First Activity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Activity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($users as $user): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-mono text-sm"><?php echo htmlspecialchars($user['user_ip']); ?></td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                                <?php echo $user['total_sessions']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-sm rounded-full">
                                <?php echo $user['total_messages']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?php echo formatDate($user['first_activity']); ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?php echo formatDate($user['last_activity']); ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php 
                            $isActiveToday = date('Y-m-d', strtotime($user['last_activity'])) === date('Y-m-d');
                            ?>
                            <span class="px-2 py-1 <?php echo $isActiveToday ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?> text-sm rounded-full">
                                <?php echo $isActiveToday ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function exportUsers() {
        window.location.href = 'pages/export-users.php';
    }
</script>

<?php require_once 'includes/footer.php'; ?>