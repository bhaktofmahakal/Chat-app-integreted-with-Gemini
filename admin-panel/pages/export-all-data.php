<?php
session_start();
require_once '../includes/config.php';
requireLogin();

$format = $_GET['format'] ?? 'json';
$conn = getDBConnection();

if (!$conn) {
    die('Database connection failed');
}

try {
    // Get all data
    $exportData = [
        'export_info' => [
            'exported_at' => date('Y-m-d H:i:s'),
            'exported_by' => getAdminInfo()['username'],
            'format' => $format
        ]
    ];
    
    // Get all sessions
    $sessions = $conn->query("SELECT * FROM chat_sessions ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
    $exportData['sessions'] = $sessions;
    
    // Get all messages
    $messages = $conn->query("SELECT * FROM chat_messages ORDER BY timestamp DESC")->fetch_all(MYSQLI_ASSOC);
    $exportData['messages'] = $messages;
    
    // Get all admins (without passwords)
    $admins = $conn->query("SELECT id, username, email, created_at FROM admins ORDER BY created_at ASC")->fetch_all(MYSQLI_ASSOC);
    $exportData['admins'] = $admins;
    
    // Get API stats
    $apiStats = $conn->query("SELECT * FROM api_stats ORDER BY date DESC")->fetch_all(MYSQLI_ASSOC);
    $exportData['api_stats'] = $apiStats;
    
    // Get statistics
    $exportData['statistics'] = [
        'total_sessions' => count($sessions),
        'total_messages' => count($messages),
        'total_admins' => count($admins),
        'unique_users' => $conn->query("SELECT COUNT(DISTINCT user_ip) as count FROM chat_sessions")->fetch_assoc()['count'],
        'messages_with_images' => $conn->query("SELECT COUNT(*) as count FROM chat_messages WHERE has_image = 1")->fetch_assoc()['count']
    ];
    
    $filename = 'chat_app_backup_' . date('Y-m-d_H-i-s');
    
    if ($format === 'csv') {
        // CSV format - create a ZIP file with multiple CSV files
        $zipFile = tempnam(sys_get_temp_dir(), 'chat_backup_') . '.zip';
        $zip = new ZipArchive();
        
        if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
            // Sessions CSV
            $csv = fopen('php://temp', 'w');
            fputcsv($csv, ['ID', 'Session ID', 'User IP', 'Title', 'Total Messages', 'Created At', 'Updated At']);
            foreach ($sessions as $session) {
                fputcsv($csv, [
                    $session['id'],
                    $session['session_id'],
                    $session['user_ip'],
                    $session['title'],
                    $session['total_messages'],
                    $session['created_at'],
                    $session['updated_at']
                ]);
            }
            rewind($csv);
            $zip->addFromString('sessions.csv', stream_get_contents($csv));
            fclose($csv);
            
            // Messages CSV
            $csv = fopen('php://temp', 'w');
            fputcsv($csv, ['ID', 'Session ID', 'Role', 'Content', 'Message Order', 'Has Image', 'Timestamp']);
            foreach ($messages as $message) {
                fputcsv($csv, [
                    $message['id'],
                    $message['session_id'],
                    $message['role'],
                    substr($message['content'], 0, 1000), // Limit content length
                    $message['message_order'],
                    $message['has_image'] ? 'Yes' : 'No',
                    $message['timestamp']
                ]);
            }
            rewind($csv);
            $zip->addFromString('messages.csv', stream_get_contents($csv));
            fclose($csv);
            
            // API Stats CSV
            $csv = fopen('php://temp', 'w');
            fputcsv($csv, ['Date', 'Total Requests', 'Successful Requests', 'Failed Requests']);
            foreach ($apiStats as $stat) {
                fputcsv($csv, [
                    $stat['date'],
                    $stat['total_requests'],
                    $stat['successful_requests'],
                    $stat['failed_requests']
                ]);
            }
            rewind($csv);
            $zip->addFromString('api_stats.csv', stream_get_contents($csv));
            fclose($csv);
            
            // Statistics CSV
            $csv = fopen('php://temp', 'w');
            fputcsv($csv, ['Metric', 'Value']);
            foreach ($exportData['statistics'] as $key => $value) {
                fputcsv($csv, [str_replace('_', ' ', ucfirst($key)), $value]);
            }
            rewind($csv);
            $zip->addFromString('statistics.csv', stream_get_contents($csv));
            fclose($csv);
            
            $zip->close();
            
            // Send ZIP file
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $filename . '.zip"');
            header('Content-Length: ' . filesize($zipFile));
            readfile($zipFile);
            unlink($zipFile);
        } else {
            throw new Exception('Failed to create ZIP file');
        }
        
    } else {
        // Default JSON format
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '.json"');
        
        echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    // Handle errors
    header('Content-Type: text/html');
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Export Error</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 50px; }
            .error { background: #fee; border: 1px solid #fcc; padding: 20px; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class="error">
            <h2>Export Failed</h2>
            <p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>
            <p><a href="../settings.php">← Back to Settings</a></p>
        </div>
    </body>
    </html>';
}
?>