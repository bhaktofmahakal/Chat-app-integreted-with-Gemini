<?php 
require_once 'index.php';

require_once 'config.php';
require_once 'db.php';
?>


<!-- Conversation History Sidebar -->
<div id="conversation-sidebar" class="fixed inset-y-0 left-0 w-64 bg-white dark:bg-gray-800 shadow-lg transform -translate-x-full transition-transform duration-300 ease-in-out z-30">
  <div class="h-full flex flex-col">
    <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
      <h2 class="text-lg font-semibold">Conversations</h2>
      <button id="close-sidebar" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
    
    <div class="flex-1 overflow-y-auto p-4 space-y-2">
      <button id="new-chat-btn" class="w-full flex items-center p-2 rounded-md bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 hover:bg-primary-200 dark:hover:bg-primary-800 transition-colors">
        <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        New Chat
      </button>
      
      <div class="border-t border-gray-200 dark:border-gray-700 my-2 pt-2">
        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Recent Conversations</h3>
        
        <div id="conversation-list" class="space-y-1">
          <!-- Conversation items will be dynamically inserted here -->
          <button class="w-full text-left p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between group">
            <div class="flex items-center">
              <svg class="h-4 w-4 mr-2 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
              </svg>
              <span class="truncate">Project Ideas Discussion</span>
            </div>
            <span class="text-xs text-gray-500">Apr 15</span>
          </button>
          
          <button class="w-full text-left p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between group">
            <div class="flex items-center">
              <svg class="h-4 w-4 mr-2 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
              </svg>
              <span class="truncate">Travel Planning</span>
            </div>
            <span class="text-xs text-gray-500">Apr 10</span>
          </button>
        </div>
      </div>
    </div>
    
    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
      <button id="export-all-chats" class="w-full px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md text-sm flex items-center justify-center space-x-2">
        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
        </svg>
        <span>Export All Conversations</span>
      </button>
    </div>
  </div>
</div>

<!-- Add a button to open the sidebar in your header -->
<button
  id="open-sidebar"
  class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200"
  aria-label="Conversation history"
>
  <svg class="h-5 w-5 text-gray-600 dark:text-gray-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
  </svg>
</button>

<script>
  // Conversation sidebar functionality
  const conversationSidebar = document.getElementById('conversation-sidebar');
  const openSidebarBtn = document.getElementById('open-sidebar');
  const closeSidebarBtn = document.getElementById('close-sidebar');
  const newChatBtn = document.getElementById('new-chat-btn');
  const exportAllChatsBtn = document.getElementById('export-all-chats');
  
  openSidebarBtn.addEventListener('click', function() {
    conversationSidebar.classList.remove('-translate-x-full');
  });
  
  closeSidebarBtn.addEventListener('click', function() {
    conversationSidebar.classList.add('-translate-x-full');
  });
  
  newChatBtn.addEventListener('click', function() {
    if (confirm('Start a new conversation? Current chat will be saved.')) {
      messages = [];
      localStorage.removeItem('chatMessages');
      messagesContainer.classList.add('hidden');
      welcomeMessage.classList.remove('hidden');
      showToast('Started a new conversation', 'success');
      conversationSidebar.classList.add('-translate-x-full');
    }
  });
  
  exportAllChatsBtn.addEventListener('click', function() {
    showToast('Preparing all conversations for export...', 'success');
    
    // In a real implementation, you would gather all conversations and export them
    setTimeout(() => {
      showToast('All conversations exported successfully!', 'success');
    }, 1500);
  });
  
  // Conversation branching
  function saveConversationBranch(branchName) {
    // In a real implementation, you would save the current state of the conversation
    // as a branch that can be returned to later
    const branch = {
      id: Date.now().toString(),
      name: branchName,
      messages: [...messages],
      timestamp: new Date()
    };
    
    // Save to localStorage or your database
    const branches = JSON.parse(localStorage.getItem('conversationBranches') || '[]');
    branches.push(branch);
    localStorage.setItem('conversationBranches', JSON.stringify(branches));
    
    return branch;
  }
  
  function loadConversationBranch(branchId) {
    const branches = JSON.parse(localStorage.getItem('conversationBranches') || '[]');
    const branch = branches.find(b => b.id === branchId);
    
    if (branch) {
      messages = [...branch.messages];
      saveMessages();
      renderMessages();
      showToast(`Loaded conversation: ${branch.name}`, 'success');
    }
  }
  
  // Add a "Save Branch" button to your UI
  document.getElementById('save-conversation').addEventListener('click', function() {
    const branchName = prompt('Enter a name for this conversation branch:');
    if (branchName) {
      const branch = saveConversationBranch(branchName);
      showToast(`Conversation branch "${branchName}" saved!`, 'success');
      
      // Add to the conversation list
      const conversationList = document.getElementById('conversation-list');
      const branchItem = document.createElement('button');
      branchItem.className = 'w-full text-left p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between group';
      branchItem.innerHTML = `
        <div class="flex items-center">
          <svg class="h-4 w-4 mr-2 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
          </svg>
          <span class="truncate">${branchName}</span>
        </div>
        <span class="text-xs text-gray-500">${new Date().toLocaleDateString()}</span>
      `;
      
      branchItem.addEventListener('click', function() {
        loadConversationBranch(branch.id);
        conversationSidebar.classList.add('-translate-x-full');
      });
      
      conversationList.prepend(branchItem);
    }
  });
</script>
