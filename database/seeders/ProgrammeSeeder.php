<?php

namespace Database\Seeders;

use App\Models\Booth;
use App\Models\EventSession;
use App\Models\Hall;
use App\Models\Organization;
use App\Models\Speaker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Halls, booths, the exhibitor directory, the sponsor wall, speakers and the
 * three-day agenda — all carried over from the approved design.
 */
class ProgrammeSeeder extends Seeder
{
    public function run(): void
    {
        $halls = $this->halls();
        $this->organizations($halls);
        $this->booths($halls);
        $speakers = $this->speakers();
        $this->sessions($halls, $speakers);
    }

    /** @return array<string, Hall> */
    private function halls(): array
    {
        $definitions = [
            [
                'code' => 'A', 'color' => '#2C4BE0', 'capacity' => 32, 'sort' => 1,
                'name' => [
                    'en' => 'Hall A — universities & institutes',
                    'ku' => 'هۆڵی A — زانکۆ و پەیمانگاکان',
                    'ar' => 'القاعة A — الجامعات والمعاهد',
                ],
                'meta' => [
                    'en' => '32 booths · Zones A1–A4',
                    'ku' => '32 ستاند · ناوچەکانی A1–A4',
                    'ar' => '32 جناحاً · المناطق A1–A4',
                ],
                'description' => [
                    'en' => 'The main exhibition hall. Public universities occupy zones A1 and A2, private institutions A3, and technical institutes A4, which sits nearest the workshop corridor into Hall C.',
                    'ku' => 'هۆڵی سەرەکی پێشانگا. زانکۆ گشتییەکان ناوچەکانی A1 و A2 دەگرن، دامەزراوە تایبەتەکان A3، و پەیمانگا تەکنیکییەکان A4 کە نزیکترینە لە کۆریدۆری وۆرکشۆپ بەرەو هۆڵی C.',
                    'ar' => 'قاعة العرض الرئيسية. تشغل الجامعات الحكومية المنطقتين A1 وA2، والمؤسسات الخاصة A3، والمعاهد التقنية A4 الأقرب إلى ممر ورش العمل المؤدي إلى القاعة C.',
                ],
            ],
            [
                'code' => 'B', 'color' => '#0E9B94', 'capacity' => 420, 'sort' => 2,
                'name' => [
                    'en' => 'Hall B — conference & main stage',
                    'ku' => 'هۆڵی B — کۆنفرانس و شانۆی سەرەکی',
                    'ar' => 'القاعة B — المؤتمر والمنصة الرئيسية',
                ],
                'meta' => [
                    'en' => '420 seats · Day 1 conference, Day 2–3 panels',
                    'ku' => '420 کورسی · کۆنفرانسی ڕۆژی یەکەم، پانێلەکانی ڕۆژی 2–3',
                    'ar' => '420 مقعداً · مؤتمر اليوم الأول وجلسات اليومين 2–3',
                ],
                'description' => [
                    'en' => 'Fixed seating for 420 with interpretation booths at the rear and a press area beside the stage. On Day 1 the hall is the conference floor; on Day 2 and Day 3 it hosts the large panels.',
                    'ku' => 'کورسی جێگیر بۆ 420 کەس لەگەڵ ژووری وەرگێڕان لە دواوە و ناوچەی ڕاگەیاندن لەتەنیشت شانۆ. لە ڕۆژی یەکەم هۆڵەکە شوێنی کۆنفرانسە؛ لە ڕۆژی دووەم و سێیەم پانێلە گەورەکانی تێدا دەبێت.',
                    'ar' => 'مقاعد ثابتة لـ 420 شخصاً مع كبائن ترجمة في الخلف ومنطقة صحفية بجانب المنصة. في اليوم الأول تكون القاعة أرضية المؤتمر، وفي اليومين الثاني والثالث تستضيف الجلسات الكبرى.',
                ],
            ],
            [
                'code' => 'C', 'color' => '#4A4B4D', 'capacity' => 24, 'sort' => 3,
                'name' => [
                    'en' => 'Hall C — exhibitors, workshops & scholarships',
                    'ku' => 'هۆڵی C — بەشداربووان، وۆرکشۆپ و سکۆلەرشیپ',
                    'ar' => 'القاعة C — العارضون وورش العمل والمنح',
                ],
                'meta' => [
                    'en' => '24 booths · Workshop rooms C10–C12',
                    'ku' => '24 ستاند · ژووری وۆرکشۆپ C10–C12',
                    'ar' => '24 جناحاً · غرف ورش العمل C10–C12',
                ],
                'description' => [
                    'en' => 'Commercial and non-academic exhibitors, the scholarship desk, and three workshop rooms running CV clinics and Zankoline support continuously through all three days.',
                    'ku' => 'بەشداربووانی بازرگانی و نائەکادیمی، مێزی سکۆلەرشیپ، و سێ ژووری وۆرکشۆپ کە کلینیکی CV و یارمەتی زانکۆلاین بەردەوام بە درێژایی هەر سێ ڕۆژ بەڕێوە دەبەن.',
                    'ar' => 'عارضون تجاريون وغير أكاديميين، ومكتب المنح، وثلاث غرف ورش تقدّم عيادات السيرة الذاتية ودعم زانكۆلاین باستمرار طوال الأيام الثلاثة.',
                ],
            ],
            [
                'code' => 'S', 'color' => '#F2A93B', 'capacity' => 4, 'sort' => 4,
                'name' => [
                    'en' => 'Service points',
                    'ku' => 'خاڵەکانی خزمەتگوزاری',
                    'ar' => 'نقاط الخدمة',
                ],
                'meta' => [
                    'en' => '4 staffed points',
                    'ku' => '4 خاڵی ستافدار',
                    'ar' => '4 نقاط مزوّدة بموظفين',
                ],
                'description' => [
                    'en' => 'Registration, scholarships, Zankoline support and accessibility. All four are staffed for the full opening hours, 10:00 to 20:00.',
                    'ku' => 'تۆمارکردن، سکۆلەرشیپ، یارمەتی زانکۆلاین و دەستگەیشتن. هەر چواریان بە درێژایی کاتی کردنەوە، 10:00 بۆ 20:00، ستافیان هەیە.',
                    'ar' => 'التسجيل والمنح ودعم زانكۆلاین وإتاحة الوصول. النقاط الأربع مزوّدة بموظفين طوال ساعات العمل من 10:00 حتى 20:00.',
                ],
            ],
        ];

        $halls = [];
        foreach ($definitions as $definition) {
            $halls[$definition['code']] = Hall::updateOrCreate(['code' => $definition['code']], $definition);
        }

        return $halls;
    }

    private function organizations(array $halls): void
    {
        $universities = [
            ['University of Sulaimani', 'زانکۆی سلێمانی', 'جامعة السليمانية', 'university'],
            ['American University of Iraq, Sulaimani', 'زانکۆی ئەمریکی عێراق، سلێمانی', 'الجامعة الأمريكية في العراق، السليمانية', 'university'],
            ['Komar University', 'زانکۆی کۆمار', 'جامعة كومار', 'university'],
            ['Cihan University', 'زانکۆی جیهان', 'جامعة جيهان', 'university'],
            ['Salahaddin University', 'زانکۆی سەلاحەدین', 'جامعة صلاح الدين', 'university'],
            ['University of Kurdistan Hewlêr', 'زانکۆی کوردستان هەولێر', 'جامعة كوردستان هولير', 'university'],
            ['Charmo University', 'زانکۆی چەرموو', 'جامعة جرمو', 'university'],
            ['University of Halabja', 'زانکۆی هەڵەبجە', 'جامعة حلبجة', 'university'],
            ['Technical Institute of Sulaimani', 'پەیمانگای تەکنیکی سلێمانی', 'المعهد التقني في السليمانية', 'institute'],
            ['Tishk International University', 'زانکۆی نێودەوڵەتی تیشک', 'جامعة تيشك الدولية', 'university'],
            ['Lebanese French University', 'زانکۆی لوبنانی فەڕەنسی', 'الجامعة اللبنانية الفرنسية', 'university'],
            ['University of Duhok', 'زانکۆی دهۆک', 'جامعة دهوك', 'university'],
            ['Bilkent University', 'زانکۆی بیلکەنت', 'جامعة بيلكنت', 'university'],
            ['Middle East Technical University', 'زانکۆی تەکنیکی ڕۆژهەڵاتی ناوەڕاست', 'جامعة الشرق الأوسط التقنية', 'university'],
            ['University of Human Development', 'زانکۆی گەشەپێدانی مرۆیی', 'جامعة التنمية البشرية', 'university'],
            ['Qaiwan International University', 'زانکۆی نێودەوڵەتی قەیوان', 'جامعة قيوان الدولية', 'university'],
            ['Erbil Polytechnic University', 'زانکۆی پۆلیتەکنیکی هەولێر', 'جامعة أربيل التقنية', 'university'],
            ['Catholic University in Erbil', 'زانکۆی کاسۆلیکی هەولێر', 'الجامعة الكاثوليكية في أربيل', 'university'],
        ];

        $descriptors = [
            0 => [
                'en' => 'Public · Medicine, engineering, humanities',
                'ku' => 'گشتی · پزیشکی، ئەندازیاری، مرۆڤایەتی',
                'ar' => 'حكومية · الطب والهندسة والعلوم الإنسانية',
            ],
            1 => [
                'en' => 'Private · Business, IT, architecture',
                'ku' => 'تایبەت · بازرگانی، IT، تەلارسازی',
                'ar' => 'خاصة · إدارة الأعمال وتقنية المعلومات والعمارة',
            ],
            2 => [
                'en' => 'Institute · Technical and vocational diplomas',
                'ku' => 'پەیمانگا · دیپلۆمی تەکنیکی و پیشەیی',
                'ar' => 'معهد · دبلومات تقنية ومهنية',
            ],
        ];

        foreach ($universities as $i => [$en, $ku, $ar, $kind]) {
            Organization::updateOrCreate(['slug' => Str::slug($en)], [
                'kind' => $kind,
                'name' => ['en' => $en, 'ku' => $ku, 'ar' => $ar],
                'description' => $descriptors[$i % 3],
                'booth' => 'A'.($i + 1),
                'hall_id' => $halls['A']->id,
                'website' => 'https://example.edu',
                'country' => $i >= 12 && $i <= 13 ? 'TR' : 'IQ',
                'year' => 2026,
                'sort' => $i + 1,
            ]);
        }

        $exhibitors = [
            ['MJ Holding', 'ئێم جەی هۆڵدینگ', 'إم جيه هولدنغ'],
            ['Qaiwan Group', 'گرووپی قەیوان', 'مجموعة قيوان'],
            ['Halabja Group', 'گرووپی هەڵەبجە', 'مجموعة حلبجة'],
            ['Click Iraq', 'کلیک عێراق', 'كليك العراق'],
            ['Lafarge Iraq', 'لافارج عێراق', 'لافارج العراق'],
            ['Fastlink', 'فاستلینک', 'فاست لينك'],
            ['Asiacell', 'ئاسیاسێل', 'آسياسيل'],
            ['KIB Bank', 'بانکی KIB', 'مصرف KIB'],
            ['Rwanga Foundation', 'دەزگای ڕوانگە', 'مؤسسة روانكة'],
            ['IT Academy Sulaimani', 'ئەکادیمیای IT سلێمانی', 'أكاديمية IT السليمانية'],
            ['Kurdistan Save the Children', 'ڕێکخراوی پاراستنی منداڵانی کوردستان', 'منظمة إنقاذ الطفل كوردستان'],
            ['British Council', 'ئەنجومەنی بەریتانی', 'المجلس الثقافي البريطاني'],
        ];

        foreach ($exhibitors as $i => [$en, $ku, $ar]) {
            Organization::updateOrCreate(['slug' => Str::slug($en)], [
                'kind' => Organization::KIND_EXHIBITOR,
                'name' => ['en' => $en, 'ku' => $ku, 'ar' => $ar],
                'description' => $i % 2 === 0
                    ? [
                        'en' => 'Careers, internships and graduate programmes',
                        'ku' => 'پیشە، خولی ڕاهێنان و پرۆگرامی دەرچووان',
                        'ar' => 'وظائف وتدريب وبرامج للخريجين',
                    ]
                    : [
                        'en' => 'Scholarships, training and student services',
                        'ku' => 'سکۆلەرشیپ، ڕاهێنان و خزمەتگوزاری قوتابیان',
                        'ar' => 'منح وتدريب وخدمات طلابية',
                    ],
                'booth' => 'C'.($i + 1),
                'hall_id' => $halls['C']->id,
                'website' => 'https://example.com',
                'year' => 2026,
                'sort' => $i + 1,
            ]);
        }

        $this->syncStrategicPartners();

        $supporters = [
            ['MJ Holding', 'Seminar programme and CV clinics', 'پرۆگرامی سیمینار و کلینیکی CV', 'برنامج الندوات وعيادات السيرة الذاتية', 2024],
            ['Qaiwan Group', 'Student competition and awards', 'پێشبڕکێی قوتابیان و خەڵاتەکان', 'مسابقة الطلبة والجوائز', 2024],
            ['Halabja Group', 'Shuttle buses from 12 schools', 'پاسی گواستنەوە لە 12 قوتابخانە', 'حافلات نقل من 12 مدرسة', 2025],
            ['Click Iraq', 'Scholarship desk and SDG reporting', 'مێزی سکۆلەرشیپ و ڕاپۆرتی SDG', 'مكتب المنح وتقارير SDG', 2023],
            ['Lafarge Iraq', 'Vocational pathways hall', 'هۆڵی ڕێڕەوە پیشەییەکان', 'قاعة المسارات المهنية', 2025],
        ];

        foreach ($supporters as $i => [$name, $en, $ku, $ar, $since]) {
            Organization::updateOrCreate(['slug' => Str::slug($name).'-supporter'], [
                'kind' => Organization::KIND_SUPPORTER,
                'name' => ['en' => $name, 'ku' => $name, 'ar' => $name],
                'description' => ['en' => $en, 'ku' => $ku, 'ar' => $ar],
                'badge' => [
                    'en' => 'Since '.$since,
                    'ku' => 'لە '.$since.'ەوە',
                    'ar' => 'منذ '.$since,
                ],
                'since_year' => $since,
                'sort' => $i + 1,
                'year' => 2026,
            ]);
        }

        $sponsors = [
            ['Asiacell', 'platinum', 'Main stage and connectivity across the venue', 'شانۆی سەرەکی و پەیوەندی لە هەموو شوێنەکە', 'المنصة الرئيسية والاتصال في أنحاء المكان'],
            ['KIB Bank', 'platinum', 'Registration hall and student card offers', 'هۆڵی تۆمارکردن و پێشکەشکراوی کارتی قوتابی', 'قاعة التسجيل وعروض بطاقة الطالب'],
            ['Fastlink', 'platinum', 'Workshop hall and free venue Wi-Fi', 'هۆڵی وۆرکشۆپ و وای-فایی بەخۆڕایی', 'قاعة ورش العمل وواي فاي مجاني'],
            ['Zain Cash', 'gold', 'Registration payments and campus offers', 'پارەدانی تۆمارکردن و پێشکەشکراوی کەمپەس', 'مدفوعات التسجيل وعروض الحرم الجامعي'],
            ['Rwanga Foundation', 'gold', 'Career guidance zone', 'ناوچەی ڕێنمایی پیشەیی', 'منطقة الإرشاد المهني'],
            ['IT Academy Sulaimani', 'gold', 'AI and coding workshops', 'وۆرکشۆپی AI و کۆدنووسین', 'ورش الذكاء الاصطناعي والبرمجة'],
            ['Korek Telecom', 'silver', 'Student data packages', 'پاکەتی داتای قوتابیان', 'باقات بيانات للطلبة'],
            ['Empire World', 'silver', 'Awards ceremony', 'ڕێوڕەسمی خەڵاتەکان', 'حفل الجوائز'],
            ['Rasan Group', 'silver', 'Vocational demonstrations', 'پیشاندانی پیشەیی', 'عروض مهنية'],
            ['Sulaimani Chamber of Commerce', 'silver', 'Employer panel', 'پانێلی خاوەنکاران', 'جلسة أصحاب العمل'],
            ['Byan Group', 'silver', 'Parents lounge', 'هۆڵی دایک و باوکان', 'استراحة أولياء الأمور'],
        ];

        foreach ($sponsors as $i => [$name, $tier, $en, $ku, $ar]) {
            Organization::updateOrCreate(['slug' => Str::slug($name).'-sponsor'], [
                'kind' => Organization::KIND_SPONSOR,
                'tier' => $tier,
                'name' => ['en' => $name, 'ku' => $name, 'ar' => $name],
                'description' => ['en' => $en, 'ku' => $ku, 'ar' => $ar],
                'badge' => [
                    'en' => ucfirst($tier),
                    'ku' => match ($tier) {
                        'platinum' => 'پلاتینیۆم', 'gold' => 'زێڕین', default => 'زیوین'
                    },
                    'ar' => match ($tier) {
                        'platinum' => 'بلاتيني', 'gold' => 'ذهبي', default => 'فضي'
                    },
                ],
                'sort' => $i + 1,
                'year' => 2026,
            ]);
        }

        $media = [
            ['NRT', 'Live opening ceremony coverage', 'پۆشینی ڕاستەوخۆی ڕێوڕەسمی کردنەوە', 'تغطية حية لحفل الافتتاح', 'Broadcast'],
            ['Rudaw', 'Conference programme coverage', 'پۆشینی پرۆگرامی کۆنفرانس', 'تغطية برنامج المؤتمر', 'Broadcast'],
            ['Esta Media Network', 'Digital and social coverage', 'پۆشینی دیجیتاڵ و سۆشیاڵ', 'تغطية رقمية واجتماعية', 'Digital'],
            ['Draw Media', 'Interviews and reels', 'چاوپێکەوتن و ڕیلز', 'مقابلات ومقاطع قصيرة', 'Digital'],
        ];

        foreach ($media as $i => [$name, $en, $ku, $ar, $badge]) {
            Organization::updateOrCreate(['slug' => Str::slug($name).'-media'], [
                'kind' => Organization::KIND_MEDIA,
                'name' => ['en' => $name, 'ku' => $name, 'ar' => $name],
                'description' => ['en' => $en, 'ku' => $ku, 'ar' => $ar],
                'badge' => [
                    'en' => $badge,
                    'ku' => $badge === 'Broadcast' ? 'پەخش' : 'دیجیتاڵ',
                    'ar' => $badge === 'Broadcast' ? 'بث' : 'رقمي',
                ],
                'sort' => $i + 1,
                'year' => 2026,
            ]);
        }
    }

    /**
     * The partners whose marks the design carries, and the page behind each.
     *
     * A static list rather than a property so a migration can read it too: an
     * installation that is already live gets these pages filled in without the
     * rest of the seed running anywhere near its real data.
     *
     * @return list<array<string, mixed>>
     */
    public static function strategicPartners(): array
    {
        return [
            [
                'slug' => 'mohe', 'logo' => 'brand/mohe.png', 'sort' => 1,
                'name' => [
                    'en' => 'Ministry of Higher Education and Scientific Research (MOHE)',
                    'ku' => 'وەزارەتی خوێندنی باڵا و توێژینەوەی زانستی (MOHE)',
                    'ar' => 'وزارة التعليم العالي والبحث العلمي (MOHE)',
                ],
                'description' => [
                    'en' => 'Convening partner. Opens the conference and chairs the accreditation roundtable.',
                    'ku' => 'هاوبەشی بانگهێشتکار. کۆنفرانس دەکاتەوە و سەرۆکایەتی مێزی گردی متمانەپێکراوی دەکات.',
                    'ar' => 'الشريك الداعي. يفتتح المؤتمر ويترأس الطاولة المستديرة للاعتماد الأكاديمي.',
                ],
                'about' => [
                    'en' => '<p>The Ministry of Higher Education and Scientific Research is the government body responsible for universities, institutes and scientific research in the Kurdistan Region. It accredits programmes, sets admission policy, and recognises degrees earned abroad.</p>',
                    'ku' => '<p>وەزارەتی خوێندنی باڵا و توێژینەوەی زانستی ئەو دەزگا حکومییەیە کە بەرپرسیارە لە زانکۆ، پەیمانگا و توێژینەوەی زانستی لە هەرێمی کوردستان. پرۆگرامەکان متمانەپێکراو دەکات، سیاسەتی وەرگرتن دادەنێت، و بڕوانامەی دەرەوەی وڵات دەناسێت.</p>',
                    'ar' => '<p>وزارة التعليم العالي والبحث العلمي هي الجهة الحكومية المسؤولة عن الجامعات والمعاهد والبحث العلمي في إقليم كوردستان. تعتمد البرامج الدراسية، وتضع سياسة القبول، وتعترف بالشهادات الممنوحة خارج البلاد.</p>',
                ],
                'partnership' => [
                    'en' => '<p>Next Step is held in partnership with the Ministry. Its mark appears beside ours on every page of this site, and the Ministry takes part across the three days of the fair.</p><p>For students, the partnership matters in one practical way: the institutions exhibiting here are ones the Ministry recognises, and the guidance given at the fair follows official admission policy rather than rumour.</p>',
                    'ku' => '<p>هەنگاوی داهاتوو بە هاوبەشی لەگەڵ وەزارەت بەڕێوە دەچێت. نیشانەکەی لەتەنیشت هی ئێمە لە هەموو لاپەڕەیەکی ئەم ماڵپەڕەدا دەردەکەوێت، و وەزارەت بە درێژایی سێ ڕۆژی پێشانگاکە بەشدارە.</p><p>بۆ خوێندکاران، هاوبەشییەکە بە شێوەیەکی پراکتیکی گرنگە: ئەو دامەزراوانەی لێرە پیشانگایان هەیە ئەوانەن کە وەزارەت دەیانناسێت، و ئەو ڕێنماییەی لە پێشانگادا دەدرێت بەپێی سیاسەتی فەرمی وەرگرتنە، نەک بەپێی قسەی خەڵک.</p>',
                    'ar' => '<p>يُقام Next Step بالشراكة مع الوزارة. يظهر شعارها إلى جانب شعارنا في كل صفحة من هذا الموقع، وتشارك الوزارة على مدى أيام المعرض الثلاثة.</p><p>وللطلبة فائدة عملية واحدة من هذه الشراكة: المؤسسات العارضة هنا معترف بها لدى الوزارة، والإرشاد المقدَّم في المعرض يتبع سياسة القبول الرسمية لا ما يُتداول بين الناس.</p>',
                ],
            ],
            [
                'slug' => 'krg', 'logo' => 'brand/krg.png', 'sort' => 2,
                'name' => [
                    'en' => 'Kurdistan Regional Government',
                    'ku' => 'حکومەتی هەرێمی کوردستان',
                    'ar' => 'حكومة إقليم كوردستان',
                ],
                'description' => [
                    'en' => 'Institutional patron, with the Sulaimani Governorate providing the venue through 2028.',
                    'ku' => 'پشتیوانی دامەزراوەیی، لەگەڵ پارێزگای سلێمانی کە شوێنەکە تا 2028 دابین دەکات.',
                    'ar' => 'الراعي المؤسسي، مع محافظة السليمانية التي توفر المكان حتى 2028.',
                ],
                'about' => [
                    'en' => '<p>The Kurdistan Regional Government is the elected government of the Kurdistan Region of Iraq. Education, and the routes open to young people leaving school, sit among its responsibilities.</p>',
                    'ku' => '<p>حکومەتی هەرێمی کوردستان حکومەتی هەڵبژێردراوی هەرێمی کوردستانی عێراقە. پەروەردە و ئەو ڕێگایانەی لەبەردەم گەنجانی دەرچووی قوتابخانەن، بەشێکن لە بەرپرسیارێتییەکانی.</p>',
                    'ar' => '<p>حكومة إقليم كوردستان هي الحكومة المنتخبة لإقليم كوردستان العراق. والتعليم، والمسارات المتاحة أمام الشباب بعد المدرسة، من ضمن مسؤولياتها.</p>',
                ],
                'partnership' => [
                    'en' => '<p>The Regional Government supports Next Step as an institutional patron, with the Sulaimani Governorate providing the venue.</p><p>That support is why entry, the seminars and the guidance desks cost students nothing.</p>',
                    'ku' => '<p>حکومەتی هەرێم وەک پشتیوانێکی دامەزراوەیی پشتگیری هەنگاوی داهاتوو دەکات، لەگەڵ پارێزگای سلێمانی کە شوێنەکە دابین دەکات.</p><p>ئەو پشتگیرییە هۆکاری ئەوەیە کە چوونەژوورەوە، سیمینارەکان و مێزەکانی ڕێنمایی هیچ تێچوویەکیان بۆ خوێندکاران نییە.</p>',
                    'ar' => '<p>تدعم حكومة الإقليم Next Step بصفتها راعياً مؤسسياً، مع توفير محافظة السليمانية للمكان.</p><p>وبفضل هذا الدعم لا يكلف الدخول ولا الندوات ولا مكاتب الإرشاد الطلبةَ شيئاً.</p>',
                ],
            ],
            [
                // No logo file ships for this one: the mark is uploaded on the
                // Brand images screen in the slot named for this slug, which is
                // where logoUrl() looks first for a strategic partner.
                'slug' => 'ksa', 'logo' => null, 'sort' => 3,
                'name' => [
                    'en' => 'Kurdistan Students Association',
                    'ku' => 'کۆمەڵەی خوێندکارانی کوردستان',
                    'ar' => 'جمعية طلبة كوردستان',
                ],
                'description' => [
                    'en' => 'Student partner. Reaches grade 12 students across the Region and staffs the guidance desks at the fair.',
                    'ku' => 'هاوبەشی خوێندکاران. دەگاتە خوێندکارانی پۆلی ١٢ لە سەرانسەری هەرێم و ستافی مێزەکانی ڕێنمایی لە پێشانگاکە دابین دەکات.',
                    'ar' => 'الشريك الطلابي. يصل إلى طلبة الصف الثاني عشر في عموم الإقليم ويشرف على مكاتب الإرشاد في المعرض.',
                ],
                'about' => [
                    'en' => '<p>The Kurdistan Students Association is a student organisation working with school and university students across the Kurdistan Region.</p>',
                    'ku' => '<p>کۆمەڵەی خوێندکارانی کوردستان ڕێکخراوێکی خوێندکارییە کە لەگەڵ خوێندکارانی قوتابخانە و زانکۆ لە سەرانسەری هەرێمی کوردستان کار دەکات.</p>',
                    'ar' => '<p>جمعية طلبة كوردستان منظمة طلابية تعمل مع طلبة المدارس والجامعات في عموم إقليم كوردستان.</p>',
                ],
                'partnership' => [
                    'en' => '<p>The Association joins Next Step for the 2026 edition. Its mark sits beside ours in the header of every page.</p><p>Its part of the work is reach: getting word of the fair, the scholarship and the Zankoline guidance desks to grade 12 students in places a poster never reaches.</p>',
                    'ku' => '<p>کۆمەڵەکە بۆ خولی ٢٠٢٦ دەبێتە هاوبەشی هەنگاوی داهاتوو. نیشانەکەی لەتەنیشت هی ئێمە لە سەرەوەی هەموو لاپەڕەیەکدا دادەنرێت.</p><p>بەشی ئەوان لە کارەکەدا گەیاندنە: گەیاندنی هەواڵی پێشانگا، سکۆلەرشیپ و مێزەکانی ڕێنمایی زانکۆلاین بە خوێندکارانی پۆلی ١٢ لەو شوێنانەی کە پۆستەر هەرگیز ناگاتێ.</p>',
                    'ar' => '<p>تنضم الجمعية إلى Next Step في دورة 2026. ويظهر شعارها إلى جانب شعارنا في أعلى كل صفحة.</p><p>ودورها في العمل هو الوصول: إيصال خبر المعرض والمنحة ومكاتب إرشاد زانكولاين إلى طلبة الصف الثاني عشر في أماكن لا يصلها الملصق.</p>',
                ],
            ],
        ];
    }

    /**
     * Write them, creating what is missing.
     *
     * Called with false from a migration, where an existing record and any
     * wording edited in the dashboard must be left exactly as it is.
     */
    public function syncStrategicPartners(bool $overwrite = true): void
    {
        foreach (self::strategicPartners() as $partner) {
            $values = [
                'kind' => Organization::KIND_STRATEGIC,
                'name' => $partner['name'],
                'description' => $partner['description'],
                // What the page behind the mark says. Starter wording: it is
                // written to be true and dull rather than to speak for a
                // ministry, and it is the first thing an editor should replace.
                'about' => $partner['about'],
                'partnership' => $partner['partnership'],
                'logo_path' => $partner['logo'],
                'sort' => $partner['sort'],
                'year' => 2026,
            ];

            $overwrite
                ? Organization::updateOrCreate(['slug' => $partner['slug']], $values)
                : Organization::firstOrCreate(['slug' => $partner['slug']], $values);
        }
    }

    private function booths(array $halls): void
    {
        // Halls A and C take their booth list from the exhibitor directory.
        foreach (['A' => 'university', 'C' => 'exhibitor'] as $code => $kind) {
            $orgs = Organization::query()
                ->when($code === 'A', fn ($q) => $q->whereIn('kind', ['university', 'institute']))
                ->when($code === 'C', fn ($q) => $q->where('kind', 'exhibitor'))
                ->orderBy('sort')->get();

            foreach ($orgs as $i => $org) {
                Booth::updateOrCreate(
                    ['hall_id' => $halls[$code]->id, 'code' => $code.($i + 1)],
                    [
                        'name' => $org->getTranslations('name'),
                        'kind' => $org->getTranslations('description'),
                        'organization_id' => $org->id,
                        'sort' => $i + 1,
                    ]
                );
            }
        }

        $hallB = [
            ['B1', 'Main stage', 'شانۆی سەرەکی', 'المنصة الرئيسية', 'Opening ceremony, keynotes, awards', 'ڕێوڕەسمی کردنەوە، وتاری سەرەکی، خەڵاتەکان', 'حفل الافتتاح والكلمات الرئيسية والجوائز'],
            ['B2', 'Interpretation booths', 'ژووری وەرگێڕان', 'كبائن الترجمة', 'Kurdish, Arabic and English', 'کوردی، عەرەبی و ئینگلیزی', 'الكردية والعربية والإنجليزية'],
            ['B3', 'Press area', 'ناوچەی ڕاگەیاندن', 'المنطقة الصحفية', '12 positions, wired feed', '12 شوێن، لینکی وایەری', '12 موقعاً مع تغذية سلكية'],
            ['B4', 'Delegation seating', 'کورسی شاندەکان', 'مقاعد الوفود', 'Front rows, reserved Day 1', 'ڕیزی پێشەوە، تەرخانکراو بۆ ڕۆژی یەکەم', 'الصفوف الأمامية، محجوزة اليوم الأول'],
            ['B5', 'Salon 2', 'ساڵۆنی 2', 'الصالون 2', 'Bilateral meetings and ministerial lunch', 'کۆبوونەوەی دووقۆڵی و نانی نیوەڕۆی وەزیری', 'اجتماعات ثنائية وغداء وزاري'],
            ['B6', 'Speaker green room', 'ژووری قسەکەران', 'غرفة المتحدثين', 'Behind the stage', 'لە پشت شانۆ', 'خلف المنصة'],
        ];

        foreach ($hallB as $i => [$code, $en, $ku, $ar, $kEn, $kKu, $kAr]) {
            Booth::updateOrCreate(['hall_id' => $halls['B']->id, 'code' => $code], [
                'name' => ['en' => $en, 'ku' => $ku, 'ar' => $ar],
                'kind' => ['en' => $kEn, 'ku' => $kKu, 'ar' => $kAr],
                'sort' => $i + 1,
            ]);
        }

        $service = [
            ['S1', 'Registration & walk-in desk', 'مێزی تۆمارکردن و هاتنی ڕاستەوخۆ', 'مكتب التسجيل والحضور المباشر', 'North entrance, Hall A · QR check-in and walk-in registration', 'دەروازەی باکوور، هۆڵی A · چوونەژوورەوەی QR و تۆمارکردنی ڕاستەوخۆ', 'المدخل الشمالي، القاعة A · تسجيل الدخول عبر QR والتسجيل المباشر'],
            ['S2', 'Scholarship desk', 'مێزی سکۆلەرشیپ', 'مكتب المنح', 'Hall C, booth C7 · named programmes and deadlines', 'هۆڵی C، ستاندی C7 · پرۆگرامی ناودار و کۆتا کاتەکان', 'القاعة C، الجناح C7 · برامج محددة ومواعيد نهائية'],
            ['S3', 'Zankoline support', 'یارمەتی زانکۆلاین', 'دعم زانكۆلاین', 'Hall C, booths C1–C3 · form completion with staff', 'هۆڵی C، ستاندەکانی C1–C3 · تەواوکردنی فۆرم لەگەڵ ستاف', 'القاعة C، الأجنحة C1–C3 · إكمال الاستمارة بمساعدة الفريق'],
            ['S4', 'Accessibility & first aid', 'دەستگەیشتن و فریاگوزاری', 'إتاحة الوصول والإسعافات الأولية', 'Gate A · wheelchairs, quiet room, medical', 'دەروازەی A · کورسی چەرخدار، ژووری بێدەنگ، پزیشکی', 'البوابة A · كراسٍ متحركة وغرفة هادئة ورعاية طبية'],
        ];

        foreach ($service as $i => [$code, $en, $ku, $ar, $kEn, $kKu, $kAr]) {
            Booth::updateOrCreate(['hall_id' => $halls['S']->id, 'code' => $code], [
                'name' => ['en' => $en, 'ku' => $ku, 'ar' => $ar],
                'kind' => ['en' => $kEn, 'ku' => $kKu, 'ar' => $kAr],
                'sort' => $i + 1,
            ]);
        }
    }

    /** @return array<string, Speaker> keyed by English name */
    private function speakers(): array
    {
        $definitions = [
            [
                'name' => ['en' => 'Dr. Rezan Ahmed Kareem', 'ku' => 'د. ڕەزان ئەحمەد کەریم', 'ar' => 'د. رزان أحمد كريم'],
                'role' => ['en' => 'Director General, Scholarships', 'ku' => 'بەڕێوەبەری گشتی، سکۆلەرشیپ', 'ar' => 'المدير العام للمنح'],
                'organization' => [
                    'en' => 'Ministry of Higher Education and Scientific Research',
                    'ku' => 'وەزارەتی خوێندنی باڵا و توێژینەوەی زانستی',
                    'ar' => 'وزارة التعليم العالي والبحث العلمي',
                ],
                'bio' => [
                    'en' => "<p>Rezan Ahmed Kareem has led the scholarships directorate at the Ministry of Higher Education and Scientific Research since 2021, with responsibility for the region's outbound funding programmes and for the recognition of qualifications earned abroad.</p><p>Before joining the Ministry he spent eleven years at the University of Sulaimani, first in admissions and then as registrar, where he rebuilt the appeals process that is now used across public institutions.</p><p>At the 2026 conference he opens the Day 1 programme and chairs the closed roundtable on cross-border recognition.</p>",
                    'ku' => '<p>ڕەزان ئەحمەد کەریم لە 2021ەوە بەڕێوەبەرایەتی سکۆلەرشیپی وەزارەتی خوێندنی باڵا و توێژینەوەی زانستی بەڕێوە دەبات، بەرپرسیارە لە پرۆگرامەکانی خەرجکردنی دەرەکی هەرێم و لە دانپێدانان بە بڕوانامەی دەرەوە.</p><p>پێش هاتنی بۆ وەزارەت، یازدە ساڵ لە زانکۆی سلێمانی بەسەربرد، سەرەتا لە وەرگرتن و پاشان وەک تۆمارکار، لەوێ پرۆسەی تانە و تەشەری دووبارە دروستکردەوە کە ئێستا لە هەموو دامەزراوە گشتییەکاندا بەکاردێت.</p><p>لە کۆنفرانسی 2026 پرۆگرامی ڕۆژی یەکەم دەکاتەوە و سەرۆکایەتی مێزە گردە داخراوەکەی دانپێدانانی سنووربەزێن دەکات.</p>',
                    'ar' => '<p>يقود رزان أحمد كريم مديرية المنح في وزارة التعليم العالي والبحث العلمي منذ 2021، وهو مسؤول عن برامج التمويل الخارجي في الإقليم وعن الاعتراف بالشهادات الممنوحة في الخارج.</p><p>قبل التحاقه بالوزارة أمضى إحدى عشرة سنة في جامعة السليمانية، في القبول أولاً ثم مسجلاً عاماً، حيث أعاد بناء آلية الاعتراضات المعتمدة اليوم في المؤسسات الحكومية.</p><p>في مؤتمر 2026 يفتتح برنامج اليوم الأول ويترأس الطاولة المستديرة المغلقة حول الاعتراف عبر الحدود.</p>',
                ],
                'track' => 'conference', 'speaker_type' => 'speaker', 'country' => 'IQ', 'featured' => true, 'sort' => 1,
                'topics' => ['en' => ['Admission policy', 'Scholarships', 'Qualification recognition', 'Zankoline']],
                'links' => [['label' => 'Ministry profile', 'url' => 'https://mhe-krg.org'], ['label' => 'LinkedIn', 'url' => 'https://linkedin.com']],
            ],
            [
                'name' => ['en' => 'Prof. Sara Hiwa Mustafa', 'ku' => 'پ.د. سارا هیوا مستەفا', 'ar' => 'أ.د. سارا هيوا مصطفى'],
                'role' => ['en' => 'Vice President for Academic Affairs', 'ku' => 'جێگری سەرۆک بۆ کاروباری ئەکادیمی', 'ar' => 'نائبة الرئيس للشؤون الأكاديمية'],
                'organization' => ['en' => 'University of Sulaimani', 'ku' => 'زانکۆی سلێمانی', 'ar' => 'جامعة السليمانية'],
                'bio' => [
                    'en' => "<p>Sara Hiwa Mustafa is Vice President for Academic Affairs at the University of Sulaimani, where she oversees curriculum reform across nine colleges and the university's accreditation programme.</p><p>Her research is on first-year attrition: why students leave in the first two semesters, and how much of it traces back to information they did not have when they chose the programme.</p><p>She has spoken at every edition of Next Step since 2024.</p>",
                    'ku' => '<p>سارا هیوا مستەفا جێگری سەرۆکی زانکۆی سلێمانییە بۆ کاروباری ئەکادیمی، لەوێ چاکسازی پڕۆگرامی خوێندن لە نۆ کۆلێژ و پرۆگرامی متمانەپێکراوی زانکۆ بەڕێوە دەبات.</p><p>توێژینەوەکەی لەسەر وازهێنانی ساڵی یەکەمە: بۆچی قوتابیان لە دوو وەرزی یەکەمدا وازدەهێنن، و چەند لەوە دەگەڕێتەوە بۆ ئەو زانیارییانەی لە کاتی هەڵبژاردنی بەشەکەدا نەیانبووە.</p><p>لە 2024ەوە لە هەموو خولێکی هەنگاوی داهاتوودا قسەی کردووە.</p>',
                    'ar' => '<p>سارا هيوا مصطفى نائبة رئيس جامعة السليمانية للشؤون الأكاديمية، تشرف على إصلاح المناهج في تسع كليات وعلى برنامج الاعتماد في الجامعة.</p><p>يتناول بحثها التسرب في السنة الأولى: لماذا يترك الطلبة الدراسة في الفصلين الأولين، وكم من ذلك يعود إلى معلومات لم تكن بحوزتهم عند اختيار البرنامج.</p><p>تحدثت في كل دورة من دورات Next Step منذ 2024.</p>',
                ],
                'track' => 'conference', 'speaker_type' => 'panelist', 'country' => 'IQ', 'featured' => true, 'sort' => 2,
                'topics' => ['en' => ['Curriculum reform', 'Accreditation', 'First-year attrition', 'Academic quality']],
                'links' => [['label' => 'University profile', 'url' => 'https://univsul.edu.iq']],
            ],
            [
                'name' => ['en' => 'Bahar Jamal Rashid', 'ku' => 'بەهار جەمال ڕەشید', 'ar' => 'بهار جمال رشيد'],
                'role' => ['en' => 'Programme Director', 'ku' => 'بەڕێوەبەری پرۆگرام', 'ar' => 'مديرة البرامج'],
                'organization' => ['en' => 'Click Iraq Foundation', 'ku' => 'دەزگای کلیک عێراق', 'ar' => 'مؤسسة كليك العراق'],
                'track' => 'fair', 'speaker_type' => 'speaker', 'country' => 'IQ', 'featured' => true, 'sort' => 3,
                'topics' => ['en' => ['Scholarships', 'Financial aid', 'Student guidance']],
            ],
            [
                'name' => ['en' => 'Dr. Hemin Latif Sabir', 'ku' => 'د. هێمن لەتیف سابیر', 'ar' => 'د. همن لطيف صابر'],
                'role' => ['en' => 'Head of Computer Science', 'ku' => 'سەرۆکی بەشی زانستی کۆمپیوتەر', 'ar' => 'رئيس قسم علوم الحاسوب'],
                'organization' => [
                    'en' => 'American University of Iraq, Sulaimani',
                    'ku' => 'زانکۆی ئەمریکی عێراق، سلێمانی',
                    'ar' => 'الجامعة الأمريكية في العراق، السليمانية',
                ],
                'track' => 'fair', 'speaker_type' => 'speaker', 'country' => 'IQ', 'featured' => true, 'sort' => 4,
                'topics' => ['en' => ['AI', 'Future skills', 'Computer science']],
            ],
            [
                'name' => ['en' => 'Ari Salih Mahmood', 'ku' => 'ئاری ساڵح مەحموود', 'ar' => 'آري صالح محمود'],
                'role' => ['en' => 'Admissions Registrar', 'ku' => 'تۆمارکاری وەرگرتن', 'ar' => 'مسجّل القبول'],
                'organization' => ['en' => 'Zankoline / MOHE', 'ku' => 'زانکۆلاین / MOHE', 'ar' => 'زانكۆلاین / MOHE'],
                'track' => 'conference', 'speaker_type' => 'panelist', 'country' => 'IQ', 'featured' => true, 'sort' => 5,
                'topics' => ['en' => ['Zankoline', 'Placement', 'Admissions']],
            ],
            [
                'name' => ['en' => 'Nazdar Omer Faraj', 'ku' => 'نازدار عومەر فەرەج', 'ar' => 'نازدار عمر فرج'],
                'role' => ['en' => 'School Counsellor', 'ku' => 'ڕاوێژکاری قوتابخانە', 'ar' => 'مرشدة مدرسية'],
                'organization' => [
                    'en' => 'Sulaimani Directorate of Education',
                    'ku' => 'بەڕێوەبەرایەتی پەروەردەی سلێمانی',
                    'ar' => 'مديرية تربية السليمانية',
                ],
                'track' => 'fair', 'speaker_type' => 'moderator', 'country' => 'IQ', 'featured' => true, 'sort' => 6,
                'topics' => ['en' => ['Parents', 'Guidance', 'Choosing a major']],
            ],
            [
                'name' => ['en' => 'Dr. Elif Kaya', 'ku' => 'د. ئەلیف کایا', 'ar' => 'د. أليف كايا'],
                'role' => ['en' => 'Director of International Admissions', 'ku' => 'بەڕێوەبەری وەرگرتنی نێودەوڵەتی', 'ar' => 'مديرة القبول الدولي'],
                'organization' => ['en' => 'Bilkent University', 'ku' => 'زانکۆی بیلکەنت', 'ar' => 'جامعة بيلكنت'],
                'track' => 'fair', 'speaker_type' => 'speaker', 'country' => 'TR', 'featured' => true, 'sort' => 7,
                'topics' => ['en' => ['Studying abroad', 'International admissions']],
            ],
            [
                'name' => ['en' => 'James Whitfield', 'ku' => 'جەیمس وایتفیڵد', 'ar' => 'جيمس ويتفيلد'],
                'role' => ['en' => 'Regional Education Adviser', 'ku' => 'ڕاوێژکاری هەرێمی خوێندن', 'ar' => 'مستشار التعليم الإقليمي'],
                'organization' => ['en' => 'British Council Iraq', 'ku' => 'ئەنجومەنی بەریتانی عێراق', 'ar' => 'المجلس الثقافي البريطاني العراق'],
                'track' => 'conference', 'speaker_type' => 'panelist', 'country' => 'UK', 'featured' => true, 'sort' => 8,
                'topics' => ['en' => ['Qualification recognition', 'International partnerships']],
            ],
        ];

        $speakers = [];
        foreach ($definitions as $definition) {
            $slug = Str::slug($definition['name']['en']);
            $speakers[$definition['name']['en']] = Speaker::updateOrCreate(
                ['slug' => $slug],
                $definition + ['year' => 2026, 'published' => true]
            );
        }

        return $speakers;
    }

    private function sessions(array $halls, array $speakers): void
    {
        $definitions = [
            // ---------------------------------------------------------- Day 1
            [
                'day' => 1, 'starts_at' => '09:00', 'ends_at' => '10:00', 'duration_label' => '60 min',
                'type' => 'Ceremony', 'track' => 'conference', 'hall' => 'B', 'languages' => 'KU · AR · EN',
                'title' => [
                    'en' => 'Opening remarks and ribbon cutting',
                    'ku' => 'وتاری کردنەوە و بڕینی ڕیبۆن',
                    'ar' => 'كلمة الافتتاح وقص الشريط',
                ],
                'description' => [
                    'en' => 'The Ministry of Higher Education and Scientific Research opens the 4th edition alongside the Sulaimani governorate.',
                    'ku' => 'وەزارەتی خوێندنی باڵا و توێژینەوەی زانستی لەگەڵ پارێزگای سلێمانی خولی چوارەم دەکەنەوە.',
                    'ar' => 'تفتتح وزارة التعليم العالي والبحث العلمي الدورة الرابعة إلى جانب محافظة السليمانية.',
                ],
                'who' => [
                    'en' => 'Ministry of Higher Education and Scientific Research',
                    'ku' => 'وەزارەتی خوێندنی باڵا و توێژینەوەی زانستی',
                    'ar' => 'وزارة التعليم العالي والبحث العلمي',
                ],
                'speakers' => ['Dr. Rezan Ahmed Kareem' => 'Opening address'],
            ],
            [
                'day' => 1, 'starts_at' => '10:15', 'ends_at' => '11:45', 'duration_label' => '90 min',
                'type' => 'Plenary', 'track' => 'conference', 'hall' => 'B', 'languages' => 'KU · EN',
                'title' => [
                    'en' => 'Admission at scale: what Zankoline gets right and what it costs students',
                    'ku' => 'وەرگرتن لە ئاستێکی بەرفراوان: زانکۆلاین چی ڕاست دەکات و چی لە قوتابیان دەبات',
                    'ar' => 'القبول على نطاق واسع: ما يصيبه زانكۆلاین وما يكلّفه الطلبة',
                ],
                'description' => [
                    'en' => 'A frank session on placement, transparency and the appeals students never file because nobody told them they could.',
                    'ku' => 'دانیشتنێکی ڕاشکاوانە لەسەر دابەشکردن، ڕوونی و ئەو تانانەی قوتابیان هەرگیز پێشکەشی ناکەن چونکە کەس پێی نەوتوون دەتوانن.',
                    'ar' => 'جلسة صريحة عن التوزيع والشفافية والاعتراضات التي لا يقدّمها الطلبة لأن أحداً لم يخبرهم أن بإمكانهم ذلك.',
                ],
                'who' => [
                    'en' => 'Dr. Rezan Ahmed Kareem · Prof. Sara Hiwa · Ari Salih',
                    'ku' => 'د. ڕەزان ئەحمەد کەریم · پ.د. سارا هیوا · ئاری ساڵح',
                    'ar' => 'د. رزان أحمد كريم · أ.د. سارا هيوا · آري صالح',
                ],
                'speakers' => [
                    'Dr. Rezan Ahmed Kareem' => 'Keynote',
                    'Prof. Sara Hiwa Mustafa' => 'Panelist',
                    'Ari Salih Mahmood' => 'Panelist',
                ],
            ],
            [
                'day' => 1, 'starts_at' => '12:00', 'ends_at' => '13:15', 'duration_label' => '75 min',
                'type' => 'Panel', 'track' => 'fair', 'hall' => 'A', 'languages' => 'KU',
                'title' => [
                    'en' => 'Choosing a major without guessing',
                    'ku' => 'هەڵبژاردنی بەش بەبێ پێشبینی',
                    'ar' => 'اختيار التخصص دون تخمين',
                ],
                'description' => [
                    'en' => 'Four recent graduates on what they picked, what it paid, and what they would choose again.',
                    'ku' => 'چوار دەرچووی نوێ باسی ئەوە دەکەن چییان هەڵبژارد، چەندی داهاتی هەبوو، و چی دووبارە هەڵدەبژێرنەوە.',
                    'ar' => 'أربعة خريجين جدد يتحدثون عمّا اختاروه، وما الذي درّه عليهم، وما سيختارونه ثانية.',
                ],
                'who' => [
                    'en' => 'Panel of 4 · moderated by Nazdar Omer',
                    'ku' => 'پانێلی 4 کەسی · بەڕێوەبردنی نازدار عومەر',
                    'ar' => 'جلسة بأربعة متحدثين · تديرها نازدار عمر',
                ],
                'speakers' => ['Nazdar Omer Faraj' => 'Moderator'],
            ],
            [
                'day' => 1, 'starts_at' => '14:00', 'ends_at' => '16:00', 'duration_label' => '120 min',
                'type' => 'Workshop', 'track' => 'fair', 'hall' => 'C', 'languages' => 'KU · AR',
                'title' => [
                    'en' => 'Zankoline application clinic',
                    'ku' => 'کلینیکی فۆرمی زانکۆلاین',
                    'ar' => 'عيادة استمارة زانكۆلاین',
                ],
                'description' => [
                    'en' => 'Bring your grades and your phone. Staff sit with you until the form is submitted.',
                    'ku' => 'نمرەکانت و مۆبایلەکەت بهێنە. ستاف لەگەڵت دادەنیشن تا فۆرمەکە دەنێردرێت.',
                    'ar' => 'أحضر درجاتك وهاتفك. يجلس معك الفريق حتى إرسال الاستمارة.',
                ],
                'who' => [
                    'en' => 'Next Step guidance team',
                    'ku' => 'تیمی ڕێنمایی هەنگاوی داهاتوو',
                    'ar' => 'فريق الإرشاد في Next Step',
                ],
            ],
            [
                'day' => 1, 'starts_at' => '16:30', 'ends_at' => '18:00', 'duration_label' => '90 min',
                'type' => 'Roundtable', 'track' => 'conference', 'hall' => 'B', 'languages' => 'EN',
                // Closed session: it is listed so the day reads honestly, but
                // nobody can add a delegations-only roundtable to their agenda.
                'bookable' => false,
                'title' => [
                    'en' => 'Accreditation and cross-border recognition',
                    'ku' => 'متمانەپێکراوی و دانپێدانانی سنووربەزێن',
                    'ar' => 'الاعتماد الأكاديمي والاعتراف عبر الحدود',
                ],
                'description' => [
                    'en' => 'Closed roundtable for delegations on recognising Kurdistan Region qualifications abroad.',
                    'ku' => 'مێزی گردی داخراو بۆ شاندەکان لەسەر دانپێدانان بە بڕوانامەکانی هەرێمی کوردستان لە دەرەوە.',
                    'ar' => 'طاولة مستديرة مغلقة للوفود حول الاعتراف بشهادات إقليم كوردستان في الخارج.',
                ],
                'who' => ['en' => 'Invited delegations', 'ku' => 'شاندە بانگهێشتکراوەکان', 'ar' => 'الوفود المدعوة'],
                'speakers' => ['Dr. Rezan Ahmed Kareem' => 'Chair', 'James Whitfield' => 'Panelist'],
            ],

            // ---------------------------------------------------------- Day 2
            [
                'day' => 2, 'starts_at' => '10:00', 'ends_at' => '20:00', 'duration_label' => 'All day',
                'type' => 'Exhibition', 'track' => 'fair', 'hall' => null, 'hall_label' => 'Halls A & C', 'languages' => 'KU · AR · EN',
                'bookable' => false,
                'title' => [
                    'en' => 'University and institute floor open',
                    'ku' => 'هۆڵی زانکۆ و پەیمانگاکان کراوەیە',
                    'ar' => 'افتتاح صالة الجامعات والمعاهد',
                ],
                'description' => [
                    'en' => '32 institutions at staffed desks. No appointment needed.',
                    'ku' => '32 دامەزراوە لەسەر مێزی ستافدار. پێویست بە کاتی پێشوەخت ناکات.',
                    'ar' => '32 مؤسسة على طاولات مزوّدة بموظفين. لا حاجة لموعد مسبق.',
                ],
                'who' => ['en' => 'All exhibitors', 'ku' => 'هەموو بەشداربووان', 'ar' => 'كل العارضين'],
            ],
            [
                'day' => 2, 'starts_at' => '11:00', 'ends_at' => '12:15', 'duration_label' => '75 min',
                'type' => 'Seminar', 'track' => 'fair', 'hall' => 'C', 'languages' => 'KU',
                'title' => [
                    'en' => 'Scholarships: who actually funds Kurdistan Region students',
                    'ku' => 'سکۆلەرشیپ: بەڕاستی کێ خەرجی قوتابیانی هەرێمی کوردستان دەکات',
                    'ar' => 'المنح: من يموّل فعلاً طلبة إقليم كوردستان',
                ],
                'description' => [
                    'en' => 'Named programmes, real deadlines, and the documents you need before you start.',
                    'ku' => 'پرۆگرامی ناودار، کۆتا کاتی ڕاستەقینە، و ئەو بەڵگەنامانەی پێش دەستپێکردن پێویستن.',
                    'ar' => 'برامج محددة بالاسم، ومواعيد نهائية حقيقية، والمستندات المطلوبة قبل أن تبدأ.',
                ],
                'who' => [
                    'en' => 'Bahar Jamal · Click Iraq Foundation',
                    'ku' => 'بەهار جەمال · دەزگای کلیک عێراق',
                    'ar' => 'بهار جمال · مؤسسة كليك العراق',
                ],
                'speakers' => ['Bahar Jamal Rashid' => 'Speaker'],
            ],
            [
                'day' => 2, 'starts_at' => '13:00', 'ends_at' => '14:30', 'duration_label' => '90 min',
                'type' => 'Workshop', 'track' => 'fair', 'hall' => 'C', 'languages' => 'EN',
                'title' => [
                    'en' => 'CV and interview clinic',
                    'ku' => 'کلینیکی CV و چاوپێکەوتن',
                    'ar' => 'عيادة السيرة الذاتية والمقابلة',
                ],
                'description' => [
                    'en' => 'Bring a draft CV. You leave with it marked up.',
                    'ku' => 'ڕەشنووسی CVەکەت بهێنە. بە نیشانەکراوی دەیبەیتەوە.',
                    'ar' => 'أحضر مسودة سيرتك الذاتية. ستغادر وقد كُتبت عليها الملاحظات.',
                ],
                'who' => ['en' => 'MJ Holding talent team', 'ku' => 'تیمی تواناکانی ئێم جەی هۆڵدینگ', 'ar' => 'فريق المواهب في إم جيه هولدنغ'],
            ],
            [
                'day' => 2, 'starts_at' => '15:00', 'ends_at' => '16:15', 'duration_label' => '75 min',
                'type' => 'Panel', 'track' => 'fair', 'hall' => 'A', 'languages' => 'KU · EN',
                'title' => [
                    'en' => 'Studying abroad: cost, visas and coming back',
                    'ku' => 'خوێندن لە دەرەوە: تێچوو، ڤیزە و گەڕانەوە',
                    'ar' => 'الدراسة في الخارج: التكلفة والتأشيرات والعودة',
                ],
                'description' => [
                    'en' => 'Students who went, and what the brochures left out.',
                    'ku' => 'ئەو قوتابییانەی چوون، و ئەوەی بڵاوکراوەکان نەیانوت.',
                    'ar' => 'طلبة سافروا، وما أغفلته الكتيبات.',
                ],
                'who' => ['en' => 'Panel of 5', 'ku' => 'پانێلی 5 کەسی', 'ar' => 'جلسة بخمسة متحدثين'],
                'speakers' => ['Dr. Elif Kaya' => 'Panelist'],
            ],
            [
                'day' => 2, 'starts_at' => '17:00', 'ends_at' => '18:00', 'duration_label' => '60 min',
                'type' => 'Seminar', 'track' => 'fair', 'hall' => 'C', 'languages' => 'KU',
                'title' => [
                    'en' => 'AI and the jobs that will exist in 2032',
                    'ku' => 'AI و ئەو کارانەی لە 2032 دەبن',
                    'ar' => 'الذكاء الاصطناعي والوظائف التي ستوجد في 2032',
                ],
                'description' => [
                    'en' => 'What to learn now that does not expire in three years.',
                    'ku' => 'چی فێربیت ئێستا کە دوای سێ ساڵ بەسەرناچێت.',
                    'ar' => 'ما الذي تتعلمه الآن ولا تنتهي صلاحيته بعد ثلاث سنوات.',
                ],
                'who' => ['en' => 'Dr. Hemin Latif', 'ku' => 'د. هێمن لەتیف', 'ar' => 'د. همن لطيف'],
                'speakers' => ['Dr. Hemin Latif Sabir' => 'Speaker'],
            ],

            // ---------------------------------------------------------- Day 3
            [
                'day' => 3, 'starts_at' => '10:00', 'ends_at' => '20:00', 'duration_label' => 'All day',
                'type' => 'Exhibition', 'track' => 'fair', 'hall' => null, 'hall_label' => 'Halls A & C', 'languages' => 'KU · AR · EN',
                'bookable' => false,
                'title' => [
                    'en' => 'University and institute floor open',
                    'ku' => 'هۆڵی زانکۆ و پەیمانگاکان کراوەیە',
                    'ar' => 'افتتاح صالة الجامعات والمعاهد',
                ],
                'description' => [
                    'en' => 'Final day. Several institutions hold on-site conditional offers.',
                    'ku' => 'ڕۆژی کۆتایی. چەند دامەزراوەیەک پێشکەشکراوی مەرجدار لە شوێنەکەدا دەدەن.',
                    'ar' => 'اليوم الأخير. تقدّم عدة مؤسسات عروض قبول مشروطة في الموقع.',
                ],
                'who' => ['en' => 'All exhibitors', 'ku' => 'هەموو بەشداربووان', 'ar' => 'كل العارضين'],
            ],
            [
                'day' => 3, 'starts_at' => '11:30', 'ends_at' => '13:00', 'duration_label' => '90 min',
                'type' => 'Workshop', 'track' => 'fair', 'hall' => 'C', 'languages' => 'KU',
                'title' => [
                    'en' => 'Vocational and technical pathways',
                    'ku' => 'ڕێڕەوە پیشەیی و تەکنیکییەکان',
                    'ar' => 'المسارات المهنية والتقنية',
                ],
                'description' => [
                    'en' => 'Trades, technical institutes and apprenticeships, with wage data.',
                    'ku' => 'پیشە، پەیمانگا تەکنیکییەکان و ڕاهێنانی کارگە، لەگەڵ داتای موچە.',
                    'ar' => 'الحرف والمعاهد التقنية والتدريب المهني، مع بيانات الأجور.',
                ],
                'who' => ['en' => 'Technical Institute of Sulaimani', 'ku' => 'پەیمانگای تەکنیکی سلێمانی', 'ar' => 'المعهد التقني في السليمانية'],
            ],
            [
                'day' => 3, 'starts_at' => '14:00', 'ends_at' => '15:15', 'duration_label' => '75 min',
                'type' => 'Panel', 'track' => 'fair', 'hall' => 'A', 'languages' => 'KU',
                'title' => [
                    'en' => 'For parents: what your child is deciding',
                    'ku' => 'بۆ دایک و باوکان: منداڵەکەت چی بڕیار دەدات',
                    'ar' => 'لأولياء الأمور: ما الذي يقرره ابنك',
                ],
                'description' => [
                    'en' => 'Cost, distance, safety and outcomes, answered plainly.',
                    'ku' => 'تێچوو، دووری، ئاسایش و ئەنجام، بە سادەیی وەڵام دەدرێنەوە.',
                    'ar' => 'التكلفة والمسافة والسلامة والنتائج، بإجابات مباشرة.',
                ],
                'who' => [
                    'en' => 'Panel of 3 · moderated by Nazdar Omer',
                    'ku' => 'پانێلی 3 کەسی · بەڕێوەبردنی نازدار عومەر',
                    'ar' => 'جلسة بثلاثة متحدثين · تديرها نازدار عمر',
                ],
                'speakers' => ['Nazdar Omer Faraj' => 'Moderator'],
            ],
            [
                'day' => 3, 'starts_at' => '16:00', 'ends_at' => '17:00', 'duration_label' => '60 min',
                'type' => 'Ceremony', 'track' => 'fair', 'hall' => 'B', 'languages' => 'KU · EN',
                'title' => [
                    'en' => 'Awards and closing',
                    'ku' => 'خەڵاتەکان و کۆتایی',
                    'ar' => 'الجوائز والختام',
                ],
                'description' => [
                    'en' => 'Student competition results and the 2027 announcement.',
                    'ku' => 'ئەنجامەکانی پێشبڕکێی قوتابیان و ڕاگەیاندنی 2027.',
                    'ar' => 'نتائج مسابقة الطلبة وإعلان 2027.',
                ],
                'who' => ['en' => 'Next Step Organization', 'ku' => 'ڕێکخراوی هەنگاوی داهاتوو', 'ar' => 'منظمة Next Step'],
            ],
        ];

        foreach ($definitions as $i => $definition) {
            $speakerRoles = $definition['speakers'] ?? [];
            $hallCode = $definition['hall'] ?? null;
            unset($definition['speakers'], $definition['hall']);

            $session = EventSession::updateOrCreate(
                ['slug' => Str::slug($definition['title']['en'].'-day-'.$definition['day'])],
                $definition + [
                    'hall_id' => $hallCode ? $halls[$hallCode]->id : null,
                    'year' => 2026,
                    'published' => true,
                    'sort' => $i + 1,
                ]
            );

            $sync = [];
            foreach ($speakerRoles as $name => $role) {
                if (isset($speakers[$name])) {
                    $sync[$speakers[$name]->id] = ['role' => $role, 'sort' => count($sync)];
                }
            }
            $session->speakers()->sync($sync);
        }
    }
}
