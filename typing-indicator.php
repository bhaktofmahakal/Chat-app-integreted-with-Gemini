
<?php
require_once 'index.php'
?>
<script>
  // Real-time typing indicator
  let typingTimeout;
  
  promptInput.addEventListener('input', function() {
    // Clear previous timeout
    clearTimeout(typingTimeout);
    
    // Set typing status
    const isTyping = this.value.trim().length > 0;
    
    if (isTyping) {
      // In a real implementation, you would send this status to the server
      // For now, we'll just simulate it with a console log
      console.log('User is typing...');
      
      // Set timeout to clear typing status after 1 second of inactivity
      typingTimeout = setTimeout(() => {
        console.log('User stopped typing');
      }, 1000);
    }
  });
  
  // Enhanced AI typing indicator
  function showTypingIndicator() {
    const typingDiv = document.createElement('div');
    typingDiv.id = 'ai-typing-indicator';
    typingDiv.className = 'flex justify-start';
    
    typingDiv.innerHTML = `
      <div class="bg-white dark:bg-gray-800 p-3 rounded-2xl shadow-md dark:shadow-gray-700/20 rounded-tl-none">
        <div class="flex items-center space-x-2">
          <div class="flex space-x-1">
            <div class="h-2 w-2 bg-gray-400 dark:bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
            <div class="h-2 w-2 bg-gray-400 dark:bg-gray-500 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
            <div class="h-2 w-2 bg-gray-400 dark:bg-gray-500 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
          </div>
          <span class="text-gray-600 dark:text-gray-300 text-sm">AI is typing...</span>
        </div>
      </div>
    `;
    
    // Replace the old loading indicator with this one
    loadingIndicator.innerHTML = '';
    loadingIndicator.appendChild(typingDiv);
    loadingIndicator.classList.remove('hidden');
  }
  
  // Update your form submission to use this
  chatForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const prompt = promptInput.value.trim();
    if (!validatePrompt(prompt)) return;
    
    addMessage(prompt, 'user');
    promptInput.value = '';
    charCount.textContent = '0/300';
    
    // Show enhanced typing indicator
    showTypingIndicator();
    sendButton.disabled = true;
    
    try {
      // Rest of your code remains the same
      // ...
    } catch (error) {
      // ...
    } finally {
      loadingIndicator.classList.add('hidden');
      sendButton.disabled = false;
    }
  });
</script>