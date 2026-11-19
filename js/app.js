/**
 * HalalLens AI - Vanilla JavaScript Application Layer
 *
 * Key Architectural Decisions:
 * 1. Arabic is the primary default language.
 * 2. The backend (api/analyze.php) is the multi-agent orchestrator.
 *    The frontend sends ONE analysis request and renders the pipeline state.
 * 3. The Restaurant Finder is decoupled and queries OSM Overpass via api/restaurants.php.
 */

'use strict';

(function () {
    const config = window.HALALLENS_CONFIG || {
        currentLang: 'ar',
        direction: 'rtl',
        i18n: {}
    };

    const i18n = config.i18n;

    // DOM Elements - Form & Dropzone
    const dropZone = document.getElementById('drop-zone');
    const imageInput = document.getElementById('image-input');
    const browseBtn = document.getElementById('browse-btn');
    const previewContainer = document.getElementById('preview-container');
    const previewImg = document.getElementById('preview-img');
    const previewFilename = document.getElementById('preview-filename');
    const previewFilesize = document.getElementById('preview-filesize');
    const removeBtn = document.getElementById('remove-btn');
    const clearBtn = document.getElementById('clear-btn');
    const submitBtn = document.getElementById('submit-btn');
    const submitBtnSpinner = submitBtn ? submitBtn.querySelector('.btn-spinner') : null;
    const submitBtnText = submitBtn ? submitBtn.querySelector('.btn-text') : null;
    const alertBanner = document.getElementById('alert-banner');
    const analyzeForm = document.getElementById('analyze-form');

    // Pipeline Steps
    const stepVision = document.getElementById('step-vision');
    const badgeVision = document.getElementById('badge-vision');
    const stepHalal = document.getElementById('step-halal');
    const badgeHalal = document.getElementById('badge-halal');

    // Stage 1 Extracted Ingredients Card (Shown immediately upon Agent 1 completion)
    const ingredientsStageCard = document.getElementById('ingredients-stage-card');
    const extractedIngredientsList = document.getElementById('extracted-ingredients-list');
    const verifyingProgressBanner = document.getElementById('verifying-progress-banner');
    const sourceTextContainer = document.getElementById('source-text-container');
    const sourceTextContent = document.getElementById('source-text-content');

    // Results Elements (Stage 2 Verdict)
    const resultsCard = document.getElementById('results-card');
    const resultsIdle = document.getElementById('results-idle');
    const resultsContent = document.getElementById('results-content');
    const verdictBadge = document.getElementById('verdict-badge');
    const confidenceVal = document.getElementById('confidence-val');
    const ingredientsTags = document.getElementById('ingredients-tags');
    const reasonsList = document.getElementById('reasons-list');
    const assessmentsList = document.getElementById('assessments-list');
    const notesContainer = document.getElementById('notes-container');
    const notesList = document.getElementById('notes-list');

    // Mobile Navigation Drawer
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const navMenu = document.getElementById('nav-menu');

    let currentSelectedFile = null;

    /* ==========================================================================
       0. Mobile Navigation Toggle
       ========================================================================== */

    if (mobileMenuBtn && navMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            const isExpanded = mobileMenuBtn.getAttribute('aria-expanded') === 'true';
            mobileMenuBtn.setAttribute('aria-expanded', String(!isExpanded));
            navMenu.classList.toggle('nav-open');
        });

        // Close menu on link click
        navMenu.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('nav-open');
                mobileMenuBtn.setAttribute('aria-expanded', 'false');
            });
        });
    }

    /* ==========================================================================
       1. File Handling & Drag-and-Drop
       ========================================================================== */

    if (browseBtn && imageInput) {
        browseBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            imageInput.click();
        });
    }

    if (dropZone && imageInput) {
        dropZone.addEventListener('click', () => {
            if (!currentSelectedFile) {
                imageInput.click();
            }
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.remove('dragover');
            });
        });

        dropZone.addEventListener('drop', (e) => {
            const files = e.dataTransfer?.files;
            if (files && files.length > 0) {
                handleFileSelection(files[0]);
            }
        });

        imageInput.addEventListener('change', () => {
            if (imageInput.files && imageInput.files.length > 0) {
                handleFileSelection(imageInput.files[0]);
            }
        });
    }

    if (removeBtn) {
        removeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            resetFileInput();
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            resetFileInput();
            resetPipeline();
            resetResults();
        });
    }

    function handleFileSelection(file) {
        hideAlert();

        // Validate type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            showAlert(i18n.errorFileType || 'Unsupported file type. Please upload JPG, PNG, or WebP.');
            resetFileInput();
            return;
        }

        // Validate size (5MB)
        const maxBytes = 5 * 1024 * 1024;
        if (file.size > maxBytes) {
            showAlert(i18n.errorFileSize || 'File size exceeds maximum permitted limit (5MB).');
            resetFileInput();
            return;
        }

        currentSelectedFile = file;

        // Render preview
        const reader = new FileReader();
        reader.onload = (e) => {
            if (previewImg) previewImg.src = e.target.result;
            if (previewFilename) previewFilename.textContent = file.name;
            if (previewFilesize) previewFilesize.textContent = formatBytes(file.size);
            if (previewContainer) previewContainer.classList.remove('hidden');
            if (clearBtn) clearBtn.classList.remove('hidden');
            if (submitBtn) submitBtn.disabled = false;
        };
        reader.readAsDataURL(file);
    }

    function resetFileInput() {
        currentSelectedFile = null;
        if (imageInput) imageInput.value = '';
        if (previewImg) previewImg.src = '';
        if (previewFilename) previewFilename.textContent = '';
        if (previewFilesize) previewFilesize.textContent = '';
        if (previewContainer) previewContainer.classList.add('hidden');
        if (clearBtn) clearBtn.classList.add('hidden');
        if (submitBtn) submitBtn.disabled = true;
    }

    function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    /* ==========================================================================
       2. Alert & Pipeline State Management
       ========================================================================== */

    function showAlert(message) {
        if (!alertBanner) return;
        alertBanner.textContent = message;
        alertBanner.classList.remove('hidden');
    }

    function hideAlert() {
        if (!alertBanner) return;
        alertBanner.textContent = '';
        alertBanner.classList.add('hidden');
    }

    function updateStep(stepEl, badgeEl, status, statusText) {
        if (!stepEl || !badgeEl) return;
        stepEl.setAttribute('data-status', status);
        badgeEl.textContent = statusText;
    }

    function resetPipeline() {
        updateStep(stepVision, badgeVision, 'idle', i18n.waiting || 'Waiting');
        updateStep(stepHalal, badgeHalal, 'idle', i18n.waiting || 'Waiting');
    }

    function resetResults() {
        if (ingredientsStageCard) ingredientsStageCard.classList.add('hidden');
        if (extractedIngredientsList) extractedIngredientsList.innerHTML = '';
        if (verifyingProgressBanner) verifyingProgressBanner.classList.add('hidden');
        if (sourceTextContainer) sourceTextContainer.classList.add('hidden');
        if (sourceTextContent) sourceTextContent.textContent = '';

        if (resultsIdle) resultsIdle.classList.remove('hidden');
        if (resultsContent) resultsContent.classList.add('hidden');
        if (verdictBadge) {
            verdictBadge.textContent = '-';
            verdictBadge.className = 'verdict-badge';
        }
        if (confidenceVal) confidenceVal.textContent = '--%';
        if (ingredientsTags) ingredientsTags.innerHTML = '';
        if (reasonsList) reasonsList.innerHTML = '';
        if (assessmentsList) assessmentsList.innerHTML = '';
        if (notesList) notesList.innerHTML = '';
        if (notesContainer) notesContainer.classList.add('hidden');
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /* ==========================================================================
       Ingredient Localization & Neutral Icon Helper
       ========================================================================== */

    const INGREDIENT_ICON_SVG = `<svg class="chip-ing-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>`;

    const INGREDIENT_DICT = {
        'sugar': 'سكر',
        'white sugar': 'سكر أبيض',
        'cane sugar': 'سكر القصب',
        'beet sugar': 'سكر الشمندر',
        'brown sugar': 'سكر بني',
        'glucose syrup': 'شراب الجلوكوز',
        'fructose syrup': 'شراب الفركتوز',
        'high fructose corn syrup': 'شراب الذرة عالي الفركتوز',
        'corn syrup': 'شراب الذرة',
        'invert sugar': 'سكر محوّل',
        'invert sugar syrup': 'شراب السكر المحوّل',
        'molasses': 'دبس السكر',
        'dextrose': 'دكستروز',
        'maltose': 'مالتوز',
        'lactose': 'لاكتوز (سكر الحليب)',
        'honey': 'عسل',
        'aspartame': 'أسبارتام',
        'sucralose': 'سكرالوز',
        'stevia': 'ستيفيا',
        'sorbitol': 'سوربيتول',
        'maltitol': 'مالتيتول',
        'xylitol': 'زيليتول',
        'erythritol': 'إريثريتول',

        'cocoa butter': 'زبدة الكاكاو',
        'cacao butter': 'زبدة الكاكاو',
        'cocoa mass': 'كتلة الكاكاو',
        'cacao mass': 'كتلة الكاكاو',
        'cocoa liquor': 'سائل الكاكاو',
        'cocoa paste': 'معجون الكاكاو',
        'cocoa powder': 'مسحوق الكاكاو',
        'fat-reduced cocoa powder': 'مسحوق كاكاو قليل الدسم',
        'reduced fat cocoa powder': 'مسحوق كاكاو قليل الدسم',
        'cocoa solids': 'جوامد الكاكاو',
        'chocolate': 'شوكولاتة',
        'milk chocolate': 'شوكولاتة بالحليب',
        'dark chocolate': 'شوكولاتة داكنة',
        'white chocolate': 'شوكولاتة بيضاء',

        'milk': 'حليب',
        'cow milk': 'حليب بقري',
        'skimmed milk powder': 'مسحوق الحليب منزوع الدسم',
        'skim milk powder': 'مسحوق الحليب منزوع الدسم',
        'nonfat dry milk': 'حليب مجفف خالي الدسم',
        'whole milk powder': 'مسحوق الحليب كامل الدسم',
        'full cream milk powder': 'مسحوق الحليب كامل الدسم',
        'milk powder': 'مسحوق الحليب',
        'powdered milk': 'حليب مجفف',
        'milk fat': 'دهن الحليب',
        'anhydrous milk fat': 'دهن الحليب اللامائي',
        'butterfat': 'دهن الزبدة',
        'butter oil': 'زيت الزبدة',
        'whey': 'مصل اللبن',
        'sweet whey': 'مصل اللبن الحلو',
        'whey powder': 'مسحوق مصل اللبن',
        'sweet whey powder': 'مسحوق مصل اللبن الحلو',
        'whey protein': 'بروتين مصل اللبن',
        'butter': 'زبدة',
        'cream': 'كريمة (قشطة)',
        'casein': 'كازين',
        'sodium caseinate': 'كازينات الصوديوم',
        'cheese': 'جبن',
        'yogurt': 'زبادي',

        'hazelnut': 'بندق',
        'hazelnuts': 'بندق',
        'hazelnut paste': 'معجون البندق',
        'almond': 'لوز',
        'almonds': 'لوز',
        'almond paste': 'معجون اللوز',
        'peanut': 'فول سوداني',
        'peanuts': 'فول سوداني',
        'peanut butter': 'زبدة الفول السوداني',
        'walnut': 'عين الجمل (جوز)',
        'walnuts': 'عين الجمل (جوز)',
        'pistachio': 'فستق',
        'pistachios': 'فستق',
        'cashew': 'كاجو',
        'cashews': 'كاجو',
        'sesame': 'سمسم',
        'sesame seeds': 'بذور السمسم',
        'tahini': 'طحينة',

        'lecithin': 'ليسيثين',
        'soy lecithin': 'ليسيثين الصويا',
        'soya lecithin': 'ليسيثين الصويا',
        'sunflower lecithin': 'ليسيثين دوار الشمس',
        'rapeseed lecithin': 'ليسيثين بذور اللفت',
        'emulsifier': 'مستحلب',
        'emulsifiers': 'مستحلبات',
        'polyglycerol polyricinoleate': 'بولي جلسرين بولي ريسينوليات (PGPR)',
        'pgpr': 'بولي جلسرين بولي ريسينوليات (PGPR)',
        'ammonium phosphatides': 'فوسفاتيد الأمونيوم',
        'mono- and diglycerides of fatty acids': 'أحادي وثنائي الجليسريدات للأحماض الدهنية',
        'mono and diglycerides': 'أحادي وثنائي الجليسريدات',

        'flavoring': 'منكهات',
        'flavouring': 'منكهات',
        'flavorings': 'منكهات',
        'flavourings': 'منكهات',
        'natural flavor': 'نكهة طبيعية',
        'natural flavour': 'نكهة طبيعية',
        'natural flavoring': 'منكهات طبيعية',
        'natural flavouring': 'منكهات طبيعية',
        'artificial flavor': 'نكهة اصطناعية',
        'artificial flavour': 'نكهة اصطناعية',
        'artificial flavoring': 'منكهات اصطناعية',
        'artificial flavouring': 'منكهات اصطناعية',
        'vanilla': 'فانيليا',
        'vanilla extract': 'مستخلص الفانيليا',
        'natural vanilla': 'فانيليا طبيعية',
        'vanillin': 'فانيلين',
        'ethyl vanillin': 'إيثيل فانيلين',

        'wheat flour': 'دقيق القمح',
        'wheat': 'قمح',
        'flour': 'دقيق',
        'whole wheat flour': 'دقيق القمح الكامل',
        'corn starch': 'نشاء الذرة',
        'cornstarch': 'نشاء الذرة',
        'maize starch': 'نشاء الذرة',
        'potato starch': 'نشاء البطاطس',
        'tapioca starch': 'نشاء التابيوكا',
        'rice flour': 'دقيق الأرز',
        'oats': 'شوفان',
        'barley': 'شعير',
        'barley malt': 'مالت الشعير',
        'gluten': 'جلوتين',
        'wheat gluten': 'جلوتين القمح',

        'palm oil': 'زيت النخيل',
        'palm fat': 'دهن النخيل',
        'vegetable oil': 'زيت نباتي',
        'vegetable fat': 'دهن نباتي',
        'vegetable fats': 'دهون نباتية',
        'vegetable oils': 'زيوت نباتية',
        'hydrogenated vegetable oil': 'زيت نباتي مهدرج',
        'hydrogenated palm oil': 'زيت نخيل مهدرج',
        'sunflower oil': 'زيت دوار الشمس',
        'canola oil': 'زيت الكانولا',
        'rapeseed oil': 'زيت بذور اللفت',
        'olive oil': 'زيت الزيتون',
        'coconut oil': 'زيت جوز الهند',
        'soybean oil': 'زيت الصويا',
        'soya oil': 'زيت الصويا',
        'corn oil': 'زيت الذرة',

        'gelatin': 'جيلاتين',
        'gelatine': 'جيلاتين',
        'pork gelatin': 'جيلاتين خنزيري',
        'beef gelatin': 'جيلاتين بقري',
        'fish gelatin': 'جيلاتين سمكي',
        'halal gelatin': 'جيلاتين حلال',
        'pectin': 'بكتين',
        'agar': 'آغار',
        'agar-agar': 'آغار آغار',
        'carrageenan': 'كاراجينان',
        'guar gum': 'صمغ الغوار',
        'xanthan gum': 'صمغ الزانثان',
        'locust bean gum': 'صمغ الخروب',
        'arabic gum': 'صمغ عربي',
        'gum arabic': 'صمغ عربي',
        'shellac': 'شيلاك (صمغ اللك)',
        'beeswax': 'شمع العسل',

        'yeast': 'خميرة',
        'baking powder': 'مسحوق الخبز (بيكنج بودر)',
        'baking soda': 'بيكربونات الصوديوم',
        'sodium bicarbonate': 'بيكربونات الصوديوم',

        'salt': 'ملح',
        'table salt': 'ملح طعام',
        'sea salt': 'ملح بحري',
        'water': 'ماء',
        'citric acid': 'حمض الستريك (ملح الليمون)',
        'ascorbic acid': 'حمض الأسكوربيك (فيتامين ج)',
        'acetic acid': 'حمض الأسيتيك (الخل)',
        'vinegar': 'خل',
        'lactic acid': 'حمض اللاكتيك',
        'sorbic acid': 'حمض السوربيك',
        'potassium sorbate': 'سوربات البوتاسيوم',
        'sodium benzoate': 'بنزوات الصوديوم',
        'calcium carbonate': 'كربونات الكالسيوم',
        'l-cysteine': 'إل-سيستين',
        'monosodium glutamate': 'جلوتامات أحادية الصوديوم (MSG)',
        'msg': 'جلوتامات أحادية الصوديوم (MSG)',
        'flavor enhancer': 'معزز نكهة',
        'flavour enhancer': 'معزز نكهة',
        'preservative': 'مادة حافظة',
        'preservatives': 'مواد حافظة',
        'color': 'لون غذائي',
        'colour': 'لون غذائي',
        'natural color': 'لون طبيعي',
        'natural colour': 'لون طبيعي',

        'egg': 'بيض',
        'eggs': 'بيض',
        'egg white': 'بياض البيض',
        'egg yolk': 'صفار البيض',
        'pork': 'لحم خنزير',
        'lard': 'شحم الخنزير (دهن خنزيري)',
        'beef': 'لحم بقري',
        'chicken': 'لحم دجاج',
        'fish': 'سمك',
        'rennet': 'منفحة',
        'pepsin': 'بيبسين'
    };

    const E_NUMBERS_DICT = {
        'e100': 'كركمين',
        'e101': 'ريبوفلافين (فيتامين ب2)',
        'e102': 'تارترازين (أصفر)',
        'e110': 'أصفر غروب الشمس',
        'e120': 'كوشينيل (كارمين قرمزي)',
        'e122': 'أزوروبين',
        'e124': 'بونصو 4R',
        'e129': 'أحمر ألورا AC',
        'e132': 'إنديجوتين',
        'e133': 'أزرق لامع FCF',
        'e140': 'كلوروفيل',
        'e141': 'معقدات نحاسية للكلوروفيل',
        'e150a': 'كراميل بسيط',
        'e150b': 'كراميل الكبريتيت',
        'e150c': 'كراميل الأمونيا',
        'e150d': 'كراميل أمونيا الكبريتيت',
        'e160a': 'بيتا كاروتين',
        'e160b': 'أناتو',
        'e160c': 'مستخلص البابريكا',
        'e162': 'أحمر الشمندر',
        'e163': 'أنثوسيانين',
        'e170': 'كربونات الكالسيوم',
        'e171': 'ثاني أكسيد التيتانيوم',
        'e200': 'حمض السوربيك',
        'e202': 'سوربات البوتاسيوم',
        'e211': 'بنزوات الصوديوم',
        'e220': 'ثاني أكسيد الكبريت',
        'e250': 'نتريت الصوديوم',
        'e251': 'نترات الصوديوم',
        'e300': 'حمض الأسكوربيك (فيتامين ج)',
        'e306': 'توكوفيرول (فيتامين هـ)',
        'e322': 'ليسيثين',
        'e330': 'حمض الستريك',
        'e407': 'كاراجينان',
        'e410': 'صمغ الخروب',
        'e412': 'صمغ الغوار',
        'e414': 'صمغ عربي',
        'e415': 'صمغ الزانثان',
        'e420': 'سوربيتول',
        'e422': 'جلسرين',
        'e428': 'جيلاتين',
        'e440': 'بكتين',
        'e442': 'فوسفاتيد الأمونيوم',
        'e471': 'أحادي وثنائي الجليسريدات',
        'e472a': 'إسترات حمض الخليك',
        'e472b': 'إسترات حمض اللاكتيك',
        'e472c': 'إسترات حمض الستريك',
        'e472e': 'إسترات حمض الطرطريك',
        'e475': 'إسترات بولي جلسرين للأحماض الدهنية',
        'e476': 'بولي جلسرين بولي ريسينوليات (PGPR)',
        'e481': 'لاكتيلات ستيرويل الصوديوم',
        'e500': 'كربونات الصوديوم',
        'e500ii': 'بيكربونات الصوديوم',
        'e503': 'كربونات الأمونيوم',
        'e621': 'جلوتامات أحادية الصوديوم (MSG)',
        'e901': 'شمع العسل',
        'e904': 'شيلاك',
        'e920': 'إل-سيستين',
        'e951': 'أسبارتام',
        'e955': 'سكرالوز',
        'e965': 'مالتيتول'
    };

    function localizeIngredientName(term) {
        if (!term || typeof term !== 'string') return '';
        const trimmed = term.trim();
        if (!trimmed) return '';

        // If already contains Arabic characters, return as-is
        if (/[\u0600-\u06FF]/.test(trimmed)) {
            return trimmed;
        }

        // Check for E-number e.g. E476, E471, E160a
        const eMatch = trimmed.match(/\bE-?(\d{3,4})([a-z]?)\b/i);
        if (eMatch) {
            const eCode = 'E' + eMatch[1] + (eMatch[2] ? eMatch[2].toLowerCase() : '');
            const eKey = eCode.toLowerCase();
            const eDesc = E_NUMBERS_DICT[eKey] || E_NUMBERS_DICT['e' + eMatch[1]];

            const prefix = trimmed.replace(/\s*\(?\bE-?\d{3,4}[a-z]?\b\)?/i, '').trim().toLowerCase().replace(/^[^\w\u0600-\u06FF]+|[^\w\u0600-\u06FF]+$/g, '');
            const prefixAr = INGREDIENT_DICT[prefix];

            if (prefixAr) {
                return eDesc ? `${prefixAr} (${eCode} - ${eDesc})` : `${prefixAr} (${eCode})`;
            }
            return eDesc ? `${eCode} (${eDesc})` : eCode;
        }

        const normalized = trimmed.toLowerCase().replace(/^[^\w\u0600-\u06FF]+|[^\w\u0600-\u06FF]+$/g, '');
        const normalizedNoPct = normalized.replace(/\s*\(\s*\d+(\.\d+)?%\s*\)/g, '').trim();

        if (INGREDIENT_DICT[normalizedNoPct]) return INGREDIENT_DICT[normalizedNoPct];
        if (INGREDIENT_DICT[normalized]) return INGREDIENT_DICT[normalized];

        // Compound pattern: e.g. emulsifier (soya lecithin)
        const subMatch = trimmed.match(/^(emulsifier|emulsifiers|flavoring|flavouring|color|colour|preservative|stabilizer)\s*\((.+)\)$/i);
        if (subMatch) {
            const outer = localizeIngredientName(subMatch[1]);
            const inner = localizeIngredientName(subMatch[2]);
            return `${outer} (${inner})`;
        }

        // Pattern: Whey powder (from milk)
        const milkMatch = trimmed.match(/^(.+?)\s*\((from\s+milk|contains\s+.+)\)$/i);
        if (milkMatch) {
            const main = localizeIngredientName(milkMatch[1]);
            if (/milk/i.test(milkMatch[2])) {
                return `${main} (من الحليب)`;
            }
            return main;
        }

        return trimmed;
    }

    /* ==========================================================================
       3. Real Extracted Ingredients Rendering (Stage 1 Output)
       ========================================================================== */

    function renderExtractedIngredients(visionData) {
        if (!ingredientsStageCard || !extractedIngredientsList) return;

        extractedIngredientsList.innerHTML = '';
        const ingredients = visionData.ingredients || [];
        const isArabicMode = (config.currentLang === 'ar');

        if (ingredients.length === 0) {
            extractedIngredientsList.innerHTML = `<p class="empty-hint">${isArabicMode ? 'لم يتم العثور على مكونات مقروءة بوضوح.' : 'No readable ingredients found.'}</p>`;
        } else {
            ingredients.forEach(item => {
                const chip = document.createElement('div');
                chip.className = 'extracted-chip';

                const rawOrig = (typeof item === 'object' && item !== null)
                    ? (item.original_name || item.name || '')
                    : String(item);
                const rawNorm = (typeof item === 'object' && item !== null)
                    ? (item.normalized_name || rawOrig)
                    : rawOrig;
                const nameAr = (typeof item === 'object' && item !== null && item.name_ar)
                    ? item.name_ar
                    : localizeIngredientName(rawNorm || rawOrig);
                const nameEn = (typeof item === 'object' && item !== null && item.name_en)
                    ? item.name_en
                    : (rawNorm || rawOrig);

                const primaryName = isArabicMode ? (nameAr || nameEn) : (nameEn || nameAr);

                // Determine if secondary text is beneficial (e.g. Latin packaging text in Arabic mode)
                let secondaryText = null;
                if (isArabicMode) {
                    const candidate = rawOrig || rawNorm;
                    if (candidate && !/[\u0600-\u06FF]/.test(candidate) && candidate.toLowerCase() !== primaryName.toLowerCase()) {
                        secondaryText = candidate;
                    }
                } else {
                    if (rawOrig && /[\u0600-\u06FF]/.test(rawOrig) && rawOrig !== primaryName) {
                        secondaryText = rawOrig;
                    }
                }

                const conf = (typeof item === 'object' && item !== null && typeof item.confidence === 'number')
                    ? Math.round(item.confidence * 100)
                    : null;

                let chipHtml = `
                    <div class="chip-content">
                        ${INGREDIENT_ICON_SVG}
                        <span class="chip-name">${escapeHtml(primaryName)}</span>
                    </div>
                `;

                if (secondaryText) {
                    const secDir = /[\u0600-\u06FF]/.test(secondaryText) ? 'rtl' : 'ltr';
                    chipHtml += `<span class="chip-original" dir="${secDir}" title="Original text">(${escapeHtml(secondaryText)})</span>`;
                }

                if (conf !== null) {
                    const confClass = conf >= 85 ? 'conf-high' : (conf >= 60 ? 'conf-med' : 'conf-low');
                    chipHtml += `<span class="chip-confidence ${confClass}" title="Confidence">${conf}%</span>`;
                }

                chip.innerHTML = chipHtml;
                extractedIngredientsList.appendChild(chip);
            });
        }

        // Show raw extracted text if available (strictly verbatim from packaging)
        if (sourceTextContainer && sourceTextContent && visionData.source_text) {
            sourceTextContent.textContent = visionData.source_text;
            sourceTextContainer.classList.remove('hidden');
        }

        // Make Stage 1 card visible immediately
        ingredientsStageCard.classList.remove('hidden');
        if (verifyingProgressBanner) {
            verifyingProgressBanner.classList.remove('hidden');
        }

        // Smooth scroll so the user immediately sees the real extracted ingredients
        ingredientsStageCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /* ==========================================================================
       4. Real Halal Assessment Rendering (Stage 2 Output)
       ========================================================================== */

    function renderResults(data) {
        if (!data) return;

        // Hide "Verifying in progress" banner once Agent 2 completes
        if (verifyingProgressBanner) {
            verifyingProgressBanner.classList.add('hidden');
        }

        const vision = data.ingredients || data.vision || {};
        const verification = data.halal_verification || data.verification || {};
        const isArabic = (config.currentLang === 'ar');

        // 1. Final Status Badge
        const rawStatus = (verification.status || 'UNKNOWN').toUpperCase();
        const localizedStatus = (isArabic ? verification.status_ar : verification.status_en) ||
                                (i18n.statuses && i18n.statuses[rawStatus]) || rawStatus;

        if (verdictBadge) {
            verdictBadge.textContent = localizedStatus;
            verdictBadge.className = 'verdict-badge status-' + rawStatus;
        }

        // 2. Confidence Percentage
        if (confidenceVal) {
            const conf = Math.round((verification.confidence || 0) * 100);
            confidenceVal.textContent = conf + '%';
        }

        // 3. Extracted Ingredients Tags in Final Report
        if (ingredientsTags) {
            ingredientsTags.innerHTML = '';
            const list = vision.ingredients || [];
            if (list.length === 0) {
                ingredientsTags.innerHTML = '<span class="ingredient-tag">--</span>';
            } else {
                list.forEach(ing => {
                    const tag = document.createElement('span');
                    tag.className = 'ingredient-tag';

                    const rawOrig = (typeof ing === 'object' && ing !== null)
                        ? (ing.original_name || ing.name || '')
                        : String(ing);
                    const rawNorm = (typeof ing === 'object' && ing !== null)
                        ? (ing.normalized_name || rawOrig)
                        : rawOrig;
                    const nameAr = (typeof ing === 'object' && ing !== null && ing.name_ar)
                        ? ing.name_ar
                        : localizeIngredientName(rawNorm || rawOrig);
                    const nameEn = (typeof ing === 'object' && ing !== null && ing.name_en)
                        ? ing.name_en
                        : (rawNorm || rawOrig);

                    const primary = isArabic ? (nameAr || nameEn) : (nameEn || nameAr);
                    let secText = null;
                    if (isArabic) {
                        const candidate = rawOrig || rawNorm;
                        if (candidate && !/[\u0600-\u06FF]/.test(candidate) && candidate.toLowerCase() !== primary.toLowerCase()) {
                            secText = candidate;
                        }
                    }

                    let tagHtml = `${INGREDIENT_ICON_SVG} <span class="tag-primary">${escapeHtml(primary)}</span>`;
                    if (secText) {
                        tagHtml += ` <span class="tag-secondary" dir="ltr">(${escapeHtml(secText)})</span>`;
                    }
                    tag.innerHTML = tagHtml;
                    ingredientsTags.appendChild(tag);
                });
            }
        }

        // 4. Overall Reasons List
        if (reasonsList) {
            reasonsList.innerHTML = '';
            const reasons = verification.reasons || [];
            if (reasons.length > 0) {
                reasons.forEach(r => {
                    const li = document.createElement('li');
                    li.className = 'reason-item';
                    li.textContent = r;
                    reasonsList.appendChild(li);
                });
            }
        }

        // 5. Individual Ingredient Assessments
        if (assessmentsList) {
            assessmentsList.innerHTML = '';
            const assessments = verification.ingredient_assessments || [];
            if (assessments.length > 0) {
                assessments.forEach(item => {
                    const card = document.createElement('div');
                    card.className = 'assessment-card';

                    const header = document.createElement('div');
                    header.className = 'assessment-card-header';

                    const rawIng = item.ingredient || '';
                    const ingAr = item.ingredient_ar || localizeIngredientName(rawIng);
                    const ingEn = item.ingredient_en || rawIng;

                    const primaryIng = isArabic ? (ingAr || ingEn) : (ingEn || ingAr);
                    let secIng = null;
                    if (isArabic && ingEn && !/[\u0600-\u06FF]/.test(ingEn) && ingEn.toLowerCase() !== primaryIng.toLowerCase()) {
                        secIng = ingEn;
                    }

                    const nameEl = document.createElement('span');
                    nameEl.className = 'assessment-ing-name';
                    let nameHtml = `${INGREDIENT_ICON_SVG} <span class="assessment-ing-primary">${escapeHtml(primaryIng)}</span>`;
                    if (secIng) {
                        nameHtml += ` <span class="assessment-ing-secondary" dir="ltr">(${escapeHtml(secIng)})</span>`;
                    }
                    nameEl.innerHTML = nameHtml;

                    const itemRawStatus = (item.status || 'UNKNOWN').toUpperCase();
                    const itemLocalizedStatus = isArabic ? (item.status_ar || i18n.statuses[itemRawStatus] || itemRawStatus)
                                                         : (item.status_en || i18n.statuses[itemRawStatus] || itemRawStatus);

                    const badgeEl = document.createElement('span');
                    badgeEl.className = 'assessment-status-badge status-' + itemRawStatus;
                    badgeEl.textContent = itemLocalizedStatus;

                    header.appendChild(nameEl);
                    header.appendChild(badgeEl);
                    card.appendChild(header);

                    if (item.reason) {
                        const reasonEl = document.createElement('p');
                        reasonEl.className = 'assessment-reason';
                        reasonEl.textContent = item.reason;
                        card.appendChild(reasonEl);
                    }

                    assessmentsList.appendChild(card);
                });
            }
        }

        // 6. Consumer Notes & Warnings
        if (notesList && notesContainer) {
            notesList.innerHTML = '';
            const notes = verification.notes || [];
            if (notes.length > 0) {
                notesContainer.classList.remove('hidden');
                notes.forEach(note => {
                    const li = document.createElement('li');
                    li.className = 'note-item';
                    li.textContent = note;
                    notesList.appendChild(li);
                });
            } else {
                notesContainer.classList.add('hidden');
            }
        }

        // Reveal results container
        if (resultsIdle) resultsIdle.classList.add('hidden');
        if (resultsContent) resultsContent.classList.remove('hidden');
    }

    /* ==========================================================================
       5. Progressive 2-Stage Analysis Controller (Streaming + Fallback)
       ========================================================================== */

    if (analyzeForm) {
        analyzeForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!currentSelectedFile) {
                showAlert(i18n.errorNoFile || 'Please select an image first.');
                return;
            }

            hideAlert();
            resetResults();

            // UI Loading state
            if (submitBtn) submitBtn.disabled = true;
            if (submitBtnSpinner) submitBtnSpinner.classList.remove('hidden');

            // Timeline: Stage 1 active, Stage 2 waiting
            updateStep(stepVision, badgeVision, 'active', i18n.stage1_status_active || i18n.processing || 'Processing...');
            updateStep(stepHalal, badgeHalal, 'idle', i18n.stage2_status_waiting || i18n.waiting || 'Waiting');

            const formData = new FormData();
            formData.append('image', currentSelectedFile);
            formData.append('lang', config.currentLang || 'ar');
            formData.append('stream', '1'); // Request real-time NDJSON stream

            try {
                const response = await fetch('api/analyze.php', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/x-ndjson, application/json'
                    },
                    body: formData,
                });

                const contentType = response.headers.get('content-type') || '';

                // Case A: Real progressive NDJSON stream supported and returned
                if (response.body && (contentType.includes('application/x-ndjson') || contentType.includes('text/event-stream'))) {
                    const reader = response.body.getReader();
                    const decoder = new TextDecoder('utf-8');
                    let streamBuffer = '';
                    let stage1Rendered = false;
                    let stage2Rendered = false;

                    while (true) {
                        const { done, value } = await reader.read();
                        if (done) break;

                        streamBuffer += decoder.decode(value, { stream: true });
                        const lines = streamBuffer.split('\n');
                        streamBuffer = lines.pop(); // Retain remainder for next chunk

                        for (const line of lines) {
                            const trimmed = line.trim();
                            if (!trimmed) continue;

                            let event;
                            try {
                                event = JSON.parse(trimmed);
                            } catch (parseErr) {
                                console.warn('Stream chunk parse error:', trimmed);
                                continue;
                            }

                            // Handle Event 1: Real Ingredients Extracted from Agent 1
                            if (event.type === 'ingredients_extracted') {
                                stage1Rendered = true;
                                updateStep(stepVision, badgeVision, 'done', i18n.stage1_status_done || 'Ingredients Extracted');
                                updateStep(stepHalal, badgeHalal, 'active', i18n.stage2_status_active || 'Verifying...');

                                const extractedData = event.data?.ingredients || event.data || {};
                                renderExtractedIngredients(extractedData);
                            }

                            // Handle Event 2: Verification Started
                            else if (event.type === 'verification_started') {
                                updateStep(stepHalal, badgeHalal, 'active', i18n.stage2_status_active || 'Verifying...');
                            }

                            // Handle Event 3: Verification Completed from Agent 2
                            else if (event.type === 'verification_completed') {
                                stage2Rendered = true;
                                updateStep(stepHalal, badgeHalal, 'done', i18n.stage2_status_done || 'Verification Completed');
                                
                                const analysisData = event.data?.product_analysis || event.data || {};
                                renderResults(analysisData);
                            }

                            // Handle Event Error
                            else if (event.type === 'error' || event.success === false) {
                                throw new Error(event.error || (i18n.errorGeneric || 'Analysis error.'));
                            }
                        }
                    }

                    // Process any final buffered line
                    if (streamBuffer.trim()) {
                        try {
                            const finalEvent = JSON.parse(streamBuffer.trim());
                            if (finalEvent.type === 'verification_completed') {
                                stage2Rendered = true;
                                updateStep(stepHalal, badgeHalal, 'done', i18n.stage2_status_done || 'Completed');
                                renderResults(finalEvent.data?.product_analysis || finalEvent.data || {});
                            }
                        } catch (e) {
                            // Non-critical trailing buffer
                        }
                    }

                    // If stream completed successfully, we are done!
                    if (stage1Rendered && stage2Rendered) {
                        return;
                    }
                }

                // Case B: Fallback / standard response (if proxy buffered or non-stream JSON was returned)
                const rawText = await response.text();
                let data;

                // Handle possible multi-line NDJSON delivered all at once
                const lines = rawText.split('\n').map(l => l.trim()).filter(Boolean);
                let lastValid = null;
                for (const line of lines) {
                    try {
                        const parsed = JSON.parse(line);
                        if (parsed.type === 'ingredients_extracted') {
                            updateStep(stepVision, badgeVision, 'done', i18n.stage1_status_done || 'Ingredients Extracted');
                            renderExtractedIngredients(parsed.data?.ingredients || parsed.data || {});
                        }
                        if (parsed.type === 'verification_completed') {
                            lastValid = parsed.data?.product_analysis || parsed.data;
                        }
                    } catch (_) {}
                }

                if (lastValid) {
                    updateStep(stepHalal, badgeHalal, 'done', i18n.stage2_status_done || 'Completed');
                    renderResults(lastValid);
                    return;
                }

                // Standard JSON single response
                try {
                    data = JSON.parse(rawText);
                } catch (parseErr) {
                    const cleanMsg = rawText.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
                    const snippet = cleanMsg ? cleanMsg.substring(0, 200) : (i18n.errorGeneric || 'Invalid server response.');
                    showAlert(snippet);
                    updateStep(stepVision, badgeVision, 'error', i18n.failed || 'Failed');
                    updateStep(stepHalal, badgeHalal, 'error', i18n.failed || 'Failed');
                    return;
                }

                if (!response.ok || !data.success) {
                    const errorMsg = data.error || (i18n.errorGeneric || 'Analysis request failed.');
                    showAlert(errorMsg);
                    updateStep(stepVision, badgeVision, 'error', i18n.failed || 'Failed');
                    updateStep(stepHalal, badgeHalal, 'error', i18n.failed || 'Failed');
                    return;
                }

                // Progressive reveal: First render extracted ingredients
                const analysisData = data.product_analysis || data.data || {};
                if (analysisData.ingredients) {
                    updateStep(stepVision, badgeVision, 'done', i18n.stage1_status_done || 'Ingredients Extracted');
                    renderExtractedIngredients(analysisData.ingredients);
                }

                // Then complete Stage 2
                updateStep(stepHalal, badgeHalal, 'done', i18n.stage2_status_done || 'Verification Completed');
                renderResults(analysisData);

            } catch (err) {
                showAlert((i18n.errorGeneric || 'Network error occurred.') + ' ' + err.message);
                updateStep(stepVision, badgeVision, 'error', i18n.failed || 'Failed');
                updateStep(stepHalal, badgeHalal, 'error', i18n.failed || 'Failed');
            } finally {
                if (submitBtn) submitBtn.disabled = false;
                if (submitBtnSpinner) submitBtnSpinner.classList.add('hidden');
            }
        });
    }

})();

