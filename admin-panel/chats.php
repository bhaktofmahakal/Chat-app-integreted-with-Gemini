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
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

// Build query
$whereConditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $whereConditions[] = "(cs.title LIKE ? OR cs.user_ip LIKE ? OR cs.session_id LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'sss';
}

if (!empty($dateFrom)) {
    $whereConditions[] = "cs.created_at >= ?";
    $params[] = $dateFrom . ' 00:00:00';
    $types .= 's';
}

if (!empty($dateTo)) {
    $whereConditions[] = "cs.created_at <= ?";
    $params[] = $dateTo . ' 23:59:59';
    $types .= 's';
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Get total count
$conn = getDBConnection();
$countQuery = "SELECT COUNT(*) as total FROM chat_sessions cs $whereClause";
$countStmt = $conn->prepare($countQuery);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalRecords = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $limit);

// Get sessions with message count
$query = "SELECT cs.*, COUNT(cm.id) as message_count,
          (SELECT timestamp FROM chat_messages WHERE session_id = cs.session_id ORDER BY timestamp DESC LIMIT 1) as last_message_time
          FROM chat_sessions cs 
          LEFT JOIN chat_messages cm ON cs.session_id = cm.session_id 
          $whereClause
          GROUP BY cs.id 
          ORDER BY cs.updated_at DESC 
          LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';
$stmt->bind_param($types, ...$params);
$stmt->execute();
$sessions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<script>
    setPageTitle('Chat Sessions', 'Manage all chat sessions');
</script>

<!-- Filters -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Search sessions..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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
                <a href="chats.php" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                    <i class="fas fa-times mr-2"></i>Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Stats Summary -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <i class="fas fa-comments text-blue-600 text-2xl mr-4"></i>
            <div>
                <p class="text-sm text-gray-500">Total Sessions</p>
                <p class="text-2xl font-bold"><?php echo number_format($totalRecords); ?></p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <i class="fas fa-clock text-green-600 text-2xl mr-4"></i>
            <div>
                <p class="text-sm text-gray-500">Active Today</p>
                <p class="text-2xl font-bold">
                    <?php 
                    $todayCount = $conn->query("SELECT COUNT(*) as count FROM chat_sessions WHERE DATE(updated_at) = CURDATE()")->fetch_assoc()['count'];
                    echo number_format($todayCount);
                    ?>
                </p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <i class="fas fa-users text-purple-600 text-2xl mr-4"></i>
            <div>
                <p class="text-sm text-gray-500">Unique IPs</p>
                <p class="text-2xl font-bold">
                    <?php 
                    $uniqueIPs = $conn->query("SELECT COUNT(DISTINCT user_ip) as count FROM chat_sessions")->fetch_assoc()['count'];
                    echo number_format($uniqueIPs);
                    ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Sessions Table -->
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Chat Sessions</h3>
            <div class="flex items-center space-x-2">
                <button onclick="exportSessions()" class="px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm">
                    <i class="fas fa-download mr-1"></i>Export
                </button>
                <button onclick="refreshSessions()" class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                    <i class="fas fa-refresh mr-1"></i>Refresh
                </button>
            </div>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Session</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User IP</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Messages</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Activity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($sessions)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>No chat sessions found</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($sessions as $session): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium text-gray-900"><?php echo htmlspecialchars($session['title']); ?></p>
                                    <p class="text-sm text-gray-500 font-mono"><?php echo substr($session['session_id'], 0, 20); ?>...</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 text-sm rounded-full font-mono">
                                    <?php echo htmlspecialchars($session['user_ip']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                                    <?php echo $session['message_count']; ?> messages
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php echo formatDate($session['created_at']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php echo formatDate($session['updated_at']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm space-x-2">
                                <a href="pages/view-chat.php?session=<?php echo urlencode($session['session_id']); ?>" 
                                   class="text-blue-600 hover:text-blue-800" title="View Chat">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <div class="relative inline-block">
                                    <button onclick="showExportMenu('<?php echo $session['session_id']; ?>')" 
                                            class="text-green-600 hover:text-green-800" title="Export Chat">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <div id="export-menu-<?php echo $session['session_id']; ?>" class="hidden absolute right-0 mt-2 w-32 bg-white border rounded-lg shadow-lg z-10">
                                        <a href="pages/export-chat.php?session=<?php echo urlencode($session['session_id']); ?>&format=txt" 
                                           class="block px-4 py-2 text-sm hover:bg-gray-50">TXT</a>
                                        <a href="pages/export-chat.php?session=<?php echo urlencode($session['session_id']); ?>&format=csv" 
                                           class="block px-4 py-2 text-sm hover:bg-gray-50">CSV</a>
                                        <a href="pages/export-chat.php?session=<?php echo urlencode($session['session_id']); ?>&format=json" 
                                           class="block px-4 py-2 text-sm hover:bg-gray-50">JSON</a>
                                    </div>
                                </div>
                                <button onclick="deleteSession('<?php echo $session['session_id']; ?>')" 
                                        class="text-red-600 hover:text-red-800" title="Delete Session">
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
                        <a href="?page=<?php echo $page - 1; ?>&<?php echo http_build_query($_GET); ?>" 
                           class="px-3 py-2 bg-white border rounded-md hover:bg-gray-50">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?>&<?php echo http_build_query($_GET); ?>" 
                           class="px-3 py-2 <?php echo $i == $page ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-50'; ?> border rounded-md">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&<?php echo http_build_query($_GET); ?>" 
                           class="px-3 py-2 bg-white border rounded-md hover:bg-gray-50">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    function deleteSession(sessionId) {
        if (confirmDelete('Are you sure you want to delete this chat session? This action cannot be undone.')) {
            // Implement delete functionality
            fetch('pages/delete-session.php', {
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
                    location.reload();
                } else {
                    showAlert('Error deleting session: ' + data.error, 'error');
                }
            })
            .catch(error => {
                showAlert('Error deleting session', 'error');
            });
        }
    }

    function exportSessions() {
        window.location.href = 'pages/export-sessions.php?' + new URLSearchParams(window.location.search);
    }

    function refreshSessions() {
        location.reload();
    }

    function showExportMenu(sessionId) {
        // Hide all other menus
        document.querySelectorAll('[id^="export-menu-"]').forEach(menu => {
            if (menu.id !== 'export-menu-' + sessionId) {
                menu.classList.add('hidden');
            }
        });
        
        // Toggle current menu
        const menu = document.getElementById('export-menu-' + sessionId);
        menu.classList.toggle('hidden');
    }

    // Close export menus when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('[onclick^="showExportMenu"]')) {
            document.querySelectorAll('[id^="export-menu-"]').forEach(menu => {
                menu.classList.add('hidden');
            });
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>