<?php


require_once 'error_handler.php';
require_once 'database_logger.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-control-allow-headers: Content-Type');
try {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    require_once 'config.php';
    require_once 'utilities.php';
    
    $conn = getDBConnection();

    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 1800,
            'path' => '/',
            'domain' => '',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        
        if (!session_start()) {
            throw new Exception("Failed to start session. Streaming is unavailable.");
        }
    }





    function streamResponse($url, $data) {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('Access-Control-Allow-Origin: *');
        
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
        
        if ($curlError || $httpCode !== 200) {
            echo "data: " . json_encode([
                "error" => "Streaming failed: " . ($curlError ?: "HTTP $httpCode")
            ]) . "\n\n";
            flush();
        }
        
        exit;
    }

    $ip = getIP();

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['stream'])) {
        error_log("Stream request - Session ID: " . (session_id() ?: 'none'));
        error_log("Session data: " . print_r($_SESSION, true));
        
        $streamToken = $_GET['token'] ?? '';
        
        if (!isset($_SESSION['pending_prompt']) || empty($_SESSION['pending_prompt']) ||
            !isset($_SESSION['stream_token']) || $_SESSION['stream_token'] !== $streamToken) {
            
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
        $parts = $_SESSION['pending_parts'] ?? [["text" => $prompt]];
        unset($_SESSION['pending_prompt']);
        unset($_SESSION['pending_parts']);
        unset($_SESSION['stream_token']);

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . API_KEY;
        
        $systemPrompt = getEnhancedSystemPrompt($prompt);
        $enhancedPrompt = $systemPrompt . "\n\nUser Request: " . $prompt;
        
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
        streamResponse($url, $data);
    }
     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (rateLimit($conn, $ip)) {
            http_response_code(429);
            throw new Exception('Rate limit exceeded.');
        }

        $prompt = '';
        $parts = [];

        if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            error_log("Image upload detected - File name: " . $_FILES['image']['name']);
            error_log("Image file size: " . $_FILES['image']['size']);
            
            $prompt = trim($_POST['prompt'] ?? '');
            $imageFile = $_FILES['image'];
            
            $fileType = mime_content_type($imageFile['tmp_name']);
            error_log("Detected file type: " . $fileType);
            
            if (!in_array($fileType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                throw new Exception("Invalid file type: $fileType");
            }
            
            $base64Image = base64_encode(file_get_contents($imageFile['tmp_name']));
            error_log("Base64 image length: " . strlen($base64Image));
            
            $parts = [["text" => $prompt], ["inline_data" => ["mime_type" => $fileType, "data" => $base64Image]]];
            error_log("Created parts array with image data");
        } else {
            if (!empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                $errorMsg = match($_FILES['image']['error']) {
                    UPLOAD_ERR_INI_SIZE => 'File too large (exceeds server limit)',
                    UPLOAD_ERR_FORM_SIZE => 'File too large (exceeds form limit)',
                    UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION => 'File upload stopped by extension',
                    default => 'Unknown upload error'
                };
                error_log("File upload error: " . $errorMsg);
                throw new Exception("File upload failed: " . $errorMsg);
            }
            
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
            $_SESSION['pending_prompt'] = $prompt;
            $_SESSION['pending_parts'] = $parts;
            $streamToken = md5(uniqid() . $prompt . time());
            $_SESSION['stream_token'] = $streamToken;
            
            error_log("Prepared stream - Session ID: " . session_id());
            error_log("Stored prompt: " . substr($prompt, 0, 50) . "...");
            error_log("Stored parts count: " . count($parts));
            error_log("Stream token: " . $streamToken);
            
            echo json_encode([
                "status" => "ready", 
                "session_id" => session_id(),
                "stream_token" => $streamToken
            ]);
            exit;
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . API_KEY;
        
        $systemPrompt = getEnhancedSystemPrompt($prompt);
        $enhancedPrompt = $systemPrompt . "\n\nUser Request: " . $prompt;
        
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
    throw $e;
}
?>