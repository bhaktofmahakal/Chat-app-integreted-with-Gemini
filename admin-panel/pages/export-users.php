<?php
session_start();
require_once '../includes/config.php';
requireLogin();

$format = $_GET['format'] ?? 'csv';

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

$filename = 'users_export_' . date('Y-m-d');

if ($format === 'json') {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '.json"');
    
    $exportData = [
        'users' => $users,
        'total_users' => count($users),
        'exported_at' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($exportData, JSON_PRETTY_PRINT);
    
} else {
    // Default CSV format
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['User IP', 'Total Sessions', 'Total Messages', 'First Activity', 'Last Activity', 'Status']);
    
    foreach ($users as $user) {
        $isActive = date('Y-m-d', strtotime($user['last_activity'])) === date('Y-m-d') ? 'Active' : 'Inactive';
        
        fputcsv($output, [
            $user['user_ip'],
            $user['total_sessions'],
            $user['total_messages'],
            $user['first_activity'],
            $user['last_activity'],
            $isActive
        ]);
    }
    fclose($output);
}
?>