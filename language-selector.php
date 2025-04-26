<!-- Language Selector -->
<?php
require_once 'index.php';

require_once 'config.php';
require_once 'db.php';
?>
<div class="relative group">
  <button
    id="language-selector-btn"
    class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200"
    aria-label="Change language"
  >
    <svg class="h-5 w-5 text-gray-600 dark:text-gray-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
    </svg>
  </button>

  <div class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-20 border border-gray-200 dark:border-gray-700">
    <div class="py-1">
      <button data-lang="en" class="language-option block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
        English
      </button>
      <button data-lang="es" class="language-option block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
        Español
      </button>
      <button data-lang="fr" class="language-option block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
        Français
      </button>
      <button data-lang="de" class="language-option block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
        Deutsch
      </button>
      <button data-lang="hi" class="language-option block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
        हिन्दी
      </button>
    </div>
  </div>
</div>

<script>
  // Language translations
  const translations = {
    en: {
      welcome: "Welcome to AI Chat Assistant",
      start_conversation: "Start a conversation by typing a message below. Your chat history will be saved locally.",
      type_message: "Type your message...",
      send: "Send",
      clear_history: "Clear Chat History",
      ai_thinking: "AI is thinking..."
    },
    es: {
      welcome: "Bienvenido al Asistente de Chat AI",
      start_conversation: "Inicia una conversación escribiendo un mensaje a continuación. Tu historial de chat se guardará localmente.",
      type_message: "Escribe tu mensaje...",
      send: "Enviar",
      clear_history: "Borrar historial de chat",
      ai_thinking: "La IA está pensando..."
    },
    fr: {
      welcome: "Bienvenue sur l'Assistant de Chat IA",
      start_conversation: "Commencez une conversation en tapant un message ci-dessous. Votre historique de chat sera enregistré localement.",
      type_message: "Tapez votre message...",
      send: "Envoyer",
      clear_history: "Effacer l'historique de chat",
      ai_thinking: "L'IA réfléchit..."
    },
    de: {
      welcome: "Willkommen beim KI-Chat-Assistenten",
      start_conversation: "Beginnen Sie ein Gespräch, indem Sie unten eine Nachricht eingeben. Ihr Chat-Verlauf wird lokal gespeichert.",
      type_message: "Geben Sie Ihre Nachricht ein...",
      send: "Senden",
      clear_history: "Chat-Verlauf löschen",
      ai_thinking: "KI denkt nach..."
    },
    hi: {
      welcome: "AI चैट असिस्टेंट में आपका स्वागत है",
      start_conversation: "नीचे एक संदेश टाइप करके बातचीत शुरू करें। आपका चैट इतिहास स्थानीय रूप से सहेजा जाएगा।",
      type_message: "अपना संदेश टाइप करें...",
      send: "भेजें",
      clear_history: "चैट इतिहास साफ़ करें",
      ai_thinking: "AI सोच रहा है..."
    }
  };
  
  // Set language
  function setLanguage(lang) {
    if (!translations[lang]) return;
    
    const t = translations[lang];
    
    // Update UI elements
    document.querySelector('#welcome-message h2').textContent = t.welcome;
    document.querySelector('#welcome-message p').textContent = t.start_conversation;
    document.querySelector('#prompt-input').placeholder = t.type_message;
    document.querySelector('#send-button').setAttribute('aria-label', t.send);
    document.querySelector('#clear-chat').textContent = t.clear_history;
    document.querySelector('#loading-indicator span').textContent = t.ai_thinking;
    
    // Save preference
    localStorage.setItem('language', lang);
    
    showToast(`Language changed to ${lang}`, 'success');
  }
  
  // Language selector event listeners
  document.querySelectorAll('.language-option').forEach(option => {
    option.addEventListener('click', function() {
      const lang = this.getAttribute('data-lang');
      setLanguage(lang);
    });
  });
  
  // Load saved language preference
  const savedLanguage = localStorage.getItem('language') || 'en';
  setLanguage(savedLanguage);
</script>