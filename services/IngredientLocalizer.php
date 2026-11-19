<?php
/**
 * HalalLens AI - Ingredient Localizer Service
 *
 * Provides bidirectional localization for food ingredients, additives, and E-numbers.
 * Ensures Arabic mode displays natural, accurate Arabic culinary and scientific terms
 * while preserving original Latin / packaging text cleanly isolated in LTR spans.
 */

declare(strict_types=1);

namespace HalalLens\Services;

class IngredientLocalizer
{
    /**
     * Standard dictionary mapping lowercase normalized English terms to Arabic.
     */
    private static array $dictionary = [
        // Sweeteners & Sugars
        'sugar' => 'سكر',
        'white sugar' => 'سكر أبيض',
        'cane sugar' => 'سكر القصب',
        'beet sugar' => 'سكر الشمندر',
        'brown sugar' => 'سكر بني',
        'glucose syrup' => 'شراب الجلوكوز',
        'fructose syrup' => 'شراب الفركتوز',
        'high fructose corn syrup' => 'شراب الذرة عالي الفركتوز',
        'corn syrup' => 'شراب الذرة',
        'invert sugar' => 'سكر محوّل',
        'invert sugar syrup' => 'شراب السكر المحوّل',
        'molasses' => 'دبس السكر',
        'dextrose' => 'دكستروز',
        'maltose' => 'مالتوز',
        'lactose' => 'لاكتوز (سكر الحليب)',
        'honey' => 'عسل',
        'maple syrup' => 'شراب القيقب',
        'aspartame' => 'أسبارتام',
        'sucralose' => 'سكرالوز',
        'stevia' => 'ستيفيا',
        'sorbitol' => 'سوربيتول',
        'maltitol' => 'مالتيتول',
        'xylitol' => 'زيليتول',
        'erythritol' => 'إريثريتول',

        // Cocoa & Chocolate Components
        'cocoa butter' => 'زبدة الكاكاو',
        'cacao butter' => 'زبدة الكاكاو',
        'cocoa mass' => 'كتلة الكاكاو',
        'cacao mass' => 'كتلة الكاكاو',
        'cocoa liquor' => 'سائل الكاكاو',
        'cocoa paste' => 'معجون الكاكاو',
        'cocoa powder' => 'مسحوق الكاكاو',
        'fat-reduced cocoa powder' => 'مسحوق كاكاو قليل الدسم',
        'reduced fat cocoa powder' => 'مسحوق كاكاو قليل الدسم',
        'cocoa solids' => 'جوامد الكاكاو',
        'chocolate' => 'شوكولاتة',
        'milk chocolate' => 'شوكولاتة بالحليب',
        'dark chocolate' => 'شوكولاتة داكنة',
        'white chocolate' => 'شوكولاتة بيضاء',

        // Dairy & Milk Derivatives
        'milk' => 'حليب',
        'cow milk' => 'حليب بقري',
        'skimmed milk powder' => 'مسحوق الحليب منزوع الدسم',
        'skim milk powder' => 'مسحوق الحليب منزوع الدسم',
        'nonfat dry milk' => 'حليب مجفف خالي الدسم',
        'whole milk powder' => 'مسحوق الحليب كامل الدسم',
        'full cream milk powder' => 'مسحوق الحليب كامل الدسم',
        'milk powder' => 'مسحوق الحليب',
        'powdered milk' => 'حليب مجفف',
        'milk fat' => 'دهن الحليب',
        'anhydrous milk fat' => 'دهن الحليب اللامائي',
        'butterfat' => 'دهن الزبدة',
        'butter oil' => 'زيت الزبدة',
        'whey' => 'مصل اللبن',
        'sweet whey' => 'مصل اللبن الحلو',
        'whey powder' => 'مسحوق مصل اللبن',
        'sweet whey powder' => 'مسحوق مصل اللبن الحلو',
        'whey permeate' => 'راشح مصل اللبن',
        'whey protein' => 'بروتين مصل اللبن',
        'whey protein concentrate' => 'مركز بروتين مصل اللبن',
        'whey protein isolate' => 'معزول بروتين مصل اللبن',
        'butter' => 'زبدة',
        'cream' => 'كريمة (قشطة)',
        'sour cream' => 'كريمة حامضة',
        'casein' => 'كازين',
        'sodium caseinate' => 'كازينات الصوديوم',
        'calcium caseinate' => 'كازينات الكالسيوم',
        'cheese' => 'جبن',
        'cheddar cheese' => 'جبن شيدر',
        'mozzarella' => 'جبن موزاريلا',
        'yogurt' => 'زبادي',
        'buttermilk' => 'لبن خض',

        // Nuts & Seeds
        'hazelnut' => 'بندق',
        'hazelnuts' => 'بندق',
        'hazelnut paste' => 'معجون البندق',
        'almond' => 'لوز',
        'almonds' => 'لوز',
        'almond paste' => 'معجون اللوز',
        'peanut' => 'فول سوداني',
        'peanuts' => 'فول سوداني',
        'peanut butter' => 'زبدة الفول السوداني',
        'walnut' => 'عين الجمل (جوز)',
        'walnuts' => 'عين الجمل (جوز)',
        'pistachio' => 'فستق',
        'pistachios' => 'فستق',
        'cashew' => 'كاجو',
        'cashews' => 'كاجو',
        'sesame' => 'سمسم',
        'sesame seeds' => 'بذور السمسم',
        'sesame paste' => 'طحينة (معجون سمسم)',
        'tahini' => 'طحينة',

        // Emulsifiers & Lecithins
        'lecithin' => 'ليسيثين',
        'soy lecithin' => 'ليسيثين الصويا',
        'soya lecithin' => 'ليسيثين الصويا',
        'sunflower lecithin' => 'ليسيثين دوار الشمس',
        'rapeseed lecithin' => 'ليسيثين بذور اللفت',
        'egg lecithin' => 'ليسيثين البيض',
        'emulsifier' => 'مستحلب',
        'emulsifiers' => 'مستحلبات',
        'polyglycerol polyricinoleate' => 'بولي جلسرين بولي ريسينوليات (PGPR)',
        'pgpr' => 'بولي جلسرين بولي ريسينوليات (PGPR)',
        'ammonium phosphatides' => 'فوسفاتيد الأمونيوم',
        'mono- and diglycerides of fatty acids' => 'أحادي وثنائي الجليسريدات للأحماض الدهنية',
        'mono and diglycerides' => 'أحادي وثنائي الجليسريدات',

        // Flavors & Extracts
        'flavoring' => 'منكهات',
        'flavouring' => 'منكهات',
        'flavorings' => 'منكهات',
        'flavourings' => 'منكهات',
        'natural flavor' => 'نكهة طبيعية',
        'natural flavour' => 'نكهة طبيعية',
        'natural flavoring' => 'منكهات طبيعية',
        'natural flavouring' => 'منكهات طبيعية',
        'artificial flavor' => 'نكهة اصطناعية',
        'artificial flavour' => 'نكهة اصطناعية',
        'artificial flavoring' => 'منكهات اصطناعية',
        'artificial flavouring' => 'منكهات اصطناعية',
        'nature-identical flavoring' => 'منكهات مماثلة للطبيعية',
        'vanilla' => 'فانيليا',
        'vanilla extract' => 'مستخلص الفانيليا',
        'natural vanilla' => 'فانيليا طبيعية',
        'vanillin' => 'فانيلين',
        'ethyl vanillin' => 'إيثيل فانيلين',

        // Flours, Cereals & Starches
        'wheat flour' => 'دقيق القمح',
        'wheat' => 'قمح',
        'flour' => 'دقيق',
        'whole wheat flour' => 'دقيق القمح الكامل',
        'corn starch' => 'نشاء الذرة',
        'cornstarch' => 'نشاء الذرة',
        'maize starch' => 'نشاء الذرة',
        'potato starch' => 'نشاء البطاطس',
        'tapioca starch' => 'نشاء التابيوكا',
        'modified starch' => 'نشاء معدّل',
        'rice flour' => 'دقيق الأرز',
        'rice' => 'أرز',
        'oats' => 'شوفان',
        'oat flakes' => 'رقائق الشوفان',
        'barley' => 'شعير',
        'barley malt' => 'مالت الشعير',
        'barley malt extract' => 'مستخلص مالت الشعير',
        'malt extract' => 'مستخلص المالت',
        'gluten' => 'جلوتين',
        'wheat gluten' => 'جلوتين القمح',

        // Oils & Fats
        'palm oil' => 'زيت النخيل',
        'palm fat' => 'دهن النخيل',
        'palm kernel oil' => 'زيت نواة النخيل',
        'vegetable oil' => 'زيت نباتي',
        'vegetable fat' => 'دهن نباتي',
        'vegetable fats' => 'دهون نباتية',
        'vegetable oils' => 'زيوت نباتية',
        'hydrogenated vegetable oil' => 'زيت نباتي مهدرج',
        'hydrogenated palm oil' => 'زيت نخيل مهدرج',
        'partially hydrogenated oil' => 'زيت مهدرج جزئياً',
        'sunflower oil' => 'زيت دوار الشمس',
        'canola oil' => 'زيت الكانولا',
        'rapeseed oil' => 'زيت بذور اللفت',
        'olive oil' => 'زيت الزيتون',
        'extra virgin olive oil' => 'زيت زيتون بكر ممتاز',
        'coconut oil' => 'زيت جوز الهند',
        'coconut fat' => 'دهن جوز الهند',
        'soybean oil' => 'زيت الصويا',
        'soya oil' => 'زيت الصويا',
        'corn oil' => 'زيت الذرة',

        // Gelling Agents, Gums & Thickeners
        'gelatin' => 'جيلاتين',
        'gelatine' => 'جيلاتين',
        'pork gelatin' => 'جيلاتين خنزيري',
        'porcine gelatin' => 'جيلاتين خنزيري',
        'beef gelatin' => 'جيلاتين بقري',
        'bovine gelatin' => 'جيلاتين بقري',
        'fish gelatin' => 'جيلاتين سمكي',
        'halal gelatin' => 'جيلاتين حلال',
        'pectin' => 'بكتين',
        'agar' => 'آغار',
        'agar-agar' => 'آغار آغار',
        'carrageenan' => 'كاراجينان',
        'guar gum' => 'صمغ الغوار',
        'xanthan gum' => 'صمغ الزانثان',
        'locust bean gum' => 'صمغ الخروب',
        'carob gum' => 'صمغ الخروب',
        'arabic gum' => 'صمغ عربي',
        'gum arabic' => 'صمغ عربي',
        'acacia gum' => 'صمغ الأكاسيا',
        'shellac' => 'شيلاك (صمغ اللك)',
        'beeswax' => 'شمع العسل',

        // Leavening & Baking
        'yeast' => 'خميرة',
        'baker\'s yeast' => 'خميرة الخباز',
        'dry yeast' => 'خميرة جافة',
        'active dry yeast' => 'خميرة جافة نشطة',
        'baking powder' => 'مسحوق الخبز (بيكنج بودر)',
        'baking soda' => 'بيكربونات الصوديوم',
        'sodium bicarbonate' => 'بيكربونات الصوديوم',
        'ammonium bicarbonate' => 'بيكربونات الأمونيوم',

        // Seasonings, Acids & Preservatives
        'salt' => 'ملح',
        'table salt' => 'ملح طعام',
        'sea salt' => 'ملح بحري',
        'water' => 'ماء',
        'citric acid' => 'حمض الستريك (ملح الليمون)',
        'ascorbic acid' => 'حمض الأسكوربيك (فيتامين ج)',
        'acetic acid' => 'حمض الأسيتيك (الخل)',
        'vinegar' => 'خل',
        'lactic acid' => 'حمض اللاكتيك',
        'sorbic acid' => 'حمض السوربيك',
        'potassium sorbate' => 'سوربات البوتاسيوم',
        'sodium benzoate' => 'بنزوات الصوديوم',
        'benzoic acid' => 'حمض البنزويك',
        'calcium carbonate' => 'كربونات الكالسيوم',
        'calcium chloride' => 'كلوريد الكالسيوم',
        'l-cysteine' => 'إل-سيستين',
        'monosodium glutamate' => 'جلوتامات أحادية الصوديوم (MSG)',
        'msg' => 'جلوتامات أحادية الصوديوم (MSG)',
        'flavor enhancer' => 'معزز نكهة',
        'flavour enhancer' => 'معزز نكهة',
        'preservative' => 'مادة حافظة',
        'preservatives' => 'مواد حافظة',
        'acidity regulator' => 'منظم حموضة',
        'stabilizer' => 'مثبت',
        'stabilizers' => 'مثبتات',
        'antioxidant' => 'مضاد أكسدة',
        'antioxidants' => 'مضادات أكسدة',
        'thickener' => 'مثخن قوام',
        'color' => 'لون غذائي',
        'colour' => 'لون غذائي',
        'natural color' => 'لون طبيعي',
        'natural colour' => 'لون طبيعي',

        // Eggs & Animal Products
        'egg' => 'بيض',
        'eggs' => 'بيض',
        'whole egg' => 'بيض كامل',
        'egg white' => 'بياض البيض',
        'egg yolk' => 'صفار البيض',
        'egg powder' => 'مسحوق البيض',
        'pork' => 'لحم خنزير',
        'lard' => 'شحم الخنزير (دهن خنزيري)',
        'bacon' => 'لحم خنزير مقدد',
        'ham' => 'لحم خنزير',
        'pork fat' => 'دهن الخنزير',
        'beef' => 'لحم بقري',
        'tallow' => 'شحم حيواني (ودك)',
        'animal fat' => 'دهن حيواني',
        'chicken' => 'لحم دجاج',
        'lamb' => 'لحم ضأن',
        'mutton' => 'لحم غنم',
        'fish' => 'سمك',
        'tuna' => 'تونة',
        'salmon' => 'سلمون',
        'rennet' => 'منفحة',
        'animal rennet' => 'منفحة حيوانية',
        'microbial rennet' => 'منفحة ميكروبية',
        'pepsin' => 'بيبسين',
    ];

    /**
     * E-number registry mapping to Arabic name.
     */
    private static array $eNumbers = [
        'E100' => 'كركمين',
        'E101' => 'ريبوفلافين (فيتامين ب2)',
        'E102' => 'تارترازين (أصفر)',
        'E110' => 'أصفر غروب الشمس',
        'E120' => 'كوشينيل (كارمين قرمزي)',
        'E122' => 'أزوروبين (كارموزين)',
        'E124' => 'بونصو 4R (أحمر)',
        'E129' => 'أحمر ألورا AC',
        'E132' => 'إنديجوتين (أزرق)',
        'E133' => 'أزرق لامع FCF',
        'E140' => 'كلوروفيل',
        'E141' => 'معقدات نحاسية للكلوروفيل',
        'E150a' => 'كراميل بسيط',
        'E150b' => 'كراميل الكبريتيت',
        'E150c' => 'كراميل الأمونيا',
        'E150d' => 'كراميل أمونيا الكبريتيت',
        'E150A' => 'كراميل بسيط',
        'E150B' => 'كراميل الكبريتيت',
        'E150C' => 'كراميل الأمونيا',
        'E150D' => 'كراميل أمونيا الكبريتيت',
        'E160a' => 'بيتا كاروتين',
        'E160b' => 'أناتو (أناتو بيكسين)',
        'E160c' => 'مستخلص البابريكا',
        'E160A' => 'بيتا كاروتين',
        'E160B' => 'أناتو (أناتو بيكسين)',
        'E160C' => 'مستخلص البابريكا',
        'E162' => 'أحمر الشمندر (بيتانين)',
        'E163' => 'أنثوسيانين',
        'E170' => 'كربونات الكالسيوم',
        'E171' => 'ثاني أكسيد التيتانيوم',
        'E200' => 'حمض السوربيك',
        'E202' => 'سوربات البوتاسيوم',
        'E211' => 'بنزوات الصوديوم',
        'E220' => 'ثاني أكسيد الكبريت',
        'E250' => 'نتريت الصوديوم',
        'E251' => 'نترات الصوديوم',
        'E300' => 'حمض الأسكوربيك (فيتامين ج)',
        'E306' => 'توكوفيرول (فيتامين هـ)',
        'E322' => 'ليسيثين',
        'E330' => 'حمض الستريك',
        'E407' => 'كاراجينان',
        'E410' => 'صمغ الخروب',
        'E412' => 'صمغ الغوار',
        'E414' => 'صمغ عربي',
        'E415' => 'صمغ الزانثان',
        'E420' => 'سوربيتول',
        'E422' => 'جلسرين (جلسرول)',
        'E428' => 'جيلاتين',
        'E440' => 'بكتين',
        'E442' => 'فوسفاتيد الأمونيوم',
        'E471' => 'أحادي وثنائي الجليسريدات',
        'E472a' => 'إسترات حمض الخليك لأحادي وثنائي الجليسريدات',
        'E472b' => 'إسترات حمض اللاكتيك لأحادي وثنائي الجليسريدات',
        'E472c' => 'إسترات حمض الستريك لأحادي وثنائي الجليسريدات',
        'E472e' => 'إسترات حمض الطرطريك ثنائي الأسيتيل',
        'E475' => 'إسترات بولي جلسرين للأحماض الدهنية',
        'E476' => 'بولي جلسرين بولي ريسينوليات (PGPR)',
        'E481' => 'لاكتيلات ستيرويل الصوديوم',
        'E500' => 'كربونات الصوديوم',
        'E500ii' => 'بيكربونات الصوديوم',
        'E503' => 'كربونات الأمونيوم',
        'E621' => 'جلوتامات أحادية الصوديوم (MSG)',
        'E901' => 'شمع العسل',
        'E904' => 'شيلاك',
        'E920' => 'إل-سيستين',
        'E951' => 'أسبارتام',
        'E955' => 'سكرالوز',
        'E965' => 'مالتيتول',
    ];

    /**
     * Translates an ingredient string to natural Arabic.
     *
     * @param string $ingredient Raw or normalized ingredient text
     * @return string Arabic localization
     */
    public static function translateToArabic(string $ingredient): string
    {
        $raw = trim($ingredient);
        if ($raw === '') {
            return '';
        }

        // 1. If text already contains Arabic characters, keep it as-is
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $raw)) {
            return $raw;
        }

        // 2. Check for E-number presence (e.g. E476, E471, E-471, E160a)
        if (preg_match('/\bE-?(\d{3,4})([a-z]?)\b/i', $raw, $eMatches)) {
            $eNum = $eMatches[1];
            $eSuffix = strtolower($eMatches[2] ?? '');
            $eCode = 'E' . $eNum . $eSuffix;
            $eDesc = self::$eNumbers[$eCode] ?? self::$eNumbers['E' . $eNum . strtoupper($eSuffix)] ?? self::$eNumbers['E' . $eNum] ?? null;

            // Check if there is an outer qualifier like "emulsifier (E476)" or "flavor enhancer (E621)"
            $cleanedPrefix = trim((string) preg_replace('/\s*\(?\bE-?\d{3,4}[a-z]?\b\)?/i', '', $raw));
            $prefixAr = '';
            if ($cleanedPrefix !== '') {
                $cleanedKey = strtolower(trim($cleanedPrefix, " \t\n\r\0\x0B:,()-"));
                if (isset(self::$dictionary[$cleanedKey])) {
                    $prefixAr = self::$dictionary[$cleanedKey];
                }
            }

            if ($prefixAr !== '') {
                return $eDesc ? "{$prefixAr} ({$eCode} - {$eDesc})" : "{$prefixAr} ({$eCode})";
            }

            return $eDesc ? "{$eCode} ({$eDesc})" : $eCode;
        }

        // 3. Normalize for dictionary lookup
        $normalized = strtolower($raw);
        $normalized = trim($normalized, " \t\n\r\0\x0B*.,:;[]()");

        // Strip trailing percentage e.g. "Sugar (45%)" -> "Sugar"
        $normalizedNoPct = preg_replace('/\s*\(\s*\d+(\.\d+)?%\s*\)/', '', $normalized);
        $normalizedNoPct = trim((string) $normalizedNoPct);

        // Direct dictionary match
        if (isset(self::$dictionary[$normalizedNoPct])) {
            return self::$dictionary[$normalizedNoPct];
        }
        if (isset(self::$dictionary[$normalized])) {
            return self::$dictionary[$normalized];
        }

        // 4. Handle common compound patterns e.g. "emulsifier (soya lecithin)"
        if (preg_match('/^(emulsifier|emulsifiers|flavoring|flavouring|color|colour|preservative|stabilizer)\s*\((.+)\)$/i', $raw, $subMatches)) {
            $outer = self::translateToArabic($subMatches[1]);
            $inner = self::translateToArabic($subMatches[2]);
            return "{$outer} ({$inner})";
        }

        // Handle "Whey powder (from milk)"
        if (preg_match('/^(.+?)\s*\((from\s+milk|contains\s+.+)\)$/i', $raw, $subMatches)) {
            $main = self::translateToArabic($subMatches[1]);
            if (stripos($subMatches[2], 'milk') !== false) {
                return "{$main} (من الحليب)";
            }
            return $main;
        }

        // Fallback: Return original string cleanly
        return $raw;
    }

    /**
     * Enriches Agent 1 structured vision result with Arabic and English localized names.
     * Original text in packaging is preserved 100% verbatim in source_text.
     *
     * @param array<string, mixed> $visionResult
     * @return array<string, mixed>
     */
    public static function localizeVisionResult(array $visionResult): array
    {
        if (!isset($visionResult['ingredients']) || !is_array($visionResult['ingredients'])) {
            return $visionResult;
        }

        $localizedIngredients = [];
        foreach ($visionResult['ingredients'] as $item) {
            if (is_string($item)) {
                $orig = $item;
                $norm = $item;
                $conf = null;
            } elseif (is_array($item)) {
                $orig = (string) ($item['original_name'] ?? ($item['name'] ?? ''));
                $norm = (string) ($item['normalized_name'] ?? $orig);
                $conf = isset($item['confidence']) && is_numeric($item['confidence']) ? (float) $item['confidence'] : null;
            } else {
                continue;
            }

            $nameAr = self::translateToArabic($norm ?: $orig);
            $nameEn = $norm ?: $orig;

            $entry = [
                'original_name'   => $orig,
                'normalized_name' => $norm,
                'name_ar'         => $nameAr,
                'name_en'         => $nameEn,
            ];

            if ($conf !== null) {
                $entry['confidence'] = $conf;
            }

            $localizedIngredients[] = $entry;
        }

        $visionResult['ingredients'] = $localizedIngredients;
        return $visionResult;
    }

    /**
     * Enriches Agent 2 verification result with localized ingredient names.
     *
     * @param array<string, mixed> $halalResult
     * @return array<string, mixed>
     */
    public static function localizeHalalResult(array $halalResult): array
    {
        if (!isset($halalResult['ingredient_assessments']) || !is_array($halalResult['ingredient_assessments'])) {
            return $halalResult;
        }

        $localizedAssessments = [];
        foreach ($halalResult['ingredient_assessments'] as $assessment) {
            if (!is_array($assessment)) {
                continue;
            }

            $rawIng = (string) ($assessment['ingredient'] ?? '');
            $assessment['ingredient_ar'] = self::translateToArabic($rawIng);
            $assessment['ingredient_en'] = $rawIng;

            $localizedAssessments[] = $assessment;
        }

        $halalResult['ingredient_assessments'] = $localizedAssessments;
        return $halalResult;
    }
}
