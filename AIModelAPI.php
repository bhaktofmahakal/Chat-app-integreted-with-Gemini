<?php


// 1. Set the global error handler as the very first thing.
require_once 'error_handler.php';
require_once 'database_logger.php';

// 2. Set headers that should always be present for this API endpoint.
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-control-allow-headers: Content-Type');

// 3. Wrap all executable logic in a try-catch block.
try {
    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    // Include dependencies within the try block
    require_once 'config.php';
    
    // Initialize database connection
    $conn = getDBConnection();

    // Start session safely with proper settings
    if (session_status() === PHP_SESSION_NONE) {
        // Set session cookie parameters for better persistence
        session_set_cookie_params([
            'lifetime' => 1800, // 30 minutes
            'path' => '/',
            'domain' => '',
            'secure' => false, // Set to true if using HTTPS
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        
        if (!session_start()) {
            throw new Exception("Failed to start session. Streaming is unavailable.");
        }
    }

    // --- Helper Functions ---

    function getIP() {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    function getSessionId() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return session_id();
    }

    function getEnhancedSystemPrompt($userPrompt) {
        $lowerPrompt = strtolower($userPrompt);
        $isCodeRequest = false;
        $isMarkdownRequest = false;
        
        // Check for markdown-specific requests
        $markdownKeywords = ['.md file', 'markdown file', 'markdown format', 'md format', 
                           'create md', 'generate md', 'make markdown', 'export markdown'];
        
        foreach ($markdownKeywords as $keyword) {
            if (strpos($lowerPrompt, $keyword) !== false) {
                $isMarkdownRequest = true;
                break;
            }
        }
        
        // Check for negative keywords that indicate NOT a code request
        $negativeKeywords = ['not code', 'no code', 'not the code', 'without code', 'only info', 'only give info', 'only theory', 'theoretical', 'explain', 'what is', 'about'];
        $isNegativeRequest = false;
        
        foreach ($negativeKeywords as $keyword) {
            if (strpos($lowerPrompt, $keyword) !== false) {
                $isNegativeRequest = true;
                break;
            }
        }
        
        // Only detect code if not explicitly asking for non-code
        if (!$isNegativeRequest) {
            // Detect if user is asking for code (more specific detection)
            $codeKeywords = ['write code', 'create code', 'generate code', 'show code', 'write function', 'create function', 'write script', 'create script', 'implement code', 'build application'];
            $languageKeywords = ['python', 'javascript', 'js', 'php', 'java', 'html', 'css', 'sql', 'bash', 'json', 'c++', 'typescript'];
            
            foreach ($codeKeywords as $keyword) {
                if (strpos($lowerPrompt, $keyword) !== false) {
                    $isCodeRequest = true;
                    break;
                }
            }
            
            foreach ($languageKeywords as $lang) {
                if (strpos($lowerPrompt, $lang) !== false) {
                    $isCodeRequest = true;
                    break;
                }
            }
        }
        
        if ($isCodeRequest) {
            return getCodeGenerationPrompt();
        } elseif ($isMarkdownRequest) {
            return getMarkdownPrompt();
        } else {
            return getGeneralAssistantPrompt();
        }
    }

    function getCodeGenerationPrompt() {
        return "# Expert Code Assistant

Provide clean, production-ready code with proper markdown formatting.

## Format Rules:
- Use ```language code blocks (e.g., ```python, ```javascript)
- Never use ** asterisks ** for code
- Add brief explanations and comments
- Include usage examples when helpful

## Response Structure:
1. Brief solution explanation
2. Code block with syntax highlighting
3. Key features/usage notes

Focus on clean, working code with clear explanations.";
    }

    function getGeneralAssistantPrompt() {
        return "You are a helpful AI assistant. Provide clear, direct responses in plain text format.

IMPORTANT FORMATTING RULES:
- Use plain text only (no markdown formatting)
- Do NOT use ** for bold text
- Do NOT use * for italics  
- Use simple bullet points with - or numbers
- Only use markdown when user specifically asks for .md file or markdown format

Guidelines:
- Be direct and concise
- Provide practical solutions
- Use simple formatting
- Include relevant examples in plain text

Keep responses natural and conversational without markdown styling.";
    }

    function getMarkdownPrompt() {
        return "You are creating a markdown document as specifically requested by the user.

FORMAT REQUIREMENTS:
- Use proper markdown syntax with ** for bold, * for italics
- Use # for headers, ## for subheaders
- Use - or * for bullet points
- Use ```language for code blocks
- Use > for blockquotes
- Use [text](url) for links

Create well-structured markdown content suitable for saving as .md file.";
    }

    function rateLimit($conn, $ip) {
        if (!$conn) return false;
        $window = defined('TIME_WINDOW') ? TIME_WINDOW : 60;
        $limit = defined('RATE_LIMIT') ? RATE_LIMIT : 10;
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM chat_history WHERE user_ip=? AND created_at > (NOW() - INTERVAL ? SECOND)");
        $stmt->bind_param("si", $ip, $window);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return $res['count'] >= $limit;
    }

    function saveChat($conn, $ip, $prompt, $response) {
        if (!$conn) return true;
        
        // Use new database logger
        $logger = new ChatLogger();
        $sessionId = getSessionId();
        
        // Save user message
        $logger->logMessage($sessionId, 'user', $prompt, false, null, $ip);
        
        // Save assistant response
        $logger->logMessage($sessionId, 'assistant', $response, false, null, $ip);
        
        // Log API usage stats
        $logger->logAPIUsage(true);
        
        // Also keep old format for backward compatibility
        $stmt = $conn->prepare("INSERT INTO chat_history (user_ip, prompt, response) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $ip, $prompt, $response);
        return $stmt->execute();
    }

    function streamResponse($url, $data) {
        // Set proper headers for Server-Sent Events
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('Access-Control-Allow-Origin: *');
        
        // Disable output buffering for streaming
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        $ch = curl_init($url . "&alt=sse");
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'ChatApp/1.0',
            CURLOPT_WRITEFUNCTION => function($curl, $chunk) {
                echo $chunk;
                flush();
                return strlen($chunk);
            }
        ]);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        // Handle errors
        if ($curlError || $httpCode !== 200) {
            echo "data: " . json_encode([
                "error" => "Streaming failed: " . ($curlError ?: "HTTP $httpCode")
            ]) . "\n\n";
            flush();
        }
        
        exit;
    }

    // --- Main Logic ---

    $ip = getIP();

    // Handle GET request for streaming
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['stream'])) {
        // Better session debugging
        error_log("Stream request - Session ID: " . (session_id() ?: 'none'));
        error_log("Session data: " . print_r($_SESSION, true));
        
        // Check token-based authentication first
        $streamToken = $_GET['token'] ?? '';
        
        // Check if session has pending prompt and valid token
        if (!isset($_SESSION['pending_prompt']) || empty($_SESSION['pending_prompt']) ||
            !isset($_SESSION['stream_token']) || $_SESSION['stream_token'] !== $streamToken) {
            
            // Set proper headers for EventSource error
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('Access-Control-Allow-Origin: *');
            
            http_response_code(400);
            echo "data: " . json_encode(["error" => "Session expired or invalid token. Please try again."]) . "\n\n";
            flush();
            exit;
        }
        
        $prompt = $_SESSION['pending_prompt'];
        unset($_SESSION['pending_prompt']);
        unset($_SESSION['stream_token']);

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . API_KEY;
        
        // Enhanced system prompt for better responses
        $systemPrompt = getEnhancedSystemPrompt($prompt);
        $enhancedPrompt = $systemPrompt . "\n\nUser Request: " . $prompt;
        
        $data = [
            "contents" => [["parts" => [["text" => $enhancedPrompt]]]],
            "generationConfig" => [ 
                "temperature" => 0.8, 
                "topK" => 50, 
                "topP" => 0.95, 
                "maxOutputTokens" => 1024,
                "candidateCount" => 1
            ],
            "safetySettings" => [
                ["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_MEDIUM_AND_ABOVE"],
                ["category" => "HARM_CATEGORY_HATE_SPEECH", "threshold" => "BLOCK_MEDIUM_AND_ABOVE"],
                ["category" => "HARM_CATEGORY_SEXUALLY_EXPLICIT", "threshold" => "BLOCK_MEDIUM_AND_ABOVE"],
                ["category" => "HARM_CATEGORY_DANGEROUS_CONTENT", "threshold" => "BLOCK_MEDIUM_AND_ABOVE"]
            ]
        ];
        streamResponse($url, $data);
    }

    // Handle POST request
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (rateLimit($conn, $ip)) {
            http_response_code(429);
            throw new Exception('Rate limit exceeded.');
        }

        $prompt = '';
        $parts = [];

        if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $prompt = trim($_POST['prompt'] ?? '');
            $imageFile = $_FILES['image'];
            $fileType = mime_content_type($imageFile['tmp_name']);
            if (!in_array($fileType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                throw new Exception("Invalid file type: $fileType");
            }
            $base64Image = base64_encode(file_get_contents($imageFile['tmp_name']));
            $parts = [["text" => $prompt], ["inline_data" => ["mime_type" => $fileType, "data" => $base64Image]]];
        } else {
            $input = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON input: ' . json_last_error_msg());
            }
            $prompt = trim($input['prompt'] ?? '');
            $parts = [["text" => $prompt]];
        }

        if (empty($prompt) || strlen($prompt) > 3000) {
            throw new Exception("Prompt must be 1-3000 characters");
        }

        if (isset($_GET['prepareStream'])) {
            // Store in session AND create a temporary token
            $_SESSION['pending_prompt'] = $prompt;
            $streamToken = md5(uniqid() . $prompt . time());
            $_SESSION['stream_token'] = $streamToken;
            
            error_log("Prepared stream - Session ID: " . session_id());
            error_log("Stored prompt: " . substr($prompt, 0, 50) . "...");
            error_log("Stream token: " . $streamToken);
            
            echo json_encode([
                "status" => "ready", 
                "session_id" => session_id(),
                "stream_token" => $streamToken
            ]);
            exit;
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . API_KEY;
        
        // Enhanced system prompt for better responses
        $systemPrompt = getEnhancedSystemPrompt($prompt);
        $enhancedPrompt = $systemPrompt . "\n\nUser Request: " . $prompt;
        
        // Update the first part with enhanced prompt
        $parts[0] = ["text" => $enhancedPrompt];
        
        $data = [
            "contents" => [["parts" => $parts]],
            "generationConfig" => [ 
                "temperature" => 0.8, 
                "topK" => 50, 
                "topP" => 0.95, 
                "maxOutputTokens" => 1024,
                "candidateCount" => 1
            ],
            "safetySettings" => [
                ["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_MEDIUM_AND_ABOVE"],
                ["category" => "HARM_CATEGORY_HATE_SPEECH", "threshold" => "BLOCK_MEDIUM_AND_ABOVE"],
                ["category" => "HARM_CATEGORY_SEXUALLY_EXPLICIT", "threshold" => "BLOCK_MEDIUM_AND_ABOVE"],
                ["category" => "HARM_CATEGORY_DANGEROUS_CONTENT", "threshold" => "BLOCK_MEDIUM_AND_ABOVE"]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_TIMEOUT => 20,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'ChatApp/1.0'
        ]);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) throw new Exception("cURL error: " . $curlError);
        if ($httpCode !== 200) throw new Exception("API HTTP error: $httpCode - " . substr($res, 0, 200));

        $result = json_decode($res, true);
        if (json_last_error() !== JSON_ERROR_NONE) throw new Exception("Invalid API response JSON.");

        $textResponse = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'No response generated';
        
        saveChat($conn, $ip, $prompt, $textResponse);
        
        echo json_encode(["response" => $textResponse]);

    } else {
        throw new Exception('Method not allowed.');
    }

} catch (Throwable $e) {
    // The global exception handler from error_handler.php will catch this,
    // log it, and output a clean JSON error response.
    throw $e;
}
?>