<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/config.php';
requireLogin();
require_once 'includes/header.php';

// Get dashboard data
$stats = getDashboardStats();
$recentSessions = getRecentSessions(5);
$apiStats = getAPIStats(7);
?>

<script>
    setPageTitle('Dashboard', 'Overview of your chat application');
</script>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <i class="fas fa-comments text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Sessions</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['total_sessions']); ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600">
                <i class="fas fa-envelope text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Messages</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['total_messages']); ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                <i class="fas fa-calendar-day text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Messages Today</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['messages_today']); ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                <i class="fas fa-users text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Unique Users</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['unique_users']); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Additional Stats Row -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                <i class="fas fa-clock text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Active Sessions (24h)</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['active_sessions']); ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-pink-100 text-pink-600">
                <i class="fas fa-chart-line text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Messages This Week</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['messages_week']); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Charts and Tables Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- API Usage Chart -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-900">API Usage (Last 7 Days)</h3>
        </div>
        <div class="p-6">
            <canvas id="apiChart" height="300"></canvas>
        </div>
    </div>

    <!-- Recent Sessions -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Recent Sessions</h3>
                <a href="chats.php" class="text-blue-600 hover:text-blue-800 text-sm">View All</a>
            </div>
        </div>
        <div class="p-6">
            <?php if (empty($recentSessions)): ?>
                <p class="text-gray-500 text-center py-8">No sessions found</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($recentSessions as $session): ?>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">
                                    <?php echo htmlspecialchars($session['title']); ?>
                                </p>
                                <p class="text-sm text-gray-500">
                                    <?php echo $session['message_count']; ?> messages • 
                                    <?php echo formatDate($session['updated_at'], 'M j, H:i'); ?>
                                </p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                                    <?php echo substr($session['user_ip'], 0, 12); ?>...
                                </span>
                                <a href="pages/view-chat.php?session=<?php echo urlencode($session['session_id']); ?>" 
                                   class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="chats.php" class="flex items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition">
                <i class="fas fa-comments text-blue-600 text-xl mr-3"></i>
                <div>
                    <p class="font-medium text-gray-900">Manage Chats</p>
                    <p class="text-sm text-gray-500">View and manage chat sessions</p>
                </div>
            </a>
            
            <a href="users.php" class="flex items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition">
                <i class="fas fa-users text-green-600 text-xl mr-3"></i>
                <div>
                    <p class="font-medium text-gray-900">User Management</p>
                    <p class="text-sm text-gray-500">Monitor user activity</p>
                </div>
            </a>
            
            <a href="analytics.php" class="flex items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition">
                <i class="fas fa-chart-bar text-purple-600 text-xl mr-3"></i>
                <div>
                    <p class="font-medium text-gray-900">Analytics</p>
                    <p class="text-sm text-gray-500">View detailed statistics</p>
                </div>
            </a>
        </div>
    </div>
</div>

<script>
    // API Usage Chart
    const ctx = document.getElementById('apiChart').getContext('2d');
    const apiData = <?php echo json_encode(array_reverse($apiStats)); ?>;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: apiData.map(item => item.date),
            datasets: [{
                label: 'Total Requests',
                data: apiData.map(item => item.total_requests),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Successful',
                data: apiData.map(item => item.successful_requests),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                }
            }
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>