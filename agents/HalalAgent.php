<?php
/**
 * HalalLens AI - Agent 2: Halal Verification Agent
 *
 * Responsibilities:
 * - Ingests structured ingredient JSON output directly from Agent 1 (Ingredient Vision Agent).
 * - Applies Islamic dietary jurisprudence (Fiqh al-At'imah) and food additive screening.
 * - Categorizes status strictly into canonical statuses: HALAL, HARAM, DOUBTFUL, UNKNOWN.
 * - Preserves uncertainty from Agent 1 without inventing or assuming missing information.
 * - Formulates detailed reasons, individual ingredient assessments, and actionable consumer notes.
 * - Enforces rigorous server-side validation on Gemini API response.
 */

declare(strict_types=1);

namespace HalalLens\Agents;

class HalalAgent
{
    public const STATUS_HALAL = 'HALAL';
    public const STATUS_HARAM = 'HARAM';
    public const STATUS_DOUBTFUL = 'DOUBTFUL';
    public const STATUS_UNKNOWN = 'UNKNOWN';

    public const CANONICAL_STATUSES = [
        self::STATUS_HALAL,
        self::STATUS_HARAM,
        self::STATUS_DOUBTFUL,
        self::STATUS_UNKNOWN,
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
     * Verifies Halal compliance of structured Agent 1 output.
     *
     * @param array<string, mixed> $agent1Data Structured JSON output from Agent 1
     * @param string $preferredLang Target explanation language ('ar' or 'en')
     * @return array<string, mixed> Strict validated Agent 2 output contract
     * @throws \RuntimeException If API key missing, network error, or invalid AI response
     * @throws \InvalidArgumentException If input data is malformed
     */
    public function verify(array $agent1Data, string $preferredLang = 'ar'): array
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('GEMINI_API_KEY is not configured on the server.');
        }

        $preferredLang = in_array(strtolower($preferredLang), ['ar', 'en'], true) ? strtolower($preferredLang) : 'ar';

        // Check if Agent 1 indicated failure or unreadable label
        $isAgent1Success = (bool) ($agent1Data['success'] ?? true);
        $sourceQuality = (string) ($agent1Data['source_quality'] ?? 'good');
        $ingredients = is_array($agent1Data['ingredients'] ?? null) ? $agent1Data['ingredients'] : [];

        if (!$isAgent1Success || $sourceQuality === 'unreadable' || empty($ingredients)) {
            $isAr = ($preferredLang === 'ar');
            return [
                'agent' => 'HalalVerificationAgent',
                'model' => $this->model,
                'success' => true,
                'status' => self::STATUS_UNKNOWN,
                'status_ar' => 'غير معروف',
                'status_en' => 'Unknown',
                'confidence' => 0.0,
                'reasons' => [
                    $isAr
                        ? 'تعذر التحقق من حِلّية المنتج لعدم وضوح أو توفر قائمة المكونات المقروءة.'
                        : 'Unable to verify Halal status because the ingredient list was unreadable or not provided.'
                ],
                'ingredient_assessments' => [],
                'notes' => [
                    $isAr
                        ? 'يرجى إعادة تصوير ملصق المكونات بوضوح مع إضاءة جيدة وتجنب الانعكاسات.'
                        : 'Please retake a clear photo of the ingredient label in good lighting.'
                ],
            ];
        }

        // Format ingredients for Gemini evaluation
        $agent1Json = json_encode($agent1Data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $langInstruction = ($preferredLang === 'ar')
            ? 'Write all text in "reasons", "reason" in ingredient_assessments, and "notes" in clear, formal Arabic (العربية الفصحى).'
            : 'Write all text in "reasons", "reason" in ingredient_assessments, and "notes" in clear, professional English.';

        $prompt = <<<PROMPT
You are the Halal Verification Agent in HalalLens AI, an expert Islamic dietary compliance (Fiqh al-At'imah) and food science verification agent.
Your responsibility is to analyze the following structured food product ingredients data extracted by the Ingredient Vision Agent:

$agent1Json

$langInstruction

Islamic Dietary Verification Rules:
1. HARAM (حرام):
   - Contains pork, lard, bacon, porcine gelatin, non-dhabihah land animals, ethyl alcohol / intoxicants, blood, or known prohibited substances.
2. DOUBTFUL (مشتبه فيه):
   - Contains ambiguous additives that can be sourced from either animal fats or plants without explicit declaration of plant origin (e.g. emulsifiers E471, E472, E475, E476, E481, E482, E491).
   - Gelatin with unspecified animal origin.
   - Animal enzymes (rennet/pepsin) from undetermined slaughter status.
   - Insect-derived colorants with jurisprudential differences (e.g. cochineal/carmine E120).
   - Shellac (E904), glycerin/glycerol of unspecified source, L-cysteine (E920).
   - Any ingredient marked with OCR uncertainty from Agent 1 (e.g. "[unclear text]", low confidence).
3. HALAL (حلال):
   - All ingredients are strictly plant-based, marine/aquatic (fish, seafood), synthetic non-toxic, microbial, or certified Halal.
4. UNKNOWN (غير معروف):
   - The label data is illegible, corrupted, or key ingredients are missing.

CRITICAL INSTRUCTIONS:
- The overall "status" field in your JSON output MUST be EXACTLY one of these four canonical uppercase strings:
  "HALAL", "HARAM", "DOUBTFUL", "UNKNOWN".
- Do NOT invent or assume ingredient origins if they are not explicitly declared on the label.
- If an emulsifier or additive could be animal-derived and has no "plant origin" or "vegetarian/halal" declaration, the overall status MUST be "DOUBTFUL".
- Preserve uncertainty: if Agent 1 flagged uncertain text, assess it with caution (Ihtiyat).
- Provide concise, informative reasons for the overall status and for each individual ingredient.

Output Format:
Return ONLY a valid JSON object matching this exact schema:
{
  "agent": "HalalVerificationAgent",
  "success": true,
  "status": "HALAL | HARAM | DOUBTFUL | UNKNOWN",
  "confidence": 0.85,
  "reasons": [
    "Primary reason explaining the overall verdict"
  ],
  "ingredient_assessments": [
    {
      "ingredient": "ingredient name or E-number",
      "status": "HALAL | HARAM | DOUBTFUL | UNKNOWN",
      "reason": "Specific Islamic dietary explanation for this item"
    }
  ],
  "notes": [
    "Actionable consumer advice, certificate recommendations, or packaging declarations"
  ]
}
Do not include markdown code fences or conversational filler.
PROMPT;

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        [
                            'text' => $prompt
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'temperature' => 0.1,
            ]
        ];

        $candidates = array_values(array_unique([$this->model, 'gemini-3-flash-preview', 'gemini-3.1-flash-lite']));
        $lastException = null;

        foreach ($candidates as $candidateModel) {
            $endpoint = $this->apiBaseUrl . rawurlencode($candidateModel) . ':generateContent?key=' . rawurlencode($this->apiKey);
            try {
                $response = $this->executeCurlPost($endpoint, $payload);
                return $this->parseResponse($response, $preferredLang);
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

        throw new \RuntimeException('Failed to verify Halal status: no response received from Gemini.');
    }

    /**
     * Backward-compatible helper method that accepts either raw ingredient array or full Agent 1 data.
     *
     * @param array<mixed> $ingredients
     * @param string $preferredLang
     * @return array<string, mixed>
     */
    public function verifyIngredients(array $ingredients, string $preferredLang = 'ar'): array
    {
        // If passed an array of strings, normalize into Agent 1 contract format
        if (!isset($ingredients['ingredients'])) {
            $formattedIngredients = [];
            foreach ($ingredients as $item) {
                if (is_string($item)) {
                    $formattedIngredients[] = [
                        'original_name' => $item,
                        'normalized_name' => $item,
                        'confidence' => 0.9,
                    ];
                } elseif (is_array($item)) {
                    $formattedIngredients[] = $item;
                }
            }

            $agent1Data = [
                'agent' => 'IngredientVisionAgent',
                'success' => !empty($formattedIngredients),
                'source_quality' => !empty($formattedIngredients) ? 'good' : 'unreadable',
                'source_text' => '',
                'ingredients' => $formattedIngredients,
                'ingredient_count' => count($formattedIngredients),
                'notes' => [],
            ];
            return $this->verify($agent1Data, $preferredLang);
        }

        return $this->verify($ingredients, $preferredLang);
    }

    /**
     * Executes POST request to Gemini endpoint via cURL.
     *
     * @param string $url
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
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
            throw new \RuntimeException('cURL error during Halal verification request: ' . $curlError);
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
     * Validates and parses the structured verification response.
     *
     * @param array<string, mixed> $response
     * @param string $preferredLang
     * @return array<string, mixed>
     * @throws \RuntimeException If response structure is invalid or unparseable
     */
    private function parseResponse(array $response, string $preferredLang = 'ar'): array
    {
        $candidateText = $response['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (empty($candidateText)) {
            throw new \RuntimeException('Gemini API returned an empty response for Halal verification.');
        }

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
            throw new \RuntimeException('Agent 2 returned invalid JSON from Gemini API: ' . json_last_error_msg());
        }

        // Validate canonical status
        $rawStatus = strtoupper(trim((string) ($parsed['status'] ?? 'UNKNOWN')));
        $canonicalStatus = in_array($rawStatus, self::CANONICAL_STATUSES, true) ? $rawStatus : self::STATUS_UNKNOWN;

        $arabicLabels = [
            self::STATUS_HALAL => 'حلال',
            self::STATUS_HARAM => 'حرام',
            self::STATUS_DOUBTFUL => 'مشتبه فيه',
            self::STATUS_UNKNOWN => 'غير معروف',
        ];

        $englishLabels = [
            self::STATUS_HALAL => 'Halal',
            self::STATUS_HARAM => 'Haram',
            self::STATUS_DOUBTFUL => 'Doubtful',
            self::STATUS_UNKNOWN => 'Unknown',
        ];

        // Validate confidence
        $confidence = isset($parsed['confidence']) && is_numeric($parsed['confidence'])
            ? max(0.0, min(1.0, (float) $parsed['confidence']))
            : 0.85;

        // Validate reasons array
        $rawReasons = $parsed['reasons'] ?? [];
        $reasons = [];
        if (is_array($rawReasons)) {
            foreach ($rawReasons as $r) {
                if (is_string($r) && trim($r) !== '') {
                    $reasons[] = trim($r);
                }
            }
        } elseif (is_string($rawReasons) && trim($rawReasons) !== '') {
            $reasons[] = trim($rawReasons);
        }

        if (empty($reasons)) {
            $reasons[] = ($preferredLang === 'ar')
                ? 'تم تقييم المنتج بناءً على تحليل قائمة المكونات المكتشفة.'
                : 'The product was evaluated based on analysis of the detected ingredient list.';
        }

        // Validate ingredient_assessments
        $rawAssessments = is_array($parsed['ingredient_assessments'] ?? null) ? $parsed['ingredient_assessments'] : [];
        $assessments = [];

        foreach ($rawAssessments as $item) {
            if (is_array($item)) {
                $ingName = trim((string) ($item['ingredient'] ?? ''));
                if ($ingName === '') {
                    continue;
                }

                $itemRawStatus = strtoupper(trim((string) ($item['status'] ?? 'UNKNOWN')));
                $itemStatus = in_array($itemRawStatus, self::CANONICAL_STATUSES, true) ? $itemRawStatus : self::STATUS_UNKNOWN;
                $itemReason = trim((string) ($item['reason'] ?? ''));

                $assessments[] = [
                    'ingredient' => $ingName,
                    'status' => $itemStatus,
                    'status_ar' => $arabicLabels[$itemStatus],
                    'status_en' => $englishLabels[$itemStatus],
                    'reason' => $itemReason,
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

        return [
            'agent' => 'HalalVerificationAgent',
            'model' => $this->model,
            'success' => true,
            'status' => $canonicalStatus,
            'status_ar' => $arabicLabels[$canonicalStatus],
            'status_en' => $englishLabels[$canonicalStatus],
            'confidence' => round($confidence, 2),
            'reasons' => $reasons,
            'ingredient_assessments' => $assessments,
            'notes' => $notes,
        ];
    }
}
