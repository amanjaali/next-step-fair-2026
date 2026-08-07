<?php

namespace Database\Seeders;

use App\Models\Field;
use App\Models\Sector;
use Illuminate\Database\Seeder;

/**
 * The shared vocabulary, in all three languages.
 *
 * This is the single most important table in the reporting story: it is the only
 * reason a student's "I want to study nursing" and a university's "BSc Nursing"
 * end up on the same row of a chart. The slugs are stable identifiers — translate
 * the names freely, never rename a slug.
 */
class TaxonomySeeder extends Seeder
{
    /** slug => [en, ku, ar] */
    private const SECTORS = [
        'health' => ['Health & Medicine', 'تەندروستی و پزیشکی', 'الصحة والطب'],
        'engineering' => ['Engineering', 'ئەندازیاری', 'الهندسة'],
        'computing' => ['Computing & IT', 'کۆمپیوتەر و تەکنەلۆژیای زانیاری', 'الحاسوب وتقنية المعلومات'],
        'business' => ['Business & Economics', 'بازرگانی و ئابووری', 'إدارة الأعمال والاقتصاد'],
        'law_politics' => ['Law & Politics', 'یاسا و سیاسەت', 'القانون والسياسة'],
        'education' => ['Education', 'پەروەردە', 'التربية'],
        'sciences' => ['Natural Sciences', 'زانستە سروشتییەکان', 'العلوم الطبيعية'],
        'humanities' => ['Humanities & Languages', 'زانستە مرۆڤایەتییەکان و زمان', 'العلوم الإنسانية واللغات'],
        'media_arts' => ['Media & Arts', 'میدیا و هونەر', 'الإعلام والفنون'],
        'agriculture' => ['Agriculture & Veterinary', 'کشتوکاڵ و ڤێتێرنەری', 'الزراعة والبيطرة'],
        'tourism' => ['Tourism & Hospitality', 'گەشتیاری و میوانداری', 'السياحة والضيافة'],
        'vocational' => ['Vocational & Technical', 'پیشەیی و تەکنیکی', 'المهني والتقني'],
    ];

    /** slug => [en, ku, ar] */
    private const FIELDS = [
        // Health
        'medicine' => ['Medicine', 'پزیشکی', 'الطب'],
        'dentistry' => ['Dentistry', 'ددانسازی', 'طب الأسنان'],
        'pharmacy' => ['Pharmacy', 'دەرمانسازی', 'الصيدلة'],
        'nursing' => ['Nursing', 'پەرستاری', 'التمريض'],
        'public_health' => ['Public Health', 'تەندروستی گشتی', 'الصحة العامة'],
        'medical_laboratory' => ['Medical Laboratory Science', 'زانستی تاقیگەی پزیشکی', 'علوم المختبرات الطبية'],
        'physiotherapy' => ['Physiotherapy', 'چارەسەری فیزیکی', 'العلاج الطبيعي'],

        // Engineering
        'civil' => ['Civil Engineering', 'ئەندازیاری شارستانی', 'الهندسة المدنية'],
        'mechanical' => ['Mechanical Engineering', 'ئەندازیاری میکانیک', 'الهندسة الميكانيكية'],
        'electrical' => ['Electrical Engineering', 'ئەندازیاری کارەبا', 'الهندسة الكهربائية'],
        'petroleum' => ['Petroleum Engineering', 'ئەندازیاری نەوت', 'هندسة النفط'],
        'architecture' => ['Architecture', 'تەلارسازی', 'العمارة'],
        'chemical' => ['Chemical Engineering', 'ئەندازیاری کیمیا', 'الهندسة الكيميائية'],
        'industrial' => ['Industrial Engineering', 'ئەندازیاری پیشەسازی', 'الهندسة الصناعية'],
        'mechatronics' => ['Mechatronics', 'میکاترۆنیک', 'الميكاترونكس'],

        // Computing
        'computer_science' => ['Computer Science', 'زانستی کۆمپیوتەر', 'علوم الحاسوب'],
        'software_engineering' => ['Software Engineering', 'ئەندازیاری سۆفتوێر', 'هندسة البرمجيات'],
        'information_technology' => ['Information Technology', 'تەکنەلۆژیای زانیاری', 'تقنية المعلومات'],
        'cybersecurity' => ['Cybersecurity', 'ئاسایشی سایبەری', 'الأمن السيبراني'],
        'data_science' => ['Data Science', 'زانستی داتا', 'علم البيانات'],
        'artificial_intelligence' => ['Artificial Intelligence', 'ژیری دەستکرد', 'الذكاء الاصطناعي'],

        // Business
        'business_administration' => ['Business Administration', 'کارگێڕی بازرگانی', 'إدارة الأعمال'],
        'accounting' => ['Accounting', 'ژمێریاری', 'المحاسبة'],
        'finance' => ['Finance', 'دارایی', 'المالية'],
        'banking' => ['Banking', 'بانکداری', 'الأعمال المصرفية'],
        'marketing' => ['Marketing', 'بازاڕگەری', 'التسويق'],
        'management' => ['Management', 'بەڕێوەبردن', 'الإدارة'],
        'economics' => ['Economics', 'ئابووری', 'الاقتصاد'],
        'logistics' => ['Logistics & Supply Chain', 'لۆجستیک و زنجیرەی دابینکردن', 'اللوجستيات وسلسلة التوريد'],

        // Law & politics
        'law' => ['Law', 'یاسا', 'القانون'],
        'political_science' => ['Political Science', 'زانستی سیاسی', 'العلوم السياسية'],
        'international_relations' => ['International Relations', 'پەیوەندییە نێودەوڵەتییەکان', 'العلاقات الدولية'],
        'public_administration' => ['Public Administration', 'کارگێڕی گشتی', 'الإدارة العامة'],

        // Education
        'primary_education' => ['Primary Education', 'پەروەردەی بنەڕەتی', 'التعليم الابتدائي'],
        'special_education' => ['Special Education', 'پەروەردەی تایبەت', 'التربية الخاصة'],
        'educational_psychology' => ['Educational Psychology', 'دەروونزانی پەروەردەیی', 'علم النفس التربوي'],
        'physical_education' => ['Physical Education', 'پەروەردەی وەرزشی', 'التربية الرياضية'],

        // Sciences
        'biology' => ['Biology', 'زیندەزانی', 'علم الأحياء'],
        'chemistry' => ['Chemistry', 'کیمیا', 'الكيمياء'],
        'physics' => ['Physics', 'فیزیا', 'الفيزياء'],
        'mathematics' => ['Mathematics', 'بیرکاری', 'الرياضيات'],
        'statistics' => ['Statistics', 'ئامار', 'الإحصاء'],
        'environmental_science' => ['Environmental Science', 'زانستی ژینگە', 'علوم البيئة'],
        'geology' => ['Geology', 'زەویناسی', 'الجيولوجيا'],

        // Humanities
        'english_language' => ['English Language', 'زمانی ئینگلیزی', 'اللغة الإنجليزية'],
        'kurdish_language' => ['Kurdish Language', 'زمانی کوردی', 'اللغة الكردية'],
        'arabic_language' => ['Arabic Language', 'زمانی عەرەبی', 'اللغة العربية'],
        'history' => ['History', 'مێژوو', 'التاريخ'],
        'sociology' => ['Sociology', 'کۆمەڵناسی', 'علم الاجتماع'],
        'psychology' => ['Psychology', 'دەروونزانی', 'علم النفس'],
        'philosophy' => ['Philosophy', 'فەلسەفە', 'الفلسفة'],
        'translation' => ['Translation', 'وەرگێڕان', 'الترجمة'],

        // Media & arts
        'journalism' => ['Journalism', 'ڕۆژنامەوانی', 'الصحافة'],
        'media_communication' => ['Media & Communication', 'میدیا و پەیوەندی', 'الإعلام والاتصال'],
        'graphic_design' => ['Graphic Design', 'دیزاینی گرافیک', 'التصميم الجرافيكي'],
        'fine_arts' => ['Fine Arts', 'هونەرە جوانەکان', 'الفنون الجميلة'],
        'music' => ['Music', 'مۆسیقا', 'الموسيقى'],
        'film_production' => ['Film Production', 'بەرهەمهێنانی فیلم', 'إنتاج الأفلام'],

        // Agriculture
        'agricultural_engineering' => ['Agricultural Engineering', 'ئەندازیاری کشتوکاڵی', 'الهندسة الزراعية'],
        'food_science' => ['Food Science', 'زانستی خۆراک', 'علوم الأغذية'],
        'veterinary' => ['Veterinary Medicine', 'پزیشکی ڤێتێرنەری', 'الطب البيطري'],
        'animal_production' => ['Animal Production', 'بەرهەمهێنانی ئاژەڵ', 'الإنتاج الحيواني'],
        'horticulture' => ['Horticulture', 'باخداری', 'البستنة'],

        // Tourism
        'tourism_management' => ['Tourism Management', 'بەڕێوەبردنی گەشتیاری', 'إدارة السياحة'],
        'hospitality' => ['Hospitality Management', 'بەڕێوەبردنی میوانداری', 'إدارة الضيافة'],
        'culinary_arts' => ['Culinary Arts', 'هونەری چێشتلێنان', 'فنون الطهي'],
        'aviation' => ['Aviation', 'ئاسمانەوانی', 'الطيران'],

        // Vocational
        'technical_trades' => ['Technical Trades', 'پیشە تەکنیکییەکان', 'المهن التقنية'],
        'nursing_diploma' => ['Nursing Diploma', 'دیپلۆمی پەرستاری', 'دبلوم التمريض'],
        'it_support' => ['IT Support', 'پاڵپشتی IT', 'الدعم التقني'],
        'accounting_technician' => ['Accounting Technician', 'تەکنیکاری ژمێریاری', 'فني محاسبة'],
        'construction_technology' => ['Construction Technology', 'تەکنەلۆژیای بیناسازی', 'تكنولوجيا البناء'],
    ];

    public function run(): void
    {
        $sectorSort = 0;

        foreach (config('taxonomy.sectors') as $sectorSlug => $fieldSlugs) {
            [$en, $ku, $ar] = self::SECTORS[$sectorSlug];

            $sector = Sector::updateOrCreate(
                ['slug' => $sectorSlug],
                ['name' => ['en' => $en, 'ku' => $ku, 'ar' => $ar], 'sort' => $sectorSort++]
            );

            $fieldSort = 0;

            foreach ($fieldSlugs as $fieldSlug) {
                [$fen, $fku, $far] = self::FIELDS[$fieldSlug];

                Field::updateOrCreate(
                    ['slug' => $fieldSlug],
                    [
                        'sector_id' => $sector->id,
                        'name' => ['en' => $fen, 'ku' => $fku, 'ar' => $far],
                        'sort' => $fieldSort++,
                        'active' => true,
                    ]
                );
            }
        }
    }
}
