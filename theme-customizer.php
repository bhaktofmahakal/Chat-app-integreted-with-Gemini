<!-- Theme Customizer Modal -->
<?php
require_once 'index.php'
?>
<div id="theme-customizer-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4">
    <div class="flex justify-between items-center p-4 border-b border-gray-200 dark:border-gray-700">
      <h3 class="text-lg font-medium">Customize Theme</h3>
      <button id="close-theme-modal" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
    
    <div class="p-4 space-y-4">
      <div>
        <label class="block text-sm font-medium mb-1">Primary Color</label>
        <div class="grid grid-cols-5 gap-2">
          <button class="theme-color-btn h-8 w-8 rounded-full bg-violet-500" data-color="violet"></button>
          <button class="theme-color-btn h-8 w-8 rounded-full bg-blue-500" data-color="blue"></button>
          <button class="theme-color-btn h-8 w-8 rounded-full bg-green-500" data-color="green"></button>
          <button class="theme-color-btn h-8 w-8 rounded-full bg-red-500" data-color="red"></button>
          <button class="theme-color-btn h-8 w-8 rounded-full bg-yellow-500" data-color="yellow"></button>
        </div>
      </div>
      
      <div>
        <label class="block text-sm font-medium mb-1">Font Size</label>
        <div class="flex items-center space-x-2">
          <button id="decrease-font" class="p-2 rounded-md bg-gray-100 dark:bg-gray-700">
            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
            </svg>
          </button>
          <div id="font-size-display" class="flex-1 text-center">Medium</div>
          <button id="increase-font" class="p-2 rounded-md bg-gray-100 dark:bg-gray-700">
            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
          </button>
        </div>
      </div>
      
      <div>
        <label class="block text-sm font-medium mb-1">Chat Bubble Style</label>
        <div class="grid grid-cols-2 gap-2">
          <button id="bubble-style-rounded" class="p-2 rounded-md bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
            <div class="h-6 w-12 rounded-full bg-primary-500"></div>
          </button>
          <button id="bubble-style-square" class="p-2 rounded-md bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
            <div class="h-6 w-12 rounded-md bg-primary-500"></div>
          </button>
        </div>
      </div>
      
      <div>
        <label class="block text-sm font-medium mb-1">Background</label>
        <div class="grid grid-cols-3 gap-2">
          <button id="bg-default" class="h-12 rounded-md bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700"></button>
          <button id="bg-pattern-1" class="h-12 rounded-md bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI1IiBoZWlnaHQ9IjUiPgo8cmVjdCB3aWR0aD0iNSIgaGVpZ2h0PSI1IiBmaWxsPSIjZmZmIj48L3JlY3Q+CjxyZWN0IHdpZHRoPSIxIiBoZWlnaHQ9IjEiIGZpbGw9IiNjY2MiPjwvcmVjdD4KPC9zdmc+')]"></button>
          <button id="bg-pattern-2" class="h-12 rounded-md bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800"></button>
        </div>
      </div>
    </div>
    
    <div class="p-4 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-2">
      <button id="reset-theme" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm">
        Reset to Default
      </button>
      <button id="save-theme" class="px-4 py-2 bg-primary-500 text-white rounded-md text-sm">
        Save Changes
      </button>
    </div>
  </div>
</div>

<!-- Add a button to open the theme customizer in your header -->
<button
  id="open-theme-customizer"
  class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200"
  aria-label="Customize theme"
>
  <svg class="h-5 w-5 text-gray-600 dark:text-gray-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
  </svg>
</button>

<script>
  // Theme customizer functionality
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
  
  // Default theme settings
  let themeSettings = {
    primaryColor: 'violet',
    fontSize: 'medium',
    bubbleStyle: 'rounded',
    background: 'default'
  };
  
  // Load saved theme settings
  const savedTheme = localStorage.getItem('themeSettings');
  if (savedTheme) {
    themeSettings = JSON.parse(savedTheme);
    applyThemeSettings();
  }
  
  // Open theme customizer
  openThemeCustomizerBtn.addEventListener('click', function() {
    themeCustomizerModal.classList.remove('hidden');
    updateThemeCustomizerUI();
  });
  
  // Close theme customizer
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
    applyThemeSettings();
    themeCustomizerModal.classList.add('hidden');
    showToast('Theme settings saved!', 'success');
  });
  
  // Reset theme to default
  resetThemeBtn.addEventListener('click', function() {
    themeSettings = {
      primaryColor: 'violet',
      fontSize: 'medium',
      bubbleStyle: 'rounded',
      background: 'default'
    };
    updateThemeCustomizerUI();
  });
  
  // Update the theme customizer UI based on current settings
  function updateThemeCustomizerUI() {
    // Update color selection
    themeColorBtns.forEach(btn => {
      const color = btn.getAttribute('data-color');
      btn.classList.toggle('ring-2', color === themeSettings.primaryColor);
      btn.classList.toggle('ring-offset-2', color === themeSettings.primaryColor);
      btn.classList.toggle('ring-black', color === themeSettings.primaryColor);
    });
    
    // Update font size display
    fontSizeDisplay.textContent = themeSettings.fontSize.charAt(0).toUpperCase() + themeSettings.fontSize.slice(1);
    
    // Update bubble style selection
    bubbleStyleRoundedBtn.classList.toggle('ring-2', themeSettings.bubbleStyle === 'rounded');
    bubbleStyleRoundedBtn.classList.toggle('ring-primary-500', themeSettings.bubbleStyle === 'rounded');
    bubbleStyleSquareBtn.classList.toggle('ring-2', themeSettings.bubbleStyle === 'square');
    bubbleStyleSquareBtn.classList.toggle('ring-primary-500', themeSettings.bubbleStyle === 'square');
    
    // Update background selection
    bgDefaultBtn.classList.toggle('ring-2', themeSettings.background === 'default');
    bgDefaultBtn.classList.toggle('ring-primary-500', themeSettings.background === 'default');
    bgPattern1Btn.classList.toggle('ring-2', themeSettings.background === 'pattern1');
    bgPattern1Btn.classList.toggle('ring-primary-500', themeSettings.background === 'pattern1');
    bgPattern2Btn.classList.toggle('ring-2', themeSettings.background === 'pattern2');
    bgPattern2Btn.classList.toggle('ring-primary-500', themeSettings.background === 'pattern2');
  }
  
  // Apply theme settings to the actual UI
  function applyThemeSettings() {
    // Apply primary color
    const root = document.documentElement;
    const colorMap = {
      violet: {
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
      },
      blue: {
        50: '#eff6ff',
        100: '#dbeafe',
        200: '#bfdbfe',
        300: '#93c5fd',
        400: '#60a5fa',
        500: '#3b82f6',
        600: '#2563eb',
        700: '#1d4ed8',
        800: '#1e40af',
        900: '#1e3a8a',
      },
      green: {
        50: '#f0fdf4',
        100: '#dcfce7',
        200: '#bbf7d0',
        300: '#86efac',
        400: '#4ade80',
        500: '#22c55e',
        600: '#16a34a',
        700: '#15803d',
        800: '#166534',
        900: '#14532d',
      },
      red: {
        50: '#fef2f2',
        100: '#fee2e2',
        200: '#fecaca',
        300: '#fca5a5',
        400: '#f87171',
        500: '#ef4444',
        600: '#dc2626',
        700: '#b91c1c',
        800: '#991b1b',
        900: '#7f1d1d',
      },
      yellow: {
        50: '#fefce8',
        100: '#fef9c3',
        200: '#fef08a',
        300: '#fde047',
        400: '#facc15',
        500: '#eab308',
        600: '#ca8a04',
        700: '#a16207',
        800: '#854d0e',
        900: '#713f12',
      }
    };
    
    const selectedColor = colorMap[themeSettings.primaryColor];
    for (const [key, value] of Object.entries(selectedColor)) {
      root.style.setProperty(`--primary-${key}`, value);
    }
    
    // Apply font size
    const fontSizeMap = {
      small: {
        base: '0.875rem',
        lg: '1rem'
      },
      medium: {
        base: '1rem',
        lg: '1.125rem'
      },
      large: {
        base: '1.125rem',
        lg: '1.25rem'
      }
    };
    
    const selectedFontSize = fontSizeMap[themeSettings.fontSize];
    document.body.style.fontSize = selectedFontSize.base;
    
    // Apply bubble style
    const bubbleStyleMap = {
      rounded: 'rounded-2xl',
      square: 'rounded-md'
    };
    
    // This would need to be applied when rendering messages
    // We'll update the renderMessages function later
    
    // Apply background
    const body = document.body;
    body.classList.remove('bg-pattern-1', 'bg-pattern-2');
    
    switch (themeSettings.background) {
      case 'pattern1':
        body.classList.add('bg-pattern-1');
        break;
      case 'pattern2':
        body.classList.add('bg-pattern-2');
        break;
      default:
        // Default background is already set in the CSS
        break;
    }
    
    // Re-render messages to apply new styles
    if (messages.length > 0) {
      renderMessages();
    }
  }
  
  // Update renderMessages function to use theme settings
  const originalRenderMessages = renderMessages;
  renderMessages = function() {
    // Apply bubble style before rendering
    const bubbleStyleClass = themeSettings.bubbleStyle === 'rounded' ? 'rounded-2xl' : 'rounded-md';
    
    // Call the original function
    originalRenderMessages();
    
    // Update all message bubbles with the new style
    document.querySelectorAll('.msg > div').forEach(bubble => {
      bubble.classList.remove('rounded-2xl', 'rounded-md');
      bubble.classList.add(bubbleStyleClass);
    });
    
    // Apply font size to messages
    const fontSizeMap = {
      small: 'text-sm',
      medium: 'text-base',
      large: 'text-lg'
    };
    
    document.querySelectorAll('.message-content').forEach(content => {
      content.classList.remove('text-sm', 'text-base', 'text-lg');
      content.classList.add(fontSizeMap[themeSettings.fontSize]);
    });
  };
</script>