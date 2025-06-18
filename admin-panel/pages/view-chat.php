<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/config.php';
requireLogin();

$sessionId = $_GET['session'] ?? '';
if (empty($sessionId)) {
    if (!headers_sent()) {
        header('Location: ../chats.php');
    } else {
        echo '<script>window.location.href="../chats.php";</script>';
    }
    exit;
}

// Include header after login check
require_once '../includes/header.php';

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
?>

<script>
    setPageTitle('View Chat', 'Session: <?php echo htmlspecialchars(substr($sessionId, 0, 20)); ?>...');
</script>

<!-- Back Button -->
<div class="mb-6">
    <a href="../chats.php" class="inline-flex items-center text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-2"></i>
        Back to Chat Sessions
    </a>
</div>

<!-- Session Info -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-6 border-b">
        <h3 class="text-lg font-semibold text-gray-900">Session Information</h3>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Session ID</label>
                <p class="font-mono text-sm bg-gray-100 p-2 rounded"><?php echo htmlspecialchars($sessionId); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">User IP</label>
                <p class="text-sm"><?php echo htmlspecialchars($session['user_ip']); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Title</label>
                <p class="text-sm"><?php echo htmlspecialchars($session['title']); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Created</label>
                <p class="text-sm"><?php echo formatDate($session['created_at']); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Last Updated</label>
                <p class="text-sm"><?php echo formatDate($session['updated_at']); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Total Messages</label>
                <p class="text-sm"><?php echo $session['total_messages']; ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="mb-6 flex items-center space-x-4">
    <div class="relative">
        <button onclick="toggleExportMenu()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
            <i class="fas fa-download mr-2"></i>Export Chat
        </button>
        <div id="export-menu" class="hidden absolute top-full left-0 mt-2 w-32 bg-white border rounded-lg shadow-lg z-10">
            <a href="export-chat.php?session=<?php echo urlencode($sessionId); ?>&format=txt" 
               class="block px-4 py-2 text-sm hover:bg-gray-50">TXT</a>
            <a href="export-chat.php?session=<?php echo urlencode($sessionId); ?>&format=csv" 
               class="block px-4 py-2 text-sm hover:bg-gray-50">CSV</a>
            <a href="export-chat.php?session=<?php echo urlencode($sessionId); ?>&format=json" 
               class="block px-4 py-2 text-sm hover:bg-gray-50">JSON</a>
        </div>
    </div>
    <button onclick="deleteChat()" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
        <i class="fas fa-trash mr-2"></i>Delete Chat
    </button>
</div>

<!-- Chat Messages -->
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <h3 class="text-lg font-semibold text-gray-900">Chat Messages</h3>
    </div>
    <div class="p-6">
        <?php if (empty($messages)): ?>
            <div class="text-center py-12">
                <i class="fas fa-inbox text-gray-400 text-4xl mb-4"></i>
                <p class="text-gray-500">No messages found for this session.</p>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($messages as $message): ?>
                    <div class="flex <?php echo $message['role'] === 'user' ? 'justify-end' : 'justify-start'; ?>">
                        <div class="max-w-3xl">
                            <div class="flex items-center mb-2 <?php echo $message['role'] === 'user' ? 'justify-end' : 'justify-start'; ?>">
                                <div class="flex items-center space-x-2">
                                    <?php if ($message['role'] === 'user'): ?>
                                        <span class="text-sm text-gray-500"><?php echo formatDate($message['timestamp'], 'M j, H:i:s'); ?></span>
                                        <span class="text-sm font-medium text-blue-600">
                                            <i class="fas fa-user mr-1"></i>User
                                        </span>
                                    <?php else: ?>
                                        <span class="text-sm font-medium text-green-600">
                                            <i class="fas fa-robot mr-1"></i>Assistant
                                        </span>
                                        <span class="text-sm text-gray-500"><?php echo formatDate($message['timestamp'], 'M j, H:i:s'); ?></span>
                                    <?php endif; ?>
                                    <span class="text-xs text-gray-400">#<?php echo $message['message_order']; ?></span>
                                </div>
                            </div>
                            
                            <div class="<?php echo $message['role'] === 'user' ? 'bg-blue-100 text-blue-900' : 'bg-gray-100 text-gray-900'; ?> rounded-lg p-4">
                                <?php if ($message['has_image'] && $message['image_data']): ?>
                                    <div class="mb-3">
                                        <img src="<?php echo htmlspecialchars($message['image_data']); ?>" 
                                             alt="User uploaded image" 
                                             class="max-w-sm rounded-lg shadow border">
                                    </div>
                                <?php endif; ?>
                                
                                <div class="whitespace-pre-wrap text-sm leading-relaxed">
                                    <?php echo htmlspecialchars($message['content']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function toggleExportMenu() {
        const menu = document.getElementById('export-menu');
        menu.classList.toggle('hidden');
    }

    // Close export menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('button[onclick="toggleExportMenu()"]')) {
            document.getElementById('export-menu').classList.add('hidden');
        }
    });

    function deleteChat() {
        if (confirmDelete('Are you sure you want to delete this chat session? This action cannot be undone.')) {
            const sessionId = '<?php echo $sessionId; ?>';
            
            fetch('delete-session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({session_id: sessionId})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Session deleted successfully');
                    window.location.href = '../chats.php';
                } else {
                    showAlert('Error deleting session: ' + data.error, 'error');
                }
            })
            .catch(error => {
                showAlert('Error deleting session', 'error');
            });
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>