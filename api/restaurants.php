<?php
/**
 * HalalLens AI - Halal Restaurant Finder Service (Decoupled OSM Overpass Proxy)
 *
 * Queries OpenStreetMap Overpass API for verified Halal restaurants, cafes, and fast-food
 * within a given radius of a latitude/longitude coordinate pair.
 *
 * Parameters (GET):
 * - lat: latitude (float, required)
 * - lng: longitude (float, required)
 * - radius: search radius in meters (int, optional, default: 3000, max: 25000)
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

    $lat = filter_input(INPUT_GET, 'lat', FILTER_VALIDATE_FLOAT);
    $lng = filter_input(INPUT_GET, 'lng', FILTER_VALIDATE_FLOAT);
    $radius = filter_input(INPUT_GET, 'radius', FILTER_VALIDATE_INT) ?: 3000;

    if ($lat === false || $lat === null || $lng === false || $lng === null) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Valid "lat" and "lng" query parameters are required.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Coordinates out of valid geographical range.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Clamp radius between 500m and 25000m
    $radius = max(500, min(25000, $radius));

    $overpassUrl = $config['osm']['overpass_url'] ?? 'https://overpass-api.de/api/interpreter';
    $userAgent = $config['osm']['user_agent'] ?? 'HalalLens-AI-Platform/1.0';
    $timeout = (int) ($config['osm']['timeout'] ?? 15);

    // Build Overpass QL query targeting halal amenities
    $overpassQuery = <<<QL
[out:json][timeout:$timeout];
(
  node["amenity"~"restaurant|fast_food|cafe"]["diet:halal"="yes"](around:$radius,$lat,$lng);
  way["amenity"~"restaurant|fast_food|cafe"]["diet:halal"="yes"](around:$radius,$lat,$lng);
  node["amenity"~"restaurant|fast_food|cafe"]["halal"="yes"](around:$radius,$lat,$lng);
  way["amenity"~"restaurant|fast_food|cafe"]["halal"="yes"](around:$radius,$lat,$lng);
  node["amenity"~"restaurant|fast_food|cafe"]["cuisine"~"halal",i](around:$radius,$lat,$lng);
  way["amenity"~"restaurant|fast_food|cafe"]["cuisine"~"halal",i](around:$radius,$lat,$lng);
);
out center tags 30;
QL;

    if (!function_exists('curl_init')) {
        throw new \RuntimeException('PHP cURL extension is required for restaurant lookup.');
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $overpassUrl,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => 'data=' . urlencode($overpassQuery),
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
        throw new \RuntimeException('Failed to connect to OpenStreetMap Overpass API: ' . $curlError);
    }

    $data = json_decode((string) $response, true);
    if (!is_array($data)) {
        throw new \RuntimeException('Invalid response received from OpenStreetMap Overpass service.');
    }

    $elements = $data['elements'] ?? [];
    $restaurants = [];

    foreach ($elements as $el) {
        $tags = $el['tags'] ?? [];
        $name = $tags['name:ar'] ?? ($tags['name'] ?? ($tags['name:en'] ?? 'مطعم حلال / Halal Restaurant'));
        
        $itemLat = $el['lat'] ?? ($el['center']['lat'] ?? null);
        $itemLng = $el['lon'] ?? ($el['center']['lon'] ?? null);

        // Calculate distance using Haversine formula
        $distance = null;
        if ($itemLat !== null && $itemLng !== null) {
            $theta = $lng - $itemLng;
            $dist = sin(deg2rad($lat)) * sin(deg2rad($itemLat)) + cos(deg2rad($lat)) * cos(deg2rad($itemLat)) * cos(deg2rad($theta));
            $dist = acos(max(-1.0, min(1.0, $dist)));
            $dist = rad2deg($dist);
            $distance = round($dist * 60 * 1.1515 * 1.609344, 2); // km
        }

        $addressParts = array_filter([
            $tags['addr:street'] ?? null,
            $tags['addr:housenumber'] ?? null,
            $tags['addr:city'] ?? null,
        ]);

        $restaurants[] = [
            'id' => $el['id'] ?? null,
            'name' => $name,
            'amenity' => $tags['amenity'] ?? 'restaurant',
            'cuisine' => $tags['cuisine'] ?? ($tags['diet:halal'] === 'yes' ? 'Halal' : 'General'),
            'lat' => $itemLat,
            'lng' => $itemLng,
            'distance_km' => $distance,
            'address' => implode(', ', $addressParts) ?: ($tags['address'] ?? null),
            'phone' => $tags['phone'] ?? ($tags['contact:phone'] ?? null),
            'website' => $tags['website'] ?? ($tags['contact:website'] ?? null),
        ];
    }

    // Sort by distance ascending
    usort($restaurants, fn($a, $b) => ($a['distance_km'] ?? 999) <=> ($b['distance_km'] ?? 999));

    echo json_encode([
        'success' => true,
        'count' => count($restaurants),
        'radius_meters' => $radius,
        'data' => $restaurants,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
