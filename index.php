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
    
    <!-- Favicon & Brand Icons -->
    <link rel="icon" type="image/png" href="assets/halallens-logo.png">
    <link rel="apple-touch-icon" href="assets/halallens-logo.png">
    
    <!-- Open Graph & Meta Tags -->
    <meta name="description" content="<?= htmlspecialchars($t['hero_desc'], ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:title" content="<?= htmlspecialchars($t['site_title'], ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($t['hero_desc'], ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image" content="assets/halallens-logo.png">
    <meta property="og:type" content="website">
    <meta name="theme-color" content="#13794f">

    <!-- Application Stylesheet -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Header & Navigation -->
    <header class="app-header">
        <div class="container header-inner">
            <div class="brand">
                <a href="#hero" class="brand-link" aria-label="<?= htmlspecialchars($t['app_name'], ENT_QUOTES, 'UTF-8') ?>">
                    <img src="assets/halallens-logo.png" alt="<?= htmlspecialchars($t['logo_alt'], ENT_QUOTES, 'UTF-8') ?>" class="app-logo" width="46" height="46">
                </a>
                <div class="brand-info">
                    <span class="brand-title"><?= htmlspecialchars($t['app_brand'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="university-tag"><?= htmlspecialchars($t['university_badge'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>

            <!-- Navigation Links (Desktop) -->
            <nav class="nav-menu" id="nav-menu" aria-label="Main Navigation">
                <a href="#hero" class="nav-link"><?= htmlspecialchars($t['nav_home'], ENT_QUOTES, 'UTF-8') ?></a>
                <a href="#analyze-section" class="nav-link"><?= htmlspecialchars($t['nav_analyze'], ENT_QUOTES, 'UTF-8') ?></a>
                <a href="#how-it-works" class="nav-link"><?= htmlspecialchars($t['nav_how_it_works'], ENT_QUOTES, 'UTF-8') ?></a>
                <a href="#multi-agent" class="nav-link"><?= htmlspecialchars($t['nav_multi_agent'], ENT_QUOTES, 'UTF-8') ?></a>
                <a href="#features" class="nav-link"><?= htmlspecialchars($t['nav_features'], ENT_QUOTES, 'UTF-8') ?></a>
                <a href="#statuses-guide" class="nav-link"><?= htmlspecialchars($t['nav_statuses'], ENT_QUOTES, 'UTF-8') ?></a>
                <a href="#restaurant-section" class="nav-link nav-link-badge">
                    <span><?= htmlspecialchars($t['nav_restaurants'], ENT_QUOTES, 'UTF-8') ?></span>
                </a>
            </nav>

            <!-- Actions: Lang Switcher & Mobile Toggle -->
            <div class="header-actions">
                <a href="?lang=<?= urlencode($t['switch_lang_target']) ?>" class="lang-switch-btn" title="Switch Language" data-lang="<?= urlencode($t['switch_lang_target']) ?>">
                    <span class="globe-icon" aria-hidden="true">🌐</span>
                    <span><?= htmlspecialchars($t['switch_lang_label'], ENT_QUOTES, 'UTF-8') ?></span>
                </a>
                <button type="button" class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Toggle Navigation Menu" aria-expanded="false">
                    <span class="menu-bar"></span>
                    <span class="menu-bar"></span>
                    <span class="menu-bar"></span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="container main-content">

        <!-- Hero Section (Desktop 2-Column / Mobile Stacked) -->
        <section class="hero-section" id="hero">
            <div class="hero-grid">
                <div class="hero-content">
                    <div class="hero-tagline-wrapper">
                        <span class="hero-tagline">
                            <span class="tagline-star" aria-hidden="true">✦</span>
                            <?= htmlspecialchars($t['hero_tagline'], ENT_QUOTES, 'UTF-8') ?>
                            <span class="tagline-star" aria-hidden="true">✦</span>
                        </span>
                    </div>
                    <h1 class="hero-title"><?= htmlspecialchars($t['hero_title'], ENT_QUOTES, 'UTF-8') ?></h1>
                    <p class="hero-subtitle"><?= htmlspecialchars($t['hero_desc'], ENT_QUOTES, 'UTF-8') ?></p>
                    
                    <div class="hero-cta-group">
                        <a href="#analyze-section" class="btn btn-primary btn-cta">
                            <span class="cta-icon" aria-hidden="true">🔍</span>
                            <span><?= htmlspecialchars($t['hero_cta_analyze'], ENT_QUOTES, 'UTF-8') ?></span>
                        </a>
                        <a href="#how-it-works" class="btn btn-outline btn-cta">
                            <span><?= htmlspecialchars($t['hero_cta_learn'], ENT_QUOTES, 'UTF-8') ?></span>
                        </a>
                    </div>
                </div>

                <div class="hero-visual">
                    <div class="hero-emblem-card">
                        <div class="emblem-pattern-bg" aria-hidden="true"></div>
                        <img src="assets/halallens-logo.png" alt="<?= htmlspecialchars($t['logo_alt'], ENT_QUOTES, 'UTF-8') ?>" class="hero-emblem-img" width="220" height="220">
                    </div>
                </div>
            </div>

            <!-- Quick Features Bar (From Brand Identity Mockup) -->
            <div class="quick-features-bar">
                <div class="quick-feature-card">
                    <div class="quick-icon-wrapper">
                        <img src="assets/icons/icon-camera.png" alt="" class="quick-icon-img" width="44" height="44" loading="lazy">
                    </div>
                    <div class="quick-feature-info">
                        <h4 class="quick-title"><?= htmlspecialchars($t['quick_capture_title'], ENT_QUOTES, 'UTF-8') ?></h4>
                        <p class="quick-desc"><?= htmlspecialchars($t['quick_capture_desc'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>

                <div class="quick-feature-card">
                    <div class="quick-icon-wrapper">
                        <img src="assets/icons/icon-analysis.png" alt="" class="quick-icon-img" width="44" height="44" loading="lazy">
                    </div>
                    <div class="quick-feature-info">
                        <h4 class="quick-title"><?= htmlspecialchars($t['quick_analysis_title'], ENT_QUOTES, 'UTF-8') ?></h4>
                        <p class="quick-desc"><?= htmlspecialchars($t['quick_analysis_desc'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>

                <div class="quick-feature-card">
                    <div class="quick-icon-wrapper">
                        <img src="assets/icons/icon-shield.png" alt="" class="quick-icon-img" width="44" height="44" loading="lazy">
                    </div>
                    <div class="quick-feature-info">
                        <h4 class="quick-title"><?= htmlspecialchars($t['quick_verify_title'], ENT_QUOTES, 'UTF-8') ?></h4>
                        <p class="quick-desc"><?= htmlspecialchars($t['quick_verify_desc'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>

                <div class="quick-feature-card is-coming-soon">
                    <div class="quick-icon-wrapper">
                        <img src="assets/icons/icon-restaurant.png" alt="" class="quick-icon-img" width="44" height="44" loading="lazy">
                    </div>
                    <div class="quick-feature-info">
                        <div class="quick-title-wrap">
                            <h4 class="quick-title"><?= htmlspecialchars($t['quick_search_title'], ENT_QUOTES, 'UTF-8') ?></h4>
                            <span class="quick-badge"><?= htmlspecialchars($t['quick_search_badge'], ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <p class="quick-desc"><?= htmlspecialchars($t['quick_search_desc'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Section (4 Steps) -->
        <section class="section-container" id="how-it-works" aria-labelledby="how-heading">
            <div class="section-header text-center">
                <span class="section-badge"><?= htmlspecialchars($t['nav_how_it_works'], ENT_QUOTES, 'UTF-8') ?></span>
                <h2 id="how-heading" class="section-title"><?= htmlspecialchars($t['how_title'], ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="section-subtitle"><?= htmlspecialchars($t['how_subtitle'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>

            <div class="how-steps-grid">
                <div class="how-step-card">
                    <div class="how-step-number">1</div>
                    <div class="how-step-icon-wrapper">
                        <img src="assets/icons/step1-capture.png" alt="" class="how-step-img" width="68" height="68" loading="lazy">
                    </div>
                    <h3 class="how-step-title"><?= htmlspecialchars($t['how_step1_title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="how-step-desc"><?= htmlspecialchars($t['how_step1_desc'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <div class="how-step-card">
                    <div class="how-step-number">2</div>
                    <div class="how-step-icon-wrapper">
                        <img src="assets/icons/step2-extract.png" alt="" class="how-step-img" width="68" height="68" loading="lazy">
                    </div>
                    <h3 class="how-step-title"><?= htmlspecialchars($t['how_step2_title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="how-step-desc"><?= htmlspecialchars($t['how_step2_desc'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <div class="how-step-card">
                    <div class="how-step-number">3</div>
                    <div class="how-step-icon-wrapper">
                        <img src="assets/icons/step3-verify.png" alt="" class="how-step-img" width="68" height="68" loading="lazy">
                    </div>
                    <h3 class="how-step-title"><?= htmlspecialchars($t['how_step3_title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="how-step-desc"><?= htmlspecialchars($t['how_step3_desc'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <div class="how-step-card">
                    <div class="how-step-number">4</div>
                    <div class="how-step-icon-wrapper">
                        <img src="assets/icons/step4-result.png" alt="" class="how-step-img" width="68" height="68" loading="lazy">
                    </div>
                    <h3 class="how-step-title"><?= htmlspecialchars($t['how_step4_title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="how-step-desc"><?= htmlspecialchars($t['how_step4_desc'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
        </section>

        <!-- Multi-Agent Architecture Explanation -->
        <section class="section-container" id="multi-agent" aria-labelledby="arch-heading">
            <div class="section-header text-center">
                <span class="section-badge"><?= htmlspecialchars($t['nav_multi_agent'], ENT_QUOTES, 'UTF-8') ?></span>
                <h2 id="arch-heading" class="section-title"><?= htmlspecialchars($t['arch_title'], ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="section-subtitle"><?= htmlspecialchars($t['arch_subtitle'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>

            <!-- Visual Pipeline Schematic -->
            <div class="arch-flow-diagram">
                <div class="flow-node">
                    <span class="flow-label"><?= htmlspecialchars($t['how_step1_title'], ENT_QUOTES, 'UTF-8') ?></span>
                    <img src="assets/icons/arch-box.png" alt="" class="flow-icon-img" width="30" height="30" loading="lazy">
                </div>
                <div class="flow-arrow" aria-hidden="true">➜</div>
                <div class="flow-node flow-node-agent">
                    <span class="flow-badge">Agent 1</span>
                    <span class="flow-label"><?= htmlspecialchars($t['agent1_name'], ENT_QUOTES, 'UTF-8') ?></span>
                    <img src="assets/icons/arch-agent1.png" alt="" class="flow-icon-img" width="32" height="32" loading="lazy">
                </div>
                <div class="flow-arrow" aria-hidden="true">➜</div>
                <div class="flow-node">
                    <span class="flow-label">JSON</span>
                    <img src="assets/icons/arch-json.png" alt="" class="flow-icon-img" width="30" height="30" loading="lazy">
                </div>
                <div class="flow-arrow" aria-hidden="true">➜</div>
                <div class="flow-node flow-node-agent">
                    <span class="flow-badge">Agent 2</span>
                    <span class="flow-label"><?= htmlspecialchars($t['agent2_name'], ENT_QUOTES, 'UTF-8') ?></span>
                    <img src="assets/icons/arch-agent2.png" alt="" class="flow-icon-img" width="32" height="32" loading="lazy">
                </div>
                <div class="flow-arrow" aria-hidden="true">➜</div>
                <div class="flow-node flow-node-verdict">
                    <span class="flow-label"><?= htmlspecialchars($t['how_step4_title'], ENT_QUOTES, 'UTF-8') ?></span>
                    <img src="assets/icons/arch-result.png" alt="" class="flow-icon-img" width="30" height="30" loading="lazy">
                </div>
            </div>

            <!-- Agent Roles Cards -->
            <div class="arch-agents-grid">
                <div class="card agent-card">
                    <div class="agent-card-header">
                        <span class="agent-badge"><?= htmlspecialchars($t['agent1_badge'], ENT_QUOTES, 'UTF-8') ?></span>
                        <h3 class="agent-title"><?= htmlspecialchars($t['agent1_name'], ENT_QUOTES, 'UTF-8') ?></h3>
                    </div>
                    <p class="agent-desc"><?= htmlspecialchars($t['agent1_desc'], ENT_QUOTES, 'UTF-8') ?></p>
                    <ul class="agent-features">
                        <li><span class="check-bullet">✓</span> <?= htmlspecialchars($t['agent1_f1'], ENT_QUOTES, 'UTF-8') ?></li>
                        <li><span class="check-bullet">✓</span> <?= htmlspecialchars($t['agent1_f2'], ENT_QUOTES, 'UTF-8') ?></li>
                        <li><span class="check-bullet">✓</span> <?= htmlspecialchars($t['agent1_f3'], ENT_QUOTES, 'UTF-8') ?></li>
                        <li><span class="check-bullet">✓</span> <?= htmlspecialchars($t['agent1_f4'], ENT_QUOTES, 'UTF-8') ?></li>
                    </ul>
                </div>

                <div class="card agent-card">
                    <div class="agent-card-header">
                        <span class="agent-badge"><?= htmlspecialchars($t['agent2_badge'], ENT_QUOTES, 'UTF-8') ?></span>
                        <h3 class="agent-title"><?= htmlspecialchars($t['agent2_name'], ENT_QUOTES, 'UTF-8') ?></h3>
                    </div>
                    <p class="agent-desc"><?= htmlspecialchars($t['agent2_desc'], ENT_QUOTES, 'UTF-8') ?></p>
                    <ul class="agent-features">
                        <li><span class="check-bullet">✓</span> <?= htmlspecialchars($t['agent2_f1'], ENT_QUOTES, 'UTF-8') ?></li>
                        <li><span class="check-bullet">✓</span> <?= htmlspecialchars($t['agent2_f2'], ENT_QUOTES, 'UTF-8') ?></li>
                        <li><span class="check-bullet">✓</span> <?= htmlspecialchars($t['agent2_f3'], ENT_QUOTES, 'UTF-8') ?></li>
                        <li><span class="check-bullet">✓</span> <?= htmlspecialchars($t['agent2_f4'], ENT_QUOTES, 'UTF-8') ?></li>
                    </ul>
                </div>
            </div>

            <div class="arch-security-banner">
                <span class="security-icon" aria-hidden="true">🔒</span>
                <p><?= htmlspecialchars($t['arch_flow_note'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </section>

        <!-- Product Image Upload & Analysis Workspace -->
        <section class="section-container" id="analyze-section" aria-labelledby="upload-heading">
            <div class="section-header text-center">
                <span class="section-badge"><?= htmlspecialchars($t['nav_analyze'], ENT_QUOTES, 'UTF-8') ?></span>
                <h2 id="upload-heading" class="section-title"><?= htmlspecialchars($t['upload_title'], ENT_QUOTES, 'UTF-8') ?></h2>
            </div>

            <!-- Upload Card -->
            <div class="card upload-card">
                <form id="analyze-form" enctype="multipart/form-data">
                    <div id="drop-zone" class="drop-zone" tabindex="0" role="region" aria-label="<?= htmlspecialchars($t['upload_title'], ENT_QUOTES, 'UTF-8') ?>">
                        <input type="file" id="image-input" name="image" accept="image/jpeg,image/png,image/webp" class="visually-hidden">
                        <div class="drop-zone-content">
                            <div class="upload-icon" aria-hidden="true">
                                <svg viewBox="0 0 48 48" width="48" height="48" fill="none">
                                    <path d="M24 10v20m-8-12l8-8 8 8" stroke="#1b7a43" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
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
            </div>

            <!-- Multi-Agent Live Visual Pipeline Timeline -->
            <div class="card pipeline-card" aria-labelledby="pipeline-heading">
                <h3 id="pipeline-heading" class="card-title"><?= htmlspecialchars($t['pipeline_title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <div class="pipeline-track">
                    <!-- Agent 1 Stage -->
                    <div class="pipeline-step" id="step-vision" data-status="idle">
                        <div class="step-indicator">
                            <span class="step-number">1</span>
                        </div>
                        <div class="step-content">
                            <h4 class="step-title"><?= htmlspecialchars($t['stage1_title'], ENT_QUOTES, 'UTF-8') ?></h4>
                            <p class="step-desc"><?= htmlspecialchars($t['stage1_desc'], ENT_QUOTES, 'UTF-8') ?></p>
                            <span class="step-badge" id="badge-vision"><?= htmlspecialchars($t['stage1_status_waiting'], ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    </div>

                    <div class="pipeline-divider" aria-hidden="true"></div>

                    <!-- Agent 2 Stage -->
                    <div class="pipeline-step" id="step-halal" data-status="idle">
                        <div class="step-indicator">
                            <span class="step-number">2</span>
                        </div>
                        <div class="step-content">
                            <h4 class="step-title"><?= htmlspecialchars($t['stage2_title'], ENT_QUOTES, 'UTF-8') ?></h4>
                            <p class="step-desc"><?= htmlspecialchars($t['stage2_desc'], ENT_QUOTES, 'UTF-8') ?></p>
                            <span class="step-badge" id="badge-halal"><?= htmlspecialchars($t['stage2_status_waiting'], ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CRITICAL STAGE 1 CARD: Extracted Ingredients Card (Shown IMMEDIATELY after Agent 1 finishes) -->
            <div id="ingredients-stage-card" class="card ingredients-stage-card hidden" aria-labelledby="stage-ingredients-heading">
                <div class="stage-card-header">
                    <div class="stage-header-title">
                        <span class="stage-icon">🌿</span>
                        <h3 id="stage-ingredients-heading" class="card-title"><?= htmlspecialchars($t['extracted_ingredients_heading'], ENT_QUOTES, 'UTF-8') ?></h3>
                    </div>
                    <span class="stage-tag"><?= htmlspecialchars($t['stage1_status_done'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <p class="stage-card-subtitle"><?= htmlspecialchars($t['extracted_ingredients_subheading'], ENT_QUOTES, 'UTF-8') ?></p>

                <!-- Extracted Ingredient Chips Container -->
                <div id="extracted-ingredients-list" class="extracted-chips-container"></div>

                <!-- Source text foldout -->
                <div id="source-text-container" class="source-text-container hidden">
                    <span class="source-text-label"><?= htmlspecialchars($t['original_text_label'], ENT_QUOTES, 'UTF-8') ?></span>
                    <p id="source-text-content" class="source-text-content"></p>
                </div>

                <!-- Verification in Progress Banner (Visible while Agent 2 is executing) -->
                <div id="verifying-progress-banner" class="verifying-progress-banner hidden">
                    <span class="banner-spinner" aria-hidden="true"></span>
                    <span class="banner-text"><?= htmlspecialchars($t['verifying_in_progress_msg'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>

            <!-- STAGE 2 CARD: Final Dietary & Halal Assessment Report -->
            <div id="results-card" class="card results-card" aria-labelledby="results-heading">
                <div class="card-header">
                    <h3 id="results-heading" class="card-title"><?= htmlspecialchars($t['results_title'], ENT_QUOTES, 'UTF-8') ?></h3>
                </div>

                <!-- Idle State Placeholder -->
                <div id="results-idle" class="results-idle">
                    <div class="idle-icon" aria-hidden="true">🔍</div>
                    <p class="idle-text"><?= htmlspecialchars($t['no_analysis_yet'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <!-- Active Results Container (Populated via Agent 2 Real Output) -->
                <div id="results-content" class="results-content hidden">
                    <!-- Verdict Banner -->
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

                    <!-- Extracted Ingredients Tags in Final Report -->
                    <div class="results-section" id="ingredients-container">
                        <h4 class="section-subtitle"><?= htmlspecialchars($t['results_ingredients_title'], ENT_QUOTES, 'UTF-8') ?></h4>
                        <div class="ingredients-tags" id="ingredients-tags"></div>
                    </div>

                    <!-- Overall Fiqh Reasons -->
                    <div class="results-section" id="reasons-container">
                        <h4 class="section-subtitle"><?= htmlspecialchars($t['results_reasons_title'], ENT_QUOTES, 'UTF-8') ?></h4>
                        <ul class="reasons-list" id="reasons-list"></ul>
                    </div>

                    <!-- Individual Ingredient Assessments -->
                    <div class="results-section" id="assessments-container">
                        <h4 class="section-subtitle"><?= htmlspecialchars($t['results_assessments_title'], ENT_QUOTES, 'UTF-8') ?></h4>
                        <div class="assessments-grid" id="assessments-list"></div>
                    </div>

                    <!-- Notes & Recommendations -->
                    <div class="results-section hidden" id="notes-container">
                        <h4 class="section-subtitle"><?= htmlspecialchars($t['results_notes_title'], ENT_QUOTES, 'UTF-8') ?></h4>
                        <ul class="notes-list" id="notes-list"></ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Platform Features Section -->
        <section class="section-container" id="features" aria-labelledby="features-heading">
            <div class="section-header text-center">
                <span class="section-badge"><?= htmlspecialchars($t['nav_features'], ENT_QUOTES, 'UTF-8') ?></span>
                <h2 id="features-heading" class="section-title"><?= htmlspecialchars($t['features_title'], ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="section-subtitle"><?= htmlspecialchars($t['features_subtitle'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>

            <div class="features-grid">
                <div class="card feature-card">
                    <div class="feature-icon-box">👁️</div>
                    <h3 class="feature-title"><?= htmlspecialchars($t['feat1_title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="feature-desc"><?= htmlspecialchars($t['feat1_desc'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <div class="card feature-card">
                    <div class="feature-icon-box">⚡</div>
                    <h3 class="feature-title"><?= htmlspecialchars($t['feat2_title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="feature-desc"><?= htmlspecialchars($t['feat2_desc'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <div class="card feature-card">
                    <div class="feature-icon-box">📖</div>
                    <h3 class="feature-title"><?= htmlspecialchars($t['feat3_title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="feature-desc"><?= htmlspecialchars($t['feat3_desc'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <div class="card feature-card">
                    <div class="feature-icon-box">🤖</div>
                    <h3 class="feature-title"><?= htmlspecialchars($t['feat4_title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="feature-desc"><?= htmlspecialchars($t['feat4_desc'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <div class="card feature-card">
                    <div class="feature-icon-box">🌐</div>
                    <h3 class="feature-title"><?= htmlspecialchars($t['feat5_title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="feature-desc"><?= htmlspecialchars($t['feat5_desc'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <div class="card feature-card">
                    <div class="feature-icon-box">🛡️</div>
                    <h3 class="feature-title"><?= htmlspecialchars($t['feat6_title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="feature-desc"><?= htmlspecialchars($t['feat6_desc'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
        </section>

        <!-- Canonical Status Guide Section -->
        <section class="section-container" id="statuses-guide" aria-labelledby="guide-heading">
            <div class="section-header text-center">
                <span class="section-badge"><?= htmlspecialchars($t['nav_statuses'], ENT_QUOTES, 'UTF-8') ?></span>
                <h2 id="guide-heading" class="section-title"><?= htmlspecialchars($t['guide_title'], ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="section-subtitle"><?= htmlspecialchars($t['guide_subtitle'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>

            <div class="guide-grid">
                <div class="card guide-card guide-card-halal">
                    <div class="guide-card-header">
                        <span class="guide-badge status-HALAL">HALAL</span>
                        <h3 class="guide-card-title"><?= htmlspecialchars($t['status_halal'], ENT_QUOTES, 'UTF-8') ?></h3>
                    </div>
                    <p class="guide-card-desc"><?= htmlspecialchars($t['guide_halal_desc'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <div class="card guide-card guide-card-haram">
                    <div class="guide-card-header">
                        <span class="guide-badge status-HARAM">HARAM</span>
                        <h3 class="guide-card-title"><?= htmlspecialchars($t['status_haram'], ENT_QUOTES, 'UTF-8') ?></h3>
                    </div>
                    <p class="guide-card-desc"><?= htmlspecialchars($t['guide_haram_desc'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <div class="card guide-card guide-card-doubtful">
                    <div class="guide-card-header">
                        <span class="guide-badge status-DOUBTFUL">DOUBTFUL</span>
                        <h3 class="guide-card-title"><?= htmlspecialchars($t['status_doubtful'], ENT_QUOTES, 'UTF-8') ?></h3>
                    </div>
                    <p class="guide-card-desc"><?= htmlspecialchars($t['guide_doubtful_desc'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <div class="card guide-card guide-card-unknown">
                    <div class="guide-card-header">
                        <span class="guide-badge status-UNKNOWN">UNKNOWN</span>
                        <h3 class="guide-card-title"><?= htmlspecialchars($t['status_unknown'], ENT_QUOTES, 'UTF-8') ?></h3>
                    </div>
                    <p class="guide-card-desc"><?= htmlspecialchars($t['guide_unknown_desc'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
        </section>

        <!-- Future Planned Feature: Nearby Halal Restaurants (Coming Soon) -->
        <section class="card restaurant-coming-soon-card" id="restaurant-section" aria-labelledby="restaurant-heading">
            <div class="coming-soon-badge-wrapper">
                <span class="coming-soon-badge">
                    <span class="coming-soon-dot" aria-hidden="true"></span>
                    <?= htmlspecialchars($t['restaurant_badge_coming_soon'], ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>
            <div class="coming-soon-icon" aria-hidden="true">
                <div class="icon-circle">
                    <img src="assets/icons/icon-restaurant.png" alt="" class="coming-soon-icon-img" width="58" height="58" loading="lazy">
                </div>
            </div>
            <h3 id="restaurant-heading" class="coming-soon-title"><?= htmlspecialchars($t['restaurant_title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="coming-soon-desc"><?= htmlspecialchars($t['restaurant_coming_soon_desc'], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="coming-soon-subdesc"><?= htmlspecialchars($t['restaurant_coming_soon_subdesc'], ENT_QUOTES, 'UTF-8') ?></p>
        </section>

        <!-- Trust & Advisory Disclaimer Section -->
        <section class="trust-section" id="about" aria-labelledby="trust-heading">
            <div class="trust-card">
                <span class="trust-icon" aria-hidden="true">⚖️</span>
                <div class="trust-body">
                    <h3 id="trust-heading" class="trust-title"><?= htmlspecialchars($t['trust_title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="trust-text"><?= htmlspecialchars($t['trust_text'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="app-footer">
        <div class="container footer-inner">
            <div class="footer-brand">
                <img src="assets/halallens-logo.png" alt="<?= htmlspecialchars($t['logo_alt'], ENT_QUOTES, 'UTF-8') ?>" class="footer-logo" width="36" height="36">
                <div class="footer-brand-info">
                    <span class="footer-title"><?= htmlspecialchars($t['app_brand'], ENT_QUOTES, 'UTF-8') ?></span>
                    <p class="footer-desc"><?= htmlspecialchars($t['footer_brand_desc'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
            <nav class="footer-links" aria-label="Footer Navigation">
                <a href="#hero"><?= htmlspecialchars($t['nav_home'], ENT_QUOTES, 'UTF-8') ?></a>
                <a href="#analyze-section"><?= htmlspecialchars($t['nav_analyze'], ENT_QUOTES, 'UTF-8') ?></a>
                <a href="#how-it-works"><?= htmlspecialchars($t['nav_how_it_works'], ENT_QUOTES, 'UTF-8') ?></a>
                <a href="#multi-agent"><?= htmlspecialchars($t['nav_multi_agent'], ENT_QUOTES, 'UTF-8') ?></a>
                <a href="#features"><?= htmlspecialchars($t['nav_features'], ENT_QUOTES, 'UTF-8') ?></a>
                <a href="#restaurant-section"><?= htmlspecialchars($t['nav_restaurants'], ENT_QUOTES, 'UTF-8') ?></a>
            </nav>
            <p class="footer-university"><?= htmlspecialchars($t['footer_text'], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="footer-disclaimer"><?= htmlspecialchars($t['footer_disclaimer'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </footer>

    <!-- Pass localization tokens safely to frontend JS -->
    <script>
        window.HALALLENS_CONFIG = {
            currentLang: "<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') ?>",
            direction: "<?= htmlspecialchars($direction, ENT_QUOTES, 'UTF-8') ?>",
            i18n: {
                waiting: "<?= htmlspecialchars($t['stage1_status_waiting'], ENT_QUOTES, 'UTF-8') ?>",
                processing: "<?= htmlspecialchars($t['stage1_status_active'], ENT_QUOTES, 'UTF-8') ?>",
                completed: "<?= htmlspecialchars($t['stage2_status_done'], ENT_QUOTES, 'UTF-8') ?>",
                failed: "<?= htmlspecialchars($t['status_failed'], ENT_QUOTES, 'UTF-8') ?>",
                stage1_status_active: "<?= htmlspecialchars($t['stage1_status_active'], ENT_QUOTES, 'UTF-8') ?>",
                stage1_status_done: "<?= htmlspecialchars($t['stage1_status_done'], ENT_QUOTES, 'UTF-8') ?>",
                stage2_status_waiting: "<?= htmlspecialchars($t['stage2_status_waiting'], ENT_QUOTES, 'UTF-8') ?>",
                stage2_status_active: "<?= htmlspecialchars($t['stage2_status_active'], ENT_QUOTES, 'UTF-8') ?>",
                stage2_status_done: "<?= htmlspecialchars($t['stage2_status_done'], ENT_QUOTES, 'UTF-8') ?>",
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
