<?php
if (!function_exists('getIP')) {
    function getIP() {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}

if (!function_exists('getEnhancedSystemPrompt')) {
    function getEnhancedSystemPrompt($userPrompt) {
        $lowerPrompt = strtolower($userPrompt);
        $isCodeRequest = false;
        $isMarkdownRequest = false;
        
        $markdownKeywords = ['.md file', 'markdown file', 'markdown format', 'md format', 
                           'create md', 'generate md', 'make markdown', 'export markdown'];
        
        foreach ($markdownKeywords as $keyword) {
            if (strpos($lowerPrompt, $keyword) !== false) {
                $isMarkdownRequest = true;
                break;
            }
        }
        
        $codeKeywords = ['write code', 'create function', 'code example', 'programming', 'script', 
                        'algorithm', 'debug', 'fix bug', 'syntax error', 'code review', 'program',
                        'class', 'method', 'variable', 'loop', 'condition', 'api', 'database',
                        'html', 'css', 'javascript', 'python', 'php', 'java', 'c++', 'sql',
                        'function', 'array', 'object', 'string', 'integer', 'boolean', 'implement',
                        'optimize', 'refactor', 'structure', 'framework', 'library', 'package'];
        
        foreach ($codeKeywords as $keyword) {
            if (strpos($lowerPrompt, $keyword) !== false) {
                $isCodeRequest = true;
                break;
            }
        }
        
        if ($isMarkdownRequest) {
            return "You are a markdown documentation expert. Create clean, well-structured markdown with proper formatting, headers, and syntax. Use appropriate markdown elements like headers (# ## ###), lists (- *), code blocks (```), links, and emphasis (*bold* _italic_). Keep the content organized and readable.";
        } elseif ($isCodeRequest) {
            return "You are a senior software developer and coding expert. Provide clean, well-commented, and efficient code solutions. Always include proper error handling, follow best practices, and explain your approach. Format code properly with syntax highlighting when possible. Be thorough but concise.";
        } else {
            return "You are a helpful and knowledgeable AI assistant. Provide accurate, informative, and well-structured responses. Be conversational yet professional, and always strive to be helpful and clear in your explanations.";
        }
    }
}

if (!function_exists('getCodeGenerationPrompt')) {
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
}

if (!function_exists('getGeneralAssistantPrompt')) {
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
}

if (!function_exists('getMarkdownPrompt')) {
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
}

if (!function_exists('rateLimit')) {
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
}

if (!function_exists('saveChat')) {
    function saveChat($conn, $ip, $prompt, $response) {
        if (!$conn) return true;
        
        $logger = new ChatLogger();
        $sessionId = getSessionId();
        
        $logger->logMessage($sessionId, 'user', $prompt, false, null, $ip);
        
        $logger->logMessage($sessionId, 'assistant', $response, false, null, $ip);
        
        $logger->logAPIUsage(true);
        
        $stmt = $conn->prepare("INSERT INTO chat_history (user_ip, prompt, response) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $ip, $prompt, $response);
        return $stmt->execute();
    }
}
?>