<?php
session_start();
require_once '../includes/config.php';
requireLogin();

$format = $_GET['format'] ?? 'csv';
$conn = getDBConnection();

// Get analytics data
$apiStats = getAPIStats(30);
$stats = getDashboardStats();

// Get messages by day (last 30 days)
$messagesQuery = "SELECT DATE(timestamp) as date, COUNT(*) as count 
                  FROM chat_messages 
                  WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
                  GROUP BY DATE(timestamp) 
                  ORDER BY date ASC";
$messagesByDay = $conn->query($messagesQuery)->fetch_all(MYSQLI_ASSOC);

// Get sessions by day (last 30 days)
$sessionsQuery = "SELECT DATE(created_at) as date, COUNT(*) as count 
                  FROM chat_sessions 
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
                  GROUP BY DATE(created_at) 
                  ORDER BY date ASC";
$sessionsByDay = $conn->query($sessionsQuery)->fetch_all(MYSQLI_ASSOC);

$filename = 'analytics_report_' . date('Y-m-d');

if ($format === 'json') {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '.json"');
    
    $exportData = [
        'summary' => $stats,
        'messages_by_day' => $messagesByDay,
        'sessions_by_day' => $sessionsByDay,
        'api_stats' => $apiStats,
        'exported_at' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($exportData, JSON_PRETTY_PRINT);
    
} else {
    // Default CSV format
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Summary
    fputcsv($output, ['Analytics Report - Generated on ' . date('Y-m-d H:i:s')]);
    fputcsv($output, []);
    fputcsv($output, ['SUMMARY']);
    fputcsv($output, ['Metric', 'Value']);
    fputcsv($output, ['Total Sessions', $stats['total_sessions']]);
    fputcsv($output, ['Total Messages', $stats['total_messages']]);
    fputcsv($output, ['Messages Today', $stats['messages_today']]);
    fputcsv($output, ['Messages This Week', $stats['messages_week']]);
    fputcsv($output, ['Unique Users', $stats['unique_users']]);
    fputcsv($output, ['Active Sessions (24h)', $stats['active_sessions']]);
    fputcsv($output, []);
    
    // Messages by day
    fputcsv($output, ['MESSAGES BY DAY (Last 30 Days)']);
    fputcsv($output, ['Date', 'Messages']);
    foreach ($messagesByDay as $day) {
        fputcsv($output, [$day['date'], $day['count']]);
    }
    fputcsv($output, []);
    
    // Sessions by day
    fputcsv($output, ['SESSIONS BY DAY (Last 30 Days)']);
    fputcsv($output, ['Date', 'Sessions']);
    foreach ($sessionsByDay as $day) {
        fputcsv($output, [$day['date'], $day['count']]);
    }
    fputcsv($output, []);
    
    // API Stats
    fputcsv($output, ['API STATISTICS (Last 30 Days)']);
    fputcsv($output, ['Date', 'Total Requests', 'Successful', 'Failed', 'Success Rate']);
    foreach ($apiStats as $stat) {
        $successRate = $stat['total_requests'] > 0 ? round(($stat['successful_requests'] / $stat['total_requests']) * 100, 2) : 0;
        fputcsv($output, [
            $stat['date'],
            $stat['total_requests'],
            $stat['successful_requests'],
            $stat['failed_requests'],
            $successRate . '%'
        ]);
    }
    
    fclose($output);
}
?>