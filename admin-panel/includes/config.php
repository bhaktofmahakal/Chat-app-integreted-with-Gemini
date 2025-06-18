<?php
// Admin Panel Configuration
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../database_logger.php';

// Admin Panel Constants
define('ADMIN_TITLE', 'Chat App Admin Panel');
define('ADMIN_VERSION', '1.0.0');
define('RECORDS_PER_PAGE', 20);

// Define admin base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$adminPath = '/chat-app/admin-panel/';
define('ADMIN_BASE_URL', $protocol . $host . $adminPath);

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && 
           $_SESSION['admin_logged_in'] === true &&
           isset($_SESSION['admin_ip']) &&
           $_SESSION['admin_ip'] === $_SERVER['REMOTE_ADDR'];
}

// Redirect if not logged in
function requireLogin() {
    if (!isAdminLoggedIn()) {
        if (!headers_sent()) {
            header('Location: login.php');
        } else {
            echo '<script>window.location.href="login.php";</script>';
        }
        exit;
    }
}

// Get admin info
function getAdminInfo() {
    if (!isAdminLoggedIn()) return null;
    
    return [
        'id' => $_SESSION['admin_id'] ?? 0,
        'username' => $_SESSION['admin_username'] ?? 'Unknown',
        'email' => $_SESSION['admin_email'] ?? ''
    ];
}

// CSRF Protection
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Rate limiting for login attempts
function checkLoginAttempts($ip) {
    $conn = getDBConnection();
    if (!$conn) return true;
    
    $stmt = $conn->prepare("SELECT COUNT(*) as attempts FROM login_attempts 
                           WHERE ip = ? AND timestamp > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['attempts'] < 5; // Max 5 attempts per 15 minutes
}

function logLoginAttempt($ip, $success) {
    $conn = getDBConnection();
    if (!$conn) return;
    
    $stmt = $conn->prepare("INSERT INTO login_attempts (ip, success, timestamp) VALUES (?, ?, NOW())");
    $stmt->bind_param("si", $ip, $success);
    $stmt->execute();
}

// Format date
function formatDate($date, $format = 'M j, Y H:i') {
    return date($format, strtotime($date));
}

// Get statistics
function getDashboardStats() {
    $conn = getDBConnection();
    if (!$conn) return [];
    
    $stats = [];
    
    // Total sessions
    $result = $conn->query("SELECT COUNT(*) as total FROM chat_sessions");
    $stats['total_sessions'] = $result->fetch_assoc()['total'];
    
    // Total messages
    $result = $conn->query("SELECT COUNT(*) as total FROM chat_messages");
    $stats['total_messages'] = $result->fetch_assoc()['total'];
    
    // Messages today
    $result = $conn->query("SELECT COUNT(*) as total FROM chat_messages WHERE DATE(timestamp) = CURDATE()");
    $stats['messages_today'] = $result->fetch_assoc()['total'];
    
    // Messages this week
    $result = $conn->query("SELECT COUNT(*) as total FROM chat_messages WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stats['messages_week'] = $result->fetch_assoc()['total'];
    
    // Unique users
    $result = $conn->query("SELECT COUNT(DISTINCT user_ip) as total FROM chat_sessions");
    $stats['unique_users'] = $result->fetch_assoc()['total'];
    
    // Active sessions (last 24 hours)
    $result = $conn->query("SELECT COUNT(*) as total FROM chat_sessions WHERE updated_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $stats['active_sessions'] = $result->fetch_assoc()['total'];
    
    return $stats;
}

// Get recent sessions
function getRecentSessions($limit = 10) {
    $conn = getDBConnection();
    if (!$conn) return [];
    
    $query = "SELECT cs.*, COUNT(cm.id) as message_count 
              FROM chat_sessions cs 
              LEFT JOIN chat_messages cm ON cs.session_id = cm.session_id 
              GROUP BY cs.id 
              ORDER BY cs.updated_at DESC 
              LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $sessions = [];
    while ($row = $result->fetch_assoc()) {
        $sessions[] = $row;
    }
    
    return $sessions;
}

// Get API usage stats
function getAPIStats($days = 7) {
    $conn = getDBConnection();
    if (!$conn) return [];
    
    $query = "SELECT * FROM api_stats WHERE date >= DATE_SUB(CURDATE(), INTERVAL ? DAY) ORDER BY date DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $days);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $stats = [];
    while ($row = $result->fetch_assoc()) {
        $stats[] = $row;
    }
    
    return $stats;
}
?>