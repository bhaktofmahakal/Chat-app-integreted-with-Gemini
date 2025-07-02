<?php


// 1. Set the global error handler as the very first thing.
require_once 'error_handler.php';
require_once 'database_logger.php';

// 2. Set headers that should always be present for this API endpoint.
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 3. Wrap all executable logic in a try-catch block.
try {
    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    // Only allow POST requests for the main logic
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        throw new Exception('Method not allowed. Only POST is accepted.');
    }

    // Include dependencies within the try block
    require_once 'config.php';
    require_once 'utilities.php'; // Include shared utilities

    //  utilities.php 

    // --- Main Logic ---

    $input = json_decode(file_get_contents("php://input"), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input: ' . json_last_error_msg());
    }

    $prompt = trim($input['prompt'] ?? '');
    $ip = getIP();

    if (empty($prompt) || strlen($prompt) > 2000) {
        http_response_code(400);
        throw new Exception('Prompt must be between 1 and 2000 characters.');
    }

    // Check rate limiting
    $conn = getDBConnection();
    if (rateLimit($conn, $ip)) {
        http_response_code(429);
        throw new Exception('Rate limit exceeded. Please wait before sending another message.');
    }

    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=" . API_KEY;
    
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

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'ChatApp/1.0'
    ]);

    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        throw new Exception("API connection error: " . $curlError);
    }
    if ($httpCode !== 200) {
        throw new Exception("API returned HTTP error $httpCode: " . substr($res, 0, 200));
    }

    $result = json_decode($res, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON response from API.");
    }

    $reply = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'Sorry, no response was generated.';
    
    // Get database connection and save chat
    $conn = getDBConnection();
    saveChat($conn, $ip, $prompt, $reply);
    
    echo json_encode([
        "success" => true,
        "response" => $reply,
        "timestamp" => time()
    ]);

} catch (Throwable $e) {
   
    throw $e;
}
?>