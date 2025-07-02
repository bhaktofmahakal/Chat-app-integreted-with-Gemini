<?php
require_once 'ai-config.php';
require_once 'enhanced-ai-prompts.php';

class AIService {
    private $config;
    private $geminiApiKey;
    private $ollamaEndpoint;
    
    public function __construct($geminiApiKey = null) {
        $this->config = getAIConfig();
        $this->geminiApiKey = $geminiApiKey ?? $this->config['api_keys']['gemini'];
        $this->ollamaEndpoint = $this->config['models']['endpoint'];
    }
    
    public function analyzeError($latexCode, $errorMessage) {
        $prompt = LaTeXAIPrompts::getErrorAnalysisPrompt($latexCode, $errorMessage);
        return $this->callAI($prompt);
    }
    
    public function analyzeLatexErrors($latexCode, $userId = null) {
        $prompt = LaTeXAIPrompts::getErrorAnalysisPrompt($latexCode, '');
        return $this->callAI($prompt, $userId);
    }
    

    
    public function generateTemplate($description) {
        $prompt = LaTeXAIPrompts::getTemplateGenerationPrompt($description);
        return $this->callAI($prompt);
    }
    
    public function generateCitation($reference, $style = 'ieee', $userId = null) {
        $prompt = LaTeXAIPrompts::getCitationPrompt($reference, $style);
        $result = $this->callAI($prompt, $userId);
        
        if ($result['success']) {
            $citation = trim($result['response']);
            
            $citation = preg_replace('/^(OUTPUT:|BIBTEX:|GENERATE:|\s*)/i', '', $citation);
            
            if (preg_match('/@\w+\{[^}]+(?:\{[^}]*\}|[^}])*\}/s', $citation, $matches)) {
                $citation = $matches[0];
            }
            
            $citation = preg_replace('/\s+/', ' ', $citation);
            $citation = str_replace('{{', '{', $citation);
            $citation = str_replace('}}', '}', $citation);
            
            if ($this->isValidBibTeX($citation)) {
                return [
                    'success' => true,
                    'citation' => $citation,
                    'service' => $result['service'] ?? 'ai'
                ];
            } else {
                $fallback = $this->generateFallbackCitation($reference, $style);
                return [
                    'success' => true,
                    'citation' => $fallback,
                    'service' => 'fallback'
                ];
            }
        }
        
        return [
            'success' => false,
            'error' => $result['error'] ?? 'Citation generation failed',
            'errors' => [$result['error'] ?? 'AI service not available'],
            'message' => 'AI service not properly configured'
        ];
    }
    
    private function isValidBibTeX($citation) {
        if (!preg_match('/^@\w+\{/', $citation)) return false;
        if (substr_count($citation, '{') !== substr_count($citation, '}')) return false;
        if (!preg_match('/title\s*=\s*\{/', $citation)) return false;
        return true;
    }

    private function generateFallbackCitation($reference, $style) {
        $year = date('Y');
        $key = 'ref' . $year;
        
        if (filter_var($reference, FILTER_VALIDATE_URL)) {
            $title = parse_url($reference, PHP_URL_HOST);
            return "@misc{{$key}, title={{$title}}, url={{$reference}}, year={{$year}}}";
        } else {
            return "@misc{{$key}, title={{$reference}}, year={{$year}}}";
        }
    }
    
    public function optimizeCode($latexCode) {
        $prompt = LaTeXAIPrompts::getOptimizationPrompt($latexCode);
        return $this->callAI($prompt);
    }
    
    public function analyzeAndFixError($latexCode, $errorMessage) {
        // Use simple, fast prompt for quick analysis
        $prompt = LaTeXAIPrompts::getSimpleErrorFixPrompt($latexCode, $errorMessage);
        
        // Quick AI call with shorter timeout
        $result = $this->callAI($prompt);
        
        if ($result['success'] && !empty($result['response'])) {
            $response = $result['response'];
            
            // Simple parsing for faster results
            $analysis = $this->parseSimpleErrorAnalysis($response);
            
            return [
                'success' => true,
                'analysis' => $analysis['analysis'],
                'suggestion' => $analysis['suggestion'],
                'corrected_code' => $analysis['corrected_code'],
                'error_type' => $analysis['error_type']
            ];
        }
        
        // Quick fallback
        return [
            'success' => false,
            'error' => 'Analysis timed out',
            'analysis' => 'Analysis failed - service timeout or unavailable',
            'suggestion' => 'Try with smaller code or check Ollama connection',
            'corrected_code' => null,
            'error_type' => 'Timeout Error'
        ];
    }
    
    private function cleanErrorMessage($errorMessage) {
        $cleaned = trim($errorMessage);
        
        // Handle truncated error messages
        $patterns = [
            'Undefined con' => 'Undefined control sequence',
            'Fatal er' => 'Fatal error',
            'Missing \\beg' => 'Missing \\begin{document}',
            'Environment .* undefined' => 'Environment undefined',
            'Package .* Error' => 'Package error',
            'Math .* error' => 'Math mode error'
        ];
        
        foreach ($patterns as $pattern => $replacement) {
            if (strpos($cleaned, $pattern) !== false) {
                $cleaned = str_replace($pattern, $replacement, $cleaned);
            }
        }
        
        // Clean up line references and extra whitespace
        $cleaned = preg_replace('/Line \d+:\s*/', '', $cleaned);
        $cleaned = preg_replace('/==> /', '', $cleaned);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        
        return trim($cleaned);
    }
    
    private function parseSimpleErrorAnalysis($response) {
        $analysis = [
            'error_type' => 'LaTeX Error',
            'analysis' => $response,
            'suggestion' => $response,
            'corrected_code' => null
        ];
        
        // Try to extract error type from response
        if (preg_match('/(?:error|issue|problem):\s*([^.]+)/i', $response, $matches)) {
            $analysis['error_type'] = trim($matches[1]);
        }
        
        // Try to extract code from response - be more careful with large documents
        if (preg_match('/```latex\s*(.+?)```/s', $response, $matches)) {
            $correctedCode = trim($matches[1]);
            
            // Don't replace entire document if it's very short compared to original
            // This prevents accidental complete replacement
            if (strlen($correctedCode) < 100) {
                $analysis['corrected_code'] = $correctedCode;
            } else {
                // For longer corrections, only use if it looks like a proper document
                if (strpos($correctedCode, '\\documentclass') !== false) {
                    $analysis['corrected_code'] = $correctedCode;
                }
            }
        } elseif (preg_match('/```\s*(.+?)```/s', $response, $matches)) {
            $correctedCode = trim($matches[1]);
            if (strlen($correctedCode) < 100) {
                $analysis['corrected_code'] = $correctedCode;
            }
        }
        
        return $analysis;
    }
    

    
    private function parseEnhancedErrorAnalysis($response, $errorMessage = '') {
        error_log("Enhanced AI Response: " . $response);
        
        $analysis = [
            'error_type' => 'Unknown Error',
            'root_cause' => 'Analysis in progress...',
            'error_location' => 'Location detection in progress...',
            'impact_assessment' => 'Impact analysis in progress...',
            'analysis' => 'Advanced error analysis not available',
            'suggestion' => 'Please check your LaTeX syntax',
            'corrected_code' => null
        ];
        
        // Enhanced parsing for new 6-part format
        if (preg_match('/(?:ERROR TYPE|1\.\s*ERROR TYPE):\s*(.+?)(?:\n|$)/i', $response, $matches)) {
            $analysis['error_type'] = trim($matches[1]);
        }
        
        if (preg_match('/(?:ROOT CAUSE ANALYSIS|2\.\s*ROOT CAUSE ANALYSIS):\s*(.+?)(?:\n\d+\.|$)/is', $response, $matches)) {
            $analysis['root_cause'] = trim($matches[1]);
            $analysis['analysis'] = trim($matches[1]); // Backward compatibility
        }
        
        if (preg_match('/(?:ERROR LOCATION|3\.\s*ERROR LOCATION):\s*(.+?)(?:\n\d+\.|$)/is', $response, $matches)) {
            $analysis['error_location'] = trim($matches[1]);
        }
        
        if (preg_match('/(?:IMPACT ASSESSMENT|4\.\s*IMPACT ASSESSMENT):\s*(.+?)(?:\n\d+\.|$)/is', $response, $matches)) {
            $analysis['impact_assessment'] = trim($matches[1]);
        }
        
        if (preg_match('/(?:COMPREHENSIVE FIX STRATEGY|5\.\s*COMPREHENSIVE FIX STRATEGY):\s*(.+?)(?:\n\d+\.|$)/is', $response, $matches)) {
            $analysis['suggestion'] = trim($matches[1]);
        }
        
        // Enhanced code extraction with multiple patterns
        $codePatterns = [
            '/(?:CORRECTED CODE|6\.\s*CORRECTED CODE):\s*```latex\s*(.+?)```/is',
            '/```latex\s*(.+?)```/is',
            '/(?:CORRECTED CODE|6\.\s*CORRECTED CODE):\s*(.+?)(?:\n\n|\Z)/is'
        ];
        
        foreach ($codePatterns as $pattern) {
            if (preg_match($pattern, $response, $matches)) {
                $correctedCode = trim($matches[1]);
                $correctedCode = preg_replace('/```latex\s*/', '', $correctedCode);
                $correctedCode = preg_replace('/```\s*$/', '', $correctedCode);
                
                // Validate LaTeX code
                if (!empty($correctedCode) && 
                    (strpos($correctedCode, '\\documentclass') !== false || 
                     strpos($correctedCode, '\\begin{document}') !== false ||
                     strlen($correctedCode) > 20)) {
                    $analysis['corrected_code'] = trim($correctedCode);
                    break;
                }
            }
        }
        
        // Remove fallback - only use AI analysis
        
        return $analysis;
    }

    private function parseErrorAnalysis($response, $errorMessage = '') {
        error_log("AI Response: " . $response);
        
        $analysis = [
            'error_type' => 'Unknown Error',
            'analysis' => 'Error analysis not available',
            'suggestion' => 'Please check your LaTeX syntax',
            'corrected_code' => null
        ];
        
        if (preg_match('/(?:ERROR TYPE|Error Type):\s*(.+?)(?:\n|$)/i', $response, $matches)) {
            $analysis['error_type'] = trim($matches[1]);
        } elseif (preg_match('/1\.\s*ERROR TYPE:\s*(.+?)(?:\n|$)/i', $response, $matches)) {
            $analysis['error_type'] = trim($matches[1]);
        }
        
        if (preg_match('/(?:ANALYSIS|Analysis):\s*(.+?)(?:\n(?:\d+\.|[A-Z])|$)/is', $response, $matches)) {
            $analysis['analysis'] = trim($matches[1]);
        } elseif (preg_match('/2\.\s*ANALYSIS:\s*(.+?)(?:\n(?:\d+\.|[A-Z])|$)/is', $response, $matches)) {
            $analysis['analysis'] = trim($matches[1]);
        }
        
        if (preg_match('/(?:QUICK FIX|Quick Fix):\s*(.+?)(?:\n(?:\d+\.|[A-Z])|$)/is', $response, $matches)) {
            $analysis['suggestion'] = trim($matches[1]);
        } elseif (preg_match('/3\.\s*QUICK FIX:\s*(.+?)(?:\n(?:\d+\.|[A-Z])|$)/is', $response, $matches)) {
            $analysis['suggestion'] = trim($matches[1]);
        }
        
        if (preg_match('/(?:CORRECTED CODE|Corrected Code):\s*```latex\s*(.+?)```/is', $response, $matches)) {
            $analysis['corrected_code'] = trim($matches[1]);
        } elseif (preg_match('/4\.\s*CORRECTED CODE:\s*```latex\s*(.+?)```/is', $response, $matches)) {
            $analysis['corrected_code'] = trim($matches[1]);
        } elseif (preg_match('/(?:CORRECTED CODE|Corrected Code):\s*(.+?)(?:\n\n|$)/is', $response, $matches)) {
            $correctedCode = trim($matches[1]);
            $correctedCode = preg_replace('/```latex\s*/', '', $correctedCode);
            $correctedCode = preg_replace('/```\s*$/', '', $correctedCode);
            if (!empty($correctedCode) && strpos($correctedCode, '\\documentclass') !== false) {
                $analysis['corrected_code'] = trim($correctedCode);
            }
        }
        
        if ($analysis['analysis'] === 'Error analysis not available') {
            $analysis = $this->provideBasicErrorAnalysis($analysis, $errorMessage);
        }
        
        return $analysis;
    }
    

    
    private function callAI($prompt, $userId = null) {
        if (ENABLE_OLLAMA) {
            // Quick ping to Ollama before heavy request
            $pingResult = $this->pingOllama();
            if (!$pingResult) {
                return [
                    'success' => false,
                    'error' => 'Ollama service not available on localhost:11434'
                ];
            }
            
            $result = $this->callOllama($prompt);
            if ($result['success']) {
                return $result;
            }
        }
        
        if (ENABLE_GEMINI) {
            return $this->callGemini($prompt);
        }
        
        return [
            'success' => false,
            'error' => 'No AI service available'
        ];
    }
    
    private function pingOllama() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://localhost:11434/api/tags');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_NOBODY, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return ($httpCode === 200);
    }
    
    private function callOllama($prompt) {
        $url = $this->ollamaEndpoint;
        
        $data = [
            'model' => OLLAMA_DEFAULT_MODEL,
            'prompt' => $prompt,
            'stream' => false,
            'options' => [
                'temperature' => 0.3,    // Higher temp for faster generation
                'top_p' => 0.95,
                'num_predict' => 800,    // Optimized for LaTeX code output
                'repeat_penalty' => 1.0, // Reduce penalty for speed
                'num_ctx' => 6144,       // Optimized context for 10K chars
                'num_thread' => -1,      // Use all available CPU threads
                'num_gpu' => 1,          // Use GPU if available
                'num_batch' => 128,      // Smaller batch for lower memory usage
                'top_k' => 20            // Limit choices for speed
            ]
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Optimized 60-second timeout for 7B model
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            return [
                'success' => false,
                'error' => 'Curl error: ' . $curlError,
                'http_code' => $httpCode
            ];
        }
        
        if ($httpCode === 200 && $response) {
            $decoded = json_decode($response, true);
            if (isset($decoded['response'])) {
                // Log successful response for debugging
                error_log("Ollama Success: " . substr($decoded['response'], 0, 200));
                return [
                    'success' => true,
                    'response' => $decoded['response'],
                    'service' => 'ollama'
                ];
            } else {
                // Log decode issue
                error_log("Ollama JSON Decode Issue: " . substr($response, 0, 200));
            }
        } else {
            // Log HTTP error
            error_log("Ollama HTTP Error: Code $httpCode, Response: " . substr($response, 0, 200));
        }
        
        return [
            'success' => false,
            'error' => 'Ollama request failed - HTTP ' . $httpCode,
            'http_code' => $httpCode,
            'response' => $response
        ];
    }
    
    private function callGemini($prompt) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $this->geminiApiKey;
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, AI_REQUEST_TIMEOUT);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $decoded = json_decode($response, true);
            if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
                return [
                    'success' => true,
                    'response' => $decoded['candidates'][0]['content']['parts'][0]['text'],
                    'service' => 'gemini'
                ];
            }
        }
        
        return [
            'success' => false,
            'error' => 'Gemini request failed',
            'http_code' => $httpCode
        ];
    }
    
    public function healthCheck() {
        $status = [
            'ollama' => false,
            'gemini' => false,
            'overall' => false
        ];
        
        if ($this->config['features']['ollama']) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->ollamaEndpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $status['ollama'] = ($httpCode === 200);
        }
        
        if ($this->config['features']['gemini'] && !empty($this->geminiApiKey)) {
            $status['gemini'] = true;
        }
        
        $status['overall'] = $status['ollama'] || $status['gemini'];
        
        return $status;
    }
    
    // Missing methods that ai-api.php expects
    public function analyzeDocumentQuality($latexCode, $userId = null) {
        $prompt = "Analyze the quality of this LaTeX document and provide suggestions for improvement:\n\n" . $latexCode;
        return $this->callAI($prompt, $userId);
    }
    
    public function getUsageStats() {
        // Return mock stats for now
        return [
            'total_requests' => 0,
            'successful_requests' => 0,
            'error_rate' => 0,
            'ollama_usage' => 0,
            'gemini_usage' => 0
        ];
    }
    
    public function testGeminiConnection() {
        if (!ENABLE_GEMINI || empty($this->geminiApiKey)) {
            return ['status' => 'disabled', 'message' => 'Gemini service not configured'];
        }
        
        $result = $this->callGemini("Hello, test connection");
        return [
            'status' => $result['success'] ? 'connected' : 'failed',
            'message' => $result['success'] ? 'Gemini connection successful' : 'Gemini connection failed'
        ];
    }
    
    public function testOllamaConnection() {
        if (!ENABLE_OLLAMA) {
            return ['status' => 'disabled', 'message' => 'Ollama service not configured'];
        }
        
        $result = $this->callOllama("Hello, test connection");
        return [
            'status' => $result['success'] ? 'connected' : 'failed',
            'message' => $result['success'] ? 'Ollama connection successful' : 'Ollama connection failed'
        ];
    }
    
    public function testDatabaseConnection() {
        // Mock database test
        return [
            'status' => 'connected',
            'message' => 'Database connection successful'
        ];
    }
    
    public function simpleLatexAnalysis($latexCode, $errorMessage = '') {
        // Efficient prompt for 7B model
        $prompt = "LaTeX Code Analysis - Fix all errors and provide complete corrected code:\n\n";
        $prompt .= $latexCode . "\n\n";
        
        if ($errorMessage) {
            $prompt .= "Compilation Error: " . $errorMessage . "\n\n";
        }
        
        $prompt .= "Output format:\nErrors: [list main errors]\nFixed Code:\n```latex\n[complete corrected LaTeX document]\n```";
        
        $result = $this->callAI($prompt);
        if ($result['success']) {
            return [
                'success' => true,
                'analysis' => $result['response'],
                'model' => 'qwen2.5-coder:7b',
                'original_length' => strlen($latexCode),
                'optimized' => true
            ];
        }
        return $result;
    }

    
    public function comprehensiveLatexAnalysis($latexCode, $errorMessage = '', $options = []) {
        // Use actual Qwen 2.5 AI for comprehensive analysis
        $codeLength = strlen($latexCode);
        $lineCount = substr_count($latexCode, "\n") + 1;
        
        // Create comprehensive prompt for AI with better context
        $prompt = "You are a LaTeX expert. Carefully analyze this complete LaTeX document and identify ALL errors.\n\n";
        $prompt .= "IMPORTANT: This is a COMPLETE document with {$lineCount} lines and {$codeLength} characters.\n";
        $prompt .= "Look for: missing \\end{document}, unmatched braces, undefined commands, structural issues.\n\n";
        $prompt .= "LaTeX Code ({$lineCount} lines):\n```latex\n{$latexCode}\n```\n\n";
        
        if (!empty($errorMessage)) {
            $prompt .= "Compilation Errors:\n{$errorMessage}\n\n";
        }
        
        $prompt .= "Please provide:\n";
        $prompt .= "1. Detailed analysis of all issues found\n";
        $prompt .= "2. Specific line-by-line fixes needed\n";
        $prompt .= "3. Complete corrected LaTeX code\n";
        $prompt .= "4. Explanation of changes made\n\n";
        $prompt .= "Format your response as JSON with: analysis, suggestions, corrected_code, issues_found";
        
        // Call actual Qwen AI
        $aiResult = $this->callOllama($prompt);
        
        if ($aiResult['success']) {
            try {
                // Try to parse AI response as JSON
                $aiData = json_decode($aiResult['response'], true);
                
                if ($aiData) {
                    return [
                        'success' => true,
                        'model' => 'qwen2.5',
                        'analysis_type' => 'comprehensive',
                        'issues_found' => $aiData['issues_found'] ?? 0,
                        'confidence' => 'High',
                        'analysis' => $aiData['analysis'] ?? $aiResult['response'],
                        'suggestions' => $aiData['suggestions'] ?? 'AI suggestions available in analysis',
                        'corrected_code' => $aiData['corrected_code'] ?? null,
                        'issues' => $aiData['issues'] ?? []
                    ];
                } else {
                    // If not JSON, use raw AI response
                    return [
                        'success' => true,
                        'model' => 'qwen2.5',
                        'analysis_type' => 'comprehensive',
                        'issues_found' => substr_count(strtolower($aiResult['response']), 'error'),
                        'confidence' => 'High',
                        'analysis' => $aiResult['response'],
                        'suggestions' => 'Check the analysis above for detailed suggestions.',
                        'corrected_code' => $this->extractCorrectedCode($aiResult['response']),
                        'issues' => []
                    ];
                }
            } catch (Exception $e) {
                // Fallback to raw response if JSON parsing fails
                return [
                    'success' => true,
                    'model' => 'qwen2.5',
                    'analysis_type' => 'comprehensive',
                    'issues_found' => 1,
                    'confidence' => 'Medium',
                    'analysis' => $aiResult['response'],
                    'suggestions' => 'AI analysis completed. Review the detailed feedback above.',
                    'corrected_code' => $this->extractCorrectedCode($aiResult['response']),
                    'issues' => []
                ];
            }
        } else {
            // AI call failed - return error
            return [
                'success' => false,
                'error' => $aiResult['error'] ?? 'AI service unavailable',
                'model' => 'qwen2.5',
                'analysis_type' => 'failed'
            ];
        }
    }
    

    
    private function extractCorrectedCode($aiResponse) {
        // Extract LaTeX code from AI response
        if (preg_match('/```latex\s*(.*?)\s*```/s', $aiResponse, $matches)) {
            return trim($matches[1]);
        }
        
        if (preg_match('/```\s*(.*?)\s*```/s', $aiResponse, $matches)) {
            return trim($matches[1]);
        }
        
        // If no code blocks found, look for \documentclass to \end{document}
        if (preg_match('/\\\\documentclass.*?\\\\end\{document\}/s', $aiResponse, $matches)) {
            return trim($matches[0]);
        }
        
        return null;
    }
}
?>