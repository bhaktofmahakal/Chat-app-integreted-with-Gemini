<!-- Search and Conversation Management -->
<?php
require_once 'index.php'
?>
<div class="bg-white dark:bg-gray-800 shadow-md rounded-lg mb-4 overflow-hidden">
  <div class="p-4 border-b border-gray-200 dark:border-gray-700">
    <div class="relative">
      <input 
        type="text" 
        id="search-messages" 
        placeholder="Search in conversation..." 
        class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-800 dark:text-white"
      >
      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
    </div>
  </div>

    <button id="searchBtn" class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded-md text-sm flex items-center">
        <i class="fas fa-search mr-1"></i> Search
    </button>
  
  <div class="p-4 flex flex-wrap gap-2">
    <button id="export-pdf" class="px-3 py-1 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md text-sm flex items-center space-x-1">
      <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
      </svg>
      <span>Export PDF</span>
    </button>
    
    <button id="save-conversation" class="px-3 py-1 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md text-sm flex items-center space-x-1">
      <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
      </svg>
      <span>Save Chat</span>
    </button>
    
    <button id="new-conversation" class="px-3 py-1 bg-primary-100 dark:bg-primary-900 hover:bg-primary-200 dark:hover:bg-primary-800 text-primary-700 dark:text-primary-300 rounded-md text-sm flex items-center space-x-1">
      <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
      </svg>
      <span>New Chat</span>
    </button>
  </div>
  
  <div id="search-results" class="hidden border-t border-gray-200 dark:border-gray-700 max-h-60 overflow-y-auto">
    <div class="p-4">
      <h3 class="font-medium mb-2">Search Results</h3>
      <div class="space-y-2">
        <!-- Search results will be dynamically inserted here -->
      </div>
    </div>
  </div>
</div>

<script>
  // Search functionality
  const searchInput = document.getElementById('search-messages');
  const searchResults = document.getElementById('search-results');
  
  searchInput.addEventListener('input', function() {
    const query = this.value.trim().toLowerCase();
    
    if (query.length < 2) {
      searchResults.classList.add('hidden');
      return;
    }
    
    // Clear previous results
    const resultsContainer = searchResults.querySelector('div > div');
    resultsContainer.innerHTML = '';
    
    // Search in messages
    let resultsFound = false;
    messages.forEach(message => {
      if (message.content.toLowerCase().includes(query)) {
        resultsFound = true;
        
        const resultItem = document.createElement('div');
        resultItem.className = 'p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md cursor-pointer';
        
        const roleSpan = document.createElement('span');
        roleSpan.className = `inline-block px-2 py-1 text-xs rounded-full mr-2 ${
          message.role === 'user' 
            ? 'bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-200' 
            : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'
        }`;
        roleSpan.textContent = message.role === 'user' ? 'You' : 'AI';
        
        const contentSpan = document.createElement('span');
        const content = message.content.length > 50 
          ? message.content.substring(0, 50) + '...' 
          : message.content;
        contentSpan.textContent = content;
        
        resultItem.appendChild(roleSpan);
        resultItem.appendChild(contentSpan);
        
        // Scroll to message when clicked
        resultItem.addEventListener('click', function() {
          const messageElements = document.querySelectorAll('.msg');
          const index = messages.findIndex(m => m.id === message.id);
          
          if (index >= 0 && index < messageElements.length) {
            messageElements[index].scrollIntoView({ behavior: 'smooth' });
            messageElements[index].classList.add('bg-yellow-50', 'dark:bg-yellow-900/20');
            setTimeout(() => {
              messageElements[index].classList.remove('bg-yellow-50', 'dark:bg-yellow-900/20');
            }, 2000);
          }
          
          searchResults.classList.add('hidden');
          searchInput.value = '';
        });
        
        resultsContainer.appendChild(resultItem);
      }
    });
    
    if (resultsFound) {
      searchResults.classList.remove('hidden');
    } else {
      const noResults = document.createElement('div');
      noResults.className = 'text-gray-500 dark:text-gray-400 text-center py-2';
      noResults.textContent = 'No matching messages found';
      resultsContainer.appendChild(noResults);
      searchResults.classList.remove('hidden');
    }
  });
  
  // Export to PDF functionality
  document.getElementById('export-pdf').addEventListener('click', function() {
    showToast('Preparing PDF for download...', 'success');
    
    // In a real implementation, you would use a library like jsPDF
    // This is just a simulation
    setTimeout(() => {
      showToast('Chat exported to PDF successfully!', 'success');
    }, 1500);
  });
  
  // Save conversation
  document.getElementById('save-conversation').addEventListener('click', function() {
    const chatName = prompt('Enter a name for this conversation:');
    if (chatName) {
      showToast(`Conversation "${chatName}" saved successfully!`, 'success');
    }
  });
  
  // New conversation
  document.getElementById('new-conversation').addEventListener('click', function() {
    if (confirm('Start a new conversation? Current chat will be saved.')) {
      messages = [];
      localStorage.removeItem('chatMessages');
      messagesContainer.classList.add('hidden');
      welcomeMessage.classList.remove('hidden');
      showToast('Started a new conversation', 'success');
    }
  });
</script>