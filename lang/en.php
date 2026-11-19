<?php
/**
 * HalalLens AI - English Localization (Secondary Language)
 * Direction: LTR
 */

declare(strict_types=1);

return [
    'lang_code' => 'en',
    'direction' => 'ltr',
    'site_title' => 'HalalLens AI | Intelligent Food Ingredient Verification',
    'app_name' => 'HalalLens AI',
    'app_tagline' => 'Collaborative Multi-Agent AI Platform for Food Ingredient Verification',
    'university_badge' => 'AI Engineering Project - Multi-Agent Architecture',

    // Navigation & Actions
    'nav_home' => 'Home',
    'nav_analyze' => 'Product Analysis',
    'nav_restaurants' => 'Halal Restaurants',
    'switch_lang_label' => 'العربية',
    'switch_lang_target' => 'ar',

    // Hero Section
    'hero_title' => 'Verify Food Halal Compliance with Agentic AI Precision',
    'hero_desc' => 'Upload a food product ingredient label. The Vision Agent extracts ingredients into structured data, and the Halal Verification Agent analyzes dietary compliance with Islamic rulings.',

    // Upload Section
    'upload_title' => 'Upload Ingredient Label Image',
    'upload_drag_drop' => 'Drag & drop ingredient label image here, or',
    'upload_browse' => 'Browse Files',
    'upload_hint' => 'Supported formats: JPG, PNG, WebP (Max size: 5 MB)',
    'btn_analyze' => 'Start Intelligent Analysis',
    'btn_clear' => 'Clear & Reset',
    'selected_file' => 'Selected file:',

    // Multi-Agent Pipeline
    'pipeline_title' => 'Multi-Agent Processing Pipeline',
    'agent_vision_title' => 'Ingredient Vision Agent',
    'agent_vision_desc' => 'Extracts raw text and converts it into structured ingredient data',
    'agent_halal_title' => 'Halal Verification Agent',
    'agent_halal_desc' => 'Cross-references ingredients & E-numbers against Islamic dietary standards',
    'status_waiting' => 'Waiting',
    'status_processing' => 'Processing...',
    'status_completed' => 'Completed',
    'status_failed' => 'Failed',

    // Halal Statuses (Canonical)
    'status_halal' => 'Halal',
    'status_haram' => 'Haram',
    'status_doubtful' => 'Doubtful',
    'status_unknown' => 'Unknown',

    // Results Section
    'results_title' => 'Dietary & Halal Assessment Report',
    'results_badge_label' => 'Final Status:',
    'results_confidence' => 'Confidence Level:',
    'results_ingredients_title' => 'Extracted Ingredients',
    'results_flagged_title' => 'Flagged Ingredients & E-Numbers',
    'results_reasons_title' => 'Overall Assessment Reasons',
    'results_assessments_title' => 'Individual Ingredient Assessments',
    'results_notes_title' => 'Consumer Notes & Recommendations',
    'no_analysis_yet' => 'No analysis performed yet. Select an image and click "Start Intelligent Analysis".',

    // Restaurant Finder Section (Future Planned Feature - Coming Soon)
    'restaurant_title' => 'Nearby Halal Restaurants',
    'restaurant_badge_coming_soon' => 'Coming Soon',
    'restaurant_coming_soon_desc' => "We're currently working on this feature.",
    'restaurant_coming_soon_subdesc' => 'It will be released in a future update to help you discover nearby halal restaurants.',

    // Alerts & Errors
    'error_no_file' => 'Please select an ingredient label image first.',
    'error_file_size' => 'File size exceeds maximum permitted limit (5 MB).',
    'error_file_type' => 'Unsupported file format. Please upload JPG, PNG, or WebP.',
    'error_api_key_missing' => 'Gemini API key is not configured on the server (GEMINI_API_KEY).',
    'error_generic' => 'An error occurred during communication with the server. Please try again.',
    'location_permission_denied' => 'Location permission denied. Please enable GPS access or search by city name.',

    // Footer
    'footer_text' => 'HalalLens AI - University AI Engineering Research Project.',
    'footer_disclaimer' => 'Disclaimer: This platform provides automated advisory analysis based on AI models and dietary databases. It does not replace official Halal certification bodies.',
];
