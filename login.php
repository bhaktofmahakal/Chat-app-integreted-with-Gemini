<?php
session_start();
require_once 'config.php';

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['username'];
    $p = $_POST['password'];
    
    $conn = getDBConnection();
    if (!$conn) {
        $error_message = "Database connection failed";
    } else {
        // Use password_verify for proper password checking
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->bind_param("s", $u);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows && $admin = $res->fetch_assoc()) {
            // Check password (supports both old hash and new bcrypt)
            if (password_verify($p, $admin['password']) || hash('sha256', $p) === $admin['password']) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $u;
                $_SESSION['admin_id'] = $admin['id'];
                header("Location: admin-panel/login.php");
                exit;
            } else {
                $error_message = "Invalid username or password";
            }
        } else {
            $error_message = "Invalid username or password";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | AI Chat System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f5f3ff',
                            100: '#ede9fe',
                            200: '#ddd6fe',
                            300: '#c4b5fd',
                            400: '#a78bfa',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                            800: '#5b21b6',
                            900: '#4c1d95',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 font-sans">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-gradient-to-r from-violet-600 to-purple-600 text-white text-2xl font-bold mb-4">
                    AI
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Admin Dashboard</h1>
                <p class="text-gray-600">Sign in to access the admin panel</p>
            </div>
            
            <!-- Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <!-- Form Header -->
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-800">Admin Login</h2>
                </div>
                
                <!-- Form -->
                <div class="p-6">
                    <?php if ($error_message): ?>
                        <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 text-red-700">
                            <p><?php echo $error_message; ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="space-y-6">
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                            <input 
                                id="username"
                                name="username" 
                                type="text" 
                                required 
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                                placeholder="Enter your username"
                            >
                        </div>
                        
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input 
                                id="password"
                                name="password" 
                                type="password" 
                                required 
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                                placeholder="Enter your password"
                            >
                        </div>
                        
                        <div>
                            <button 
                                type="submit" 
                                class="w-full bg-gradient-to-r from-violet-600 to-purple-600 text-white py-2 px-4 rounded-md hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-all duration-150 font-medium"
                            >
                                Sign In
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Footer -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 text-center text-sm text-gray-600">
                    AI Chat System Admin Panel
                </div>
            </div>
            
            <!-- Security Notice -->
            <div class="mt-6 text-center text-xs text-gray-500">
                <p>This is a secure area. Unauthorized access is prohibited.</p>
                <p class="mt-1">© <?php echo date('Y'); ?> AI Chat System. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
