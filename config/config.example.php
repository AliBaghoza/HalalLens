<?php
/**
 * HalalLens AI - Application Configuration Template
 *
 * COPY THIS FILE TO 'config.php' FOR LOCAL SERVER CONFIGURATION.
 * 'config.php' is ignored by Git to keep secrets safe.
 *
 * In production or cloud environments, prefer setting environment variables:
 * - GEMINI_API_KEY
 * - GEMINI_MODEL
 */

declare(strict_types=1);

/**
 * Returns the merged application configuration.
 * Priority:
 * 1. Environment variables
 * 2. Local config.php overrides (if file exists)
 * 3. Default configuration settings
 *
 * @return array<string, mixed>
 */
function getAppConfig(): array
{
    // Base configuration with default settings & placeholders
    $defaults = [
        'gemini' => [
            // Leave empty here. Set via GEMINI_API_KEY environment variable or in local config.php
            'api_key' => getenv('GEMINI_API_KEY') ?: ($_ENV['GEMINI_API_KEY'] ?? ''),
            // Configurable Gemini model supporting image input & structured output
            'model' => getenv('GEMINI_MODEL') ?: ($_ENV['GEMINI_MODEL'] ?? 'gemini-3.8-flash'),
            'api_base_url' => 'https://generativelanguage.googleapis.com/v1beta/models/',
            'timeout' => 60, // seconds for cURL requests
        ],
        'upload' => [
            'max_file_size' => 5 * 1024 * 1024, // 5 MB
            'allowed_mime_types' => [
                'image/jpeg',
                'image/png',
                'image/webp'
            ],
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp'],
            'upload_dir' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR,
        ],
        'osm' => [
            'overpass_url' => 'https://overpass-api.de/api/interpreter',
            'nominatim_url' => 'https://nominatim.openstreetmap.org/search',
            'user_agent' => 'HalalLens-AI-Platform/1.0 (University-AI-Engineering-Project)',
            'timeout' => 15,
        ],
        'app' => [
            'default_lang' => 'ar',
            'supported_langs' => ['ar', 'en'],
            'debug' => false,
        ],
    ];

    // Load local config.php if present (not tracked in Git)
    $localConfigFile = __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
    if (file_exists($localConfigFile)) {
        $localConfig = require $localConfigFile;
        if (is_array($localConfig)) {
            $defaults = array_replace_recursive($defaults, $localConfig);
        }
    }

    // Ensure environment variables always take precedence if defined
    $envApiKey = getenv('GEMINI_API_KEY') ?: ($_ENV['GEMINI_API_KEY'] ?? ($_SERVER['GEMINI_API_KEY'] ?? ''));
    if (!empty($envApiKey)) {
        $defaults['gemini']['api_key'] = $envApiKey;
    }

    $envModel = getenv('GEMINI_MODEL') ?: ($_ENV['GEMINI_MODEL'] ?? ($_SERVER['GEMINI_MODEL'] ?? ''));
    if (!empty($envModel)) {
        $defaults['gemini']['model'] = $envModel;
    }

    return $defaults;
}

/**
 * Resilient image MIME type detector that works across all PHP environments,
 * including minimal shared hosting installations without ext-fileinfo.
 *
 * @param string $filePath
 * @param string|null $fallbackClientMime
 * @return string
 */
function detectImageMimeType(string $filePath, ?string $fallbackClientMime = null): string
{
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return '';
    }

    // 1. Try getimagesize() - built into PHP core/GD
    if (function_exists('getimagesize')) {
        $imgInfo = @getimagesize($filePath);
        if (is_array($imgInfo) && !empty($imgInfo['mime'])) {
            return (string) $imgInfo['mime'];
        }
    }

    // 2. Try fileinfo extension if loaded
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

    // 3. Try mime_content_type if available
    if (function_exists('mime_content_type')) {
        $mime = @mime_content_type($filePath);
        if ($mime) {
            return (string) $mime;
        }
    }

    // 4. Magic bytes inspection
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

    return $fallbackClientMime ?: 'application/octet-stream';
}

