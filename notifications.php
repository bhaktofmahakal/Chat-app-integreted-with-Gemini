<!-- Notification Center -->
<?php
require_once 'index.php';

require_once 'config.php';
require_once 'db.php';
?>
<div id="notification-center" class="fixed inset-y-0 right-0 w-80 bg-white dark:bg-gray-800 shadow-lg transform translate-x-full transition-transform duration-300 ease-in-out z-30">
  <div class="h-full flex flex-col">
    <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
      <h2 class="text-lg font-semibold">Notifications</h2>
      <button id="close-notifications" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
    
    <div class="flex-1 overflow-y-auto p-4 space-y-2" id="notifications-list">
      <!-- Notifications will be dynamically inserted here -->
      <div class="p-3 bg-white dark:bg-gray-700 rounded-lg shadow-sm border border-gray-200 dark:border-gray-600">
        <div class="flex items-start">
          <div class="flex-shrink-0 bg-primary-100 dark:bg-primary-900 rounded-full p-2">
            <svg class="h-5 w-5 text-primary-600 dark:text-primary-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
          </div>
          <div class="ml-3 flex-1">
            <p class="text-sm font-medium text-gray-900 dark:text-white">New Feature Available</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try out our new voice commands feature!</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">2 hours ago</p>
          </div>
        </div>
      </div>
      
      <div class="p-3 bg-white dark:bg-gray-700 rounded-lg shadow-sm border border-gray-200 dark:border-gray-600">
        <div class="flex items-start">
          <div class="flex-shrink-0 bg-green-100 dark:bg-green-900 rounded-full p-2">
            <svg class="h-5 w-5 text-green-600 dark:text-green-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-3 flex-1">
            <p class="text-sm font-medium text-gray-900 dark:text-white">Chat History Saved</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Your conversation has been saved successfully.</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Yesterday</p>
          </div>
        </div>
      </div>
    </div>
    
    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
      <button id="clear-notifications" class="w-full px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md text-sm">
        Clear All Notifications
      </button>
    </div>
  </div>
</div>

<!-- Add a notification bell to your header -->
<button
  id="open-notifications"
  class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200 relative"
  aria-label="Notifications"
>
  <svg class="h-5 w-5 text-gray-600 dark:text-gray-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
  </svg>
  <span id="notification-badge" class="absolute top-0 right-0 h-4 w-4 bg-red-500 rounded-full text-xs text-white flex items-center justify-center">2</span>
</button>

<script>
  // Notification center functionality
  const notificationCenter = document.getElementById('notification-center');
  const openNotificationsBtn = document.getElementById('open-notifications');
  const closeNotificationsBtn = document.getElementById('close-notifications');
  const clearNotificationsBtn = document.getElementById('clear-notifications');
  const notificationBadge = document.getElementById('notification-badge');
  const notificationsList = document.getElementById('notifications-list');
  
  // Notification data
  let notifications = [
    {
      id: '1',
      type: 'info',
      title: 'New Feature Available',
      message: 'Try out our new voice commands feature!',
      timestamp: new Date(Date.now() - 2 * 60 * 60 * 1000), // 2 hours ago
      read: false
    },
    {
      id: '2',
      type: 'success',
      title: 'Chat History Saved',
      message: 'Your conversation has been saved successfully.',
      timestamp: new Date(Date.now() - 24 * 60 * 60 * 1000), // 24 hours ago (yesterday)
      read: false
    }
  ];
  
  // Open notification center
  openNotificationsBtn.addEventListener('click', function() {
    notificationCenter.classList.remove('translate-x-full');
    updateNotificationBadge();
  });
  
  // Close notification center
  closeNotificationsBtn.addEventListener('click', function() {
    notificationCenter.classList.add('translate-x-full');
  });
  
  // Clear all notifications
  clearNotificationsBtn.addEventListener('click', function() {
    notifications = [];
    renderNotifications();
    updateNotificationBadge();
    showToast('All notifications cleared', 'success');
  });
  
  // Render notifications
  function renderNotifications() {
    notificationsList.innerHTML = '';
    
    if (notifications.length === 0) {
      const emptyState = document.createElement('div');
      emptyState.className = 'text-center py-8 text-gray-500 dark:text-gray-400';
      emptyState.innerHTML = `
        <svg class="h-12 w-12 mx-auto text-gray-400 dark:text-gray-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <p>No notifications</p>
      `;
      notificationsList.appendChild(emptyState);
      return;
    }
    
    notifications.forEach(notification => {
      const notificationItem = document.createElement('div');
      notificationItem.className = `p-3 bg-white dark:bg-gray-700 rounded-lg shadow-sm border border-gray-200 dark:border-gray-600 ${notification.read ? 'opacity-75' : ''}`;
      
      // Get icon based on notification type
      let iconBg, iconColor, icon;
      switch (notification.type) {
        case 'info':
          iconBg = 'bg-primary-100 dark:bg-primary-900';
          iconColor = 'text-primary-600 dark:text-primary-300';
          icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />';
          break;
        case 'success':
          iconBg = 'bg-green-100 dark:bg-green-900';
          iconColor = 'text-green-600 dark:text-green-300';
          icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />';
          break;
        case 'warning':
          iconBg = 'bg-yellow-100 dark:bg-yellow-900';
          iconColor = 'text-yellow-600 dark:text-yellow-300';
          icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />';
          break;
        case 'error':
          iconBg = 'bg-red-100 dark:bg-red-900';
          iconColor = 'text-red-600 dark:text-red-300';
          icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />';
          break;
        default:
          iconBg = 'bg-gray-100 dark:bg-gray-900';
          iconColor = 'text-gray-600 dark:text-gray-300';
          icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />';
      }
      
      // Format timestamp
      const timeAgo = formatTimeAgo(notification.timestamp);
      
      notificationItem.innerHTML = `
        <div class="flex items-start">
          <div class="flex-shrink-0 ${iconBg} rounded-full p-2">
            <svg class="h-5 w-5 ${iconColor}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              ${icon}
            </svg>
          </div>
          <div class="ml-3 flex-1">
            <p class="text-sm font-medium text-gray-900 dark:text-white">${notification.title}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">${notification.message}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">${timeAgo}</p>
          </div>
          <button class="ml-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300" data-id="${notification.id}">
            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      `;
      
      // Mark as read when clicked
      notificationItem.addEventListener('click', function(e) {
        if (e.target.closest('button[data-id]')) {
          const id = e.target.closest('button[data-id]').getAttribute('data-id');
          removeNotification(id);
        } else {
          const index = notifications.findIndex(n => n.id === notification.id);
          if (index !== -1) {
            notifications[index].read = true;
            renderNotifications();
            updateNotificationBadge();
          }
        }
      });
      
      notificationsList.appendChild(notificationItem);
    });
  }
  
  // Remove a notification
  function removeNotification(id) {
    notifications = notifications.filter(n => n.id !== id);
    renderNotifications();
    updateNotificationBadge();
  }
  
  // Update notification badge
  function updateNotificationBadge() {
    const unreadCount = notifications.filter(n => !n.read).length;
    
    if (unreadCount > 0) {
      notificationBadge.textContent = unreadCount;
      notificationBadge.classList.remove('hidden');
    } else {
      notificationBadge.classList.add('hidden');
    }
  }
  
  // Format time ago
  function formatTimeAgo(date) {
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) {
      return 'Just now';
    }
    
    const diffInMinutes = Math.floor(diffInSeconds / 60);
    if (diffInMinutes < 60) {
      return `${diffInMinutes} minute${diffInMinutes > 1 ? 's' : ''} ago`;
    }
    
    const diffInHours = Math.floor(diffInMinutes / 60);
    if (diffInHours < 24) {
      return `${diffInHours} hour${diffInHours > 1 ? 's' : ''} ago`;
    }
    
    const diffInDays = Math.floor(diffInHours / 24);
    if (diffInDays < 7) {
      return diffInDays === 1 ? 'Yesterday' : `${diffInDays} days ago`;
    }
    
    return date.toLocaleDateString();
  }
  
  // Add a new notification
  function addNotification(type, title, message) {
    const notification = {
      id: Date.now().toString(),
      type,
      title,
      message,
      timestamp: new Date(),
      read: false
    };
    
    notifications.unshift(notification);
    renderNotifications();
    updateNotificationBadge();
    
    // Show a toast notification
    showToast(`New notification: ${title}`, 'info');
  }
  
  // Initialize
  renderNotifications();
  updateNotificationBadge();
  
  // Example: Add a notification when saving a chat
  document.getElementById('save-conversation').addEventListener('click', function() {
    const chatName = prompt('Enter a name for this conversation:');
    if (chatName) {
      showToast(`Conversation "${chatName}" saved successfully!`, 'success');
      addNotification('success', 'Chat Saved', `Your conversation "${chatName}" has been saved successfully.`);
    }
  });
</script>
