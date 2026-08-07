<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * News, and the student-facing blog.
 *
 * Headlines, standfirsts and excerpts are seeded in all three languages. Article
 * bodies are seeded in English only: they are placeholder history that the
 * newsroom replaces, and the admin's translation indicator shows them as
 * incomplete until it does.
 */
class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $categories = $this->categories();
        $this->news($categories);
        $this->blog($categories);
    }

    /** @return array<string, Category> */
    private function categories(): array
    {
        $definitions = [
            ['news', 'announcements', '#B64698', ['en' => 'Announcements', 'ku' => 'ڕاگەیاندن', 'ar' => 'إعلانات']],
            ['news', 'partnerships', '#2C4BE0', ['en' => 'Partnerships', 'ku' => 'هاوبەشی', 'ar' => 'شراكات']],
            ['news', 'activities', '#B64698', ['en' => 'Activities', 'ku' => 'چالاکی', 'ar' => 'أنشطة']],
            ['news', 'press-coverage', '#4A4B4D', ['en' => 'Press coverage', 'ku' => 'پۆشینی ڕاگەیاندن', 'ar' => 'تغطية صحفية']],
            ['news', 'government-relations', '#2C4BE0', ['en' => 'Government relations', 'ku' => 'پەیوەندی حکومی', 'ar' => 'العلاقات الحكومية']],
            ['news', 'sdg-initiatives', '#3F7E44', ['en' => 'SDG initiatives', 'ku' => 'دەستپێشخەری SDG', 'ar' => 'مبادرات SDG']],
            ['blog', 'choosing-a-major', '#B64698', ['en' => 'Choosing a major', 'ku' => 'هەڵبژاردنی بەش', 'ar' => 'اختيار التخصص']],
            ['blog', 'applications', '#2C4BE0', ['en' => 'Applications', 'ku' => 'داواکارییەکان', 'ar' => 'الطلبات']],
            ['blog', 'careers', '#0E9B94', ['en' => 'Careers', 'ku' => 'پیشە', 'ar' => 'المهن']],
        ];

        $categories = [];
        foreach ($definitions as $i => [$type, $slug, $accent, $name]) {
            $categories[$slug] = Category::updateOrCreate(
                ['type' => $type, 'slug' => $slug],
                ['name' => $name, 'accent' => $accent, 'sort' => $i]
            );
        }

        return $categories;
    }

    private function news(array $categories): void
    {
        $articles = [
            [
                'category' => 'government-relations', 'date' => '2026-07-22', 'pinned' => true,
                'title' => [
                    'en' => 'Ministry confirms the Day 1 conference programme',
                    'ku' => 'وەزارەت پرۆگرامی کۆنفرانسی ڕۆژی یەکەم پشتڕاست دەکاتەوە',
                    'ar' => 'الوزارة تؤكد برنامج مؤتمر اليوم الأول',
                ],
                'excerpt' => [
                    'en' => 'The Ministry of Higher Education and Scientific Research will open the 4th edition and chair the closed roundtable on cross-border recognition of Kurdistan Region qualifications.',
                    'ku' => 'وەزارەتی خوێندنی باڵا و توێژینەوەی زانستی خولی چوارەم دەکاتەوە و سەرۆکایەتی مێزە گردە داخراوەکەی دانپێدانانی سنووربەزێن بە بڕوانامەکانی هەرێمی کوردستان دەکات.',
                    'ar' => 'تفتتح وزارة التعليم العالي والبحث العلمي الدورة الرابعة وتترأس الطاولة المستديرة المغلقة حول الاعتراف بشهادات إقليم كوردستان عبر الحدود.',
                ],
                'placeholder' => 'Photo — ministerial signing, July 2026',
                'quote' => ['en' => 'A placement system is only as fair as the number of students who understand it.'],
                'quote_by' => ['en' => 'Dr. Rezan Ahmed Kareem, Director General for Scholarships, MOHE'],
                'body' => '<p>The Day 1 programme of Next Step Fair 2026 is confirmed. The Ministry of Higher Education and Scientific Research will deliver the opening remarks on 28 September and chair the afternoon roundtable on the recognition of Kurdistan Region qualifications outside Iraq.</p><p>Nine directorates and four diplomatic missions have confirmed delegations. The conference runs in Hall B of the Cultural Factory from 09:00 to 18:00, with simultaneous Kurdish, Arabic and English interpretation in every session.</p><p>The morning session, Admission at scale, examines how the Zankoline placement system works in practice: where transparency has improved since 2024, where students still lose places to missing paperwork, and how many appeals are never filed because nobody explains that they can be.</p><p>Registration for the conference is separate from the fair. Delegates RSVP with their institution and title, and receive a badge carrying their name and institution by email. Fair registration for students and parents remains free and open on WhatsApp.</p><p>The afternoon roundtable is closed to invited delegations. Its output is a short recommendations note, published with the edition impact report in December.</p>',
                'facts' => [
                    ['k' => 'Date', 'v' => '28 September 2026'],
                    ['k' => 'Location', 'v' => 'Hall B, Cultural Factory'],
                    ['k' => 'Delegations', 'v' => '9 directorates, 4 missions'],
                    ['k' => 'Languages', 'v' => 'KU · AR · EN'],
                ],
            ],
            [
                'category' => 'partnerships', 'date' => '2026-07-14',
                'title' => [
                    'en' => 'Four Turkish universities join the 2026 floor',
                    'ku' => 'چوار زانکۆی تورکی دەبنە بەشێک لە شانۆی 2026',
                    'ar' => 'أربع جامعات تركية تنضم إلى صالة 2026',
                ],
                'excerpt' => [
                    'en' => 'Bilkent, METU, Bahçeşehir and Sabancı will hold desks in Hall A across all three days, with on-site conditional offers.',
                    'ku' => 'بیلکەنت، METU، باهچەشەهیر و سابانجی بە درێژایی هەر سێ ڕۆژ مێزیان لە هۆڵی A دەبێت، لەگەڵ پێشکەشکراوی مەرجدار لە شوێنەکەدا.',
                    'ar' => 'ستقيم بيلكنت وMETU وبهتشة شهير وسابانجي طاولات في القاعة A طوال الأيام الثلاثة، مع عروض قبول مشروطة في الموقع.',
                ],
                'placeholder' => 'Photo — Hall A desks, 2025',
                'body' => '<p>Four Turkish universities have confirmed desks at the 2026 edition. Admission staff from Bilkent, Middle East Technical University, Bahçeşehir and Sabancı will sit in Hall A for all three days and can issue conditional offers at the desk for students who bring their grades.</p><p>Next Step Fair 2026 runs from 28 to 30 September at the Cultural Factory in Sulaimani. Entry is free for students, graduates and parents, and registration takes about two minutes on a phone.</p>',
            ],
            [
                'category' => 'sdg-initiatives', 'date' => '2026-07-02',
                'title' => [
                    'en' => '2025 carbon footprint statement published',
                    'ku' => 'ڕاگەیاندنی شوێنپێی کاربۆنی 2025 بڵاو کرایەوە',
                    'ar' => 'نشر بيان البصمة الكربونية لعام 2025',
                ],
                'excerpt' => [
                    'en' => 'Printed material fell 62% against 2024 after the move to digital badges, directories and agendas.',
                    'ku' => 'کەرەستەی چاپکراو 62% کەمی کرد بەراورد بە 2024 دوای گواستنەوە بۆ باج، ڕێنمایی و خشتەی دیجیتاڵ.',
                    'ar' => 'انخفضت المواد المطبوعة 62% مقارنة بـ2024 بعد التحول إلى البطاقات والأدلة والبرامج الرقمية.',
                ],
                'placeholder' => 'Photo — signage reuse',
                'body' => '<p>The carbon footprint statement for the 2025 edition is published today. Printed material fell 62% against 2024, driven almost entirely by moving badges, the exhibitor directory and the agenda onto phones.</p><p>The statement covers printed material, signage reuse, catering waste and speaker travel, and is published alongside the edition impact report.</p>',
            ],
            [
                'category' => 'announcements', 'date' => '2026-06-18',
                'title' => [
                    'en' => 'Registration opens for the 4th edition',
                    'ku' => 'تۆمارکردن بۆ خولی چوارەم دەستی پێکرد',
                    'ar' => 'فتح التسجيل للدورة الرابعة',
                ],
                'excerpt' => [
                    'en' => 'Students and parents register in four steps and receive a QR badge on WhatsApp. Entry stays free.',
                    'ku' => 'قوتابی و دایک و باوکان بە چوار هەنگاو تۆمار دەکەن و باجی QR لە واتسئاپ وەردەگرن. چوونەژوورەوە بەخۆڕایی دەمێنێتەوە.',
                    'ar' => 'يسجّل الطلبة وأولياء الأمور في أربع خطوات ويتلقون بطاقة QR على واتساب. ويبقى الدخول مجانياً.',
                ],
                'placeholder' => 'Photo — registration desk',
                'body' => '<p>Registration for the 4th edition is open. Students and parents complete four short steps, verify their phone number, and receive a QR badge on WhatsApp in Kurdish, Arabic or English. Entry is free, as it has been every year.</p><p>Conference RSVP for government and official delegates is a separate form, and confirmations there arrive by email with a badge PDF attached.</p>',
            ],
            [
                'category' => 'activities', 'date' => '2026-05-30',
                'title' => [
                    'en' => 'School roadshow reaches 34 schools in Sulaimani and Halabja',
                    'ku' => 'گەشتی قوتابخانەکان دەگاتە 34 قوتابخانە لە سلێمانی و هەڵەبجە',
                    'ar' => 'جولة المدارس تصل إلى 34 مدرسة في السليمانية وحلبجة',
                ],
                'excerpt' => [
                    'en' => 'Counsellors ran Zankoline sessions with 12th grade classes ahead of the September fair.',
                    'ku' => 'ڕاوێژکاران دانیشتنی زانکۆلاینیان لەگەڵ پۆلەکانی 12 بەڕێوەبرد پێش پێشانگای ئەیلول.',
                    'ar' => 'أدار المرشدون جلسات زانكۆلاین مع صفوف الثاني عشر قبل معرض أيلول.',
                ],
                'placeholder' => 'Photo — school session',
                'body' => '<p>Between March and May, the guidance team visited 34 schools across Sulaimani and Halabja, sitting with 12th grade classes to walk through the Zankoline application form line by line before the September fair.</p>',
            ],
            [
                'category' => 'press-coverage', 'date' => '2026-05-11',
                'title' => [
                    'en' => 'NRT and Rudaw confirmed as media partners',
                    'ku' => 'NRT و ڕووداو وەک هاوبەشی میدیا پشتڕاست کرانەوە',
                    'ar' => 'تأكيد NRT ورووداو شريكين إعلاميين',
                ],
                'excerpt' => [
                    'en' => 'Both broadcasters will cover the opening ceremony and the Day 1 conference programme live.',
                    'ku' => 'هەردوو کەناڵ ڕێوڕەسمی کردنەوە و پرۆگرامی کۆنفرانسی ڕۆژی یەکەم بە ڕاستەوخۆ دەگەیەنن.',
                    'ar' => 'ستنقل القناتان حفل الافتتاح وبرنامج مؤتمر اليوم الأول مباشرة.',
                ],
                'placeholder' => 'Photo — press area',
                'body' => '<p>NRT and Rudaw join the 2026 edition as media partners. Both will broadcast the opening ceremony and carry the Day 1 conference programme live, with a dedicated press area beside Hall B.</p>',
            ],
            [
                'category' => 'government-relations', 'date' => '2025-11-20', 'year' => 2025,
                'title' => [
                    'en' => 'Sulaimani Governorate extends the venue agreement to 2028',
                    'ku' => 'پارێزگای سلێمانی ڕێککەوتننامەی شوێنەکە تا 2028 درێژ دەکاتەوە',
                    'ar' => 'محافظة السليمانية تمدد اتفاقية المكان حتى 2028',
                ],
                'excerpt' => [
                    'en' => 'The Cultural Factory is confirmed as the fair venue for the next three editions.',
                    'ku' => 'کارگەی کولتوری وەک شوێنی پێشانگا بۆ سێ خولی داهاتوو پشتڕاست کرایەوە.',
                    'ar' => 'تأكيد مصنع الثقافة مكاناً للمعرض في الدورات الثلاث القادمة.',
                ],
                'placeholder' => 'Photo — Cultural Factory',
                'body' => '<p>The Sulaimani Governorate has extended the Cultural Factory venue agreement through 2028. The fair keeps the same three halls, which means the floor plan students learned this year stays recognisable for the next three editions.</p>',
            ],
            [
                'category' => 'sdg-initiatives', 'date' => '2025-10-03', 'year' => 2025,
                'title' => [
                    'en' => 'Next Step registered as an SDG Acceleration Action with UN DESA',
                    'ku' => 'هەنگاوی داهاتوو وەک کردەی خێراکردنی SDG لای UN DESA تۆمار کرا',
                    'ar' => 'تسجيل Next Step كمبادرة لتسريع أهداف التنمية المستدامة لدى UN DESA',
                ],
                'excerpt' => [
                    'en' => 'The commitment covers five goals with indicators published annually and verified publicly on Act4SDGs.',
                    'ku' => 'پابەندییەکە پێنج ئامانج دەگرێتەوە لەگەڵ پێوەرەکان کە ساڵانە بڵاو دەکرێنەوە و بە گشتی لە Act4SDGs پشتڕاست دەکرێنەوە.',
                    'ar' => 'يشمل الالتزام خمسة أهداف بمؤشرات تُنشر سنوياً ويمكن التحقق منها علناً عبر Act4SDGs.',
                ],
                'placeholder' => 'Photo — SDG panel',
                'body' => '<p>Next Step is now registered as an SDG Acceleration Action with the UN Department of Economic and Social Affairs. The commitment names five goals, each with an indicator reported every year and verifiable publicly on Act4SDGs.</p>',
            ],
            [
                'category' => 'activities', 'date' => '2025-10-01', 'year' => 2025,
                'title' => [
                    'en' => '14,600 visitors across the 2025 edition',
                    'ku' => '14,600 سەردانکەر لە خولی 2025',
                    'ar' => '14,600 زائر في دورة 2025',
                ],
                'excerpt' => [
                    'en' => '28 institutions, 19 seminars and the first scholarship desk. The full impact report is available to download.',
                    'ku' => '28 دامەزراوە، 19 سیمینار و یەکەم مێزی سکۆلەرشیپ. ڕاپۆرتی تەواوی کاریگەری بۆ داگرتن بەردەستە.',
                    'ar' => '28 مؤسسة و19 ندوة وأول مكتب للمنح. تقرير الأثر الكامل متاح للتنزيل.',
                ],
                'placeholder' => 'Photo — the hall from above',
                'body' => '<p>The 2025 edition closed with 14,600 visitors over two days, 28 participating institutions and 19 seminars. It was also the first year with a dedicated scholarship desk, which handled 1,140 enquiries.</p>',
            ],
        ];

        foreach ($articles as $i => $article) {
            $date = Carbon::parse($article['date']);

            Post::updateOrCreate(
                ['slug' => Str::slug($article['title']['en'])],
                [
                    'type' => Post::TYPE_NEWS,
                    'category_id' => $categories[$article['category']]->id,
                    'title' => $article['title'],
                    'excerpt' => $article['excerpt'],
                    'standfirst' => $article['excerpt'],
                    'body' => ['en' => $article['body']],
                    'cover_placeholder' => $article['placeholder'],
                    'cover_caption' => ['en' => $date->format('j F Y').', Sulaimani. Photography by the Next Step media team.'],
                    'author_name' => 'Next Step Organization',
                    'author_role' => ['en' => 'Communications team', 'ku' => 'تیمی پەیوەندییەکان', 'ar' => 'فريق الاتصالات'],
                    'quote' => $article['quote'] ?? null,
                    'quote_by' => $article['quote_by'] ?? null,
                    'facts' => $article['facts'] ?? null,
                    'pinned' => $article['pinned'] ?? false,
                    'status' => 'published',
                    'published_at' => $date,
                    'year' => $article['year'] ?? $date->year,
                ]
            );
        }
    }

    private function blog(array $categories): void
    {
        $posts = [
            [
                'category' => 'choosing-a-major', 'date' => '2026-07-08',
                'title' => [
                    'en' => 'Four questions to ask at every university desk',
                    'ku' => 'چوار پرسیار کە لە هەموو مێزێکی زانکۆدا بیانکە',
                    'ar' => 'أربعة أسئلة اسألها عند كل طاولة جامعية',
                ],
                'excerpt' => [
                    'en' => 'Entry score, total cost, what the last graduating class is doing now, and what happens if you fail a year.',
                    'ku' => 'نمرەی وەرگرتن، کۆی تێچوو، ئێستا کۆتا خولی دەرچووان چی دەکەن، و چی ڕوودەدات ئەگەر ساڵێک بکەویت.',
                    'ar' => 'درجة القبول، والكلفة الإجمالية، وماذا يفعل آخر فوج تخرّج الآن، وماذا يحدث إن رسبت في سنة.',
                ],
                'body' => '<h2>Ask for the entry score, not the average</h2><p>Every institution publishes an entry score. Ask for last year\'s actual cut-off for the programme you want, not the faculty average — they are often ten points apart.</p><h2>Ask for the total cost, not the tuition</h2><p>Tuition is one line. Ask about registration fees, lab fees, resit fees, and whether the fee is fixed for the whole degree or set annually.</p><h2>Ask what last year\'s graduates are doing</h2><p>If the desk cannot answer this, the programme is not tracking it. That is itself an answer.</p><h2>Ask what happens if you fail a year</h2><p>Repeat rules, resit costs and whether a failed year can be carried differ widely between institutions.</p>',
            ],
            [
                'category' => 'applications', 'date' => '2026-06-25',
                'title' => [
                    'en' => 'The Zankoline form, line by line',
                    'ku' => 'فۆرمی زانکۆلاین، دێڕ بە دێڕ',
                    'ar' => 'استمارة زانكۆلاین، سطراً بسطر',
                ],
                'excerpt' => [
                    'en' => 'Where students lose places to missing paperwork, and what to have on your phone before you start.',
                    'ku' => 'قوتابیان لەکوێ بەهۆی کەمی بەڵگەنامەوە شوێن لەدەست دەدەن، و چی لەسەر مۆبایلەکەت بێت پێش دەستپێکردن.',
                    'ar' => 'أين يفقد الطلبة مقاعدهم بسبب نقص المستندات، وما الذي يجب أن يكون على هاتفك قبل أن تبدأ.',
                ],
                'body' => '<h2>Before you open the form</h2><p>Have a photograph of your national ID, your grade transcript and any language certificate on your phone, each under 2 MB.</p><h2>The choices section</h2><p>Order matters. The system places you at the highest choice your score reaches, so a popular programme listed second is not a safety net.</p><h2>After you submit</h2><p>Keep the confirmation number. Appeals are possible, and most students never file one because nobody tells them they can.</p>',
            ],
            [
                'category' => 'careers', 'date' => '2026-06-10',
                'title' => [
                    'en' => 'Vocational is not the fallback',
                    'ku' => 'پیشەیی چارەی دواین نییە',
                    'ar' => 'المسار المهني ليس خطة بديلة',
                ],
                'excerpt' => [
                    'en' => 'A technician who finishes a two-year diploma and earns from the first month is not behind anyone.',
                    'ku' => 'تەکنیشیانێک کە دیپلۆمی دوو ساڵ تەواو دەکات و لە مانگی یەکەمەوە داهاتی هەیە، لە کەس دوانەکەوتووە.',
                    'ar' => 'الفني الذي ينهي دبلوماً لسنتين ويكسب من الشهر الأول ليس متأخراً عن أحد.',
                ],
                'body' => '<h2>What the wage data says</h2><p>Technical institutes publish starting wages by trade. In several fields the two-year diploma reaches the four-year graduate\'s starting salary within eighteen months.</p><h2>Where to look at the fair</h2><p>Technical institutes hold zone A4 in Hall A, beside the workshop corridor into Hall C, and run demonstrations through all three days.</p>',
            ],
        ];

        foreach ($posts as $post) {
            $date = Carbon::parse($post['date']);

            Post::updateOrCreate(
                ['slug' => Str::slug($post['title']['en'])],
                [
                    'type' => Post::TYPE_BLOG,
                    'category_id' => $categories[$post['category']]->id,
                    'title' => $post['title'],
                    'excerpt' => $post['excerpt'],
                    'standfirst' => $post['excerpt'],
                    'body' => ['en' => $post['body']],
                    'cover_placeholder' => 'Illustration — guidance',
                    'author_name' => 'Next Step guidance team',
                    'author_role' => ['en' => 'Student guidance', 'ku' => 'ڕێنمایی قوتابیان', 'ar' => 'إرشاد الطلبة'],
                    'author_bio' => 'The guidance team runs the Zankoline clinics and the school roadshow between March and May.',
                    'status' => 'published',
                    'published_at' => $date,
                    'year' => $date->year,
                ]
            );
        }
    }
}
