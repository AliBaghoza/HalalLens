<?php
/**
 * HalalLens AI - Arabic Localization (Default Language)
 * Direction: RTL
 */

declare(strict_types=1);

return [
    'lang_code' => 'ar',
    'direction' => 'rtl',
    'site_title' => 'عدسة الحلال الذكية | HalalLens AI',
    'app_name' => 'عدسة الحلال الذكية',
    'app_tagline' => 'منصة ذكية متعددة الوكلاء للتحقق من حِلّية المكونات الغذائية',
    'university_badge' => 'مشروع هندسة الذكاء الاصطناعي - بنية متعددة الوكلاء',

    // Navigation & Actions
    'nav_home' => 'الرئيسية',
    'nav_analyze' => 'فحص المنتج',
    'nav_restaurants' => 'مطاعم حلال',
    'switch_lang_label' => 'English',
    'switch_lang_target' => 'en',

    // Hero Section
    'hero_title' => 'تحقق من حِلّية طعامك بدقة الذكاء الاصطناعي',
    'hero_desc' => 'ارفع صورة ملصق المكونات لأي منتج غذائي، وسيقوم وكيل الرؤية باستخراج قائمة المكونات ثم يحللها وكيل التحقق الشرعي لتحديد حالة الحِلّية بدقة وموثوقية.',

    // Upload Section
    'upload_title' => 'رفع صورة ملصق المكونات',
    'upload_drag_drop' => 'اسحب صورة الملصق وأفلتها هنا، أو',
    'upload_browse' => 'استعرض الملفات',
    'upload_hint' => 'الملفات المدعومة: JPG, PNG, WebP (الحد الأقصى: 5 ميجابايت)',
    'btn_analyze' => 'بدء التحليل الذكي',
    'btn_clear' => 'إلغاء وتفريغ',
    'selected_file' => 'الملف المختار:',

    // Multi-Agent Pipeline
    'pipeline_title' => 'مراحل خط معالجة الوكلاء الذكية',
    'agent_vision_title' => 'وكيل الرؤية للمكونات',
    'agent_vision_desc' => 'استخراج نصوص المكونات وتحويلها إلى بيانات هيكلية',
    'agent_halal_title' => 'وكيل التحقق من الحلال',
    'agent_halal_desc' => 'مطابقة المكونات والأرقام القياسية (E-Numbers) مع المعايير الفقهية',
    'status_waiting' => 'في الانتظار',
    'status_processing' => 'جارِ المعالجة...',
    'status_completed' => 'اكتملت المعالجة',
    'status_failed' => 'تعذر الإكمال',

    // Halal Statuses (Canonical)
    'status_halal' => 'حلال',
    'status_haram' => 'حرام',
    'status_doubtful' => 'مشتبه فيه',
    'status_unknown' => 'غير معروف',

    // Results Section
    'results_title' => 'تقرير التحليل الشرعي والغذائي',
    'results_badge_label' => 'التقييم النهائي:',
    'results_confidence' => 'درجة الموثوقية:',
    'results_ingredients_title' => 'المكونات المكتشفة',
    'results_flagged_title' => 'المكونات المنبه عليها والأرقام القياسية (E-Numbers)',
    'results_reasons_title' => 'حيثيات وأسباب التقييم العام',
    'results_assessments_title' => 'التقييم الشرعي لكل مكون',
    'results_notes_title' => 'تنبيهات وتوصيات للمستهلك',
    'no_analysis_yet' => 'لم يتم إجراء فحص حتى الآن. يرجى اختيار صورة والضغط على "بدء التحليل الذكي".',

    // Restaurant Finder Section (Decoupled Service)
    'restaurant_title' => 'البحث عن مطاعم حلال قريبة',
    'restaurant_subtitle' => 'استكشف المطاعم المعتمدة القريبة عبر بيانات OpenStreetMap',
    'restaurant_search_placeholder' => 'أدخل اسم المدينة أو المنطقة...',
    'btn_search_location' => 'بحث',
    'btn_current_location' => 'استخدام موقعي الحالي',
    'restaurant_no_results' => 'لم يتم العثور على مطاعم حلال في هذا النطاق.',
    'restaurant_cuisine' => 'نوع المطبخ:',
    'restaurant_distance' => 'المسافة التقريبية:',
    'restaurant_address' => 'العنوان:',

    // Alerts & Errors
    'error_no_file' => 'يرجى اختيار صورة ملصق المنتج أولاً.',
    'error_file_size' => 'حجم الملف يتجاوز الحد المسموح به (5 ميجابايت).',
    'error_file_type' => 'نوع الملف غير مدعوم. يرجى رفع ملف JPG أو PNG أو WebP.',
    'error_api_key_missing' => 'مفتاح API الخاص بـ Gemini غير مهيأ على الخادم (GEMINI_API_KEY).',
    'error_generic' => 'حدث خطأ أثناء الاتصال بالخادم. يرجى المحاولة مرة أخرى.',
    'location_permission_denied' => 'تعذر الوصول إلى الموقع الجغرافي. يرجى السماح بالإذن أو البحث بالاسم.',

    // Footer
    'footer_text' => 'HalalLens AI - مشروع بحثي في هندسة الذكاء الاصطناعي.',
    'footer_disclaimer' => 'تنبيه: هذا النظام يقدم تحليلاً استرشادياً مبنياً على النماذج الذكية والمعايير الغذائية المعتمدة، ولا يُغني عن شهادات جهات الفتوى والرقابة الرسمية.',
];
