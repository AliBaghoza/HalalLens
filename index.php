<?php
/**
 * HalalLens AI - Main Web Application Entry Point
 *
 * Defaults to Arabic (lang="ar", dir="rtl") and Tajawal typography.
 */

declare(strict_types=1);

// Language resolution: Default strictly to Arabic ('ar')
$supportedLangs = ['ar', 'en'];
$lang = 'ar';

if (isset($_GET['lang']) && in_array($_GET['lang'], $supportedLangs, true)) {
    $lang = $_GET['lang'];
    setcookie('halallens_lang', $lang, [
        'expires' => time() + (86400 * 30),
        'path' => '/',
        'samesite' => 'Lax'
    ]);
} elseif (isset($_COOKIE['halallens_lang']) && in_array($_COOKIE['halallens_lang'], $supportedLangs, true)) {
    $lang = $_COOKIE['halallens_lang'];
}

// Load appropriate localization dictionary
$langFile = __DIR__ . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $lang . '.php';
if (!file_exists($langFile)) {
    $langFile = __DIR__ . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'ar.php';
}
$t = require $langFile;

$direction = $t['direction'] ?? 'rtl';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') ?>" dir="<?= htmlspecialchars($direction, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($t['site_title'], ENT_QUOTES, 'UTF-8') ?></title>
    
    <!-- Universal Typography: Tajawal Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    
    <!-- Application Stylesheet -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Header & Navigation -->
    <header class="app-header">
        <div class="container header-inner">
            <div class="brand">
                <div class="brand-logo" aria-hidden="true">
                    <svg viewBox="0 0 40 40" width="36" height="36" fill="none">
                        <circle cx="20" cy="20" r="18" stroke="#13794f" stroke-width="2.5" fill="#f0fdf4"/>
                        <circle cx="20" cy="20" r="9" stroke="#c59b27" stroke-width="2"/>
                        <path d="M20 14v12M14 20h12" stroke="#13794f" stroke-width="2.5" stroke-linecap="round"/>
                        <path d="M26 26l7 7" stroke="#13794f" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="brand-info">
                    <h1 class="brand-title"><?= htmlspecialchars($t['app_name'], ENT_QUOTES, 'UTF-8') ?></h1>
                    <span class="university-tag"><?= htmlspecialchars($t['university_badge'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>

            <!-- Language Switcher -->
            <div class="header-actions">
                <a href="?lang=<?= urlencode($t['switch_lang_target']) ?>" class="lang-switch-btn" title="Switch Language" data-lang="<?= urlencode($t['switch_lang_target']) ?>">
                    <span class="globe-icon" aria-hidden="true">🌐</span>
                    <span><?= htmlspecialchars($t['switch_lang_label'], ENT_QUOTES, 'UTF-8') ?></span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="container main-content">

        <!-- Hero Section -->
        <section class="hero-section">
            <h2 class="hero-title"><?= htmlspecialchars($t['hero_title'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="hero-subtitle"><?= htmlspecialchars($t['hero_desc'], ENT_QUOTES, 'UTF-8') ?></p>
        </section>

        <!-- Product Image Upload Card -->
        <section class="card upload-card" aria-labelledby="upload-heading">
            <div class="card-header">
                <h3 id="upload-heading" class="card-title"><?= htmlspecialchars($t['upload_title'], ENT_QUOTES, 'UTF-8') ?></h3>
            </div>

            <form id="analyze-form" enctype="multipart/form-data">
                <div id="drop-zone" class="drop-zone" tabindex="0" role="region" aria-label="<?= htmlspecialchars($t['upload_title'], ENT_QUOTES, 'UTF-8') ?>">
                    <input type="file" id="image-input" name="image" accept="image/jpeg,image/png,image/webp" class="visually-hidden">
                    <div class="drop-zone-content">
                        <div class="upload-icon" aria-hidden="true">
                            <svg viewBox="0 0 48 48" width="48" height="48" fill="none">
                                <path d="M24 10v20m-8-12l8-8 8 8" stroke="#13794f" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M8 30v8a4 4 0 004 4h24a4 4 0 004-4v-8" stroke="#64748b" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <p class="drop-prompt">
                            <?= htmlspecialchars($t['upload_drag_drop'], ENT_QUOTES, 'UTF-8') ?>
                            <button type="button" id="browse-btn" class="btn-link"><?= htmlspecialchars($t['upload_browse'], ENT_QUOTES, 'UTF-8') ?></button>
                        </p>
                        <p class="drop-hint"><?= htmlspecialchars($t['upload_hint'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>

                    <!-- Image Preview Area -->
                    <div id="preview-container" class="preview-container hidden">
                        <img id="preview-img" src="" alt="Product label preview" class="preview-thumbnail">
                        <div class="preview-details">
                            <span id="preview-filename" class="preview-filename"></span>
                            <span id="preview-filesize" class="preview-filesize"></span>
                            <button type="button" id="remove-btn" class="btn-remove" title="<?= htmlspecialchars($t['btn_clear'], ENT_QUOTES, 'UTF-8') ?>">✕</button>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" id="submit-btn" class="btn btn-primary" disabled>
                        <span class="btn-spinner hidden" aria-hidden="true"></span>
                        <span class="btn-text"><?= htmlspecialchars($t['btn_analyze'], ENT_QUOTES, 'UTF-8') ?></span>
                    </button>
                    <button type="button" id="clear-btn" class="btn btn-secondary hidden">
                        <?= htmlspecialchars($t['btn_clear'], ENT_QUOTES, 'UTF-8') ?>
                    </button>
                </div>
            </form>

            <div id="alert-banner" class="alert-banner hidden" role="alert"></div>
        </section>

        <!-- Multi-Agent Live Pipeline Visualizer -->
        <section class="card pipeline-card" aria-labelledby="pipeline-heading">
            <h3 id="pipeline-heading" class="card-title"><?= htmlspecialchars($t['pipeline_title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <div class="pipeline-track">
                <!-- Agent 1 Stage -->
                <div class="pipeline-step" id="step-vision" data-status="idle">
                    <div class="step-indicator">
                        <span class="step-number">1</span>
                    </div>
                    <div class="step-content">
                        <h4 class="step-title"><?= htmlspecialchars($t['agent_vision_title'], ENT_QUOTES, 'UTF-8') ?></h4>
                        <p class="step-desc"><?= htmlspecialchars($t['agent_vision_desc'], ENT_QUOTES, 'UTF-8') ?></p>
                        <span class="step-badge" id="badge-vision"><?= htmlspecialchars($t['status_waiting'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>

                <div class="pipeline-divider" aria-hidden="true"></div>

                <!-- Agent 2 Stage -->
                <div class="pipeline-step" id="step-halal" data-status="idle">
                    <div class="step-indicator">
                        <span class="step-number">2</span>
                    </div>
                    <div class="step-content">
                        <h4 class="step-title"><?= htmlspecialchars($t['agent_halal_title'], ENT_QUOTES, 'UTF-8') ?></h4>
                        <p class="step-desc"><?= htmlspecialchars($t['agent_halal_desc'], ENT_QUOTES, 'UTF-8') ?></p>
                        <span class="step-badge" id="badge-halal"><?= htmlspecialchars($t['status_waiting'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Analysis Results Section Placeholder -->
        <section id="results-card" class="card results-card" aria-labelledby="results-heading">
            <div class="card-header">
                <h3 id="results-heading" class="card-title"><?= htmlspecialchars($t['results_title'], ENT_QUOTES, 'UTF-8') ?></h3>
            </div>

            <!-- Initial Idle State -->
            <div id="results-idle" class="results-idle">
                <div class="idle-icon" aria-hidden="true">🔍</div>
                <p class="idle-text"><?= htmlspecialchars($t['no_analysis_yet'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>

            <!-- Active Results Container (Populated via Real API response) -->
            <div id="results-content" class="results-content hidden">
                <!-- Verdict Header -->
                <div class="verdict-banner" id="verdict-banner">
                    <div class="verdict-main">
                        <span class="verdict-label"><?= htmlspecialchars($t['results_badge_label'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="verdict-badge" id="verdict-badge">-</span>
                    </div>
                    <div class="confidence-indicator">
                        <span class="confidence-label"><?= htmlspecialchars($t['results_confidence'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="confidence-val" id="confidence-val">--%</span>
                    </div>
                </div>

                <!-- Extracted Ingredients List -->
                <div class="results-section" id="ingredients-container">
                    <h4 class="section-subtitle"><?= htmlspecialchars($t['results_ingredients_title'], ENT_QUOTES, 'UTF-8') ?></h4>
                    <div class="ingredients-tags" id="ingredients-tags"></div>
                </div>

                <!-- Overall Reasons -->
                <div class="results-section" id="reasons-container">
                    <h4 class="section-subtitle"><?= htmlspecialchars($t['results_reasons_title'], ENT_QUOTES, 'UTF-8') ?></h4>
                    <ul class="reasons-list" id="reasons-list"></ul>
                </div>

                <!-- Individual Ingredient Assessments -->
                <div class="results-section" id="assessments-container">
                    <h4 class="section-subtitle"><?= htmlspecialchars($t['results_assessments_title'], ENT_QUOTES, 'UTF-8') ?></h4>
                    <div class="assessments-grid" id="assessments-list"></div>
                </div>

                <!-- Notes & Warnings -->
                <div class="results-section hidden" id="notes-container">
                    <h4 class="section-subtitle"><?= htmlspecialchars($t['results_notes_title'], ENT_QUOTES, 'UTF-8') ?></h4>
                    <ul class="notes-list" id="notes-list"></ul>
                </div>
            </div>
        </section>

        <!-- Future Planned Feature: Nearby Halal Restaurants (Coming Soon) -->
        <section class="card restaurant-coming-soon-card" aria-labelledby="restaurant-heading">
            <div class="coming-soon-badge-wrapper">
                <span class="coming-soon-badge">
                    <span class="coming-soon-dot" aria-hidden="true"></span>
                    <?= htmlspecialchars($t['restaurant_badge_coming_soon'], ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>
            <div class="coming-soon-icon" aria-hidden="true">
                <div class="icon-circle">
                    <svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                </div>
            </div>
            <h3 id="restaurant-heading" class="coming-soon-title"><?= htmlspecialchars($t['restaurant_title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="coming-soon-desc"><?= htmlspecialchars($t['restaurant_coming_soon_desc'], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="coming-soon-subdesc"><?= htmlspecialchars($t['restaurant_coming_soon_subdesc'], ENT_QUOTES, 'UTF-8') ?></p>
        </section>

    </main>

    <!-- Footer -->
    <footer class="app-footer">
        <div class="container footer-inner">
            <p class="footer-title"><?= htmlspecialchars($t['footer_text'], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="footer-disclaimer"><?= htmlspecialchars($t['footer_disclaimer'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </footer>

    <!-- Pass localization tokens safely to frontend JS -->
    <script>
        window.HALALLENS_CONFIG = {
            currentLang: "<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') ?>",
            direction: "<?= htmlspecialchars($direction, ENT_QUOTES, 'UTF-8') ?>",
            i18n: {
                waiting: "<?= htmlspecialchars($t['status_waiting'], ENT_QUOTES, 'UTF-8') ?>",
                processing: "<?= htmlspecialchars($t['status_processing'], ENT_QUOTES, 'UTF-8') ?>",
                completed: "<?= htmlspecialchars($t['status_completed'], ENT_QUOTES, 'UTF-8') ?>",
                failed: "<?= htmlspecialchars($t['status_failed'], ENT_QUOTES, 'UTF-8') ?>",
                errorNoFile: "<?= htmlspecialchars($t['error_no_file'], ENT_QUOTES, 'UTF-8') ?>",
                errorFileSize: "<?= htmlspecialchars($t['error_file_size'], ENT_QUOTES, 'UTF-8') ?>",
                errorFileType: "<?= htmlspecialchars($t['error_file_type'], ENT_QUOTES, 'UTF-8') ?>",
                errorGeneric: "<?= htmlspecialchars($t['error_generic'], ENT_QUOTES, 'UTF-8') ?>",
                statuses: {
                    HALAL: "<?= htmlspecialchars($t['status_halal'], ENT_QUOTES, 'UTF-8') ?>",
                    HARAM: "<?= htmlspecialchars($t['status_haram'], ENT_QUOTES, 'UTF-8') ?>",
                    DOUBTFUL: "<?= htmlspecialchars($t['status_doubtful'], ENT_QUOTES, 'UTF-8') ?>",
                    UNKNOWN: "<?= htmlspecialchars($t['status_unknown'], ENT_QUOTES, 'UTF-8') ?>"
                }
            }
        };
    </script>
    <script src="js/app.js"></script>
</body>
</html>
