<?php
/**
 * HalalLens AI - Backend Multi-Agent Orchestrator Endpoint
 *
 * Production Multi-Agent Pipeline Flow:
 * 1. Receives uploaded product image via HTTP POST.
 * 2. Validates image MIME type, file size, and upload integrity.
 * 3. Invokes Agent 1 (Ingredient Vision Agent) to extract structured ingredients.
 * 4. Validates Agent 1 output structure.
 * 5. Passes validated Agent 1 JSON directly to Agent 2 (Halal Verification Agent).
 * 6. Validates Agent 2 output structure.
 * 7. Returns one combined response with product_analysis to the frontend.
 *
 * NOTE: The frontend does NOT orchestrate the agent handoff.
 */

declare(strict_types=1);

// Prevent PHP execution timeout during AI vision processing (up to 120s)
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

// Load configuration helper and agents
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.example.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'agents' . DIRECTORY_SEPARATOR . 'IngredientAgent.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'agents' . DIRECTORY_SEPARATOR . 'HalalAgent.php';

use HalalLens\Agents\IngredientAgent;
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

    // Validate uploaded file existence
    if (!isset($_FILES['image']) || !is_array($_FILES['image'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'No image file uploaded. Key "image" is required.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $file = $_FILES['image'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the form.',
            UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
        ];
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $uploadErrors[$file['error']] ?? 'Unknown upload error.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Validate file size (max 5MB)
    $maxSize = (int) ($config['upload']['max_file_size'] ?? (5 * 1024 * 1024));
    if ($file['size'] > $maxSize) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'File size exceeds maximum permitted limit (' . round($maxSize / (1024 * 1024)) . ' MB).',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Validate MIME type using resilient detector
    $mimeType = detectImageMimeType($file['tmp_name'], $file['type'] ?? null);

    $allowedMimes = $config['upload']['allowed_mime_types'] ?? ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mimeType, $allowedMimes, true)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid file format (' . ($mimeType ?: 'unknown') . '). Only JPG, PNG, and WebP are allowed.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Sanitize extension and destination filename
    $extensionMap = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];
    $ext = $extensionMap[$mimeType] ?? 'jpg';
    $uploadDir = $config['upload']['upload_dir'] ?? (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR);

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $safeFilename = 'label_' . bin2hex(random_bytes(16)) . '.' . $ext;
    $targetPath = $uploadDir . $safeFilename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to save uploaded file to server storage.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Check if GEMINI_API_KEY is configured
    if (empty($apiKey)) {
        http_response_code(503);
        echo json_encode([
            'success' => false,
            'error' => 'GEMINI_API_KEY is not configured on the server. Please set the GEMINI_API_KEY environment variable or define it in server config.',
            'pipeline_status' => 'unconfigured',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Determine streaming or extraction mode
    $isStreaming = (isset($_POST['stream']) && $_POST['stream'] === '1') 
                || (isset($_GET['stream']) && $_GET['stream'] === '1')
                || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/x-ndjson') !== false);
    $action = (string) ($_POST['action'] ?? ($_GET['action'] ?? 'full'));

    // Determine target language for reasoning
    $lang = (string) ($_POST['lang'] ?? ($_COOKIE['halallens_lang'] ?? 'ar'));
    $lang = in_array(strtolower($lang), ['ar', 'en'], true) ? strtolower($lang) : 'ar';

    // If streaming, prepare chunked unbuffered output headers
    if ($isStreaming) {
        while (ob_get_level()) {
            @ob_end_flush();
        }
        header('Content-Type: application/x-ndjson; charset=UTF-8');
        header('Cache-Control: no-cache, no-transform');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');
    }

    // =========================================================================
    // Backend Multi-Agent Orchestration Flow
    // =========================================================================

    $baseUrl = (string) ($config['gemini']['api_base_url'] ?? 'https://generativelanguage.googleapis.com/v1beta/models/');
    $timeout = (int) ($config['gemini']['timeout'] ?? 60);

    // Stage 1: Invoke Agent 1 (Ingredient Vision Agent)
    $visionAgent = new IngredientAgent($apiKey, $model, $baseUrl, $timeout);
    $visionResult = $visionAgent->extractIngredients($targetPath);

    // Validate Agent 1 output
    if (!is_array($visionResult) || !isset($visionResult['ingredients']) || !isset($visionResult['success'])) {
        http_response_code(500);
        $errPayload = [
            'success' => false,
            'type' => 'error',
            'error' => 'Ingredient Vision Agent returned an invalid response structure.',
        ];
        echo json_encode($errPayload, JSON_UNESCAPED_UNICODE) . ($isStreaming ? "\n" : '');
        exit;
    }

    // If client requested Step 1 fallback only (extract action)
    if ($action === 'extract') {
        echo json_encode([
            'success' => true,
            'action' => 'extract',
            'file_id' => $safeFilename,
            'ingredients' => $visionResult,
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    // In streaming mode: Flush Event 1 (Real ingredients extracted from Agent 1)
    if ($isStreaming) {
        echo json_encode([
            'type' => 'ingredients_extracted',
            'success' => true,
            'file_id' => $safeFilename,
            'data' => [
                'ingredients' => $visionResult,
            ],
        ], JSON_UNESCAPED_UNICODE) . "\n";
        @ob_flush();
        @flush();

        // Flush Event 2 (Verification starting)
        echo json_encode([
            'type' => 'verification_started',
            'success' => true,
        ], JSON_UNESCAPED_UNICODE) . "\n";
        @ob_flush();
        @flush();
    }

    // Stage 2: Direct backend handoff to Agent 2 (Halal Verification Agent)
    $halalAgent = new HalalAgent($apiKey, $model, $baseUrl, $timeout);
    $verificationResult = $halalAgent->verify($visionResult, $lang);

    // Validate Agent 2 output
    if (!is_array($verificationResult) || !isset($verificationResult['status']) || !isset($verificationResult['success'])) {
        http_response_code(500);
        $errPayload = [
            'success' => false,
            'type' => 'error',
            'error' => 'Halal Verification Agent returned an invalid response structure.',
        ];
        echo json_encode($errPayload, JSON_UNESCAPED_UNICODE) . ($isStreaming ? "\n" : '');
        exit;
    }

    // If streaming: Flush Event 3 (Final Halal Verification Completed)
    if ($isStreaming) {
        echo json_encode([
            'type' => 'verification_completed',
            'success' => true,
            'file_id' => $safeFilename,
            'data' => [
                'halal_verification' => $verificationResult,
                'product_analysis' => [
                    'file_id' => $safeFilename,
                    'ingredients' => $visionResult,
                    'halal_verification' => $verificationResult,
                ],
            ],
        ], JSON_UNESCAPED_UNICODE) . "\n";
        @ob_flush();
        @flush();
        exit;
    }

    // Standard Non-Streaming Output (100% backward compatible for tests and traditional clients)
    echo json_encode([
        'success' => true,
        'product_analysis' => [
            'file_id' => $safeFilename,
            'ingredients' => $visionResult,
            'halal_verification' => $verificationResult,
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (\Throwable $e) {
    http_response_code(500);
    // Sanitize any accidental API key leakage from error message
    $sanitizedMsg = preg_replace('/key=[a-zA-Z0-9_\-]+/', 'key=[REDACTED]', $e->getMessage());
    $errPayload = [
        'success' => false,
        'type' => 'error',
        'error' => $sanitizedMsg,
    ];
    $isStreaming = (isset($_POST['stream']) && $_POST['stream'] === '1') || (isset($_GET['stream']) && $_GET['stream'] === '1');
    echo json_encode($errPayload, JSON_UNESCAPED_UNICODE) . ($isStreaming ? "\n" : '');
}
