<!-- File Upload Button -->
<?php
require_once 'index.php';

require_once 'config.php';
require_once 'db.php';
?>
<button
  type="button"
  id="file-upload-btn"
  class="p-3 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:opacity-80 transition-colors duration-200 ml-1"
  aria-label="Upload file"
>
  <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
  </svg>
</button>

<button id="fileButton" type="button" class="ml-2 bg-gray-200 hover:bg-gray-300 rounded-full w-10 h-10 flex items-center justify-center">
    <i class="fas fa-paperclip text-gray-600"></i>
</button>
<input type="file" id="fileInput" class="hidden" multiple></script>
<!-- Hidden File Input -->
<input type="file" id="file-input" class="hidden" accept="image/*,.pdf,.doc,.docx,.txt">

<script>
  
  // File upload functionality
  const fileUploadBtn = document.getElementById('file-upload-btn');
  const fileInput = document.getElementById('file-input');
  
  
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
          // Create image preview message
          const imagePreview = `
            <div class="image-upload">
              <img src="${e.target.result}" alt="Uploaded image" class="max-w-full h-auto rounded-lg">
              <p class="mt-1 text-sm text-gray-500">${file.name}</p>
            </div>
          `;
          
          // Add to prompt or as a separate message
          addMessage(`Uploaded image: ${file.name}`, 'user');
          
          // In a real implementation, you would upload the file to your server
          // and then reference it in the message
        };
        
        reader.readAsDataURL(file);
      } else {
        // For other file types, just show the file name
        addMessage(`Uploaded file: ${file.name}`, 'user');
      }
      
      // Reset file input
      this.value = '';
    }
  });
</script>
