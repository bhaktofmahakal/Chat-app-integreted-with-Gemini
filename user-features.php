<!-- User Authentication System -->
<?php
require_once 'index.php'
?>
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
  <h2 class="text-xl font-semibold mb-4">User Account Features</h2>
  
  <!-- Login/Register Form -->
  <div id="auth-container" class="space-y-4">
    <div class="flex space-x-4">
      <button id="show-login" class="px-4 py-2 bg-primary-500 text-white rounded-md">Login</button>
      <button id="show-register" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md">Register</button>
    </div>
    
    <!-- Login Form -->
    <form id="login-form" class="space-y-4">
      <div>
        <label class="block text-sm font-medium mb-1">Email</label>
        <input type="email" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md" required>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Password</label>
        <input type="password" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md" required>
      </div>
      <button type="submit" class="w-full px-4 py-2 bg-primary-500 text-white rounded-md">Login</button>
    </form>
    
    <!-- Register Form (hidden by default) -->
    <form id="register-form" class="hidden space-y-4">
      <div>
        <label class="block text-sm font-medium mb-1">Name</label>
        <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md" required>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Email</label>
        <input type="email" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md" required>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Password</label>
        <input type="password" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md" required>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Confirm Password</label>
        <input type="password" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md" required>
      </div>
      <button type="submit" class="w-full px-4 py-2 bg-primary-500 text-white rounded-md">Register</button>
    </form>
  </div>
  
  <!-- User Profile (shown when logged in) -->
  <div id="user-profile" class="hidden space-y-4">
    <div class="flex items-center space-x-4">
      <div class="w-12 h-12 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-primary-600 dark:text-primary-300 font-bold">
        JD
      </div>
      <div>
        <h3 class="font-medium">John Doe</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">john.doe@example.com</p>
      </div>
    </div>
    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
      <h4 class="font-medium mb-2">Your Conversations</h4>
      <div class="space-y-2">
        <div class="flex justify-between items-center p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md">
          <span>AI Chat - Apr 15, 2023</span>
          <button class="text-primary-500">Open</button>
        </div>
        <div class="flex justify-between items-center p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md">
          <span>Project Ideas - Apr 10, 2023</span>
          <button class="text-primary-500">Open</button>
        </div>
      </div>
    </div>
    <button id="logout-btn" class="w-full px-4 py-2 bg-red-500 text-white rounded-md">Logout</button>
  </div>
</div>

<script>
  // Toggle between login and register forms
  document.getElementById('show-login').addEventListener('click', function() {
    document.getElementById('login-form').classList.remove('hidden');
    document.getElementById('register-form').classList.add('hidden');
    document.getElementById('show-login').classList.add('bg-primary-500', 'text-white');
    document.getElementById('show-login').classList.remove('bg-gray-200', 'dark:bg-gray-700');
    document.getElementById('show-register').classList.add('bg-gray-200', 'dark:bg-gray-700');
    document.getElementById('show-register').classList.remove('bg-primary-500', 'text-white');
  });
  
  document.getElementById('show-register').addEventListener('click', function() {
    document.getElementById('register-form').classList.remove('hidden');
    document.getElementById('login-form').classList.add('hidden');
    document.getElementById('show-register').classList.add('bg-primary-500', 'text-white');
    document.getElementById('show-register').classList.remove('bg-gray-200', 'dark:bg-gray-700');
    document.getElementById('show-login').classList.add('bg-gray-200', 'dark:bg-gray-700');
    document.getElementById('show-login').classList.remove('bg-primary-500', 'text-white');
  });
  
  // Demo login functionality
  document.getElementById('login-form').addEventListener('submit', function(e) {
    e.preventDefault();
    document.getElementById('auth-container').classList.add('hidden');
    document.getElementById('user-profile').classList.remove('hidden');
  });
  
  // Demo logout functionality
  document.getElementById('logout-btn').addEventListener('click', function() {
    document.getElementById('user-profile').classList.add('hidden');
    document.getElementById('auth-container').classList.remove('hidden');
  });
</script>