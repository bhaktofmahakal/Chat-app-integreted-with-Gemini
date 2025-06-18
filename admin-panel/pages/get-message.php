<?php
session_start();
require_once '../includes/config.php';
requireLogin();

header('Content-Type: application/json');

$messageId = $_GET['id'] ?? '';

if (empty($messageId)) {
    echo json_encode(['success' => false, 'error' => 'Message ID required']);
    exit;
}

try {
    $conn = getDBConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    $stmt = $conn->prepare("SELECT * FROM chat_messages WHERE id = ?");
    $stmt->bind_param("i", $messageId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Message not found']);
        exit;
    }
    
    $message = $result->fetch_assoc();
    echo json_encode(['success' => true, 'message' => $message]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>