// DOM Elements
const chatForm = document.getElementById('chat-form');
const promptInput = document.getElementById('prompt-input');
const sendButton = document.getElementById('send-button');
const micButton = document.getElementById('mic-button');
const fileUploadBtn = document.getElementById('file-upload-btn');
const fileInput = document.getElementById('file-input');
const messagesContainer = document.getElementById('messages-container');
const welcomeMessage = document.getElementById('welcome-message');
const loadingIndicator = document.getElementById('loading-indicator');
const charCount = document.getElementById('char-count');
const themeToggle = document.getElementById('theme-toggle');
const clearChatButton = document.getElementById('clear-chat');
const toast = document.getElementById('toast');
const toastMessage = document.getElementById('toast-message');
const closeToast = document.getElementById('close-toast');
const chatContainer = document.getElementById('chat-container');
const scrollTopBtn = document.getElementById('scroll-top-btn');
const scrollBottomBtn = document.getElementById('scroll-bottom-btn');
const searchInput = document.getElementById('search-messages');
const searchResults = document.getElementById('search-results');
const quickReplyButtons = document.querySelectorAll('.quick-reply-btn');
const exportPdfBtn = document.getElementById('export-pdf');
const saveConversationBtn = document.getElementById('save-conversation');
const newConversationBtn = document.getElementById('new-conversation');
const conversationSidebar = document.getElementById('conversation-sidebar');
const openSidebarBtn = document.getElementById('open-sidebar');
const closeSidebarBtn = document.getElementById('close-sidebar');
const newChatBtn = document.getElementById('new-chat-btn');
const exportAllChatsBtn = document.getElementById('export-all-chats');
const notificationCenter = document.getElementById('notification-center');
const openNotificationsBtn = document.getElementById('open-notifications');
const closeNotificationsBtn = document.getElementById('close-notifications');
const clearNotificationsBtn = document.getElementById('clear-notifications');
const notificationBadge = document.getElementById('notification-badge');
const notificationsList = document.getElementById('notifications-list');
const themeCustomizerModal = document.getElementById('theme-customizer-modal');
const openThemeCustomizerBtn = document.getElementById('open-theme-customizer');
const closeThemeModalBtn = document.getElementById('close-theme-modal');
const saveThemeBtn = document.getElementById('save-theme');
const resetThemeBtn = document.getElementById('reset-theme');
const themeColorBtns = document.querySelectorAll('.theme-color-btn');
const decreaseFontBtn = document.getElementById('decrease-font');
const increaseFontBtn = document.getElementById('increase-font');
const fontSizeDisplay = document.getElementById('font-size-display');
const bubbleStyleRoundedBtn = document.getElementById('bubble-style-rounded');
const bubbleStyleSquareBtn = document.getElementById('bubble-style-square');
const bgDefaultBtn = document.getElementById('bg-default');
const bgPattern1Btn = document.getElementById('bg-pattern-1');
const bgPattern2Btn = document.getElementById('bg-pattern-2');
const keyboardShortcutsModal = document.getElementById('keyboard-shortcuts-modal');
const showShortcutsBtn = document.getElementById('show-shortcuts');
const closeShortcutsModalBtn = document.getElementById('close-shortcuts-modal');
const authModal = document.getElementById('auth-modal');
const loginBtn = document.getElementById('login-btn');
const closeAuthModalBtn = document.getElementById('close-auth-modal');
const showLoginFormBtn = document.getElementById('show-login-form');
const showRegisterFormBtn = document.getElementById('show-register-form');
const loginForm = document.getElementById('login-form');
const registerForm = document.getElementById('register-form');

// State
let messages = [];
let isListening = false;
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
let themeSettings = {
  primaryColor: 'violet',
  fontSize: 'medium',
  bubbleStyle: 'rounded',
  background: 'default'
};

// Load messages from localStorage
function loadMessages() {
  const savedMessages = localStorage.getItem('chatMessages');
  if (savedMessages) {
    messages = JSON.parse(savedMessages);
    if (messages.length > 0) {
      welcomeMessage.classList.add('hidden');
      messagesContainer.classList.remove('hidden');
      renderMessages();
    }
  }
}

// Save messages to localStorage
function saveMessages() {
  localStorage.setItem('chatMessages', JSON.stringify(messages));
}

// Render messages
function renderMessages() {
  messagesContainer.innerHTML = '';
  
  messages.forEach(message => {
    const messageDiv = document.createElement('div');
    messageDiv.className = `flex ${message.role === 'user' ? 'justify-end' : 'justify-start'} msg ${message.role}`;
    messageDiv.setAttribute('data-id', message.id);
    
    const bubbleDiv = document.createElement('div');
    const bubbleStyleClass = themeSettings.bubbleStyle === 'rounded' ? 'rounded-2xl' : 'rounded-md';
    bubbleDiv.className = `max-w-[80%] md:max-w-[70%] p-3 ${bubbleStyleClass} ${
      message.role === 'user'
        ? 'bg-gradient-to-r from-primary-500 to-primary-700 text-white rounded-tr-none'
        : 'bg-white dark:bg-gray-800 shadow-md dark:shadow-gray-700/20 text-gray-800 dark:text-gray-200 rounded-tl-none'
    }`;
    
    // Create message content with pre-wrap formatting
    const contentP = document.createElement('p');
    const fontSizeMap = {
      small: 'text-sm',
      medium: 'text-base',
      large: 'text-lg'
    };
    contentP.className = `${fontSizeMap[themeSettings.fontSize]} message-content`;
    
    // Handle file attachments
    if (message.fileType) {
      if (message.fileType.startsWith('image/')) {
        const img = document.createElement('img');
        img.src = message.fileUrl;
        img.alt = 'Uploaded image';
        img.className = 'max-w-full h-auto rounded-lg mb-2';
        bubbleDiv.appendChild(img);
        
        contentP.textContent = message.content || `Shared an image: ${message.fileName}`;
      } else {
        const fileLink = document.createElement('a');
        fileLink.href = message.fileUrl;
        fileLink.target = '_blank';
        fileLink.className = 'flex items-center p-2 bg-gray-100 dark:bg-gray-700 rounded-md mb-2';
        
        const fileIcon = document.createElement('div');
        fileIcon.className = 'mr-2 text-gray-500 dark:text-gray-400';
        fileIcon.innerHTML = '📄';
        
        const fileName = document.createElement('span');
        fileName.textContent = message.fileName;
        
        fileLink.appendChild(fileIcon);
        fileLink.appendChild(fileName);
        bubbleDiv.appendChild(fileLink);
        
        contentP.textContent = message.content || `Shared a file: ${message.fileName}`;
      }
    } else {
      contentP.textContent = message.content;
    }
    
    // Create timestamp
    const timeDiv = document.createElement('div');
    timeDiv.className = `text-xs mt-1 ${
      message.role === 'user' ? 'text-primary-200' : 'text-gray-500 dark:text-gray-400'
    }`;
    timeDiv.textContent = new Date(message.timestamp).toLocaleTimeString([], {
      hour: '2-digit',
      minute: '2-digit'
    });
    
    // Add actions for assistant messages
    if (message.role === 'assistant') {
      const actionsDiv = document.createElement('div');
      actionsDiv.className = 'flex justify-end mt-1 space-x-2';
      
      // Copy button
      const copyButton = document.createElement('button');
      copyButton.className = 'text-gray-500 dark:text-gray-400 hover:text-primary-500 dark:hover:text-primary-400 focus:outline-none';
      copyButton.innerHTML = '📋';
      copyButton.setAttribute('aria-label', 'Copy message');
      copyButton.onclick = function() {
        navigator.clipboard.writeText(message.content)
          .then(() => {
            this.innerHTML = '✅';
            this.classList.add('copy-success');
            setTimeout(() => {
              this.innerHTML = '📋';
              this.classList.remove('copy-success');
            }, 1500);
          })
          .catch(err => {
            console.error('Failed to copy: ', err);
            showToast('Failed to copy message', 'error');
          });
      };
      
      actionsDiv.appendChild(copyButton);
      
      // Reaction buttons
      const reactions = ['👍', '👎', '❤️', '😂', '😮'];
      reactions.forEach(emoji => {
        const reactionBtn = document.createElement('button');
        reactionBtn.className = 'reaction-btn p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full';
        reactionBtn.setAttribute('data-emoji', emoji);
        reactionBtn.textContent = emoji;
        reactionBtn.onclick = function() {
          this.classList.toggle('bg-primary-100');
          this.classList.toggle('dark:bg-primary-900');
          showToast(`Reaction ${emoji} recorded`, 'success');
        };
        actionsDiv.appendChild(reactionBtn);
      });
      
      bubbleDiv.appendChild(contentP);
      bubbleDiv.appendChild(timeDiv);
      bubbleDiv.appendChild(actionsDiv);
    } else {
      bubbleDiv.appendChild(contentP);
      bubbleDiv.appendChild(timeDiv);
    }
    
    messageDiv.appendChild(bubbleDiv);
    messagesContainer.appendChild(messageDiv);
  });
  
  // Scroll to bottom after rendering messages
  chatContainer.scrollTop = chatContainer.scrollHeight;
  
  // Show/hide scroll buttons based on content
  updateScrollButtonsVisibility();
}

// Add a message
function addMessage(content, role, fileData = null) {
  const message = {
    id: Date.now().toString(),
    content,
    role,
    timestamp: new Date()
  };
  
  // Add file data if present
  if (fileData) {
    message.fileName = fileData.name;
    message.fileType = fileData.type;
    message.fileUrl = fileData.url;
  }
  
  messages.push(message);
  saveMessages();
  
  if (messages.length === 1) {
    welcomeMessage.classList.add('hidden');
    messagesContainer.classList.remove('hidden');
  }
  
  renderMessages();
}

// Show toast notification
function showToast(message, type = 'success') {
  toastMessage.textContent = message;
  toast.classList.remove('bg-green-500', 'bg-red-500');
  toast.classList.add(type === 'success' ? 'bg-green-500' : 'bg-red-500');
  toast.classList.remove('hidden');
  
  setTimeout(() => {
    toast.classList.add('hidden');
  }, 3000);
}

// Validate prompt
function validatePrompt(prompt) {
  if (prompt.trim() === '') {
    showToast('Please enter a message before sending.', 'error');
    return false;
  }
  
  if (prompt.length > 300) {
    showToast('Your message exceeds 300 characters. Please shorten it.', 'error');
    return false;
  }
  
  return true;
}

// Handle form submission
chatForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const prompt = promptInput.value.trim();
  if (!validatePrompt(prompt)) return;
  
  addMessage(prompt, 'user');
  promptInput.value = '';
  charCount.textContent = '0/300';
  
  loadingIndicator.classList.remove('hidden');
  sendButton.disabled = true;
  
  try {
    const res = await fetch('api/AIModelAPI.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ prompt }),
    });
    
    const data = await res.json();
    
    if (data.error) {
      showToast(data.error, 'error');
      return;
    }
    
    addMessage(data.response, 'assistant');
    
    // Add notification for new response
    addNotification('info', 'New Response', 'AI has responded to your message.');
  } catch (error) {
    showToast('Failed to process your message. Please try again.', 'error');
    console.error('Error:', error);
  } finally {
    loadingIndicator.classList.add('hidden');
    sendButton.disabled = false;
  }
});

// Handle input changes
promptInput.addEventListener('input', () => {
  charCount.textContent = `${promptInput.value.length}/300`;
  
  // Real-time typing indicator
  clearTimeout(typingTimeout);
  
  // Set typing status
  const isTyping = promptInput.value.trim().length > 0;
  
  if (isTyping) {
    // In a real implementation, you would send this status to the server
    console.log('User is typing...');
    
    // Set timeout to clear typing status after 1 second of inactivity
    typingTimeout = setTimeout(() => {
      console.log('User stopped typing');
    }, 1000);
  }
});

// Handle voice input
micButton.addEventListener('click', () => {
  if (!isListening) {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SpeechRecognition) {
      showToast('Speech recognition is not supported in your browser.', 'error');
      return;
    }
    
    const recognition = new SpeechRecognition();
    recognition.lang = 'en-US';
    recognition.continuous = false;
    recognition.interimResults = true;
    
    recognition.onstart = () => {
      isListening = true;
      micButton.classList.add('mic-active');
      micButton.classList.remove('bg-gray-100', 'dark:bg-gray-700');
      micButton.classList.add('bg-red-500', 'text-white');
    };
    
    recognition.onresult = (event) => {
      const transcript = Array.from(event.results)
        .map(result => result[0])
        .map(result => result.transcript)
        .join('');
      
      promptInput.value = transcript;
      charCount.textContent = `${transcript.length}/300`;
    };
    
    recognition.onend = () => {
      isListening = false;
      micButton.classList.remove('mic-active');
      micButton.classList.remove('bg-red-500', 'text-white');
      micButton.classList.add('bg-gray-100', 'dark:bg-gray-700');
    };
    
    recognition.start();
  }
});

// File upload functionality
fileUploadBtn.addEventListener('click', function() {
  fileInput.click();
});

fileInput.addEventListener('change', function() {
  if (this.files && this.files[0]) {
    const file = this.files[0];
    
    // Check file size (limit to 5MB)
    if (file.size > 5 * 1024 * 1024) {
      showToast('File size exceeds 5MB limit', 'error');
      return;
    }
    
    // For images, show preview
    if (file.type.startsWith('image/')) {
      const reader = new FileReader();
      
      reader.onload = function(e) {
        // In a real implementation, you would upload the file to your server
        // Here we're just using the data URL
        const fileData = {
          name: file.name,
          type: file.type,
          url: e.target.result
        };
        
        addMessage(`Shared an image: ${file.name}`, 'user', fileData);
      };
      
      reader.readAsDataURL(file);
    } else {
      // For other file types
      const reader = new FileReader();
      
      reader.onload = function(e) {
        const fileData = {
          name: file.name,
          type: file.type,
          url: e.target.result
        };
        
        addMessage(`Shared a file: ${file.name}`, 'user', fileData);
      };
      
      reader.readAsDataURL(file);
    }
    
    // Reset file input
    this.value = '';
  }
});

// Handle theme toggle
themeToggle.addEventListener('click', () => {
  document.documentElement.classList.toggle('dark');
  localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
});

// Handle clear chat
clearChatButton.addEventListener('click', () => {
  if (confirm('Are you sure you want to clear all chat history?')) {
    messages = [];
    localStorage.removeItem('chatMessages');
    messagesContainer.classList.add('hidden');
    welcomeMessage.classList.remove('hidden');
    showToast('Chat history cleared.');
  }
});

// Handle toast close
closeToast.addEventListener('click', () => {
  toast.classList.add('hidden');
});

// Scroll functions
function scrollToTop() {
  chatContainer.scrollTo({
    top: 0,
    behavior: 'smooth'
  });
}

function scrollToBottom() {
  chatContainer.scrollTo({
    top: chatContainer.scrollHeight,
    behavior: 'smooth'
  });
}

// Update scroll buttons visibility
function updateScrollButtonsVisibility() {
  // Only show scroll buttons if there's enough content to scroll
  if (chatContainer.scrollHeight > chatContainer.clientHeight) {
    scrollTopBtn.style.display = 'flex';
    scrollBottomBtn.style.display = 'flex';
  } else {
    scrollTopBtn.style.display = 'none';
    scrollBottomBtn.style.display = 'none';
  }
}

// Scroll button event listeners
scrollTopBtn.addEventListener('click', scrollToTop);
scrollBottomBtn.addEventListener('click', scrollToBottom);

// Listen for scroll events to update button visibility
chatContainer.addEventListener('scroll', () => {
  const isAtTop = chatContainer.scrollTop === 0;
  const isAtBottom = chatContainer.scrollTop + chatContainer.clientHeight >= chatContainer.scrollHeight - 10;
  
  scrollTopBtn.style.opacity = isAtTop ? '0.5' : '1';
  scrollBottomBtn.style.opacity = isAtBottom ? '0.5' : '1';
});

// Search functionality
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
        const messageElement = document.querySelector(`.msg[data-id="${message.id}"]`);
        
        if (messageElement) {
          messageElement.scrollIntoView({ behavior: 'smooth' });
          messageElement.classList.add('bg-yellow-50', 'dark:bg-yellow-900/20');
          setTimeout(() => {
            messageElement.classList.remove('bg-yellow-50', 'dark:bg-yellow-900/20');
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

// Quick reply functionality
quickReplyButtons.forEach(button => {
  button.addEventListener('click', function() {
    const prompt = this.textContent.trim();
    promptInput.value = prompt;
    charCount.textContent = `${prompt.length}/300`;
    
    // Optional: Auto-submit
    // chatForm.dispatchEvent(new Event('submit'));
  });
});

// Export to PDF functionality
exportPdfBtn.addEventListener('click', function() {
  showToast('Preparing PDF for download...', 'success');
  
  // In a real implementation, you would use a library like jsPDF
  // This is just a simulation
  setTimeout(() => {
    showToast('Chat exported to PDF successfully!', 'success');
  }, 1500);
});

// Save conversation
saveConversationBtn.addEventListener('click', function() {
  const chatName = prompt('Enter a name for this conversation:');
  if (chatName) {
    const branch = saveConversationBranch(chatName);
    showToast(`Conversation "${chatName}" saved successfully!`, 'success');
    addNotification('success', 'Chat Saved', `Your conversation "${chatName}" has been saved successfully.`);
  }
});

// New conversation
newConversationBtn.addEventListener('click', function() {
  if (confirm('Start a new conversation? Current chat will be saved.')) {
    // Save current conversation if it has messages
    if (messages.length > 0) {
      const timestamp = new Date().toLocaleString();
      saveConversationBranch(`Chat ${timestamp}`);
    }
    
    messages = [];
    localStorage.removeItem('chatMessages');
    messagesContainer.classList.add('hidden');
    welcomeMessage.classList.remove('hidden');
    showToast('Started a new conversation', 'success');
  }
});

// Conversation sidebar functionality
openSidebarBtn.addEventListener('click', function() {
  conversationSidebar.classList.remove('-translate-x-full');
  loadConversationList();
});

closeSidebarBtn.addEventListener('click', function() {
  conversationSidebar.classList.add('-translate-x-full');
});

newChatBtn.addEventListener('click', function() {
  if (confirm('Start a new conversation? Current chat will be saved.')) {
    // Save current conversation if it has messages
    if (messages.length > 0) {
      const timestamp = new Date().toLocaleString();
      saveConversationBranch(`Chat ${timestamp}`);
    }
    
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
  
  // Save to localStorage
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
    welcomeMessage.classList.add('hidden');
    messagesContainer.classList.remove('hidden');
    renderMessages();
    showToast(`Loaded conversation: ${branch.name}`, 'success');
  }
}

function loadConversationList() {
  const conversationList = document.getElementById('conversation-list');
  conversationList.innerHTML = '';
  
  const branches = JSON.parse(localStorage.getItem('conversationBranches') || '[]');
  
  if (branches.length === 0) {
    const emptyState = document.createElement('div');
    emptyState.className = 'text-center py-4 text-gray-500 dark:text-gray-400';
    emptyState.textContent = 'No saved conversations';
    conversationList.appendChild(emptyState);
    return;
  }
  
  // Sort branches by timestamp, newest first
  branches.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
  
  branches.forEach(branch => {
    const branchItem = document.createElement('button');
    branchItem.className = 'w-full text-left p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between group';
    
    const date = new Date(branch.timestamp);
    const formattedDate = date.toLocaleDateString();
    
    branchItem.innerHTML = `
      <div class="flex items-center">
        <svg class="h-4 w-4 mr-2 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
        </svg>
        <span class="truncate">${branch.name}</span>
      </div>
      <span class="text-xs text-gray-500">${formattedDate}</span>
    `;
    
    branchItem.addEventListener('click', function() {
      loadConversationBranch(branch.id);
      conversationSidebar.classList.add('-translate-x-full');
    });
    
    conversationList.appendChild(branchItem);
  });
}

// Notification center functionality
openNotificationsBtn.addEventListener('click', function() {
  notificationCenter.classList.remove('translate-x-full');
  renderNotifications();
  updateNotificationBadge();
});

closeNotificationsBtn.addEventListener('click', function() {
  notificationCenter.classList.add('translate-x-full');
});

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
  const diffInSeconds = Math.floor((now - new Date(date)) / 1000);
  
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
  
  return new Date(date).toLocaleDateString();
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
}

// Theme customizer functionality
openThemeCustomizerBtn.addEventListener('click', function() {
  themeCustomizerModal.classList.remove('hidden');
  updateThemeCustomizerUI();
});

closeThemeModalBtn.addEventListener('click', function() {
  themeCustomizerModal.classList.add('hidden');
});

// Close modal when clicking outside
themeCustomizerModal.addEventListener('click', function(e) {
  if (e.target === themeCustomizerModal) {
    themeCustomizerModal.classList.add('hidden');
  }
});

// Color selection
themeColorBtns.forEach(btn => {
  btn.addEventListener('click', function() {
    const color = this.getAttribute('data-color');
    themeSettings.primaryColor = color;
    updateThemeCustomizerUI();
  });
});

// Font size controls
decreaseFontBtn.addEventListener('click', function() {
  const sizes = ['small', 'medium', 'large'];
  const currentIndex = sizes.indexOf(themeSettings.fontSize);
  if (currentIndex > 0) {
    themeSettings.fontSize = sizes[currentIndex - 1];
    updateThemeCustomizerUI();
  }
});

increaseFontBtn.addEventListener('click', function() {
  const sizes = ['small', 'medium', 'large'];
  const currentIndex = sizes.indexOf(themeSettings.fontSize);
  if (currentIndex < sizes.length - 1) {
    themeSettings.fontSize = sizes[currentIndex + 1];
    updateThemeCustomizerUI();
  }
});

// Bubble style selection
bubbleStyleRoundedBtn.addEventListener('click', function() {
  themeSettings.bubbleStyle = 'rounded';
  updateThemeCustomizerUI();
});

bubbleStyleSquareBtn.addEventListener('click', function() {
  themeSettings.bubbleStyle = 'square';
  updateThemeCustomizerUI();
});

// Background selection
bgDefaultBtn.addEventListener('click', function() {
  themeSettings.background = 'default';
  updateThemeCustomizerUI();
});

bgPattern1Btn.addEventListener('click', function() {
  themeSettings.background = 'pattern1';
  updateThemeCustomizerUI();
});

bgPattern2Btn.addEventListener('click', function() {
  themeSettings.background = 'pattern2';
  updateThemeCustomizerUI();
});



// Save theme settings
saveThemeBtn.addEventListener('click', function() {
    localStorage.setItem('themeSettings', JSON.stringify(themeSettings));
    applyTheme(themeSettings); // Theme apply karne ke liye function call
  });

  // Send message to server (AJAX request to your PHP backend)
fetch('AIModelAPI.php', {
  method: 'POST',
  headers: {
      'Content-Type': 'application/json',
  },
  body: JSON.stringify({
      message: message,
      // Add any other parameters your API needs
  }),
})
.then(response => response.json())
.then(data => {
  // Process response
})
//Load conversation history
fetch('conversation-history.php')
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const historyList = document.getElementById('historyList');
            historyList.innerHTML = '';
            
            data.data.forEach(conversation => {
                const conversationItem = document.createElement('div');
                conversationItem.className = 'border rounded-lg p-3 hover:bg-gray-50 cursor-pointer';
                conversationItem.innerHTML = `
                    <h3 class="font-medium text-gray-800">${conversation.title}</h3>
                    <p class="text-gray-600 text-sm truncate">${conversation.last_message}</p>
                    <p class="text-gray-400 text-xs mt-1">${formatDate(conversation.timestamp)}</p>
                `;
                
                conversationItem.addEventListener('click', () => {
                    loadConversation(conversation.id);
                    historyModal.classList.add('hidden');
                });
                
                historyList.appendChild(conversationItem);
            });
        }
    });
    // File upload
const fileButton = document.getElementById('fileButton');
// const fileInput = document.getElementById('fileInput');

fileButton.addEventListener('click', function() {
    fileInput.click();
});

fileInput.addEventListener('change', function() {
    if (this.files.length > 0) {
        const formData = new FormData();
        
        for (let i = 0; i < this.files.length; i++) {
            formData.append('files[]', this.files[i]);
        }
        
        // Show loading spinner
        loadingSpinner.classList.remove('hidden');
        
        // Upload files
        fetch('file-upload.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            loadingSpinner.classList.add('hidden');
            
            if (data.status === 'success') {
                // Add file message
                const fileLinks = data.files.map(file => 
                    `<a href="${file.url}" target="_blank" class="text-blue-500 hover:underline flex items-center">
                        <i class="fas fa-file mr-1"></i> ${file.name}
                    </a>`
                ).join('<br>');
                
                addMessage(`I've shared the following files:<br>${fileLinks}`, 'user', true);
                scrollToBottom();
            } else {
                alert('Error uploading files: ' + data.message);
            }
        })
        .catch(error => {
            loadingSpinner.classList.add('hidden');
            console.error('Error:', error);
            alert('Error uploading files. Please try again.');
        });
        
        // Clear the input
        this.value = '';
    }
});

// Update addMessage function to support HTML content
function addMessage(text, sender, isHTML = false) {
    // Existing code...
    
    if (sender === 'user') {
        if (isHTML) {
            messageBubble.innerHTML = text;
        } else {
            messageBubble.textContent = text;
        }
    } else {
        // Existing AI message code...
    }
    
    // Rest of the function...
}
 // Search functionality
const searchBtn = document.getElementById('searchBtn');

searchBtn.addEventListener('click', function() {
    const searchTerm = prompt('Enter search term:');
    
    if (searchTerm && searchTerm.trim() !== '') {
        // Show loading spinner
        loadingSpinner.classList.remove('hidden');
        
        // Search messages
        fetch(`search-feature.php?q=${encodeURIComponent(searchTerm.trim())}`)
            .then(response => response.json())
            .then(data => {
                loadingSpinner.classList.add('hidden');
                
                if (data.status === 'success' && data.results.length > 0) {
                    // Display search results
                    alert(`Found ${data.results.length} results for "${searchTerm}"`);
                    
                    // You could display these in a modal similar to the history modal
                } else {
                    alert(`No results found for "${searchTerm}"`);
                }
            })
            .catch(error => {
                loadingSpinner.classList.add('hidden');
                console.error('Error:', error);
                alert('Error searching messages. Please try again.');
            });
    }
   
function setupNotifications() {
  if (!('Notification' in window)) {
      console.log('This browser does not support notifications');
      return;
  }
  
  if (Notification.permission === 'granted') {
      return;
  } else if (Notification.permission !== 'denied') {
      Notification.requestPermission().then(permission => {
          console.log('Notification permission:', permission);
      });
  }
}

// Call this function when the page loads
setupNotifications();

// Function to show notification
function showNotification(title, body) {
  if (Notification.permission === 'granted' && userSettings.notifications) {
      const notification = new Notification(title, {
          body: body,
          icon: '/assets/icons/placeholder-logo.png'
      });
      
      notification.onclick = function() {
          window.focus();
          this.close();
      };
  }
}

// Call this when receiving a new message
// showNotification('New Message', 'You have received a new message');
});