const chatBox = document.getElementById("chat-box");
const promptInput = document.getElementById("prompt");
const sendBtn = document.getElementById("send");
const loading = document.getElementById("loading");
const mic = document.getElementById("mic");

function appendMsg(sender, msg) {
  const div = document.createElement("div");
  div.className = "msg " + sender;
  div.textContent = msg;
  chatBox.appendChild(div);
  chatBox.scrollTop = chatBox.scrollHeight;
}

sendBtn.onclick = async () => {
  const prompt = promptInput.value.trim();
  if (!prompt) return;

  appendMsg("user", prompt);
  promptInput.value = "";
  loading.classList.remove("hidden");

  try {
    const res = await fetch("ask.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({ prompt })
    });

    const data = await res.json();
    appendMsg("bot", data.response || data.error || "Error");
  } catch (error) {
    appendMsg("bot", "Something went wrong. Please try again.");
  } finally {
    loading.classList.add("hidden");
  }
};

// Voice input
mic.onclick = () => {
  const recog = new webkitSpeechRecognition();
  recog.lang = 'en-US';
  recog.start();
  recog.onresult = (e) => {
    promptInput.value = e.results[0][0].transcript;
  };
};
// File upload
const fileButton = document.getElementById('fileButton');
const fileInput = document.getElementById('fileInput');

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
    // Add this to your script
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