<script type="text/javascript">
        var gk_isXlsx = false;
        var gk_xlsxFileLookup = {};
        var gk_fileData = {};
        function filledCell(cell) {
          return cell !== '' && cell != null;
        }
        function loadFileData(filename) {
        if (gk_isXlsx && gk_xlsxFileLookup[filename]) {
            try {
                var workbook = XLSX.read(gk_fileData[filename], { type: 'base64' });
                var firstSheetName = workbook.SheetNames[0];
                var worksheet = workbook.Sheets[firstSheetName];

                // Convert sheet to JSON to filter blank rows
                var jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1, blankrows: false, defval: '' });
                // Filter out blank rows (rows where all cells are empty, null, or undefined)
                var filteredData = jsonData.filter(row => row.some(filledCell));

                // Heuristic to find the header row by ignoring rows with fewer filled cells than the next row
                var headerRowIndex = filteredData.findIndex((row, index) =>
                  row.filter(filledCell).length >= filteredData[index + 1]?.filter(filledCell).length
                );
                // Fallback
                if (headerRowIndex === -1 || headerRowIndex > 25) {
                  headerRowIndex = 0;
                }

                // Convert filtered JSON back to CSV
                var csv = XLSX.utils.aoa_to_sheet(filteredData.slice(headerRowIndex)); // Create a new sheet from filtered array of arrays
                csv = XLSX.utils.sheet_to_csv(csv, { header: 1 });
                return csv;
            } catch (e) {
                console.error(e);
                return "";
            }
        }
        return gk_fileData[filename] || "";
        }
        </script>
<?php
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="en" class="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>AI Chat Assistant</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
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
              950: '#2e1065',
            },
          }
        }
      }
    }
  </script>
  <style>
    /* Custom scrollbar */
    .scrollbar-thin::-webkit-scrollbar {
      width: 6px;
    }
    .scrollbar-thin::-webkit-scrollbar-track {
      background: transparent;
    }
    .scrollbar-thin::-webkit-scrollbar-thumb {
      background-color: #d1d5db;
      border-radius: 3px;
    }
    .dark .scrollbar-thin::-webkit-scrollbar-thumb {
      background-color: #4b5563;
    }
    
    /* Scroll buttons */
    .scroll-btn {
      position: fixed;
      right: 20px;
      width: 40px;
      height: 40px;
      background-color: rgba(139, 92, 246, 0.9);
      color: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      transition: all 0.2s ease;
      z-index: 5;  /* Changed from 50 to 5 to be below the chat input */
    }
    .scroll-btn:hover {
      transform: scale(1.1);
    }
    #scroll-top-btn {
      bottom: 160px;  /* Increased from 120px */
    }
    #scroll-bottom-btn {
      bottom: 100px;  /* Increased from 60px */
    }
    
    /* Message pre-wrap formatting */
    .message-content {
      white-space: pre-wrap;
      word-break: break-word;
    }
    
    /* Copy button animation */
    .copy-success {
      animation: fadeOut 1.5s forwards;
    }
    @keyframes fadeOut {
      0% { opacity: 1; }
      70% { opacity: 1; }
      100% { opacity: 0; }
    }
    
    /* Mic animation */
    .mic-active {
      animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.1); }
      100% { transform: scale(1); }
    }
  </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-white transition-colors duration-200">
  <div class="flex flex-col h-screen">
    <!-- Header -->
    <header class="sticky top-0 z-10 backdrop-blur-md bg-white/70 dark:bg-gray-800/70 border-b border-gray-200 dark:border-gray-700 shadow-sm">
      <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <div class="flex items-center space-x-2">
          <div class="h-8 w-8 rounded-full bg-gradient-to-r from-primary-500 to-primary-700 flex items-center justify-center text-white font-bold">
            AI
          </div>
          <h1 class="text-xl font-semibold text-gray-800 dark:text-white">AI Chat Assistant</h1>
        </div>

        <div class="flex items-center space-x-3">
          <button
            id="theme-toggle"
            class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200"
            aria-label="Toggle dark mode"
          >
            <svg id="sun-icon" class="h-5 w-5 text-gray-600 hidden dark:block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <svg id="moon-icon" class="h-5 w-5 text-gray-600 block dark:hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
          </button>

          <div class="relative group">
            <button
              class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200"
              aria-label="Settings"
            >
              <svg class="h-5 w-5 text-gray-600 dark:text-gray-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </button>

            <div class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-20 border border-gray-200 dark:border-gray-700">
              <div class="py-1">
                <button
                  id="clear-chat"
                  class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                  Clear Chat History
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Chat Container -->
    <div id="chat-container" class="flex-1 overflow-y-auto p-4 space-y-4 scrollbar-thin">
      <div id="welcome-message" class="flex flex-col items-center justify-center h-full text-center">
        <div class="w-16 h-16 mb-4 rounded-full bg-gradient-to-r from-primary-500 to-primary-700 flex items-center justify-center">
          <span class="text-white text-2xl">AI</span>
        </div>
        <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200 mb-2">
          Welcome to AI Chat Assistant
        </h2>
        <p class="text-gray-500 dark:text-gray-400 max-w-md">
          Start a conversation by typing a message below. Your chat history will be saved locally.
        </p>
      </div>

      <div id="messages-container" class="hidden space-y-4">
        <!-- Messages will be dynamically inserted here -->
      </div>

      <div id="loading-indicator" class="hidden flex justify-start">
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-md dark:shadow-gray-700/20 rounded-tl-none">
          <div class="flex items-center space-x-2">
            <svg class="h-5 w-5 text-primary-500 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-600 dark:text-gray-300 text-sm">AI is thinking...</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Chat Input -->
    <div class="sticky bottom-0 z-10 backdrop-blur-md bg-white/70 dark:bg-gray-800/70 border-t border-gray-200 dark:border-gray-700 p-4">
      <form id="chat-form" class="container mx-auto">
        <div class="relative flex items-center">
          <div class="absolute inset-0 -z-10 rounded-full bg-white dark:bg-gray-700/50 shadow-lg backdrop-blur-sm border border-gray-200 dark:border-gray-700"></div>

          <button
            type="button"
            id="mic-button"
            class="p-3 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:opacity-80 transition-colors duration-200 ml-1"
            aria-label="Start voice input"
          >
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
            </svg>
          </button>

          <input
            type="text"
            id="prompt-input"
            placeholder="Type your message..."
            class="flex-1 bg-transparent border-0 focus:ring-0 text-gray-800 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 px-4 py-3"
            maxlength="300"
          />

          <div id="char-count" class="mr-2 text-xs text-gray-500 dark:text-gray-400">0/300</div>

          <button
            type="submit"
            id="send-button"
            class="p-3 rounded-full bg-gradient-to-r from-primary-500 to-primary-700 text-white hover:opacity-90 transition-colors duration-200 mr-1 ml-2 disabled:opacity-50 disabled:cursor-not-allowed"  /* Added ml-2 for left margin */
            aria-label="Send message"
          >
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
            </svg>
          </button>
        </div>
      </form>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-24 left-1/2 transform -translate-x-1/2 z-50 hidden">
      <div class="px-4 py-3 rounded-lg shadow-lg flex items-center space-x-2 bg-green-500 text-white">
        <span id="toast-message">Message sent successfully!</span>
        <button id="close-toast" class="text-white hover:text-gray-200">
          <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
    
    <!-- Scroll Buttons -->
    <button id="scroll-top-btn" class="scroll-btn" aria-label="Scroll to top">
      <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
      </svg>
    </button>
    <button id="scroll-bottom-btn" class="scroll-btn" aria-label="Scroll to bottom">
      <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
      </svg>
    </button>
  </div>

  <script>
    // DOM Elements
    const chatForm = document.getElementById('chat-form');
    const promptInput = document.getElementById('prompt-input');
    const sendButton = document.getElementById('send-button');
    const micButton = document.getElementById('mic-button');
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

    // State
    let messages = [];
    let isListening = false;

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
        
        const bubbleDiv = document.createElement('div');
        bubbleDiv.className = `max-w-[80%] md:max-w-[70%] p-3 rounded-2xl ${
          message.role === 'user'
            ? 'bg-gradient-to-r from-primary-500 to-primary-700 text-white rounded-tr-none'
            : 'bg-white dark:bg-gray-800 shadow-md dark:shadow-gray-700/20 text-gray-800 dark:text-gray-200 rounded-tl-none'
        }`;
        
        // Create message content with pre-wrap formatting
        const contentP = document.createElement('p');
        contentP.className = 'text-sm md:text-base message-content';
        contentP.textContent = message.content;
        
        // Create timestamp
        const timeDiv = document.createElement('div');
        timeDiv.className = `text-xs mt-1 ${
          message.role === 'user' ? 'text-primary-200' : 'text-gray-500 dark:text-gray-400'
        }`;
        timeDiv.textContent = new Date(message.timestamp).toLocaleTimeString([], {
          hour: '2-digit',
          minute: '2-digit'
        });
        
        // Add copy button for assistant messages
        if (message.role === 'assistant') {
          const actionsDiv = document.createElement('div');
          actionsDiv.className = 'flex justify-end mt-1';
          
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
    function addMessage(content, role) {
      const message = {
        id: Date.now().toString(),
        content,
        role,
        timestamp: new Date()
      };
      
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
        const res = await fetch('AIModelAPI.php', {
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

    // Handle theme toggle
    themeToggle.addEventListener('click', () => {
      document.documentElement.classList.toggle('dark');
      localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
    });

    // Handle clear chat
    clearChatButton.addEventListener('click', () => {
      messages = [];
      localStorage.removeItem('chatMessages');
      messagesContainer.classList.add('hidden');
      welcomeMessage.classList.remove('hidden');
      showToast('Chat history cleared.');
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

    // Check for dark mode preference
    if (localStorage.getItem('darkMode') === 'true' || 
        (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      document.documentElement.classList.add('dark');
    }

    // Initialize
    loadMessages();
    updateScrollButtonsVisibility();
    promptInput.focus();
  </script>
</body>
</html>