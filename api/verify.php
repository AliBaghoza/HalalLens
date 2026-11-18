<?php
/**
 * HalalLens AI - Direct Halal Verification Endpoint (Secondary/Modular)
 *
 * Used for direct ingredient list testing and verification without requiring image re-upload.
 * Note: The production flow runs via the backend orchestrator in `api/analyze.php`.
 */

declare(strict_types=1);

// Prevent PHP execution timeout during AI processing (up to 120s)
@set_time_limit(120);
@ini_set('max_execution_time', '120');
@ini_set('display_errors', '0');

// Buffer output so unexpected warnings never corrupt JSON output
ob_start();

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (ob_get_length()) {
            ob_clean();
        }
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json; charset=UTF-8');
        }
        echo json_encode([
            'success' => false,
            'error' => 'Server execution error: ' . $error['message'],
        ], JSON_UNESCAPED_UNICODE);
    }
});

header('Content-Type: application/json; charset=UTF-8');

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.example.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'agents' . DIRECTORY_SEPARATOR . 'HalalAgent.php';

use HalalLens\Agents\HalalAgent;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed. Use POST.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $config = getAppConfig();
    $apiKey = $config['gemini']['api_key'] ?? '';
    $model = $config['gemini']['model'] ?? 'gemini-3.8-flash';

    $rawInput = file_get_contents('php://input');
    $inputData = json_decode($rawInput, true);

    if (!is_array($inputData) || !isset($inputData['ingredients']) || !is_array($inputData['ingredients'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid JSON input. Expected {"ingredients": ["ingredient1", "ingredient2", ...]}',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (empty($apiKey)) {
        http_response_code(503);
        echo json_encode([
            'success' => false,
            'error' => 'GEMINI_API_KEY is not configured on the server. Please set the GEMINI_API_KEY environment variable.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $lang = (string) ($inputData['lang'] ?? 'ar');
    $baseUrl = (string) ($config['gemini']['api_base_url'] ?? 'https://generativelanguage.googleapis.com/v1beta/models/');
    $timeout = (int) ($config['gemini']['timeout'] ?? 60);
    $halalAgent = new HalalAgent($apiKey, $model, $baseUrl, $timeout);
    $result = $halalAgent->verifyIngredients($inputData['ingredients'], $lang);

    echo json_encode([
        'success' => true,
        'data' => $result,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (\Throwable $e) {
    http_response_code(500);
    $sanitizedMsg = preg_replace('/key=[a-zA-Z0-9_\-]+/', 'key=[REDACTED]', $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $sanitizedMsg,
    ], JSON_UNESCAPED_UNICODE);
}
