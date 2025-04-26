<!-- Keyboard Shortcuts Help -->
<?php
require_once 'index.php';

require_once 'config.php';
require_once 'db.php';
?>
<div id="keyboard-shortcuts-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4">
    <div class="flex justify-between items-center p-4 border-b border-gray-200 dark:border-gray-700">
      <h3 class="text-lg font-medium">Keyboard Shortcuts</h3>
      <button id="close-shortcuts-modal" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
    <div class="p-4 space-y-4">
      <div class="flex justify-between">
        <span class="font-medium">Send message</span>
        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-sm">Enter</span>
      </div>
      <div class="flex justify-between">
        <span class="font-medium">New line in message</span>
        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-sm">Shift + Enter</span>
      </div>
      <div class="flex justify-between">
        <span class="font-medium">Clear input</span>
        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-sm">Esc</span>
      </div>
      <div class="flex justify-between">
        <span class="font-medium">Focus input</span>
        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-sm">/</span>
      </div>
      <div class="flex justify-between">
        <span class="font-medium">Toggle dark mode</span>
        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-sm">Alt + D</span>
      </div>
      <div class="flex justify-between">
        <span class="font-medium">Start new chat</span>
        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-sm">Alt + N</span>
      </div>
      <div class="flex justify-between">
        <span class="font-medium">Search in chat</span>
        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-sm">Ctrl + F</span>
      </div>
    </div>
  </div>
</div>

<!-- Add keyboard icon to header -->
<button
  id="show-shortcuts"
  class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200"
  aria-label="Keyboard shortcuts"
>
  <svg class="h-5 w-5 text-gray-600 dark:text-gray-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" />
  </svg>
</button>

<script>
  // Keyboard shortcuts modal
  const keyboardShortcutsModal = document.getElementById('keyboard-shortcuts-modal');
  const showShortcutsBtn = document.getElementById('show-shortcuts');
  const closeShortcutsModalBtn = document.getElementById('close-shortcuts-modal');
  
  showShortcutsBtn.addEventListener('click', function() {
    keyboardShortcutsModal.classList.remove('hidden');
  });
  
  closeShortcutsModalBtn.addEventListener('click', function() {
    keyboardShortcutsModal.classList.add('hidden');
  });
  
  // Close modal when clicking outside
  keyboardShortcutsModal.addEventListener('click', function(e) {
    if (e.target === keyboardShortcutsModal) {
      keyboardShortcutsModal.classList.add('hidden');
    }
  });
  
  // Implement keyboard shortcuts
  document.addEventListener('keydown', function(e) {
    // Focus input when pressing /
    if (e.key === '/' && document.activeElement !== promptInput) {
      e.preventDefault();
      promptInput.focus();
    }
    
    // Clear input when pressing Escape
    if (e.key === 'Escape' && document.activeElement === promptInput) {
      promptInput.value = '';
      charCount.textContent = '0/300';
    }
    
    // Toggle dark mode with Alt+D
    if (e.key === 'd' && e.altKey) {
      e.preventDefault();
      themeToggle.click();
    }
    
    // New chat with Alt+N
    if (e.key === 'n' && e.altKey) {
      e.preventDefault();
      document.getElementById('new-conversation').click();
    }
    
    // Search with Ctrl+F
    if (e.key === 'f' && (e.ctrlKey || e.metaKey)) {
      e.preventDefault();
      document.getElementById('search-messages').focus();
    }
    
    // Show keyboard shortcuts with ?
    if (e.key === '?' && e.shiftKey) {
      e.preventDefault();
      keyboardShortcutsModal.classList.remove('hidden');
    }
  });
</script>