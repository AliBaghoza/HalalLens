<?php
/**
 * HalalLens AI - Arabic Localization (Default Language)
 * Direction: RTL
 */

declare(strict_types=1);

return [
    'lang_code' => 'ar',
    'direction' => 'rtl',
    'site_title' => 'هلالنس الذكي لفحص الحلال | HalalLens AI',
    'app_name' => 'هلالنس الذكي لفحص الحلال',
    'app_brand' => 'HalalLens AI',
    'app_tagline' => 'اعرف مكونات طعامك بثقة',
    'hero_tagline' => 'اعرف مكونات طعامك بثقة',
    'logo_alt' => 'شعار HalalLens AI',
    'university_badge' => 'مشروع هندسة الذكاء الاصطناعي - بنية متعددة الوكلاء',

    // Navigation
    'nav_home' => 'الرئيسية',
    'nav_analyze' => 'فحص المنتج',
    'nav_how_it_works' => 'كيف يعمل',
    'nav_multi_agent' => 'بنية الوكلاء',
    'nav_features' => 'المميزات',
    'nav_statuses' => 'دليل التقييم',
    'nav_about' => 'عن المنصة',
    'nav_restaurants' => 'المطاعم — قريبًا',
    'switch_lang_label' => 'English',
    'switch_lang_target' => 'en',

    // Hero Section
    'hero_title' => 'تحقق من حِلّية طعامك بدقة الذكاء الاصطناعي',
    'hero_desc' => 'ارفع صورة ملصق المكونات لأي منتج غذائي، وسيقوم وكيل الرؤية باستخراج قائمة المكونات ثم يحللها وكيل التحقق الشرعي لتحديد حالة الحِلّية بدقة وموثوقية.',
    'hero_cta_analyze' => 'ابدأ التحليل',
    'hero_cta_learn' => 'تعرف على المنصة',

    // 4 Quick Feature Cards (From Brand Identity)
    'quick_capture_title' => 'تصوير',
    'quick_capture_desc' => 'التقط صورة الملصق',
    'quick_analysis_title' => 'تحليل',
    'quick_analysis_desc' => 'بذكاء الذكاء الاصطناعي',
    'quick_verify_title' => 'تحقق',
    'quick_verify_desc' => 'تدقيق شرعياً بدقة',
    'quick_search_title' => 'ابحث',
    'quick_search_desc' => 'ابحث عن مطاعم حلال',
    'quick_search_badge' => 'قريبًا',

    // How It Works (4 Steps)
    'how_title' => 'كيف تعمل المنصة؟',
    'how_subtitle' => 'رحلة فحص المنتج بدقة وموثوقية في أربع خطوات سهلة',
    'how_step1_title' => 'صوّر',
    'how_step1_desc' => 'التقط صورة واضحة لملصق المكونات لأي منتج غذائي وقم برفعها إلى المنصة.',
    'how_step2_title' => 'استخرج',
    'how_step2_desc' => 'يقوم وكيل الرؤية (Ingredient Vision Agent) بقراءة النص واستخراج المكونات في بيانات منظمة فوراً.',
    'how_step3_title' => 'تحقّق',
    'how_step3_desc' => 'يقوم وكيل التحقق الشرعي (Halal Verification Agent) بمطابقة المكونات والأرقام القياسية مع المعايير الفقهية.',
    'how_step4_title' => 'النتيجة',
    'how_step4_desc' => 'تحصل على تقييم شامل لحالة المنتج مصحوباً بالحيثيات الشرعية والملاحظات الغذائية.',

    // Multi-Agent Architecture
    'arch_title' => 'بنية الوكلاء الذكية (Multi-Agent Architecture)',
    'arch_subtitle' => 'فصل المهام إلى وكيلين مستقلين لضمان الشفافية والموثوقية العلمية',
    'agent1_name' => 'وكيل الرؤية للمكونات',
    'agent1_badge' => 'Agent 1: Ingredient Vision',
    'agent1_desc' => 'يعالج صورة الملصق بصرياً عبر نموذج Gemini Vision المتطور، ويستخرج أسماء المكونات والأرقام القياسية (E-Numbers) دون إصدار أحكام فقهية.',
    'agent1_f1' => 'استخراج بصري دقيق للمكونات والرموز',
    'agent1_f2' => 'الاحتفاظ بمفردات العبوة الأصلية',
    'agent1_f3' => 'تحديد نسبة الثقة ومستوى الجودة',
    'agent1_f4' => 'تحويل المخرجات إلى هيكل JSON نقي',
    'agent2_name' => 'وكيل التحقق الشرعي',
    'agent2_badge' => 'Agent 2: Halal Verification',
    'agent2_desc' => 'يستلم بيانات المكونات المنظمة من الوكيل الأول مباشرة عبر الخادم، ويطابقها مع فقه الأطعمة وقواعد المضافات الغذائية لإصدار التقييم النهائي.',
    'agent2_f1' => 'فحص المصادر الحيوانية والنباتية والميكروبية',
    'agent2_f2' => 'كشف المضافات المشتبه فيها والمحرمة',
    'agent2_f3' => 'تصنيف معياري صارم: حلال، حرام، مشتبه فيه',
    'agent2_f4' => 'صياغة حيثيات فقهية وتوصيات للمستهلك',
    'arch_flow_note' => 'التنسيق بين الوكيلين يتم بالكامل داخل بيئة الخادم الآمنة (Server-Side Memory Handoff) دون أي تدخل من المتصفح.',

    // Upload Section
    'upload_title' => 'رفع صورة ملصق المكونات',
    'upload_drag_drop' => 'اسحب صورة الملصق وأفلتها هنا، أو',
    'upload_browse' => 'استعرض الملفات',
    'upload_hint' => 'الملفات المدعومة: JPG, PNG, WebP (الحد الأقصى: 5 ميجابايت)',
    'btn_analyze' => 'بدء التحليل الذكي',
    'btn_clear' => 'إلغاء وتفريغ',
    'selected_file' => 'الملف المختار:',

    // Progressive Pipeline Statuses
    'pipeline_title' => 'مراحل خط المعالجة المباشر',
    'stage1_title' => 'المرحلة 1: قراءة المكونات',
    'stage1_desc' => 'وكيل الرؤية يستخرج المكونات ويحولها إلى بيانات منظمة',
    'stage1_status_waiting' => 'في الانتظار',
    'stage1_status_active' => 'جارٍ قراءة المكونات...',
    'stage1_status_done' => 'اكتملت قراءة المكونات',
    'stage2_title' => 'المرحلة 2: التحقق من حالة الحلال',
    'stage2_desc' => 'وكيل التحقق الشرعي يحلل المكونات ويصنفها فقهياً',
    'stage2_status_waiting' => 'في الانتظار',
    'stage2_status_active' => 'جارٍ التحقق الشرعي...',
    'stage2_status_done' => 'اكتمل التحقق الشرعي',
    'status_failed' => 'تعذر الإكمال',

    // Extracted Ingredients Card (Shown immediately after Agent 1)
    'extracted_ingredients_heading' => 'المكونات المستخرجة',
    'extracted_ingredients_subheading' => 'تم استخراج هذه المكونات بنجاح من ملصق المنتج بواسطة وكيل الرؤية:',
    'verifying_in_progress_msg' => 'جارٍ التحقق من المكونات شرعياً ومطابقتها مع المعايير الفقهية...',
    'confidence_label' => 'نسبة التأكد:',
    'original_text_label' => 'النص الأصلي من العبوة:',

    // Halal Statuses (Canonical)
    'status_halal' => 'حلال',
    'status_haram' => 'حرام',
    'status_doubtful' => 'مشتبه فيه',
    'status_unknown' => 'غير معروف',

    // Final Assessment Report
    'results_title' => 'تقرير التحليل الشرعي والغذائي',
    'results_badge_label' => 'التقييم النهائي:',
    'results_confidence' => 'درجة الموثوقية العامة:',
    'results_ingredients_title' => 'المكونات المكتشفة',
    'results_reasons_title' => 'حيثيات وأسباب التقييم العام',
    'results_assessments_title' => 'التقييم الشرعي لكل مكون على حدة',
    'results_notes_title' => 'تنبيهات وتوصيات للمستهلك',
    'no_analysis_yet' => 'لم يتم إجراء فحص حتى الآن. يرجى اختيار صورة والضغط على "بدء التحليل الذكي".',

    // Platform Features
    'features_title' => 'مميزات المنصة',
    'features_subtitle' => 'حلول ذكية متطورة تجعل فحص الأغذية سهلاً وموثوقاً',
    'feat1_title' => 'تحليل بصري دقيق',
    'feat1_desc' => 'قراءة ملصقات المنتجات بأعلى دقة ومعالجة مختلف الخطوط واللغات.',
    'feat2_title' => 'عرض فوري للمكونات',
    'feat2_desc' => 'رؤية المكونات المستخرجة فوراً في مرحلة أولى قبل اكتمال التحقق النهائي.',
    'feat3_title' => 'مطابقة فقهية دقيقة',
    'feat3_desc' => 'الاستناد إلى معايير فقه الأطعمة وقواعد الأرقام القياسية والمضافات الغذائية.',
    'feat4_title' => 'بنية وكلاء مستقلة',
    'feat4_desc' => 'فصل وكيل الرؤية عن وكيل الحكم الشرعي لمنع الانحياز وضمان الشفافية.',
    'feat5_title' => 'ثنائية اللغة الكاملة',
    'feat5_desc' => 'واجهة عربية أصيلة بنظام RTL مع دعم كامل وسلس للغة الإنجليزية LTR.',
    'feat6_title' => 'خصوصية وأمان تام',
    'feat6_desc' => 'معالجة سرية للبيانات وتشفير كامل للاتصال دون مشاركة أي أسرار مع المتصفح.',

    // Status Guide Section
    'guide_title' => 'دليل حالات التقييم المعيارية',
    'guide_subtitle' => 'فهم واضح للتصنيفات الشرعية المعتمدة في تقارير المنصة',
    'guide_halal_title' => 'حلال (HALAL)',
    'guide_halal_desc' => 'منتج مباح شرعاً، مكوّن من عناصر نباتية، بحرية، أو حيوانية مذكاة ذكاة شرعية معتمدة.',
    'guide_haram_title' => 'حرام (HARAM)',
    'guide_haram_desc' => 'منتج محرم قطعي، يحتوي على مشتقات الخنزير، الكحول، الميتة، أو مواد ضارة بالصحة.',
    'guide_doubtful_title' => 'مشتبه فيه (DOUBTFUL)',
    'guide_doubtful_desc' => 'مكونات تحتمل المصدر الحيواني أو النباتي (مثل E471 والجيلاتين مجهول المصدر) لحين التحقق.',
    'guide_unknown_title' => 'غير معروف (UNKNOWN)',
    'guide_unknown_desc' => 'ملصق غير مقروء بوضوح أو مكونات غير قابلة للتحديد، ويتطلب إعادة التصوير بدقة أعلى.',

    // Restaurant Finder Section (Future Feature - Coming Soon)
    'restaurant_title' => 'المطاعم الحلال القريبة',
    'restaurant_badge_coming_soon' => 'قريبًا',
    'restaurant_coming_soon_desc' => 'نعمل حاليًا على تطوير ميزة اكتشاف المطاعم الحلال.',
    'restaurant_coming_soon_subdesc' => 'سيتم إطلاقها في تحديث مستقبلي لمساعدتك في العثور على المطاعم الحلال الموثوقة بالقرب من موقعك.',

    // Trust & Disclaimer
    'trust_title' => 'إخلاء المسؤولية والموثوقية',
    'trust_text' => 'HalalLens AI يقدم تحليلاً آلياً لمكونات المنتجات الغذائية، ولا يُعد بديلاً عن شهادة الحلال الرسمية أو التحقق من الجهة المصنعة عند الحاجة.',

    // Alerts & Errors
    'error_no_file' => 'يرجى اختيار صورة ملصق المنتج أولاً.',
    'error_file_size' => 'حجم الملف يتجاوز الحد المسموح به (5 ميجابايت).',
    'error_file_type' => 'نوع الملف غير مدعوم. يرجى رفع ملف JPG أو PNG أو WebP.',
    'error_api_key_missing' => 'مفتاح API الخاص بـ Gemini غير مهيأ على الخادم (GEMINI_API_KEY).',
    'error_generic' => 'حدث خطأ أثناء الاتصال بالخادم. يرجى المحاولة مرة أخرى.',

    // Footer
    'footer_brand_desc' => 'منصة ذكية متعددة الوكلاء للتحقق من حِلّية المكونات الغذائية بدقة واحترافية.',
    'footer_text' => 'هلالنس الذكي لفحص الحلال | HalalLens AI — مشروع بحثي في هندسة الذكاء الاصطناعي.',
    'footer_disclaimer' => 'تنبيه: هذا النظام يقدم تحليلاً استرشادياً مبنياً على النماذج الذكية والمعايير الغذائية المعتمدة، ولا يُغني عن شهادات جهات الفتوى والرقابة الرسمية.',
];
