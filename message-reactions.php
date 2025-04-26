<!-- Add this to your renderMessages function for AI messages -->
<?php
require_once 'index.php';

require_once 'config.php';
require_once 'db.php';
?>
<div class="flex space-x-2 mt-2">
  <button class="reaction-btn p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full" data-emoji="👍">
    👍
  </button>
  <button class="reaction-btn p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full" data-emoji="👎">
    👎
  </button>
  <button class="reaction-btn p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full" data-emoji="❤️">
    ❤️
  </button>
  <button class="reaction-btn p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full" data-emoji="😂">
    😂
  </button>
  <button class="reaction-btn p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full" data-emoji="😮">
    😮
  </button>
</div>

<script>
  // Message reactions
  document.addEventListener('click', function(e) {
    if (e.target.classList.contains('reaction-btn') || e.target.parentElement.classList.contains('reaction-btn')) {
      const button = e.target.classList.contains('reaction-btn') ? e.target : e.target.parentElement;
      const emoji = button.getAttribute('data-emoji');
      const messageElement = button.closest('.msg');
      
      // Toggle active state
      button.classList.toggle('bg-primary-100');
      button.classList.toggle('dark:bg-primary-900');
      
      // In a real implementation, you would save this reaction to your database
      showToast(`Reaction ${emoji} recorded`, 'success');
    }
  });
</script>