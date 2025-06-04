# 🧠 AI Assistant (Powered by Gemini AI)

A **real-time, intelligent chat application** built with **PHP**, **MySQL**, **JavaScript**, and **AJAX**, fully integrated with the **Gemini AI API** to generate smart, dynamic responses. Users can enjoy a human-like conversational experience in real-time!

<p align="center">
  <img src="https://img.shields.io/badge/Language-PHP-blue?style=for-the-badge&logo=php&logoColor=white">
  <img src="https://img.shields.io/badge/Database-MySQL-yellow?style=for-the-badge&logo=mysql&logoColor=black">
  <img src="https://img.shields.io/badge/Frontend-HTML5%2C%20CSS3%2C%20JavaScript-orange?style=for-the-badge&logo=html5&logoColor=white">
  <img src="https://img.shields.io/badge/AJAX-Real--Time-lightgrey?style=for-the-badge&logo=ajax">
  <img src="https://img.shields.io/badge/AI-Gemini%20API-purple?style=for-the-badge&logo=googlecloud">
  <img src="https://img.shields.io/badge/Version%20Control-Git-black?style=for-the-badge&logo=git&logoColor=white">
</p>

---

## ✨ Features

- 🔐 **User Registration & Secure Login**  
  - Passwords hashed using PHP’s `password_hash()`  
  - Session-based authentication  

- 💬 **Real-Time Chat**  
  - AJAX-powered messaging for instant updates  
  - Live typing indicator & online/offline status  

- 🤖 **AI-Powered Responses**  
  - Messages sent to Gemini API via PHP server  
  - Gemini AI generates smart replies in milliseconds  
  - Seamless conversational flow—just like chatting with a real person  

- 🚀 **Lightweight & Fast**  
  - Minimal dependencies for snappy performance  
  - Clean, responsive UI ensures smooth experience on desktop and mobile  

- 🔒 **Security Best Practices**  
  - Input validation & sanitization to prevent SQL Injection  
  - CSRF protection on critical endpoints  
  - HTTPS (recommended for production)  

---

## 🧰 Tech Stack

| Layer            | Technology                         |
|------------------|-------------------------------------|
| Frontend         | HTML5, CSS3, JavaScript (AJAX)      |
| Backend          | PHP (Core PHP)                      |
| AI Integration   | Gemini AI API                       |
| Database         | MySQL                                |
| Version Control  | Git                                 |

---

## 🧠 How Gemini AI Integration Works

1. **User Sends a Message**  
   - The chat interface captures the user’s input and triggers an AJAX request.

2. **PHP Server Forwards to Gemini API**  
   - `includes/gemini_api.php` handles API authentication and request formatting.
   - The user’s message is sent over HTTPS to the Gemini endpoint.

3. **Gemini AI Generates a Reply**  
   - Gemini processes the prompt and returns a context-aware, human-like response.

4. **Reply Displayed in Real-Time**  
   - AJAX callback renders Gemini’s reply instantly in the chat window.

✨ This seamless integration creates an **interactive, intelligent chat** that feels remarkably natural.

---

## 📂 Project Structure

<details>
<summary>Click to expand</summary>



</details>

---

## 📸 Screenshots

> Screenshots will go here once available.

Example placeholders:

- **Login Page**  
  ![Login Page](image/login.png)

- **Registration Page**  
  ![Register Page](image/register.png)

- **Chat Interface**  
  ![Chat Screen](image/chat_screen.png)

---

## 🚀 Installation & Setup

Follow these steps to get the Chat App up and running locally:

### 1. Clone the Repository
```bash

git clone https://github.com/bhaktofmahakal/Chat-app-integreted-with-Gemini.git
cd Chat-app-integreted-with-Gemini
