<?php

error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');
ini_set('max_execution_time', 65); // 60-second timeout + 5-second buffer
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'ai-config.php';
require_once 'ai-service.php';

// Initialize AI service with configuration
$initResult = initializeAIService();
if (!$initResult['success']) {
    echo json_encode([
        'success' => false,
        'message' => 'AI service not properly configured',
        'errors' => $initResult['errors'] ?? [$initResult['error'] ?? 'Unknown error']
    ]);
    exit;
}

try {
    $aiService = new AIService(GEMINI_API_KEY);
    
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    $response = ['success' => false, 'message' => 'Invalid action'];
    
    switch ($action) {
        case 'analyze_errors':
            $latexCode = $_POST['latex_code'] ?? '';
            $errorMessage = $_POST['error_message'] ?? '';
            $userId = $_POST['user_id'] ?? null;
            
            if (empty($latexCode) || empty($errorMessage)) {
                $response = ['success' => false, 'message' => 'LaTeX code and error message are required'];
                break;
            }
            
            $result = $aiService->analyzeAndFixError($latexCode, $errorMessage);
            $response = [
                'success' => $result['success'],
                'data' => $result,
                'message' => $result['success'] ? 'Error analysis completed' : 'Error analysis failed'
            ];
            break;
            
        case 'comprehensive_analysis':
            $latexCode = $_POST['latex_code'] ?? '';
            $errorMessage = $_POST['error_message'] ?? '';
            $codeLength = intval($_POST['code_length'] ?? 0);
            $lineCount = intval($_POST['line_count'] ?? 0);
            $userId = $_POST['user_id'] ?? null;
            
            if (empty($latexCode)) {
                $response = ['success' => false, 'message' => 'LaTeX code is required for comprehensive analysis'];
                break;
            }
            
            // Simple analysis for up to 15K characters
            $result = $aiService->simpleLatexAnalysis($latexCode, $errorMessage);
            
            $response = [
                'success' => $result['success'],
                'data' => $result,
                'message' => $result['success'] ? 'Comprehensive analysis completed' : 'Analysis failed'
            ];
            break;
            
        case 'analyze_quality':
            $latexCode = $_POST['latex_code'] ?? '';
            $userId = $_POST['user_id'] ?? null;
            
            if (empty($latexCode)) {
                $response = ['success' => false, 'message' => 'LaTeX code is required'];
                break;
            }
            
            $result = $aiService->analyzeDocumentQuality($latexCode, $userId);
            $response = [
                'success' => true,
                'data' => $result,
                'message' => 'Quality analysis completed'
            ];
            break;
            
        case 'generate_citation':
            $input = $_POST['input'] ?? '';
            $style = $_POST['style'] ?? 'ieee';
            $userId = $_POST['user_id'] ?? null;
            
            if (empty($input)) {
                $response = ['success' => false, 'message' => 'Input (URL or text) is required'];
                break;
            }
            
            $result = $aiService->generateCitation($input, $style, $userId);
            
            if (is_array($result) && isset($result['success'])) {
                if ($result['success']) {
                    $response = [
                        'success' => true,
                        'data' => ['citation' => $result['citation']],
                        'message' => 'Citation generated successfully'
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'error' => $result['error'],
                        'errors' => $result['errors'],
                        'message' => $result['message']
                    ];
                }
            } else {
                $response = [
                    'success' => true,
                    'data' => ['citation' => $result],
                    'message' => 'Citation generated successfully'
                ];
            }
            break;
            
        case 'get_ai_stats':
            // Get AI usage statistics
            $stats = $aiService->getUsageStats();
            $response = [
                'success' => true,
                'data' => $stats,
                'message' => 'Statistics retrieved'
            ];
            break;
            
        case 'comprehensive_analysis':
            $latexCode = $_POST['latex_code'] ?? '';
            $errorMessage = $_POST['error_message'] ?? '';
            
            if (empty($latexCode)) {
                $response = ['success' => false, 'message' => 'LaTeX code is required'];
                break;
            }
            
            // Use simple analysis for better performance
            $result = $aiService->simpleLatexAnalysis($latexCode, $errorMessage);
            
            if ($result['success']) {
                $response = [
                    'success' => true,
                    'data' => [
                        'analysis' => $result['analysis'],
                        'model' => $result['model'],
                        'original_length' => $result['original_length']
                    ],
                    'message' => 'Comprehensive analysis completed'
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => $result['error'] ?? 'Analysis failed',
                    'error' => $result['error'] ?? 'Unknown error'
                ];
            }
            break;
            
        case 'test_services':
            // Test both Gemini and Ollama connectivity
            $testResults = [
                'gemini' => $aiService->testGeminiConnection(),
                'ollama' => $aiService->testOllamaConnection(),
                'database' => $aiService->testDatabaseConnection()
            ];
            
            $response = [
                'success' => true,
                'data' => $testResults,
                'message' => 'Service test completed'
            ];
            break;
            
        default:
            $response = [
                'success' => false,
                'message' => 'Unknown action: ' . $action,
                'available_actions' => [
                    'analyze_errors',
                    'analyze_quality', 
                    'comprehensive_analysis',
                    'generate_citation',
                    'get_ai_stats',
                    'test_services'
                ]
            ];
    }
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => 'AI Service Error: ' . $e->getMessage(),
        'error_code' => $e->getCode()
    ];
}

// Ensure clean JSON output
ob_clean();
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
?>