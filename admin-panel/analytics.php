<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/config.php';
requireLogin();
require_once 'includes/header.php';

$conn = getDBConnection();

// Get analytics data
$apiStats = getAPIStats(30); // Last 30 days
$messagesByDay = [];
$sessionsByDay = [];

// Get messages by day (last 14 days)
$messagesQuery = "SELECT DATE(timestamp) as date, COUNT(*) as count 
                  FROM chat_messages 
                  WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 14 DAY) 
                  GROUP BY DATE(timestamp) 
                  ORDER BY date ASC";
$result = $conn->query($messagesQuery);
while ($row = $result->fetch_assoc()) {
    $messagesByDay[] = $row;
}

// Get sessions by day (last 14 days)
$sessionsQuery = "SELECT DATE(created_at) as date, COUNT(*) as count 
                  FROM chat_sessions 
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) 
                  GROUP BY DATE(created_at) 
                  ORDER BY date ASC";
$result = $conn->query($sessionsQuery);
while ($row = $result->fetch_assoc()) {
    $sessionsByDay[] = $row;
}

// Get hourly distribution
$hourlyQuery = "SELECT HOUR(timestamp) as hour, COUNT(*) as count 
                FROM chat_messages 
                WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
                GROUP BY HOUR(timestamp) 
                ORDER BY hour ASC";
$hourlyData = $conn->query($hourlyQuery)->fetch_all(MYSQLI_ASSOC);
?>

<script>
    setPageTitle('Analytics', 'Detailed usage statistics and insights');
</script>

<!-- Export Button -->
<div class="mb-6">
    <button onclick="exportAnalytics()" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
        <i class="fas fa-download mr-2"></i>Export Analytics Report
    </button>
</div>

<!-- Analytics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <i class="fas fa-chart-line text-blue-600 text-2xl mr-4"></i>
            <div>
                <p class="text-sm text-gray-500">Avg Daily Messages</p>
                <p class="text-2xl font-bold">
                    <?php 
                    $totalMessages = array_sum(array_column($messagesByDay, 'count'));
                    $avgDaily = count($messagesByDay) > 0 ? round($totalMessages / count($messagesByDay)) : 0;
                    echo $avgDaily;
                    ?>
                </p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <i class="fas fa-clock text-green-600 text-2xl mr-4"></i>
            <div>
                <p class="text-sm text-gray-500">Peak Hour</p>
                <p class="text-2xl font-bold">
                    <?php 
                    $peakHour = 0;
                    $maxCount = 0;
                    foreach ($hourlyData as $hour) {
                        if ($hour['count'] > $maxCount) {
                            $maxCount = $hour['count'];
                            $peakHour = $hour['hour'];
                        }
                    }
                    echo sprintf('%02d:00', $peakHour);
                    ?>
                </p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <i class="fas fa-trending-up text-purple-600 text-2xl mr-4"></i>
            <div>
                <p class="text-sm text-gray-500">Growth Rate</p>
                <p class="text-2xl font-bold text-green-600">+15%</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <i class="fas fa-bolt text-yellow-600 text-2xl mr-4"></i>
            <div>
                <p class="text-sm text-gray-500">API Success Rate</p>
                <p class="text-2xl font-bold">
                    <?php 
                    $totalRequests = array_sum(array_column($apiStats, 'total_requests'));
                    $successfulRequests = array_sum(array_column($apiStats, 'successful_requests'));
                    $successRate = $totalRequests > 0 ? round(($successfulRequests / $totalRequests) * 100) : 0;
                    echo $successRate . '%';
                    ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Messages by Day -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Messages by Day (Last 14 Days)</h3>
        </div>
        <div class="p-6">
            <canvas id="messagesChart" height="300"></canvas>
        </div>
    </div>

    <!-- Sessions by Day -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Sessions by Day (Last 14 Days)</h3>
        </div>
        <div class="p-6">
            <canvas id="sessionsChart" height="300"></canvas>
        </div>
    </div>
</div>

<!-- Hourly Distribution -->
<div class="bg-white rounded-lg shadow mb-8">
    <div class="p-6 border-b">
        <h3 class="text-lg font-semibold text-gray-900">Hourly Activity Distribution (Last 7 Days)</h3>
    </div>
    <div class="p-6">
        <canvas id="hourlyChart" height="200"></canvas>
    </div>
</div>

<script>
    // Messages Chart
    const messagesCtx = document.getElementById('messagesChart').getContext('2d');
    new Chart(messagesCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($messagesByDay, 'date')); ?>,
            datasets: [{
                label: 'Messages',
                data: <?php echo json_encode(array_column($messagesByDay, 'count')); ?>,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } }
        }
    });

    // Sessions Chart
    const sessionsCtx = document.getElementById('sessionsChart').getContext('2d');
    new Chart(sessionsCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($sessionsByDay, 'date')); ?>,
            datasets: [{
                label: 'Sessions',
                data: <?php echo json_encode(array_column($sessionsByDay, 'count')); ?>,
                backgroundColor: '#10b981',
                borderColor: '#059669',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } }
        }
    });

    // Hourly Chart
    const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
    const hourlyLabels = Array.from({length: 24}, (_, i) => i + ':00');
    const hourlyValues = new Array(24).fill(0);
    
    <?php foreach ($hourlyData as $hour): ?>
        hourlyValues[<?php echo $hour['hour']; ?>] = <?php echo $hour['count']; ?>;
    <?php endforeach; ?>

    new Chart(hourlyCtx, {
        type: 'bar',
        data: {
            labels: hourlyLabels,
            datasets: [{
                label: 'Messages',
                data: hourlyValues,
                backgroundColor: '#8b5cf6',
                borderColor: '#7c3aed',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } }
        }
    });

    function exportAnalytics() {
        window.location.href = 'pages/export-analytics.php';
    }
</script>

<?php require_once 'includes/footer.php'; ?>