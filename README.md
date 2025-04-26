🧠 Chat App (Powered by Gemini AI)
A real-time, intelligent chat application built using PHP, MySQL, JavaScript, and AJAX, integrated with Gemini AI API for generating smart and dynamic responses.
This app allows users to experience human-like conversation with an AI agent in real-time!

✨ Features

    User Registration & Secure Login
    
    Real-time Chat (AJAX-based)
    
    AI-Powered Responses (Gemini API Integration)
    
    Instant Messaging Experience
    
    Live Typing Indicator
    
    Online/Offline User Status
    
    Clean and Responsive UI
    
    Passwords Secured with Hashing
    
    Lightweight and Fast Performance

🧰 Tech Stack

    Frontend: HTML, CSS, JavaScript (AJAX)
    
    Backend: Core PHP
    
    AI Integration: Gemini AI API
    
    Database: MySQL

🧠 How Gemini API is Used

    User sends a message ➔
    
    Message is sent to Gemini API via PHP server ➔
    
    Gemini AI generates a smart reply ➔
    
    Reply is displayed in the chat instantly.
    
    ✨ This adds a dynamic, intelligent conversational experience just like chatting with a real human.

📂 Project Structure

bash

Copy

Edit

    Chat-app/
    ├── assets/           # Static files: CSS, JS, Images
    ├── includes/         # PHP scripts: DB connection, authentication, Gemini API integration
    ├── index.php         # Login page
    ├── register.php      # Registration page
    ├── chat.php          # Chat interface (AI chat)
    ├── logout.php        # Logout script
    ├── gemini_api.php    # (Handles communication with Gemini API)
    └── README.md
    
🚀 Setup Instructions
Clone the repository:

bash

Copy

Edit

    git clone https://github.com/bhaktofmahakal/Chat-app.git
Create a MySQL database.

Configure your database credentials in includes/db.php.

Set your Gemini API Key in includes/gemini_api.php:

    php
    Copy
    Edit
    $apiKey = 'YOUR_GEMINI_API_KEY';
Start your XAMPP/WAMP server and open the app in your browser.

Register a new account, log in, and start chatting with Gemini AI!

📸 Screenshots (Recommended)

(Add screenshots of login page, registration page, chat screen showing AI responses)

📝 License

    This project is open-source and free to use for learning purposes.
