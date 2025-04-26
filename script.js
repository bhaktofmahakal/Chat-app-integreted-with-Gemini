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

function scrollToBottom() {
  chatBox.scrollTop = chatBox.scrollHeight;
}

function scrollToTop() {
  chatBox.scrollTop = 0;
}

function copyToClipboard(btn) {
  const text = btn.previousElementSibling.textContent;
  navigator.clipboard.writeText(text);
  btn.innerText = "✅";
  setTimeout(() => (btn.innerText = "📋"), 1000);
}
