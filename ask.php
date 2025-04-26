<?php
require_once 'config.php';
require_once 'db.php';

header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

function getIP() {
    return $_SERVER['REMOTE_ADDR'];
}

function rateLimit($conn, $ip) {
    $window = TIME_WINDOW;
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM chat_history WHERE user_ip=? AND created_at > (NOW() - INTERVAL ? SECOND)");
    $stmt->bind_param("si", $ip, $window);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    return $res['count'] >= RATE_LIMIT;
}

function saveChat($conn, $ip, $prompt, $response) {
    $stmt = $conn->prepare("INSERT INTO chat_history (user_ip, prompt, response) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $ip, $prompt, $response);
    $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $prompt = trim($input['prompt'] ?? '');
    $ip = getIP();

    if (strlen($prompt) === 0 || strlen($prompt) > 300) {
        echo json_encode(["error" => "Prompt must be 1–300 characters."]);
        exit;
    }

    if (rateLimit($conn, $ip)) {
        echo json_encode(["error" => "Rate limit exceeded. Try again in 1 minute."]);
        exit;
    }

    $apiKey = 'AIzaSyBRThL0mxynPxeZB3ox3kksn_v0Rn6SK_E';
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=" . $apiKey;

    $data = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $prompt]
                ]
            ]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);

    $res = curl_exec($ch);

    if (curl_errno($ch)) {
        echo json_encode(["error" => "Curl error: " . curl_error($ch)]);
        exit;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($httpCode !== 200) {
        echo json_encode(["error" => "API error $httpCode"]);
        exit;
    }

    $result = json_decode($res, true);
    $reply = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'No response';

    saveChat($conn, $ip, $prompt, $reply);
    echo json_encode(["response" => $reply]);
}
?>