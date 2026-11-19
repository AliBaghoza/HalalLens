<?php
/**
 * HalalLens AI - English Localization (Secondary Language)
 * Direction: LTR
 */

declare(strict_types=1);

return [
    'lang_code' => 'en',
    'direction' => 'ltr',
    'site_title' => 'HalalLens AI | Smart Halal Food Verification',
    'app_name' => 'HalalLens AI',
    'app_brand' => 'HalalLens AI',
    'app_tagline' => 'Know your food ingredients with confidence',
    'hero_tagline' => 'Know your food ingredients with confidence',
    'logo_alt' => 'HalalLens AI logo',
    'university_badge' => 'AI Engineering Project - Multi-Agent Architecture',

    // Navigation
    'nav_home' => 'Home',
    'nav_analyze' => 'Product Analysis',
    'nav_how_it_works' => 'How It Works',
    'nav_multi_agent' => 'Multi-Agent',
    'nav_features' => 'Features',
    'nav_statuses' => 'Status Guide',
    'nav_about' => 'About',
    'nav_restaurants' => 'Restaurants — Soon',
    'switch_lang_label' => 'العربية',
    'switch_lang_target' => 'ar',

    // Hero Section
    'hero_title' => 'Verify Food Halal Compliance with Agentic AI Precision',
    'hero_desc' => 'Upload a food product ingredient label. The Vision Agent extracts ingredients into structured data, and the Halal Verification Agent analyzes dietary compliance with Islamic rulings.',
    'hero_cta_analyze' => 'Start Analysis',
    'hero_cta_learn' => 'Discover Platform',

    // 4 Quick Feature Cards (From Brand Identity)
    'quick_capture_title' => 'Capture',
    'quick_capture_desc' => 'Snap label image',
    'quick_analysis_title' => 'Analyze',
    'quick_analysis_desc' => 'Vision AI extraction',
    'quick_verify_title' => 'Verify',
    'quick_verify_desc' => 'Dietary jurisprudence',
    'quick_search_title' => 'Discover',
    'quick_search_desc' => 'Halal restaurants',
    'quick_search_badge' => 'Soon',

    // How It Works (4 Steps)
    'how_title' => 'How It Works',
    'how_subtitle' => 'A transparent, four-step verification journey powered by collaborative AI',
    'how_step1_title' => 'Capture',
    'how_step1_desc' => 'Take a clear photo of the ingredient label on any food packaging and upload it.',
    'how_step2_title' => 'Extract',
    'how_step2_desc' => 'The Ingredient Vision Agent reads the label and extracts ingredients into structured data immediately.',
    'how_step3_title' => 'Verify',
    'how_step3_desc' => 'The Halal Verification Agent cross-references ingredients and E-numbers against Islamic dietary standards.',
    'how_step4_title' => 'Results',
    'how_step4_desc' => 'Receive an instant, transparent assessment report with detailed jurisprudential reasoning and consumer notes.',

    // Multi-Agent Architecture
    'arch_title' => 'Multi-Agent AI Architecture',
    'arch_subtitle' => 'Decoupled specialized agents ensuring scientific accuracy, integrity, and transparency',
    'agent1_name' => 'Ingredient Vision Agent',
    'agent1_badge' => 'Agent 1: Ingredient Vision',
    'agent1_desc' => 'Processes label imagery using multimodal Gemini Vision to extract ingredient names and E-numbers without making dietary rulings.',
    'agent1_f1' => 'High-precision multimodal optical character recognition',
    'agent1_f2' => 'Preserves exact packaging phrasing and terminology',
    'agent1_f3' => 'Assigns extraction confidence ratings',
    'agent1_f4' => 'Outputs validated structured JSON schema',
    'agent2_name' => 'Halal Verification Agent',
    'agent2_badge' => 'Agent 2: Halal Verification',
    'agent2_desc' => 'Directly ingests structured ingredient data from Agent 1 within server memory, evaluating compliance against Islamic food jurisprudence.',
    'agent2_f1' => 'Scrutinizes animal, plant, and microbial origins',
    'agent2_f2' => 'Identifies doubtful and prohibited food additives',
    'agent2_f3' => 'Strict canonical taxonomy: HALAL, HARAM, DOUBTFUL',
    'agent2_f4' => 'Formulates bilingual reasoning and consumer advice',
    'arch_flow_note' => 'Agent 1 to Agent 2 collaboration occurs entirely inside secure server memory (Server-Side Memory Handoff). No intermediary browser tampering is possible.',

    // Upload Section
    'upload_title' => 'Upload Ingredient Label Image',
    'upload_drag_drop' => 'Drag & drop ingredient label image here, or',
    'upload_browse' => 'Browse Files',
    'upload_hint' => 'Supported formats: JPG, PNG, WebP (Max size: 5 MB)',
    'btn_analyze' => 'Start Intelligent Analysis',
    'btn_clear' => 'Clear & Reset',
    'selected_file' => 'Selected file:',

    // Progressive Pipeline Statuses
    'pipeline_title' => 'Live Multi-Agent Pipeline',
    'stage1_title' => 'Stage 1: Ingredient Vision',
    'stage1_desc' => 'Vision Agent extracts text into structured data',
    'stage1_status_waiting' => 'Waiting',
    'stage1_status_active' => 'Reading ingredients...',
    'stage1_status_done' => 'Ingredients Extracted',
    'stage2_title' => 'Stage 2: Halal Verification',
    'stage2_desc' => 'Halal Agent evaluates dietary compliance',
    'stage2_status_waiting' => 'Waiting',
    'stage2_status_active' => 'Verifying ingredients...',
    'stage2_status_done' => 'Verification Completed',
    'status_failed' => 'Failed',

    // Extracted Ingredients Card (Shown immediately after Agent 1)
    'extracted_ingredients_heading' => 'Extracted Ingredients',
    'extracted_ingredients_subheading' => 'These ingredients were extracted from the label by the Vision Agent:',
    'verifying_in_progress_msg' => 'Evaluating ingredients against Islamic dietary jurisprudence...',
    'confidence_label' => 'Confidence:',
    'original_text_label' => 'Packaging text:',

    // Halal Statuses (Canonical)
    'status_halal' => 'Halal',
    'status_haram' => 'Haram',
    'status_doubtful' => 'Doubtful',
    'status_unknown' => 'Unknown',

    // Final Assessment Report
    'results_title' => 'Dietary & Halal Assessment Report',
    'results_badge_label' => 'Final Status:',
    'results_confidence' => 'Overall Confidence Score:',
    'results_ingredients_title' => 'Extracted Ingredients',
    'results_reasons_title' => 'Overall Assessment Reasons',
    'results_assessments_title' => 'Individual Ingredient Assessments',
    'results_notes_title' => 'Consumer Notes & Recommendations',
    'no_analysis_yet' => 'No analysis performed yet. Select an image and click "Start Intelligent Analysis".',

    // Platform Features
    'features_title' => 'Platform Features',
    'features_subtitle' => 'Advanced AI engineering designed to empower informed dietary decisions',
    'feat1_title' => 'Multimodal Vision AI',
    'feat1_desc' => 'High-accuracy OCR designed specifically for complex curved packaging and multiline food labels.',
    'feat2_title' => 'Instant Ingredient Preview',
    'feat2_desc' => 'Extracted ingredients appear immediately in stage 1 before dietary verification concludes.',
    'feat3_title' => 'Fiqh & Science Standards',
    'feat3_desc' => 'Cross-references Islamic dietary rulings (Fiqh al-At\'imah) with international food additive data.',
    'feat4_title' => 'Decoupled Multi-Agent AI',
    'feat4_desc' => 'Separates visual extraction from jurisprudential ruling to eliminate bias and ensure transparency.',
    'feat5_title' => 'Fully Bilingual (RTL/LTR)',
    'feat5_desc' => 'Native Arabic RTL interface paired with an equally comprehensive English LTR mode.',
    'feat6_title' => 'Zero-Trust Data Security',
    'feat6_desc' => 'Server-side API key protection with encrypted transmission and isolated processing.',

    // Status Guide Section
    'guide_title' => 'Canonical Status Guide',
    'guide_subtitle' => 'Understanding the standardized classifications in our reports',
    'guide_halal_title' => 'HALAL (حلال)',
    'guide_halal_desc' => 'Permissible. Composed purely of plant, marine, synthetic, microbial, or certified Halal slaughtered animals.',
    'guide_haram_title' => 'HARAM (حرام)',
    'guide_haram_desc' => 'Strictly prohibited. Contains porcine ingredients, lard, blood, intoxicants, or unslaughtered land animal derivatives.',
    'guide_doubtful_title' => 'DOUBTFUL (مشتبه فيه)',
    'guide_doubtful_desc' => 'Ambiguous origin. Additives like mono- and diglycerides (E471) or gelatin without specified plant/halal animal source.',
    'guide_unknown_title' => 'UNKNOWN (غير معروف)',
    'guide_unknown_desc' => 'Unidentifiable or illegible packaging data requiring higher-resolution imaging.',

    // Restaurant Finder Section (Future Feature - Coming Soon)
    'restaurant_title' => 'Nearby Halal Restaurants',
    'restaurant_badge_coming_soon' => 'Coming Soon',
    'restaurant_coming_soon_desc' => "We're currently working on this feature.",
    'restaurant_coming_soon_subdesc' => 'It will be released in a future update to help you discover verified halal dining locations nearby.',

    // Trust & Disclaimer
    'trust_title' => 'Trust & Advisory Disclaimer',
    'trust_text' => 'HalalLens AI provides automated analytical advice on food product ingredients and is not a substitute for official Halal certification or manufacturer verification when required.',

    // Alerts & Errors
    'error_no_file' => 'Please select an ingredient label image first.',
    'error_file_size' => 'File size exceeds maximum permitted limit (5 MB).',
    'error_file_type' => 'Unsupported file format. Please upload JPG, PNG, or WebP.',
    'error_api_key_missing' => 'Gemini API key is not configured on the server (GEMINI_API_KEY).',
    'error_generic' => 'An error occurred during communication with the server. Please try again.',

    // Footer
    'footer_brand_desc' => 'Smart Multi-Agent AI platform for verifying food product ingredients with precision.',
    'footer_text' => 'HalalLens AI — AI Engineering Research Project.',
    'footer_disclaimer' => 'Disclaimer: This platform provides automated advisory analysis based on AI models and dietary databases. It does not replace official Halal certification bodies.',
];
