<?php

namespace Database\Seeders;

use App\Models\Download;
use App\Models\FeatureCard;
use App\Models\SdgGoal;
use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * SDG goals, the "why attend" cards, downloads and the small settings an editor
 * can change without a deploy. Copy is carried over from the approved design.
 */
class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->sdgGoals();
        $this->whyAttendCards();
        $this->downloads();
        $this->settings();
    }

    private function sdgGoals(): void
    {
        $goals = [
            [
                'number' => 4, 'color' => '#C5192D', 'figure' => '14,600', 'sort' => 1,
                'title' => [
                    'en' => 'Quality education',
                    'ku' => 'خوێندنی چاک',
                    'ar' => 'التعليم الجيد',
                ],
                'what' => [
                    'en' => 'Widening who gets to ask the questions',
                    'ku' => 'فراوانکردنی ئەوەی کێ دەتوانێت پرسیار بکات',
                    'ar' => 'توسيع دائرة من يستطيع أن يسأل',
                ],
                'detail' => [
                    'en' => 'Free entry, school shuttles from 12 districts, and Zankoline application desks staffed for all three days. Digital literacy and AI sessions run in the workshop hall.',
                    'ku' => 'چوونەژوورەوەی بەخۆڕایی، پاسی قوتابخانە لە 12 ناوچە، و مێزی یارمەتی زانکۆلاین بە درێژایی هەر سێ ڕۆژ. دانیشتنی زانستی دیجیتاڵ و AI لە هۆڵی وۆرکشۆپ بەڕێوە دەچن.',
                    'ar' => 'دخول مجاني، وحافلات مدرسية من 12 قضاء، ومكاتب دعم زانكۆلاین طوال الأيام الثلاثة. تُقام جلسات المهارات الرقمية والذكاء الاصطناعي في قاعة ورش العمل.',
                ],
                'metric' => [
                    'en' => '14,600 students reached in 2025',
                    'ku' => '14,600 قوتابی لە 2025 گەیشتوونەتێ',
                    'ar' => 'وصلنا إلى 14,600 طالب في 2025',
                ],
            ],
            [
                'number' => 8, 'color' => '#A21942', 'figure' => '38', 'sort' => 2,
                'title' => [
                    'en' => 'Decent work & growth',
                    'ku' => 'کاری شایستە و گەشە',
                    'ar' => 'العمل اللائق والنمو',
                ],
                'what' => [
                    'en' => 'Skills that employers name, not guess at',
                    'ku' => 'ئەو شارەزاییانەی خاوەنکار ناویان دەبات، نەک پێشبینییان دەکات',
                    'ar' => 'مهارات يسمّيها أصحاب العمل، لا يخمّنونها',
                ],
                'detail' => [
                    'en' => 'Career guidance and CV clinics are delivered by employers in the room, with vocational pathways given equal floor space to academic ones.',
                    'ku' => 'ڕێنمایی پیشەیی و کلینیکی CV لەلایەن خاوەنکارانەوە پێشکەش دەکرێن، و ڕێڕەوە پیشەییەکان هەمان شوێنیان پێدەدرێت وەک ئەکادیمییەکان.',
                    'ar' => 'يقدّم أصحاب العمل الإرشاد المهني وعيادات السيرة الذاتية داخل القاعة، مع منح المسارات المهنية مساحة مساوية للمسارات الأكاديمية.',
                ],
                'metric' => [
                    'en' => '38 employer-led career sessions',
                    'ku' => '38 دانیشتنی پیشەیی بەڕێوەبراو لەلایەن خاوەنکاران',
                    'ar' => '38 جلسة مهنية يقودها أصحاب العمل',
                ],
            ],
            [
                'number' => 9, 'color' => '#FD6925', 'figure' => '11', 'sort' => 3,
                'title' => [
                    'en' => 'Industry & innovation',
                    'ku' => 'پیشەسازی و داهێنان',
                    'ar' => 'الصناعة والابتكار',
                ],
                'what' => [
                    'en' => 'Technology as infrastructure, not decoration',
                    'ku' => 'تەکنەلۆژیا وەک ژێرخان، نەک ڕازاندنەوە',
                    'ar' => 'التقنية كبنية تحتية لا كزينة',
                ],
                'detail' => [
                    'en' => 'Registration, badging and check-in run digitally end to end. Partners demonstrate applied AI and engineering work students can enter.',
                    'ku' => 'تۆمارکردن، باج و چوونەژوورەوە سەرتاسەری بە دیجیتاڵ کار دەکەن. هاوبەشەکان کاری AI و ئەندازیاری پراکتیکی پیشان دەدەن کە قوتابیان دەتوانن بچنە ناوی.',
                    'ar' => 'التسجيل وإصدار البطاقات وتسجيل الدخول تعمل رقمياً من طرف إلى طرف. ويعرض الشركاء تطبيقات الذكاء الاصطناعي والهندسة التي يمكن للطلبة دخولها.',
                ],
                'metric' => [
                    'en' => '11 technology partners',
                    'ku' => '11 هاوبەشی تەکنەلۆژیا',
                    'ar' => '11 شريكاً تقنياً',
                ],
            ],
            [
                'number' => 13, 'color' => '#3F7E44', 'figure' => '62%', 'sort' => 4,
                'title' => [
                    'en' => 'Climate action',
                    'ku' => 'کرداری کەشوهەوا',
                    'ar' => 'العمل المناخي',
                ],
                'what' => [
                    'en' => 'A fair that does not print itself',
                    'ku' => 'پێشانگایەک کە خۆی چاپ ناکات',
                    'ar' => 'معرض لا يطبع نفسه',
                ],
                'detail' => [
                    'en' => 'Digital-first materials, reusable signage substrates, and a published carbon footprint statement for each edition.',
                    'ku' => 'کەرەستەی دیجیتاڵ لە پێشەوە، بنکەی بانەری دووبارە بەکارهاتوو، و بڵاوکردنەوەی ڕاگەیاندنی شوێنپێی کاربۆن بۆ هەر خولێک.',
                    'ar' => 'مواد رقمية أولاً، ولافتات قابلة لإعادة الاستخدام، وبيان معلن للبصمة الكربونية لكل دورة.',
                ],
                'metric' => [
                    'en' => '62% less printed material than 2024',
                    'ku' => '62% کەمتر کەرەستەی چاپکراو لە 2024',
                    'ar' => 'انخفاض المواد المطبوعة 62% مقارنة بـ 2024',
                ],
            ],
            [
                'number' => 17, 'color' => '#19486A', 'figure' => '24', 'sort' => 5,
                'title' => [
                    'en' => 'Partnerships',
                    'ku' => 'هاوبەشییەکان',
                    'ar' => 'الشراكات',
                ],
                'what' => [
                    'en' => 'Ministries, universities and industry at one table',
                    'ku' => 'وەزارەت، زانکۆ و پیشەسازی لەسەر یەک مێز',
                    'ar' => 'الوزارات والجامعات والقطاع الصناعي على طاولة واحدة',
                ],
                'detail' => [
                    'en' => 'The fair is convened with the Ministry of Higher Education and Scientific Research alongside universities, NGOs and private sector partners.',
                    'ku' => 'پێشانگاکە لەگەڵ وەزارەتی خوێندنی باڵا و توێژینەوەی زانستی، لەگەڵ زانکۆکان، ڕێکخراوە نەحکومییەکان و هاوبەشانی کەرتی تایبەت ڕێک دەخرێت.',
                    'ar' => 'يُعقد المعرض بالتعاون مع وزارة التعليم العالي والبحث العلمي إلى جانب الجامعات والمنظمات غير الحكومية وشركاء القطاع الخاص.',
                ],
                'metric' => [
                    'en' => '24 institutional partnerships',
                    'ku' => '24 هاوبەشی دامەزراوەیی',
                    'ar' => '24 شراكة مؤسسية',
                ],
            ],
        ];

        foreach ($goals as $goal) {
            SdgGoal::updateOrCreate(['number' => $goal['number']], $goal);
        }
    }

    private function whyAttendCards(): void
    {
        // Icons are bar compositions from the logo's Step element: right angles
        // only, one magenta bar each. `icon_path` is ink, `icon_accent` magenta.
        $cards = [
            [
                'sort' => 1,
                'icon_path' => 'M0 2h24v4H0z M0 10h16v4H0z',
                'icon_accent' => 'M0 18h9v4H0z',
                'title' => [
                    'en' => 'Finish your Zankoline application',
                    'ku' => 'فۆرمی زانکۆلاینەکەت تەواو بکە',
                    'ar' => 'أكمل استمارة زانكۆلاین',
                ],
                'body' => [
                    'en' => 'Staffed desks walk you through the form, document by document, until it is submitted.',
                    'ku' => 'مێزە ستافدارەکان فۆرمەکەت پێدەبەن، بەڵگەنامە بە بەڵگەنامە، تا ناردنی.',
                    'ar' => 'مكاتب مزوّدة بموظفين ترافقك في الاستمارة، مستنداً تلو الآخر، حتى إرسالها.',
                ],
            ],
            [
                'sort' => 2,
                'icon_path' => 'M0 4h5v16H0z M7.5 4h5v16h-5z',
                'icon_accent' => 'M15 4h5v16h-5z',
                'title' => [
                    'en' => 'Meet admission officers',
                    'ku' => 'بەرپرسانی وەرگرتن ببینە',
                    'ar' => 'التق بمسؤولي القبول',
                ],
                'body' => [
                    'en' => 'Ask about entry scores, tuition and transfers to the person who decides.',
                    'ku' => 'دەربارەی نمرەی وەرگرتن، کرێی خوێندن و گواستنەوە لەو کەسە بپرسە کە بڕیار دەدات.',
                    'ar' => 'اسأل عن درجات القبول والرسوم والتحويل من الشخص الذي يقرر.',
                ],
            ],
            [
                'sort' => 3,
                'icon_path' => 'M0 2h10v9H0z M13 2h10v9H13z M13 14h10v9H13z',
                'icon_accent' => 'M0 14h10v9H0z',
                'title' => [
                    'en' => 'Workshops and panels',
                    'ku' => 'وۆرکشۆپ و پانێلەکان',
                    'ar' => 'ورش عمل وجلسات حوارية',
                ],
                'body' => [
                    'en' => 'CV clinics, interview practice, and graduates explaining what they actually chose.',
                    'ku' => 'کلینیکی CV، ڕاهێنانی چاوپێکەوتن، و دەرچووان ڕوونی دەکەنەوە بەڕاستی چییان هەڵبژارد.',
                    'ar' => 'عيادات السيرة الذاتية، وتدريب على المقابلات، وخريجون يشرحون ما اختاروه فعلاً.',
                ],
            ],
            [
                'sort' => 4,
                'icon_path' => 'M0 5h14v4H0z M0 15h14v4H0z',
                'icon_accent' => 'M18 5h6v14h-6z',
                'title' => [
                    'en' => 'Scholarships and discounts',
                    'ku' => 'سکۆلەرشیپ و داشکاندن',
                    'ar' => 'منح وحسومات',
                ],
                'body' => [
                    'en' => 'Named programmes with real deadlines, plus fee reductions offered at the fair only.',
                    'ku' => 'پرۆگرامی ناودار لەگەڵ کۆتا کاتی ڕاستەقینە، لەگەڵ کەمکردنەوەی کرێ کە تەنها لە پێشانگادا پێشکەش دەکرێت.',
                    'ar' => 'برامج محددة بالاسم بمواعيد نهائية حقيقية، إضافة إلى حسومات تُمنح داخل المعرض فقط.',
                ],
            ],
            [
                'sort' => 5,
                'icon_path' => 'M0 16h6v7H0z M9 9h6v14H9z',
                'icon_accent' => 'M18 2h6v21h-6z',
                'title' => [
                    'en' => 'Career guidance',
                    'ku' => 'ڕێنمایی پیشەیی',
                    'ar' => 'إرشاد مهني',
                ],
                'body' => [
                    'en' => 'Employers describe the roles they are hiring for and the skills they screen on.',
                    'ku' => 'خاوەنکاران باسی ئەو پۆستانە دەکەن کە دایاندەمەزرێنن و ئەو شارەزاییانەی پێیان هەڵدەسەنگێنن.',
                    'ar' => 'يصف أصحاب العمل الوظائف التي يوظفون لها والمهارات التي يفرزون على أساسها.',
                ],
            ],
            [
                'sort' => 6,
                'icon_path' => 'M0 2h10v4H0z M14 10h10v4H14z',
                'icon_accent' => 'M0 18h10v4H0z',
                'title' => [
                    'en' => 'Networking',
                    'ku' => 'دروستکردنی پەیوەندی',
                    'ar' => 'بناء العلاقات',
                ],
                'body' => [
                    'en' => 'Students, teachers, universities and employers in one hall for three days.',
                    'ku' => 'قوتابی، مامۆستا، زانکۆ و خاوەنکار لە یەک هۆڵدا بۆ سێ ڕۆژ.',
                    'ar' => 'طلبة ومعلمون وجامعات وأصحاب عمل في قاعة واحدة لثلاثة أيام.',
                ],
            ],
        ];

        foreach ($cards as $card) {
            FeatureCard::updateOrCreate(
                ['group' => 'why_attend', 'sort' => $card['sort']],
                $card + ['group' => 'why_attend']
            );
        }
    }

    private function downloads(): void
    {
        $items = [
            ['group' => 'press', 'sort' => 1, 'accent' => '#B64698', 'size_label' => 'ZIP, 4.2 MB',
                'kind' => ['en' => 'Logos', 'ku' => 'لۆگۆ', 'ar' => 'شعارات'],
                'name' => ['en' => 'Logo pack', 'ku' => 'پاکەتی لۆگۆ', 'ar' => 'حزمة الشعارات'],
                'description' => [
                    'en' => 'Primary lockup and wordmark, black and white, SVG and PNG, with the clear-space rule.',
                    'ku' => 'لۆگۆی سەرەکی و وشەنیشان، ڕەش و سپی، SVG و PNG، لەگەڵ یاسای بۆشایی.',
                    'ar' => 'الشعار الرئيسي والاسم المكتوب، بالأبيض والأسود، SVG وPNG، مع قاعدة المساحة الفارغة.',
                ]],
            ['group' => 'press', 'sort' => 2, 'accent' => '#2C4BE0', 'size_label' => 'ZIP, 380 MB', 'year' => 2025,
                'kind' => ['en' => 'Photography', 'ku' => 'وێنەگری', 'ar' => 'تصوير'],
                'name' => ['en' => '2025 photo set', 'ku' => 'کۆمەڵە وێنەی 2025', 'ar' => 'مجموعة صور 2025'],
                'description' => [
                    'en' => '60 high-resolution images from the 2025 edition, captioned and released.',
                    'ku' => '60 وێنەی خاوێن لە خولی 2025، بە سەردێڕ و ڕەزامەندی.',
                    'ar' => '60 صورة عالية الدقة من دورة 2025، مع تعليقات وتصاريح نشر.',
                ]],
            ['group' => 'press', 'sort' => 3, 'accent' => '#2C4BE0', 'size_label' => 'ZIP, 240 MB', 'year' => 2024,
                'kind' => ['en' => 'Photography', 'ku' => 'وێنەگری', 'ar' => 'تصوير'],
                'name' => ['en' => '2024 photo set', 'ku' => 'کۆمەڵە وێنەی 2024', 'ar' => 'مجموعة صور 2024'],
                'description' => [
                    'en' => '40 high-resolution images from the 2024 edition.',
                    'ku' => '40 وێنەی خاوێن لە خولی 2024.',
                    'ar' => '40 صورة عالية الدقة من دورة 2024.',
                ]],
            ['group' => 'press', 'sort' => 4, 'accent' => '#B64698', 'size_label' => 'PDF, 320 KB', 'year' => 2026,
                'kind' => ['en' => 'Document', 'ku' => 'بەڵگەنامە', 'ar' => 'مستند'],
                'name' => ['en' => 'Fact sheet 2026', 'ku' => 'پەڕەی زانیاری 2026', 'ar' => 'ورقة حقائق 2026'],
                'description' => [
                    'en' => 'Dates, venue, audience, programme and partner list on one page.',
                    'ku' => 'بەروار، شوێن، ئامادەبووان، پرۆگرام و لیستی هاوبەشەکان لە یەک لاپەڕەدا.',
                    'ar' => 'التواريخ والمكان والجمهور والبرنامج وقائمة الشركاء في صفحة واحدة.',
                ]],
            ['group' => 'report', 'sort' => 5, 'accent' => '#B64698', 'size_label' => 'PDF, 6.1 MB', 'year' => 2025,
                'kind' => ['en' => 'Document', 'ku' => 'بەڵگەنامە', 'ar' => 'مستند'],
                'name' => ['en' => 'Impact report 2025', 'ku' => 'ڕاپۆرتی کاریگەری 2025', 'ar' => 'تقرير الأثر 2025'],
                'description' => [
                    'en' => 'Attendance by city and day, session popularity and SDG indicators.',
                    'ku' => 'بەشداری بەپێی شار و ڕۆژ، بەناوبانگی دانیشتنەکان و پێوەرەکانی SDG.',
                    'ar' => 'الحضور بحسب المدينة واليوم، وإقبال الجلسات، ومؤشرات SDG.',
                ]],
            ['group' => 'report', 'sort' => 6, 'accent' => '#3F7E44', 'size_label' => 'PDF, 1.1 MB', 'year' => 2025,
                'kind' => ['en' => 'Document', 'ku' => 'بەڵگەنامە', 'ar' => 'مستند'],
                'name' => [
                    'en' => 'Carbon footprint statement 2025',
                    'ku' => 'ڕاگەیاندنی شوێنپێی کاربۆن 2025',
                    'ar' => 'بيان البصمة الكربونية 2025',
                ],
                'description' => [
                    'en' => 'Printed material, signage reuse, catering waste and travel for the 2025 edition.',
                    'ku' => 'کەرەستەی چاپکراو، دووبارە بەکارهێنانی بانەر، خۆڵی خواردن و گەشت بۆ خولی 2025.',
                    'ar' => 'المواد المطبوعة وإعادة استخدام اللافتات ونفايات الضيافة والسفر لدورة 2025.',
                ]],
            ['group' => 'press', 'sort' => 7, 'accent' => '#4A4B4D', 'size_label' => 'MP4, 620 MB', 'year' => 2025,
                'kind' => ['en' => 'Video', 'ku' => 'ڤیدیۆ', 'ar' => 'فيديو'],
                'name' => ['en' => 'B-roll 2025', 'ku' => 'ڤیدیۆی خاوی 2025', 'ar' => 'لقطات إضافية 2025'],
                'description' => [
                    'en' => 'Two minutes of unedited floor and ceremony footage, no music, 1080p.',
                    'ku' => 'دوو خولەک ڤیدیۆی دەستکاری نەکراوی هۆڵ و ڕێوڕەسم، بێ مۆسیقا، 1080p.',
                    'ar' => 'دقيقتان من لقطات القاعة والحفل دون مونتاج، بلا موسيقى، بدقة 1080p.',
                ]],
            ['group' => 'deck', 'sort' => 8, 'accent' => '#B64698', 'size_label' => 'PDF, 8.4 MB', 'year' => 2026,
                'kind' => ['en' => 'Document', 'ku' => 'بەڵگەنامە', 'ar' => 'مستند'],
                'name' => ['en' => 'Sponsorship deck 2026', 'ku' => 'پێشکەشکراوی سپۆنسەری 2026', 'ar' => 'عرض الرعاية 2026'],
                'description' => [
                    'en' => 'Tiers, deliverables, artwork deadlines and last year’s audience figures.',
                    'ku' => 'ئاستەکان، بەرهەمەکان، کۆتا کاتی ئارتوۆرک و ژمارەکانی ئامادەبووانی ساڵی پار.',
                    'ar' => 'الفئات والمخرجات ومواعيد التصاميم وأرقام جمهور العام الماضي.',
                ]],
            ['group' => 'floorplan', 'sort' => 9, 'accent' => '#2C4BE0', 'size_label' => 'PDF, A4', 'year' => 2026,
                'kind' => ['en' => 'Document', 'ku' => 'بەڵگەنامە', 'ar' => 'مستند'],
                'name' => ['en' => 'Floor plan (A4)', 'ku' => 'پلانی شوێن (A4)', 'ar' => 'مخطط القاعات (A4)'],
                'description' => [
                    'en' => 'Halls A–C, booth numbers and the four service points, printable.',
                    'ku' => 'هۆڵی A–C، ژمارەی ستاندەکان و چوار خاڵی خزمەتگوزاری، بۆ چاپکردن.',
                    'ar' => 'القاعات A–C وأرقام الأجنحة ونقاط الخدمة الأربع، قابل للطباعة.',
                ]],
        ];

        foreach ($items as $item) {
            Download::updateOrCreate(
                ['group' => $item['group'], 'sort' => $item['sort']],
                $item
            );
        }
    }

    private function settings(): void
    {
        Setting::put('counters', [
            'universities' => 32,
            'sessions' => 26,
            // Registered attendees is read live from the registrations table; this
            // value only seeds the figure shown before the first registration.
            'registered_baseline' => 0,
        ], 'home');

        Setting::put('green_practices', [
            'en' => [
                'Digital-first materials: agenda, badges and directories are all on phones.',
                'Reusable signage substrates, no edition-dated print on anything reusable.',
                'Waste separation at every hall entrance, no single-use plastics at catering.',
                'Published carbon footprint statement for each edition.',
            ],
            'ku' => [
                'کەرەستەی دیجیتاڵ لە پێشەوە: خشتە، باج و ڕێنماییەکان هەموویان لەسەر مۆبایلن.',
                'بنکەی بانەری دووبارە بەکارهاتوو، هیچ چاپێکی بەروارداری خول لەسەر شتی دووبارە بەکارهاتوو نییە.',
                'جیاکردنەوەی خۆڵ لە هەموو دەروازەیەکی هۆڵ، هیچ پلاستیکی یەکجارەکی لە خواردندا نییە.',
                'بڵاوکردنەوەی ڕاگەیاندنی شوێنپێی کاربۆن بۆ هەر خولێک.',
            ],
            'ar' => [
                'مواد رقمية أولاً: البرنامج والبطاقات والأدلة كلها على الهاتف.',
                'ركائز لافتات قابلة لإعادة الاستخدام، وبلا طباعة مؤرخة على أي شيء يُعاد استخدامه.',
                'فرز النفايات عند كل مدخل قاعة، وبلا بلاستيك أحادي الاستخدام في الضيافة.',
                'بيان معلن للبصمة الكربونية لكل دورة.',
            ],
        ], 'sdg');

        Setting::put('access_notes', [
            'en' => [
                'Gate A on Salim Street is the main entrance and is step-free. Wheelchairs are available at registration.',
                'Gate B is reserved for conference delegations on Day 1, with protocol staff from 08:15.',
                'Free parking for 400 cars behind Hall C; school shuttles drop at Gate A on Day 2 and Day 3.',
                'Every zone is marked with a letter and an icon as well as a colour, and panels are mounted at 1.4–1.6 m.',
            ],
            'ku' => [
                'دەروازەی A لە شەقامی سالم دەروازەی سەرەکییە و بێ پلیکانەیە. کورسی چەرخدار لە شوێنی تۆمارکردن بەردەستە.',
                'دەروازەی B لە ڕۆژی یەکەم بۆ شاندەکانی کۆنفرانس تەرخانکراوە، لەگەڵ ستافی پرۆتۆکۆل لە 08:15.',
                'پارکینگی بەخۆڕایی بۆ 400 ئۆتۆمبێل لە پشتی هۆڵی C؛ پاسی قوتابخانەکان لە ڕۆژی دووەم و سێیەم لە دەروازەی A دادەبەزێنن.',
                'هەر ناوچەیەک بە پیت و ئایکۆن و ڕەنگ نیشانکراوە، و تابلۆکان لە بەرزی 1.4–1.6 م دانراون.',
            ],
            'ar' => [
                'البوابة A في شارع سالم هي المدخل الرئيسي وخالية من الدرج. الكراسي المتحركة متاحة عند التسجيل.',
                'البوابة B مخصصة لوفود المؤتمر في اليوم الأول، مع فريق المراسم من الساعة 08:15.',
                'موقف مجاني لـ 400 سيارة خلف القاعة C؛ وتُنزل حافلات المدارس الطلبة عند البوابة A في اليومين الثاني والثالث.',
                'كل منطقة معلّمة بحرف وأيقونة إضافة إلى اللون، واللوحات مثبتة على ارتفاع 1.4–1.6 م.',
            ],
        ], 'venue');

        Setting::put('press_facts', [
            ['k' => ['en' => 'Event', 'ku' => 'ڕووداو', 'ar' => 'الحدث'], 'v' => 'Next Step Fair 2026'],
            ['k' => ['en' => 'Edition', 'ku' => 'خول', 'ar' => 'الدورة'], 'v' => '4th'],
            ['k' => ['en' => 'Dates', 'ku' => 'بەروار', 'ar' => 'التواريخ'], 'v' => '28–30 September 2026'],
            ['k' => ['en' => 'Venue', 'ku' => 'شوێن', 'ar' => 'المكان'], 'v' => 'Cultural Factory, Sulaimani'],
            ['k' => ['en' => 'Entry', 'ku' => 'چوونەژوورەوە', 'ar' => 'الدخول'], 'v' => 'Free'],
            ['k' => ['en' => 'Institutions', 'ku' => 'دامەزراوە', 'ar' => 'المؤسسات'], 'v' => '32'],
            ['k' => ['en' => 'Sessions', 'ku' => 'دانیشتن', 'ar' => 'الجلسات'], 'v' => '26'],
            ['k' => ['en' => 'Languages', 'ku' => 'زمانەکان', 'ar' => 'اللغات'], 'v' => 'Kurdish · Arabic · English'],
        ], 'press');

        Setting::put('sponsor_tier_table', [
            'head' => ['Benefit', 'Platinum', 'Gold', 'Silver', 'Bronze'],
            'rows' => [
                ['Booth size', '24 m²', '18 m²', '12 m²', '6 m²'],
                ['Logo on main stage', 'Yes', 'Yes', '—', '—'],
                ['Named session or hall', 'Hall', 'Session', '—', '—'],
                ['Speaking slot', '20 min', '10 min', '—', '—'],
                ['Programme listing', 'Full page', 'Half page', 'Logo', 'Logo'],
                ['Attendee report after the fair', 'Yes', 'Yes', 'Yes', '—'],
            ],
        ], 'sponsors');
    }
}
