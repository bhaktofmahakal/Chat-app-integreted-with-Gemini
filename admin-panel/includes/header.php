<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

// Security check
if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$admin = getAdminInfo();
$currentPage = basename($_SERVER['PHP_SELF'] ?? '', '.php');

// Auto-logout after 2 hours
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
    session_destroy();
    header('Location: login.php?timeout=1');
    exit;
}
$_SESSION['last_activity'] = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ADMIN_TITLE; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar-link.active { background: #3b82f6; color: white; }
        .sidebar-link:hover { background: #e5e7eb; }
        .sidebar-link.active:hover { background: #2563eb; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="bg-white w-64 shadow-lg">
            <div class="p-6 border-b">
                <h2 class="text-xl font-bold text-gray-800"><?php echo ADMIN_TITLE; ?></h2>
                <p class="text-sm text-gray-500">v<?php echo ADMIN_VERSION; ?></p>
            </div>
            
            <nav class="mt-6">
                <?php 
                // Get base URL for admin panel
                $adminBase = '';
                $currentPath = $_SERVER['PHP_SELF'] ?? '';
                if (strpos($currentPath, '/pages/') !== false) {
                    $adminBase = '../';
                }
                
                // Alternative: use full URL if needed
                $useFullURL = false; // Change to true if relative paths don't work
                if ($useFullURL) {
                    $adminBase = ADMIN_BASE_URL;
                }
                ?>
                <a href="<?php echo $adminBase; ?>index.php" class="sidebar-link flex items-center px-6 py-3 text-gray-700 <?php echo $currentPage == 'index' ? 'active' : ''; ?>">
                    <i class="fas fa-dashboard mr-3"></i>
                    Dashboard
                </a>
                <a href="<?php echo $adminBase; ?>chats.php" class="sidebar-link flex items-center px-6 py-3 text-gray-700 <?php echo $currentPage == 'chats' ? 'active' : ''; ?>">
                    <i class="fas fa-comments mr-3"></i>
                    Chat Sessions
                </a>
                <a href="<?php echo $adminBase; ?>messages.php" class="sidebar-link flex items-center px-6 py-3 text-gray-700 <?php echo $currentPage == 'messages' ? 'active' : ''; ?>">
                    <i class="fas fa-envelope mr-3"></i>
                    Messages
                </a>
                <a href="<?php echo $adminBase; ?>users.php" class="sidebar-link flex items-center px-6 py-3 text-gray-700 <?php echo $currentPage == 'users' ? 'active' : ''; ?>">
                    <i class="fas fa-users mr-3"></i>
                    Users
                </a>
                <a href="<?php echo $adminBase; ?>analytics.php" class="sidebar-link flex items-center px-6 py-3 text-gray-700 <?php echo $currentPage == 'analytics' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar mr-3"></i>
                    Analytics
                </a>
                <a href="<?php echo $adminBase; ?>settings.php" class="sidebar-link flex items-center px-6 py-3 text-gray-700 <?php echo $currentPage == 'settings' ? 'active' : ''; ?>">
                    <i class="fas fa-cog mr-3"></i>
                    Settings
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="bg-white shadow-sm border-b">
                <div class="flex items-center justify-between px-6 py-4">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-800" id="page-title">Dashboard</h1>
                        <p class="text-sm text-gray-500" id="page-subtitle">Welcome to the admin panel</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <button class="flex items-center space-x-2 text-gray-700 hover:text-gray-900" onclick="toggleUserMenu()">
                                <i class="fas fa-user-circle text-2xl"></i>
                                <span><?php echo htmlspecialchars($admin['username']); ?></span>
                                <i class="fas fa-chevron-down text-sm"></i>
                            </button>
                            <div id="user-menu" class="hidden absolute right-0 mt-2 w-48 bg-white border rounded-lg shadow-lg z-50">
                                <a href="<?php echo $adminBase; ?>settings.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-user mr-2"></i>Profile
                                </a>
                                <a href="<?php echo $adminBase; ?>../index.php" target="_blank" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-external-link-alt mr-2"></i>View Site
                                </a>
                                <hr class="my-1">
                                <a href="<?php echo $adminBase; ?>logout.php" class="block px-4 py-2 text-red-600 hover:bg-red-50">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">