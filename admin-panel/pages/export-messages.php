<?php
session_start();
require_once '../includes/config.php';
requireLogin();

$format = $_GET['format'] ?? 'csv';

// Get filters from URL
$search = $_GET['search'] ?? '';
$role = $_GET['role'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

// Build query (same as in messages.php)
$whereConditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $whereConditions[] = "cm.content LIKE ?";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $types .= 's';
}

if (!empty($role)) {
    $whereConditions[] = "cm.role = ?";
    $params[] = $role;
    $types .= 's';
}

if (!empty($dateFrom)) {
    $whereConditions[] = "cm.timestamp >= ?";
    $params[] = $dateFrom . ' 00:00:00';
    $types .= 's';
}

if (!empty($dateTo)) {
    $whereConditions[] = "cm.timestamp <= ?";
    $params[] = $dateTo . ' 23:59:59';
    $types .= 's';
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Get messages
$conn = getDBConnection();
$query = "SELECT cm.*, cs.user_ip, cs.title as session_title
          FROM chat_messages cm 
          LEFT JOIN chat_sessions cs ON cm.session_id = cs.session_id 
          $whereClause
          ORDER BY cm.timestamp DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$filename = 'messages_export_' . date('Y-m-d');

if ($format === 'json') {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '.json"');
    
    $exportData = [
        'messages' => $messages,
        'total_messages' => count($messages),
        'filters' => [
            'search' => $search,
            'role' => $role,
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ],
        'exported_at' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($exportData, JSON_PRETTY_PRINT);
    
} else {
    // Default CSV format
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // CSV Headers
    fputcsv($output, [
        'Message ID',
        'Session ID', 
        'Role',
        'Content',
        'Message Order',
        'Has Image',
        'Timestamp',
        'User IP',
        'Session Title'
    ]);
    
    foreach ($messages as $message) {
        fputcsv($output, [
            $message['id'],
            $message['session_id'],
            $message['role'],
            $message['content'],
            $message['message_order'],
            $message['has_image'] ? 'Yes' : 'No',
            $message['timestamp'],
            $message['user_ip'] ?? 'N/A',
            $message['session_title'] ?? 'Unknown'
        ]);
    }
    
    fclose($output);
}
?>