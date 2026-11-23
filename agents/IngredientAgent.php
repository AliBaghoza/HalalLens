<?php
/**
 * HalalLens AI - Agent 1: Ingredient Vision Agent
 *
 * Responsibilities:
 * - Ingests product label image from the backend pipeline.
 * - Communicates with Gemini Vision API via cURL using configurable GEMINI_MODEL.
 * - Extracts visible ingredient text, preserving original packaging wording.
 * - Separates ingredients intelligently without breaking nested parenthetical expressions.
 * - Normalizes ingredient names where appropriate without inventing information.
 * - Explicitly evaluates source quality ('good', 'fair', 'poor', 'unreadable').
 * - Explicitly marks uncertainty and never hallucinates unreadable ingredients.
 * - Strictly does NOT perform Halal/Haram classification (Agent 2 responsibility).
 * - Enforces rigorous server-side JSON schema validation on Gemini's output.
 */

declare(strict_types=1);

namespace HalalLens\Agents;

class IngredientAgent
{
    public const QUALITY_GOOD = 'good';
    public const QUALITY_FAIR = 'fair';
    public const QUALITY_POOR = 'poor';
    public const QUALITY_UNREADABLE = 'unreadable';

    public const VALID_QUALITIES = [
        self::QUALITY_GOOD,
        self::QUALITY_FAIR,
        self::QUALITY_POOR,
        self::QUALITY_UNREADABLE,
    ];

    private string $apiKey;
    private string $model;
    private string $apiBaseUrl;
    private int $timeout;

    /**
     * @param string $apiKey Gemini API Key
     * @param string $model Model identifier (configurable via GEMINI_MODEL, defaults to gemini-3.8-flash)
     * @param string $apiBaseUrl API endpoint base
     * @param int $timeout cURL timeout in seconds
     */
    public function __construct(
        string $apiKey,
        string $model = 'gemini-3.8-flash',
        string $apiBaseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/',
        int $timeout = 60
    ) {
        $this->apiKey = trim($apiKey);
        $this->model = trim($model) ?: 'gemini-3.8-flash';
        $this->apiBaseUrl = rtrim($apiBaseUrl, '/') . '/';
        $this->timeout = $timeout;
    }

    /**
     * Extracts ingredients from a validated image file using Gemini Vision.
     *
     * @param string $imagePath Absolute path to the validated image file
     * @return array<string, mixed> Structured result adhering to Agent 1 contract
     * @throws \RuntimeException If API key missing, network error, or invalid AI response
     * @throws \InvalidArgumentException If image path or format is invalid
     */
    public function extractIngredients(string $imagePath): array
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('GEMINI_API_KEY is not configured on the server.');
        }

        if (!file_exists($imagePath) || !is_readable($imagePath)) {
            throw new \InvalidArgumentException('Image file not found or unreadable: ' . basename($imagePath));
        }

        // Validate MIME type (JPEG, PNG, WebP)
        $mimeType = $this->detectMimeType($imagePath);
        $validMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!$mimeType || !in_array($mimeType, $validMimes, true)) {
            throw new \InvalidArgumentException('Invalid image format: ' . ($mimeType ?: 'unknown') . '. Expected JPEG, PNG, or WebP.');
        }

        // Encode image for Gemini inlineData
        $imageData = file_get_contents($imagePath);
        if ($imageData === false) {
            throw new \RuntimeException('Failed to read image data from disk.');
        }
        $base64Image = base64_encode($imageData);

        // System instructions & prompt for structured extraction
        $prompt = <<<PROMPT
You are the Ingredient Vision Agent in HalalLens AI, a food product label extraction system.
Your role is EXCLUSIVELY to inspect this food product label image and extract the ingredient information into structured JSON.
Do NOT make any Halal, Haram, Kosher, or religious dietary rulings. Your responsibility is strictly objective text and ingredient extraction.

Instructions:
1. Locate the ingredient list on the packaging label.
2. Preserve original wording: extract the verbatim text of the ingredient section into "source_text".
3. Separate into individual ingredients: do not blindly split inside parentheses or descriptive clauses. For example: "Wheat flour (wheat)", "Emulsifier (mono- and diglycerides of fatty acids E471)", "Vegetable oils (palm, sunflower)" must each be kept together as a single ingredient item.
4. For each individual ingredient, provide:
   - "original_name": exact wording as printed on the packaging.
   - "normalized_name": standardized common name, scientific name, or clean E-number (e.g. "E471", "Soy lecithin", "Palm oil") or null if uncertain.
   - "confidence": estimated OCR reading confidence between 0.0 and 1.0.
5. Handling Uncertainty:
   - If any portion of text is blurry, occluded, torn, cut off, or illegible, DO NOT guess or hallucinate missing ingredients.
   - Explicitly mark uncertain items (e.g., original_name: "[unclear text]" or "[partially legible: ...]", normalized_name: null, confidence: 0.25).
   - Record any visibility difficulties or extraction doubts in "notes".
6. Source Quality Assessment:
   - Set "source_quality" strictly to one of:
     * "good": The ingredient text is sharp and clearly readable.
     * "fair": Most ingredients are readable but minor portions are degraded or uncertain.
     * "poor": Significant blur, glare, occlusion, or OCR difficulty.
     * "unreadable": The ingredient list is missing, completely illegible, or not a food label.
   - If "source_quality" is "unreadable": set "success": false, "source_text": "", "ingredients": [], "ingredient_count": 0, and explain in "notes".
7. Ingredient Count:
   - "ingredient_count": integer matching the count of items in "ingredients".
8. Output Format:
   Return ONLY a valid JSON object matching this exact schema:
{
  "agent": "IngredientVisionAgent",
  "success": true,
  "source_quality": "good | fair | poor | unreadable",
  "source_text": "Original ingredient text extracted from the label",
  "ingredients": [
    {
      "original_name": "Original ingredient wording",
      "normalized_name": "Normalized ingredient name",
      "confidence": 0.95
    }
  ],
  "ingredient_count": 5,
  "notes": [
    "Any relevant extraction uncertainty"
  ]
}
Do not include markdown code fences or any conversational filler.
PROMPT;

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        [
                            'text' => $prompt
                        ],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data' => $base64Image
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'temperature' => 0.1,
            ]
        ];

        $candidates = array_values(array_unique([
            $this->model,
            'gemini-flash-lite-latest',
            'gemini-3.5-flash-lite',
            'gemini-3.6-flash',
            'gemini-3.8-flash',
            'gemini-3-flash-preview',
            'gemini-3.1-flash-lite'
        ]));
        $lastException = null;

        foreach ($candidates as $candidateModel) {
            $endpoint = $this->apiBaseUrl . rawurlencode($candidateModel) . ':generateContent?key=' . rawurlencode($this->apiKey);
            try {
                $response = $this->executeCurlPost($endpoint, $payload);
                return $this->parseResponse($response);
            } catch (\RuntimeException $e) {
                $lastException = $e;
                $isTemporaryError = (
                    strpos($e->getMessage(), '503') !== false ||
                    strpos($e->getMessage(), 'high demand') !== false ||
                    strpos($e->getMessage(), '404') !== false ||
                    strpos($e->getMessage(), 'timed out') !== false
                );
                if (!$isTemporaryError) {
                    throw $e;
                }
            }
        }

        if ($lastException !== null) {
            throw $lastException;
        }

        throw new \RuntimeException('Failed to extract ingredients: no response received from Gemini.');
    }

    /**
     * Executes POST request to Gemini endpoint via cURL.
     *
     * @param string $url
     * @param array<string, mixed> $payload
     * @return array<string, mixed> Decoded HTTP response body
     * @throws \RuntimeException
     */
    private function executeCurlPost(string $url, array $payload): array
    {
        if (!function_exists('curl_init')) {
            throw new \RuntimeException('PHP cURL extension is required but not installed or enabled.');
        }

        $ch = curl_init();
        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonPayload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $rawResponse = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        if (PHP_VERSION_ID < 80500) {
            @curl_close($ch);
        }

        if ($rawResponse === false) {
            throw new \RuntimeException('cURL error during Gemini Vision request: ' . $curlError);
        }

        $decoded = json_decode((string) $rawResponse, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Invalid JSON response received from Gemini API. HTTP status: ' . $httpCode);
        }

        if ($httpCode >= 400) {
            $errorMessage = $decoded['error']['message'] ?? ('HTTP ' . $httpCode . ' from Gemini API');
            throw new \RuntimeException('Gemini API Error: ' . $errorMessage);
        }

        return $decoded;
    }

    /**
     * Validates and parses Gemini content response into strict Agent 1 structured contract.
     *
     * @param array<string, mixed> $response
     * @return array<string, mixed>
     * @throws \RuntimeException If response structure is invalid or unparseable
     */
    private function parseResponse(array $response): array
    {
        $candidateText = $response['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (empty($candidateText)) {
            throw new \RuntimeException('Gemini API returned an empty or unparseable text response.');
        }

        // Strip any markdown code fences if present
        $cleaned = trim($candidateText);
        if (str_starts_with($cleaned, '```json')) {
            $cleaned = substr($cleaned, 7);
        } elseif (str_starts_with($cleaned, '```')) {
            $cleaned = substr($cleaned, 3);
        }
        if (str_ends_with($cleaned, '```')) {
            $cleaned = substr($cleaned, 0, -3);
        }
        $cleaned = trim($cleaned);

        $parsed = json_decode($cleaned, true);
        if (!is_array($parsed)) {
            throw new \RuntimeException('Agent 1 returned invalid JSON from Gemini Vision: ' . json_last_error_msg());
        }

        // Validate source_quality
        $rawQuality = strtolower(trim((string) ($parsed['source_quality'] ?? self::QUALITY_FAIR)));
        $sourceQuality = in_array($rawQuality, self::VALID_QUALITIES, true) ? $rawQuality : self::QUALITY_FAIR;

        // Validate success flag
        $success = (bool) ($parsed['success'] ?? ($sourceQuality !== self::QUALITY_UNREADABLE));
        if ($sourceQuality === self::QUALITY_UNREADABLE) {
            $success = false;
        }

        // Validate source_text
        $sourceText = trim((string) ($parsed['source_text'] ?? ''));

        // Validate ingredients array
        $rawIngredients = is_array($parsed['ingredients'] ?? null) ? $parsed['ingredients'] : [];
        $sanitizedIngredients = [];

        foreach ($rawIngredients as $item) {
            if (is_string($item)) {
                $name = trim($item);
                if ($name !== '') {
                    $sanitizedIngredients[] = [
                        'original_name' => $name,
                        'normalized_name' => $name,
                        'confidence' => 0.85,
                    ];
                }
            } elseif (is_array($item)) {
                $orig = trim((string) ($item['original_name'] ?? ''));
                if ($orig === '') {
                    $orig = '[unclear text]';
                }

                $norm = isset($item['normalized_name']) && $item['normalized_name'] !== null
                    ? trim((string) $item['normalized_name'])
                    : null;
                if ($norm === '') {
                    $norm = null;
                }

                $conf = isset($item['confidence']) && is_numeric($item['confidence'])
                    ? max(0.0, min(1.0, (float) $item['confidence']))
                    : ($orig === '[unclear text]' ? 0.25 : 0.85);

                $sanitizedIngredients[] = [
                    'original_name' => $orig,
                    'normalized_name' => $norm,
                    'confidence' => round($conf, 2),
                ];
            }
        }

        // Validate notes
        $rawNotes = $parsed['notes'] ?? [];
        $notes = [];
        if (is_array($rawNotes)) {
            foreach ($rawNotes as $n) {
                if (is_string($n) && trim($n) !== '') {
                    $notes[] = trim($n);
                }
            }
        } elseif (is_string($rawNotes) && trim($rawNotes) !== '') {
            $notes[] = trim($rawNotes);
        }

        // If quality is unreadable and no note exists, add default diagnostic note
        if ($sourceQuality === self::QUALITY_UNREADABLE && empty($notes)) {
            $notes[] = 'No legible ingredient list could be detected in the provided image.';
        }

        return [
            'agent' => 'IngredientVisionAgent',
            'model' => $this->model,
            'success' => $success,
            'source_quality' => $sourceQuality,
            'source_text' => $sourceText,
            'ingredients' => $sanitizedIngredients,
            'ingredient_count' => count($sanitizedIngredients),
            'notes' => $notes,
        ];
    }

    /**
     * Resilient MIME detection using getimagesize, magic bytes, or fileinfo.
     *
     * @param string $filePath
     * @return string
     */
    private function detectMimeType(string $filePath): string
    {
        if (function_exists('getimagesize')) {
            $info = @getimagesize($filePath);
            if (is_array($info) && !empty($info['mime'])) {
                return (string) $info['mime'];
            }
        }

        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = finfo_file($finfo, $filePath);
                finfo_close($finfo);
                if ($mime) {
                    return (string) $mime;
                }
            }
        }

        if (function_exists('mime_content_type')) {
            $mime = @mime_content_type($filePath);
            if ($mime) {
                return (string) $mime;
            }
        }

        $handle = @fopen($filePath, 'rb');
        if ($handle) {
            $header = (string) fread($handle, 16);
            fclose($handle);
            if (str_starts_with($header, "\xFF\xD8\xFF")) {
                return 'image/jpeg';
            }
            if (str_starts_with($header, "\x89PNG\x0D\x0A\x1A\x0A")) {
                return 'image/png';
            }
            if (str_starts_with($header, 'RIFF') && substr($header, 8, 4) === 'WEBP') {
                return 'image/webp';
            }
        }

        return '';
    }
}
