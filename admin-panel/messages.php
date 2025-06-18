<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/config.php';
requireLogin();
require_once 'includes/header.php';

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = RECORDS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Search and filters
$search = $_GET['search'] ?? '';
$role = $_GET['role'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

// Build query
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

// Get total count
$conn = getDBConnection();
$countQuery = "SELECT COUNT(*) as total FROM chat_messages cm $whereClause";
$countStmt = $conn->prepare($countQuery);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalRecords = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $limit);

// Get messages with session info
$query = "SELECT cm.*, cs.user_ip, cs.title as session_title
          FROM chat_messages cm 
          LEFT JOIN chat_sessions cs ON cm.session_id = cs.session_id 
          $whereClause
          ORDER BY cm.timestamp DESC 
          LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';
$stmt->bind_param($types, ...$params);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get message statistics
$statsQuery = "SELECT 
                 COUNT(*) as total_messages,
                 COUNT(CASE WHEN role = 'user' THEN 1 END) as user_messages,
                 COUNT(CASE WHEN role = 'assistant' THEN 1 END) as assistant_messages,
                 COUNT(CASE WHEN has_image = 1 THEN 1 END) as messages_with_images,
                 COUNT(CASE WHEN DATE(timestamp) = CURDATE() THEN 1 END) as messages_today
               FROM chat_messages";
$stats = $conn->query($statsQuery)->fetch_assoc();
?>

<script>
    setPageTitle('Messages', 'View and manage all chat messages');
</script>

<!-- Filters -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search Content</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Search messages..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                <select name="role" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Roles</option>
                    <option value="user" <?php echo $role === 'user' ? 'selected' : ''; ?>>User</option>
                    <option value="assistant" <?php echo $role === 'assistant' ? 'selected' : ''; ?>>Assistant</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                <input type="date" name="date_from" value="<?php echo htmlspecialchars($dateFrom); ?>" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                <input type="date" name="date_to" value="<?php echo htmlspecialchars($dateTo); ?>" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="flex items-end space-x-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
                <a href="messages.php" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                    <i class="fas fa-times mr-2"></i>Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Stats Summary -->
<div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <i class="fas fa-envelope text-blue-600 text-2xl mr-4"></i>
            <div>
                <p class="text-sm text-gray-500">Total Messages</p>
                <p class="text-2xl font-bold"><?php echo number_format($stats['total_messages']); ?></p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <i class="fas fa-user text-green-600 text-2xl mr-4"></i>
            <div>
                <p class="text-sm text-gray-500">User Messages</p>
                <p class="text-2xl font-bold"><?php echo number_format($stats['user_messages']); ?></p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <i class="fas fa-robot text-purple-600 text-2xl mr-4"></i>
            <div>
                <p class="text-sm text-gray-500">Bot Messages</p>
                <p class="text-2xl font-bold"><?php echo number_format($stats['assistant_messages']); ?></p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <i class="fas fa-image text-yellow-600 text-2xl mr-4"></i>
            <div>
                <p class="text-sm text-gray-500">With Images</p>
                <p class="text-2xl font-bold"><?php echo number_format($stats['messages_with_images']); ?></p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <i class="fas fa-calendar-day text-red-600 text-2xl mr-4"></i>
            <div>
                <p class="text-sm text-gray-500">Today</p>
                <p class="text-2xl font-bold"><?php echo number_format($stats['messages_today']); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Export Button -->
<div class="mb-6">
    <button onclick="exportMessages()" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
        <i class="fas fa-download mr-2"></i>Export Messages
    </button>
</div>

<!-- Messages Table -->
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Messages</h3>
            <div class="text-sm text-gray-500">
                Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $limit, $totalRecords); ?> of <?php echo $totalRecords; ?> messages
            </div>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Content</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Session</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User IP</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($messages)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>No messages found</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($messages as $message): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <?php if ($message['role'] === 'user'): ?>
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                                        <i class="fas fa-user mr-1"></i>User
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 py-1 bg-purple-100 text-purple-800 text-sm rounded-full">
                                        <i class="fas fa-robot mr-1"></i>Bot
                                    </span>
                                <?php endif; ?>
                                <?php if ($message['has_image']): ?>
                                    <i class="fas fa-image text-yellow-500 ml-2" title="Has Image"></i>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="max-w-md">
                                    <p class="text-sm text-gray-900 truncate">
                                        <?php echo htmlspecialchars(substr($message['content'], 0, 100)); ?>
                                        <?php if (strlen($message['content']) > 100): ?>...<?php endif; ?>
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Order: #<?php echo $message['message_order']; ?>
                                    </p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($message['session_title'] ?? 'Unknown'); ?>
                                    </p>
                                    <p class="text-xs text-gray-500 font-mono">
                                        <?php echo substr($message['session_id'], 0, 15); ?>...
                                    </p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 text-sm rounded-full font-mono">
                                    <?php echo htmlspecialchars($message['user_ip'] ?? 'N/A'); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php echo formatDate($message['timestamp']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm space-x-2">
                                <button onclick="viewMessage('<?php echo $message['id']; ?>')" 
                                        class="text-blue-600 hover:text-blue-800" title="View Full Message">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="pages/view-chat.php?session=<?php echo urlencode($message['session_id']); ?>" 
                                   class="text-green-600 hover:text-green-800" title="View Session">
                                    <i class="fas fa-comments"></i>
                                </a>
                                <button onclick="deleteMessage('<?php echo $message['id']; ?>')" 
                                        class="text-red-600 hover:text-red-800" title="Delete Message">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="px-6 py-4 border-t bg-gray-50">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-500">
                    Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $limit, $totalRecords); ?> of <?php echo $totalRecords; ?> results
                </div>
                <div class="flex items-center space-x-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&<?php echo http_build_query(array_diff_key($_GET, ['page' => ''])); ?>" 
                           class="px-3 py-2 bg-white border rounded-md hover:bg-gray-50">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_diff_key($_GET, ['page' => ''])); ?>" 
                           class="px-3 py-2 <?php echo $i == $page ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-50'; ?> border rounded-md">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&<?php echo http_build_query(array_diff_key($_GET, ['page' => ''])); ?>" 
                           class="px-3 py-2 bg-white border rounded-md hover:bg-gray-50">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Message Modal -->
<div id="messageModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900" id="modalTitle">Message Details</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="modalContent" class="text-sm text-gray-700 max-h-96 overflow-y-auto">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
    function exportMessages() {
        const params = new URLSearchParams(window.location.search);
        window.location.href = 'pages/export-messages.php?' + params.toString();
    }

    function viewMessage(messageId) {
        fetch('pages/get-message.php?id=' + messageId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const message = data.message;
                    document.getElementById('modalTitle').textContent = 
                        `${message.role.charAt(0).toUpperCase() + message.role.slice(1)} Message (#${message.message_order})`;
                    
                    let content = `
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Content:</label>
                                <div class="bg-gray-50 p-4 rounded-lg whitespace-pre-wrap">${message.content}</div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Role:</label>
                                    <p>${message.role}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Timestamp:</label>
                                    <p>${message.timestamp}</p>
                                </div>
                            </div>
                            ${message.has_image ? '<div><label class="block text-sm font-medium text-gray-500 mb-1">Has Image:</label><p>Yes</p></div>' : ''}
                        </div>
                    `;
                    
                    document.getElementById('modalContent').innerHTML = content;
                    document.getElementById('messageModal').classList.remove('hidden');
                } else {
                    showAlert('Error loading message: ' + data.error, 'error');
                }
            })
            .catch(error => {
                showAlert('Error loading message', 'error');
            });
    }

    function closeModal() {
        document.getElementById('messageModal').classList.add('hidden');
    }

    function deleteMessage(messageId) {
        if (confirmDelete('Are you sure you want to delete this message? This action cannot be undone.')) {
            fetch('pages/delete-message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({message_id: messageId})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Message deleted successfully');
                    location.reload();
                } else {
                    showAlert('Error deleting message: ' + data.error, 'error');
                }
            })
            .catch(error => {
                showAlert('Error deleting message', 'error');
            });
        }
    }

    // Close modal when clicking outside
    document.getElementById('messageModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>