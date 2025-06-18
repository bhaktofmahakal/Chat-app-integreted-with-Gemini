<?php
session_start();
require_once '../includes/config.php';
requireLogin();

$sessionId = $_GET['session'] ?? '';
$format = $_GET['format'] ?? 'txt';

if (empty($sessionId)) {
    header('Location: ../chats.php');
    exit;
}

// Get session info
$conn = getDBConnection();
$sessionQuery = "SELECT * FROM chat_sessions WHERE session_id = ?";
$stmt = $conn->prepare($sessionQuery);
$stmt->bind_param("s", $sessionId);
$stmt->execute();
$session = $stmt->get_result()->fetch_assoc();

if (!$session) {
    header('Location: ../chats.php');
    exit;
}

// Get messages
$logger = new ChatLogger();
$messages = $logger->getSessionMessages($sessionId);

$filename = 'chat_' . substr($sessionId, 0, 8) . '_' . date('Y-m-d');

if ($format === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Order', 'Role', 'Content', 'Timestamp', 'Has Image']);
    
    foreach ($messages as $message) {
        fputcsv($output, [
            $message['message_order'],
            $message['role'],
            $message['content'],
            $message['timestamp'],
            $message['has_image'] ? 'Yes' : 'No'
        ]);
    }
    fclose($output);
    
} elseif ($format === 'json') {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '.json"');
    
    $exportData = [
        'session' => $session,
        'messages' => $messages,
        'exported_at' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($exportData, JSON_PRETTY_PRINT);
    
} else {
    // Default TXT format
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . $filename . '.txt"');
    
    echo "Chat Session Export\n";
    echo "==================\n\n";
    echo "Session ID: " . $sessionId . "\n";
    echo "Title: " . $session['title'] . "\n";
    echo "User IP: " . $session['user_ip'] . "\n";
    echo "Created: " . $session['created_at'] . "\n";
    echo "Updated: " . $session['updated_at'] . "\n";
    echo "Total Messages: " . count($messages) . "\n\n";
    echo "Messages:\n";
    echo "=========\n\n";
    
    foreach ($messages as $message) {
        echo "[" . $message['timestamp'] . "] ";
        echo strtoupper($message['role']) . " (#" . $message['message_order'] . "):\n";
        
        if ($message['has_image']) {
            echo "[Image attached]\n";
        }
        
        echo $message['content'] . "\n\n";
        echo str_repeat('-', 50) . "\n\n";
    }
}
?>