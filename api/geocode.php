<?php
/**
 * HalalLens AI - Nominatim Geocoding Proxy Service
 *
 * Converts a text location query (e.g. city name, district) into geographical coordinates
 * using OpenStreetMap Nominatim with server-side User-Agent compliance.
 *
 * Parameters (GET):
 * - q: query string (string, required)
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.example.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed. Use GET.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $config = getAppConfig();

    $query = filter_input(INPUT_GET, 'q', FILTER_DEFAULT);
    if (empty($query) || trim((string) $query) === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Query parameter "q" is required.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $nominatimUrl = $config['osm']['nominatim_url'] ?? 'https://nominatim.openstreetmap.org/search';
    $userAgent = $config['osm']['user_agent'] ?? 'HalalLens-AI-Platform/1.0';
    $timeout = (int) ($config['osm']['timeout'] ?? 10);

    $params = [
        'q' => trim((string) $query),
        'format' => 'json',
        'addressdetails' => 1,
        'limit' => 5,
        'accept-language' => 'ar,en',
    ];

    $requestUrl = $nominatimUrl . '?' . http_build_query($params);

    if (!function_exists('curl_init')) {
        throw new \RuntimeException('PHP cURL extension is required for geocoding.');
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $requestUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => $userAgent,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    if (PHP_VERSION_ID < 80500) {
        @curl_close($ch);
    }

    if ($response === false) {
        throw new \RuntimeException('Failed to query Nominatim geocoding service: ' . $curlError);
    }

    $data = json_decode((string) $response, true);
    if (!is_array($data)) {
        throw new \RuntimeException('Invalid response from Nominatim geocoding service.');
    }

    $results = [];
    foreach ($data as $item) {
        $results[] = [
            'display_name' => $item['display_name'] ?? '',
            'lat' => (float) ($item['lat'] ?? 0),
            'lng' => (float) ($item['lon'] ?? 0),
            'type' => $item['type'] ?? '',
        ];
    }

    echo json_encode([
        'success' => true,
        'count' => count($results),
        'data' => $results,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
