<?php
/**
 * Language selector API endpoint
 * Returns current language settings and available languages
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Start session to get language preference
session_start();

// Available languages
$availableLanguages = [
    'en' => 'English',
    'es' => 'Español',
    'fr' => 'Français',
    'de' => 'Deutsch',
    'it' => 'Italiano',
    'pt' => 'Português',
    'ru' => 'Русский',
    'zh' => '中文',
    'ja' => '日本語',
    'ko' => '한국어',
    'ar' => 'العربية',
    'hi' => 'हिन्दी'
];

// Get current language from session or default to English
$currentLanguage = $_SESSION['language'] ?? 'en';

// Handle POST request to change language
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $newLanguage = $input['language'] ?? 'en';
    
    // Validate language
    if (array_key_exists($newLanguage, $availableLanguages)) {
        $_SESSION['language'] = $newLanguage;
        $currentLanguage = $newLanguage;
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Language updated successfully',
            'current_language' => $currentLanguage,
            'language_name' => $availableLanguages[$currentLanguage]
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid language code'
        ]);
    }
    exit;
}

// Handle GET request to get current language
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        'status' => 'success',
        'current_language' => $currentLanguage,
        'language_name' => $availableLanguages[$currentLanguage],
        'available_languages' => $availableLanguages
    ]);
    exit;
}

http_response_code(405);
echo json_encode([
    'status' => 'error',
    'message' => 'Method not allowed'
]);
?>