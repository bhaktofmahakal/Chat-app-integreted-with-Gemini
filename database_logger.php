<?php
// Database Chat Logger
require_once 'config.php';

class ChatLogger {
    private $conn;
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    public function logMessage($sessionId, $role, $content, $hasImage = false, $imageData = null, $userIP = null) {
        if (!$this->conn) return false;
        
        try {
            // Get or create session
            $chatSessionId = $this->getOrCreateSession($sessionId, $userIP);
            
            // Get message order for this session
            $orderQuery = "SELECT COALESCE(MAX(message_order), 0) + 1 as next_order FROM chat_messages WHERE session_id = ?";
            $orderStmt = $this->conn->prepare($orderQuery);
            $orderStmt->bind_param("s", $sessionId);
            $orderStmt->execute();
            $orderResult = $orderStmt->get_result();
            $messageOrder = $orderResult->fetch_assoc()['next_order'];
            
            // Insert message
            $query = "INSERT INTO chat_messages (session_id, role, content, has_image, image_data, user_ip, message_order) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("sssissi", $sessionId, $role, $content, $hasImage, $imageData, $userIP, $messageOrder);
            
            $result = $stmt->execute();
            
            if ($result) {
                // Update session message count
                $updateQuery = "UPDATE chat_sessions SET total_messages = total_messages + 1, updated_at = NOW() WHERE session_id = ?";
                $updateStmt = $this->conn->prepare($updateQuery);
                $updateStmt->bind_param("s", $sessionId);
                $updateStmt->execute();
                
                error_log("[ChatLogger] Message saved: Session=$sessionId, Role=$role, Length=" . strlen($content));
                return $stmt->insert_id;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("[ChatLogger] Error: " . $e->getMessage());
            return false;
        }
    }
    
    private function getOrCreateSession($sessionId, $userIP) {
        try {
            // Check if session exists
            $checkQuery = "SELECT id FROM chat_sessions WHERE session_id = ?";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bind_param("s", $sessionId);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            if ($result->num_rows > 0) {
                return $result->fetch_assoc()['id'];
            }
            
            // Create new session
            $insertQuery = "INSERT INTO chat_sessions (session_id, user_ip, session_title) VALUES (?, ?, 'New Chat')";
            $insertStmt = $this->conn->prepare($insertQuery);
            $insertStmt->bind_param("ss", $sessionId, $userIP);
            
            if ($insertStmt->execute()) {
                error_log("[ChatLogger] New session created: $sessionId");
                return $insertStmt->insert_id;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("[ChatLogger] Session error: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateSessionTitle($sessionId, $title) {
        if (!$this->conn) return false;
        
        try {
            $query = "UPDATE chat_sessions SET session_title = ? WHERE session_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ss", $title, $sessionId);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("[ChatLogger] Title update error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getSessionMessages($sessionId) {
        if (!$this->conn) return [];
        
        try {
            $query = "SELECT * FROM chat_messages WHERE session_id = ? ORDER BY message_order ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $sessionId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $messages = [];
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }
            
            return $messages;
        } catch (Exception $e) {
            error_log("[ChatLogger] Get messages error: " . $e->getMessage());
            return [];
        }
    }
    
    public function logAPIUsage($isSuccessful = true) {
        if (!$this->conn) return false;
        
        try {
            $date = date('Y-m-d');
            $query = "INSERT INTO api_usage_stats (date, total_requests, successful_requests, failed_requests) 
                     VALUES (?, 1, ?, ?) 
                     ON DUPLICATE KEY UPDATE 
                     total_requests = total_requests + 1,
                     successful_requests = successful_requests + ?,
                     failed_requests = failed_requests + ?";
            
            $success = $isSuccessful ? 1 : 0;
            $failure = $isSuccessful ? 0 : 1;
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("siiii", $date, $success, $failure, $success, $failure);
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("[ChatLogger] API stats error: " . $e->getMessage());
            return false;
        }
    }
}

// Helper function to get unique session ID
function getSessionId() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['chat_session_id'])) {
        $_SESSION['chat_session_id'] = 'chat_' . uniqid() . '_' . time();
    }
    
    return $_SESSION['chat_session_id'];
}
?>