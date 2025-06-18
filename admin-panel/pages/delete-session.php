<?php
session_start();
require_once '../includes/config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$sessionId = $input['session_id'] ?? '';

if (empty($sessionId)) {
    echo json_encode(['success' => false, 'error' => 'Session ID required']);
    exit;
}

try {
    $conn = getDBConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    // Delete messages first (due to foreign key constraints)
    $deleteMessagesQuery = "DELETE FROM chat_messages WHERE session_id = ?";
    $stmt = $conn->prepare($deleteMessagesQuery);
    $stmt->bind_param("s", $sessionId);
    $stmt->execute();
    
    // Delete from chat_history (if exists)
    $deleteHistoryQuery = "DELETE FROM chat_history WHERE user_ip = (SELECT user_ip FROM chat_sessions WHERE session_id = ?)";
    $stmt = $conn->prepare($deleteHistoryQuery);
    $stmt->bind_param("s", $sessionId);
    $stmt->execute();
    
    // Delete session
    $deleteSessionQuery = "DELETE FROM chat_sessions WHERE session_id = ?";
    $stmt = $conn->prepare($deleteSessionQuery);
    $stmt->bind_param("s", $sessionId);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        throw new Exception('Session not found');
    }
    
    // Commit transaction
    $conn->commit();
    
    // Log deletion
    $admin = getAdminInfo();
    error_log("Admin {$admin['username']} deleted session: {$sessionId}");
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>