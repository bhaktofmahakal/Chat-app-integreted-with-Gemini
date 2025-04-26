<!-- Quick Replies & Suggested Prompts -->
<?php
require_once 'index.php'
?>
<div id="quick-replies" class="sticky bottom-20 z-10 px-4 py-2 overflow-x-auto whitespace-nowrap scrollbar-thin">
  <div class="inline-flex space-x-2">
    <button class="quick-reply-btn px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-full text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
      Tell me a joke
    </button>
    <button class="quick-reply-btn px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-full text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
      Explain quantum computing
    </button>
    <button class="quick-reply-btn px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-full text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
      Write a poem about nature
    </button>
    <button class="quick-reply-btn px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-full text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
      Help me with JavaScript
    </button>
    <button class="quick-reply-btn px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-full text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
      Recommend a book
    </button>
    <button class="quick-reply-btn px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-full text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
      Create a workout plan
    </button>
  </div>
</div>

<script>
  // Quick reply functionality
  const quickReplyButtons = document.querySelectorAll('.quick-reply-btn');
  
  quickReplyButtons.forEach(button => {
    button.addEventListener('click', function() {
      const prompt = this.textContent.trim();
      promptInput.value = prompt;
      charCount.textContent = `${prompt.length}/300`;
      
      // Optional: Auto-submit
      // chatForm.dispatchEvent(new Event('submit'));
    });
  });
</script>