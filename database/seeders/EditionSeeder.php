<?php

namespace Database\Seeders;

use App\Models\Edition;
use Illuminate\Database\Seeder;

/**
 * Past editions, 2023–2025. The year is a route parameter, so adding 2027 is a
 * row in this table rather than a code change.
 *
 * Headlines, summaries and theme titles are seeded in all three languages; the
 * organiser's address is seeded in English and flagged incomplete in the admin
 * until the delivered Kurdish text is transcribed.
 */
class EditionSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->editions() as $edition) {
            Edition::updateOrCreate(['year' => $edition['year']], $edition);
        }
    }

    private function editions(): array
    {
        return [
            [
                'year' => 2025,
                'edition_no' => 3,
                'edition_label' => ['en' => '3rd edition', 'ku' => 'خولی سێیەم', 'ar' => 'الدورة الثالثة'],
                'dates_label' => ['en' => '29–30 September 2025', 'ku' => '29–30 ئەیلولی 2025', 'ar' => '29–30 أيلول 2025'],
                'venue_label' => [
                    'en' => 'Cultural Factory, Sulaimani',
                    'ku' => 'کارگەی کولتوری، سلێمانی',
                    'ar' => 'مصنع الثقافة، السليمانية',
                ],
                'headline' => [
                    'en' => 'Scholarships moved from rumour to paperwork',
                    'ku' => 'سکۆلەرشیپ لە دەنگۆوە گۆڕا بۆ بەڵگەنامە',
                    'ar' => 'المنح انتقلت من الإشاعة إلى الأوراق',
                ],
                'summary' => [
                    'en' => 'The third edition doubled the seminar programme and opened the first dedicated scholarship desk, which handled 1,140 enquiries in two days. It was also the year Next Step registered as an SDG Acceleration Action with UN DESA.',
                    'ku' => 'خولی سێیەم پرۆگرامی سیمیناری دووهێندە کرد و یەکەم مێزی تایبەتی سکۆلەرشیپی کردەوە، کە لە دوو ڕۆژدا 1,140 پرسیاری وەڵام دایەوە. هەروەها ئەو ساڵە بوو کە هەنگاوی داهاتوو وەک کردەی خێراکردنی SDG لای UN DESA تۆمار کرا.',
                    'ar' => 'ضاعفت الدورة الثالثة برنامج الندوات وافتتحت أول مكتب مخصص للمنح تعامل مع 1,140 استفساراً في يومين. وفي تلك السنة أيضاً سُجّلت Next Step كمبادرة لتسريع أهداف التنمية المستدامة لدى UN DESA.',
                ],
                'stats' => [
                    ['k' => ['en' => 'Visitors', 'ku' => 'سەردانکەر', 'ar' => 'زائر'], 'v' => '14,600'],
                    ['k' => ['en' => 'Institutions', 'ku' => 'دامەزراوە', 'ar' => 'مؤسسة'], 'v' => '28'],
                    ['k' => ['en' => 'Exhibitors', 'ku' => 'بەشداربوو', 'ar' => 'عارض'], 'v' => '21'],
                    ['k' => ['en' => 'Seminars', 'ku' => 'سیمینار', 'ar' => 'ندوة'], 'v' => '19'],
                    ['k' => ['en' => 'Panels', 'ku' => 'پانێل', 'ar' => 'جلسة'], 'v' => '7'],
                    ['k' => ['en' => 'Workshops', 'ku' => 'وۆرکشۆپ', 'ar' => 'ورشة'], 'v' => '11'],
                ],
                'theme_title' => [
                    'en' => 'Three themes carried the 2025 programme',
                    'ku' => 'سێ بابەت پرۆگرامی 2025ی هەڵگرت',
                    'ar' => 'ثلاثة محاور حملت برنامج 2025',
                ],
                'themes' => [
                    [
                        'num' => '01', 'accent' => '#B64698',
                        'title' => ['en' => 'Funding the decision', 'ku' => 'خەرجکردنی بڕیارەکە', 'ar' => 'تمويل القرار'],
                        'body' => [
                            'en' => 'Named scholarship programmes with real deadlines, presented by the people who administer them rather than by brochure.',
                            'ku' => 'پرۆگرامی سکۆلەرشیپی ناودار لەگەڵ کۆتا کاتی ڕاستەقینە، لەلایەن ئەو کەسانەوە پێشکەش کران کە بەڕێوەیان دەبەن، نەک بە بڵاوکراوە.',
                            'ar' => 'برامج منح محددة بالاسم بمواعيد نهائية حقيقية، قدّمها من يديرونها لا كتيّب دعائي.',
                        ],
                        'outcome' => [
                            'en' => '1,140 scholarship enquiries at the desk',
                            'ku' => '1,140 پرسیاری سکۆلەرشیپ لەسەر مێزەکە',
                            'ar' => '1,140 استفساراً عن المنح عند المكتب',
                        ],
                    ],
                    [
                        'num' => '02', 'accent' => '#2C4BE0',
                        'title' => ['en' => 'Vocational is not the fallback', 'ku' => 'پیشەیی چارەی دواین نییە', 'ar' => 'المسار المهني ليس بديلاً'],
                        'body' => [
                            'en' => 'Technical institutes given equal floor space to universities, with wage data for each pathway published on the panel.',
                            'ku' => 'پەیمانگا تەکنیکییەکان هەمان شوێنیان پێدرا وەک زانکۆکان، لەگەڵ داتای موچە بۆ هەر ڕێڕەوێک کە لەسەر پانێلەکە بڵاو کرایەوە.',
                            'ar' => 'مُنحت المعاهد التقنية مساحة مساوية للجامعات، مع نشر بيانات الأجور لكل مسار على اللوحة.',
                        ],
                        'outcome' => [
                            'en' => '2,300 students visited the vocational hall',
                            'ku' => '2,300 قوتابی سەردانی هۆڵی پیشەییان کرد',
                            'ar' => '2,300 طالب زاروا القاعة المهنية',
                        ],
                    ],
                    [
                        'num' => '03', 'accent' => '#0E9B94',
                        'title' => ['en' => 'AI and what to learn now', 'ku' => 'AI و ئەوەی ئێستا فێری دەبیت', 'ar' => 'الذكاء الاصطناعي وما تتعلمه الآن'],
                        'body' => [
                            'en' => 'Employers named the skills they screen on, and where automation is already changing entry-level hiring in the region.',
                            'ku' => 'خاوەنکاران ناوی ئەو شارەزاییانەیان برد کە پێیان هەڵدەسەنگێنن، و ئەوەی ئۆتۆماتیککردن لەکوێ ئێستا دامەزراندنی ئاستی سەرەتایی لە هەرێمدا دەگۆڕێت.',
                            'ar' => 'سمّى أصحاب العمل المهارات التي يفرزون على أساسها، وأين تغيّر الأتمتة فعلاً التوظيف في المستويات المبتدئة بالإقليم.',
                        ],
                        'outcome' => [
                            'en' => '9 employer-led sessions',
                            'ku' => '9 دانیشتنی بەڕێوەبراو لەلایەن خاوەنکاران',
                            'ar' => '9 جلسات يقودها أصحاب العمل',
                        ],
                    ],
                ],
                'organizer_name' => 'Avin Qadir',
                'organizer_role' => [
                    'en' => 'Organizer and Co-Founder of Next Step',
                    'ku' => 'ڕێکخەر و هاوبەشی دامەزرێنەری هەنگاوی داهاتوو',
                    'ar' => 'المنظِّمة والشريكة المؤسِّسة لـ Next Step',
                ],
                'speech_where' => [
                    'en' => 'Opening ceremony, 29 September 2025, Hall B',
                    'ku' => 'ڕێوڕەسمی کردنەوە، 29ی ئەیلولی 2025، هۆڵی B',
                    'ar' => 'حفل الافتتاح، 29 أيلول 2025، القاعة B',
                ],
                'speech_quote' => [
                    'en' => 'We are not here to inspire anyone. We are here to answer questions.',
                    'ku' => 'ئێمە لێرە نین بۆ هاندانی کەس. لێرەین بۆ وەڵامدانەوەی پرسیارەکان.',
                    'ar' => 'لسنا هنا لإلهام أحد. نحن هنا للإجابة عن الأسئلة.',
                ],
                'speech' => ['en' => '<p>Two years ago we put nine universities in one hall because a teacher in Chamchamal told us her students were choosing a degree from a photocopied list. This year there are 28 institutions, and the list has been replaced by people who can be asked follow-up questions.</p><p>What changed most this year is money. Cost is the first question every parent asks and the last thing anybody publishes clearly. The scholarship desk exists so that a student can find out, in one conversation, whether the programme they want is affordable and what documents they need before the deadline.</p><p>We also stopped treating vocational programmes as the consolation prize. A technician who finishes a two-year diploma and earns from the first month is not behind anyone. This year the technical institutes stand in the same hall, the same size of booth.</p>'],
                'speaker_count' => [
                    'en' => '31 speakers and panelists',
                    'ku' => '31 قسەکەر و پانێلیست',
                    'ar' => '31 متحدثاً ومشاركاً',
                ],
                'speakers' => [
                    ['name' => 'Dr. Rezan Ahmed Kareem', 'role' => 'Director General, Scholarships', 'org' => 'MOHE', 'role2' => 'Keynote', 'accent' => '#2C4BE0'],
                    ['name' => 'Prof. Sara Hiwa Mustafa', 'role' => 'Vice President, Academic Affairs', 'org' => 'University of Sulaimani', 'role2' => 'Panelist', 'accent' => '#2C4BE0'],
                    ['name' => 'Bahar Jamal Rashid', 'role' => 'Programme Director', 'org' => 'Click Iraq Foundation', 'role2' => 'Speaker', 'accent' => '#B64698'],
                    ['name' => 'Dr. Hemin Latif Sabir', 'role' => 'Head of Computer Science', 'org' => 'AUIS', 'role2' => 'Speaker', 'accent' => '#B64698'],
                ],
                'panel_note' => [
                    'en' => '19 seminars, 7 panels, 11 workshops',
                    'ku' => '19 سیمینار، 7 پانێل، 11 وۆرکشۆپ',
                    'ar' => '19 ندوة و7 جلسات و11 ورشة',
                ],
                'panels' => [
                    ['day' => 'Day 1', 'time' => '11:00', 'type' => 'Panel', 'hall' => 'Hall B', 'title' => 'Who actually funds Kurdistan Region students', 'who' => 'MOHE · Click Iraq · British Council · Rwanga Foundation', 'attendance' => '420'],
                    ['day' => 'Day 1', 'time' => '14:00', 'type' => 'Seminar', 'hall' => 'Hall C', 'title' => 'The Zankoline form, line by line', 'who' => 'Next Step guidance team', 'attendance' => '610'],
                    ['day' => 'Day 1', 'time' => '16:30', 'type' => 'Panel', 'hall' => 'Hall A', 'title' => 'Vocational pathways and what they pay', 'who' => 'Technical Institute of Sulaimani · Lafarge · Rasan Group', 'attendance' => '380'],
                    ['day' => 'Day 2', 'time' => '12:00', 'type' => 'Panel', 'hall' => 'Hall B', 'title' => 'Studying abroad: cost, visas and coming back', 'who' => 'Five graduates · moderated by Nazdar Omer', 'attendance' => '540'],
                    ['day' => 'Day 2', 'time' => '15:00', 'type' => 'Workshop', 'hall' => 'Hall C', 'title' => 'CV and interview clinic', 'who' => 'MJ Holding talent team', 'attendance' => '290'],
                ],
                'sponsor_note' => [
                    'en' => '34 partners, sponsors and exhibitors supported the 2025 edition.',
                    'ku' => '34 هاوبەش، سپۆنسەر و بەشداربوو پشتیوانی خولی 2025یان کرد.',
                    'ar' => 'دعم دورة 2025 عدد 34 شريكاً وراعياً وعارضاً.',
                ],
                'sponsor_tiers' => [
                    ['tier' => ['en' => 'Strategic partners', 'ku' => 'هاوبەشە ستراتیژییەکان', 'ar' => 'الشركاء الاستراتيجيون'], 'accent' => '#2C4BE0', 'note' => ['en' => 'Convening institutions'], 'logos' => ['MOHE', 'Kurdistan Regional Government', 'Sulaimani Governorate']],
                    ['tier' => ['en' => 'Institutional supporters', 'ku' => 'پشتیوانە دامەزراوەییەکان', 'ar' => 'الداعمون المؤسسيون'], 'accent' => '#2C4BE0', 'note' => ['en' => 'Multi-year supporters'], 'logos' => ['MJ Holding', 'Qaiwan Group', 'Halabja Group', 'Click Iraq', 'Lafarge']],
                    ['tier' => ['en' => 'Sponsors', 'ku' => 'سپۆنسەرەکان', 'ar' => 'الرعاة'], 'accent' => '#B64698', 'note' => ['en' => 'Platinum, gold and silver'], 'logos' => ['Asiacell', 'KIB Bank', 'Fastlink', 'Zain Cash', 'Rwanga', 'IT Academy', 'Korek', 'Empire World']],
                    ['tier' => ['en' => 'Media partners', 'ku' => 'هاوبەشانی میدیا', 'ar' => 'الشركاء الإعلاميون'], 'accent' => '#4A4B4D', 'note' => ['en' => 'Broadcast and digital'], 'logos' => ['NRT', 'Rudaw', 'Esta Media', 'Draw Media']],
                ],
            ],

            [
                'year' => 2024,
                'edition_no' => 2,
                'edition_label' => ['en' => '2nd edition', 'ku' => 'خولی دووەم', 'ar' => 'الدورة الثانية'],
                'dates_label' => ['en' => '1–2 October 2024', 'ku' => '1–2 تشرینی یەکەمی 2024', 'ar' => '1–2 تشرين الأول 2024'],
                'venue_label' => ['en' => 'Cultural Factory, Sulaimani', 'ku' => 'کارگەی کولتوری، سلێمانی', 'ar' => 'مصنع الثقافة، السليمانية'],
                'headline' => [
                    'en' => 'The year the seminar programme arrived',
                    'ku' => 'ئەو ساڵەی پرۆگرامی سیمینار هات',
                    'ar' => 'السنة التي وصل فيها برنامج الندوات',
                ],
                'summary' => [
                    'en' => 'The second edition doubled in size and added a structured seminar programme alongside the exhibition floor. It was the first year parents were registered separately, which changed how the panels were written.',
                    'ku' => 'خولی دووەم قەبارەکەی دووهێندە بوو و پرۆگرامێکی سیمیناری ڕێکخراوی لەگەڵ شانۆی پێشانگا زیاد کرد. یەکەم ساڵ بوو کە دایک و باوکان بە جیا تۆمار کران، ئەمەش شێوازی نووسینی پانێلەکانی گۆڕی.',
                    'ar' => 'تضاعف حجم الدورة الثانية وأضافت برنامج ندوات منظّماً إلى جانب صالة العرض. وكانت أول سنة يُسجَّل فيها أولياء الأمور بشكل منفصل، وهو ما غيّر طريقة كتابة الجلسات.',
                ],
                'stats' => [
                    ['k' => ['en' => 'Visitors', 'ku' => 'سەردانکەر', 'ar' => 'زائر'], 'v' => '9,800'],
                    ['k' => ['en' => 'Institutions', 'ku' => 'دامەزراوە', 'ar' => 'مؤسسة'], 'v' => '19'],
                    ['k' => ['en' => 'Exhibitors', 'ku' => 'بەشداربوو', 'ar' => 'عارض'], 'v' => '14'],
                    ['k' => ['en' => 'Seminars', 'ku' => 'سیمینار', 'ar' => 'ندوة'], 'v' => '12'],
                    ['k' => ['en' => 'Panels', 'ku' => 'پانێل', 'ar' => 'جلسة'], 'v' => '4'],
                    ['k' => ['en' => 'Workshops', 'ku' => 'وۆرکشۆپ', 'ar' => 'ورشة'], 'v' => '6'],
                ],
                'theme_title' => [
                    'en' => 'Two themes shaped the 2024 edition',
                    'ku' => 'دوو بابەت خولی 2024ی شێوە پێدا',
                    'ar' => 'محوران شكّلا دورة 2024',
                ],
                'themes' => [
                    [
                        'num' => '01', 'accent' => '#B64698',
                        'title' => ['en' => 'Parents in the room', 'ku' => 'دایک و باوکان لە ژوورەکەدا', 'ar' => 'أولياء الأمور في القاعة'],
                        'body' => [
                            'en' => 'Separate registration for parents, and panels written for the questions they actually ask: cost, distance and safety.',
                            'ku' => 'تۆمارکردنی جیا بۆ دایک و باوکان، و پانێلەکان بۆ ئەو پرسیارانە نووسران کە بەڕاستی دەیانکەن: تێچوو، دووری و ئاسایش.',
                            'ar' => 'تسجيل منفصل لأولياء الأمور، وجلسات كُتبت للأسئلة التي يطرحونها فعلاً: الكلفة والمسافة والسلامة.',
                        ],
                        'outcome' => ['en' => '1,900 parents registered', 'ku' => '1,900 دایک و باوک تۆمار کران', 'ar' => 'تسجيل 1,900 ولي أمر'],
                    ],
                    [
                        'num' => '02', 'accent' => '#2C4BE0',
                        'title' => ['en' => 'Beyond medicine and engineering', 'ku' => 'زیاتر لە پزیشکی و ئەندازیاری', 'ar' => 'أبعد من الطب والهندسة'],
                        'body' => [
                            'en' => 'Programmes in design, agriculture and education given their own sessions rather than a shared table at the back.',
                            'ku' => 'پرۆگرامەکانی دیزاین، کشتوکاڵ و پەروەردە دانیشتنی تایبەت بە خۆیان پێدرا لەبری مێزێکی هاوبەش لە دواوە.',
                            'ar' => 'مُنحت برامج التصميم والزراعة والتربية جلسات خاصة بها بدل طاولة مشتركة في الخلف.',
                        ],
                        'outcome' => ['en' => '12 seminars across 9 fields', 'ku' => '12 سیمینار لە 9 بواردا', 'ar' => '12 ندوة في 9 مجالات'],
                    ],
                ],
                'organizer_name' => 'Avin Qadir',
                'organizer_role' => [
                    'en' => 'Organizer and Co-Founder of Next Step',
                    'ku' => 'ڕێکخەر و هاوبەشی دامەزرێنەری هەنگاوی داهاتوو',
                    'ar' => 'المنظِّمة والشريكة المؤسِّسة لـ Next Step',
                ],
                'speech_where' => [
                    'en' => 'Opening ceremony, 1 October 2024, Main hall',
                    'ku' => 'ڕێوڕەسمی کردنەوە، 1ی تشرینی یەکەمی 2024، هۆڵی سەرەکی',
                    'ar' => 'حفل الافتتاح، 1 تشرين الأول 2024، القاعة الرئيسية',
                ],
                'speech_quote' => [
                    'en' => 'Last year we counted visitors. This year we started counting what they left with.',
                    'ku' => 'ساڵی پار سەردانکەرمان ژمارد. ئەمساڵ دەستمان کرد بە ژماردنی ئەوەی پێی ڕۆیشتن.',
                    'ar' => 'العام الماضي عددنا الزوار. هذا العام بدأنا نعدّ ما غادروا به.',
                ],
                'speech' => ['en' => '<p>The first edition proved there was demand. Four thousand people came to a hall we were not sure anyone would find. The question for this year was not how many more we could fit, but whether anyone left with a decision instead of a tote bag.</p><p>So we built a programme. Twelve seminars, four panels, and for the first time parents registered in their own right, because a seventeen-year-old rarely makes this decision alone and pretending otherwise wastes everybody\'s afternoon.</p><p>We also widened the field list. Medicine and engineering will always draw the longest queues, but a student who wants to study agriculture or design should not have to find that table by accident.</p>'],
                'speaker_count' => ['en' => '18 speakers and panelists', 'ku' => '18 قسەکەر و پانێلیست', 'ar' => '18 متحدثاً ومشاركاً'],
                'speakers' => [
                    ['name' => 'Prof. Sara Hiwa Mustafa', 'role' => 'Vice President, Academic Affairs', 'org' => 'University of Sulaimani', 'role2' => 'Keynote', 'accent' => '#2C4BE0'],
                    ['name' => 'Nazdar Omer Faraj', 'role' => 'School Counsellor', 'org' => 'Sulaimani Directorate of Education', 'role2' => 'Moderator', 'accent' => '#B64698'],
                    ['name' => 'Ari Salih Mahmood', 'role' => 'Admissions Registrar', 'org' => 'Zankoline / MOHE', 'role2' => 'Panelist', 'accent' => '#2C4BE0'],
                    ['name' => 'Shad Kamal Tahir', 'role' => 'Careers Adviser', 'org' => 'Komar University', 'role2' => 'Speaker', 'accent' => '#B64698'],
                ],
                'panel_note' => ['en' => '12 seminars, 4 panels, 6 workshops', 'ku' => '12 سیمینار، 4 پانێل، 6 وۆرکشۆپ', 'ar' => '12 ندوة و4 جلسات و6 ورش'],
                'panels' => [
                    ['day' => 'Day 1', 'time' => '11:30', 'type' => 'Panel', 'hall' => 'Main hall', 'title' => 'For parents: what your child is deciding', 'who' => 'Three counsellors · moderated by Nazdar Omer', 'attendance' => '470'],
                    ['day' => 'Day 1', 'time' => '14:00', 'type' => 'Seminar', 'hall' => 'Room 2', 'title' => 'How Zankoline placement actually works', 'who' => 'Ari Salih Mahmood, Zankoline', 'attendance' => '390'],
                    ['day' => 'Day 2', 'time' => '11:00', 'type' => 'Panel', 'hall' => 'Main hall', 'title' => 'Careers nobody told you about', 'who' => 'Design, agriculture and education graduates', 'attendance' => '260'],
                    ['day' => 'Day 2', 'time' => '15:30', 'type' => 'Workshop', 'hall' => 'Room 3', 'title' => 'Writing a first CV', 'who' => 'Komar University careers team', 'attendance' => '180'],
                ],
                'sponsor_note' => [
                    'en' => '21 partners, sponsors and exhibitors supported the 2024 edition.',
                    'ku' => '21 هاوبەش، سپۆنسەر و بەشداربوو پشتیوانی خولی 2024یان کرد.',
                    'ar' => 'دعم دورة 2024 عدد 21 شريكاً وراعياً وعارضاً.',
                ],
                'sponsor_tiers' => [
                    ['tier' => ['en' => 'Strategic partners', 'ku' => 'هاوبەشە ستراتیژییەکان', 'ar' => 'الشركاء الاستراتيجيون'], 'accent' => '#2C4BE0', 'note' => ['en' => 'Convening institutions'], 'logos' => ['MOHE', 'Sulaimani Governorate']],
                    ['tier' => ['en' => 'Institutional supporters', 'ku' => 'پشتیوانە دامەزراوەییەکان', 'ar' => 'الداعمون المؤسسيون'], 'accent' => '#2C4BE0', 'note' => ['en' => 'First year of support'], 'logos' => ['MJ Holding', 'Qaiwan Group', 'Click Iraq']],
                    ['tier' => ['en' => 'Sponsors', 'ku' => 'سپۆنسەرەکان', 'ar' => 'الرعاة'], 'accent' => '#B64698', 'note' => ['en' => 'Gold and silver'], 'logos' => ['Asiacell', 'KIB Bank', 'Korek', 'IT Academy', 'Empire World']],
                    ['tier' => ['en' => 'Media partners', 'ku' => 'هاوبەشانی میدیا', 'ar' => 'الشركاء الإعلاميون'], 'accent' => '#4A4B4D', 'note' => ['en' => 'Broadcast'], 'logos' => ['NRT', 'Esta Media']],
                ],
            ],

            [
                'year' => 2023,
                'edition_no' => 1,
                'edition_label' => ['en' => '1st edition', 'ku' => 'خولی یەکەم', 'ar' => 'الدورة الأولى'],
                'dates_label' => ['en' => '3–4 October 2023', 'ku' => '3–4 تشرینی یەکەمی 2023', 'ar' => '3–4 تشرين الأول 2023'],
                'venue_label' => ['en' => 'Cultural Factory, Sulaimani', 'ku' => 'کارگەی کولتوری، سلێمانی', 'ar' => 'مصنع الثقافة، السليمانية'],
                'headline' => [
                    'en' => 'One hall, nine universities, no precedent',
                    'ku' => 'یەک هۆڵ، نۆ زانکۆ، بێ نموونەی پێشوو',
                    'ar' => 'قاعة واحدة، تسع جامعات، بلا سابقة',
                ],
                'summary' => [
                    'en' => 'The first edition was a two-day exhibition with nine institutions and no seminar programme. It ran on the assumption that students would come if the information was free, and 4,200 did.',
                    'ku' => 'خولی یەکەم پێشانگایەکی دوو ڕۆژە بوو بە نۆ دامەزراوە و بەبێ پرۆگرامی سیمینار. لەسەر ئەو بڕوایە بەڕێوەچوو کە قوتابیان دێن ئەگەر زانیارییەکە بەخۆڕایی بێت، و 4,200 کەس هاتن.',
                    'ar' => 'كانت الدورة الأولى معرضاً ليومين بتسع مؤسسات وبلا برنامج ندوات. قامت على افتراض أن الطلبة سيأتون إذا كانت المعلومة مجانية، فجاء 4,200.',
                ],
                'stats' => [
                    ['k' => ['en' => 'Visitors', 'ku' => 'سەردانکەر', 'ar' => 'زائر'], 'v' => '4,200'],
                    ['k' => ['en' => 'Institutions', 'ku' => 'دامەزراوە', 'ar' => 'مؤسسة'], 'v' => '9'],
                    ['k' => ['en' => 'Exhibitors', 'ku' => 'بەشداربوو', 'ar' => 'عارض'], 'v' => '6'],
                    ['k' => ['en' => 'Seminars', 'ku' => 'سیمینار', 'ar' => 'ندوة'], 'v' => '4'],
                    ['k' => ['en' => 'Panels', 'ku' => 'پانێل', 'ar' => 'جلسة'], 'v' => '1'],
                    ['k' => ['en' => 'Workshops', 'ku' => 'وۆرکشۆپ', 'ar' => 'ورشة'], 'v' => '2'],
                ],
                'theme_title' => [
                    'en' => 'One question defined the first edition',
                    'ku' => 'یەک پرسیار خولی یەکەمی پێناسە کرد',
                    'ar' => 'سؤال واحد عرّف الدورة الأولى',
                ],
                'themes' => [
                    [
                        'num' => '01', 'accent' => '#B64698',
                        'title' => ['en' => 'Free, and in one room', 'ku' => 'بەخۆڕایی، و لە یەک ژووردا', 'ar' => 'مجاناً، وفي قاعة واحدة'],
                        'body' => [
                            'en' => 'No entry fee, no appointments, no pre-registration. Nine institutions agreed to staff a desk for two days and answer whatever was asked.',
                            'ku' => 'بێ کرێی چوونەژوورەوە، بێ کاتی پێشوەخت، بێ تۆمارکردنی پێشوەخت. نۆ دامەزراوە ڕازی بوون بۆ دوو ڕۆژ ستاف لەسەر مێزێک دابنێن و وەڵامی هەر پرسیارێک بدەنەوە.',
                            'ar' => 'بلا رسوم دخول ولا مواعيد ولا تسجيل مسبق. وافقت تسع مؤسسات على تزويد طاولة بموظفين ليومين والإجابة عن أي سؤال.',
                        ],
                        'outcome' => ['en' => '4,200 visitors in two days', 'ku' => '4,200 سەردانکەر لە دوو ڕۆژدا', 'ar' => '4,200 زائر في يومين'],
                    ],
                    [
                        'num' => '02', 'accent' => '#2C4BE0',
                        'title' => ['en' => 'Ask the officer, not the rumour', 'ku' => 'لە بەرپرس بپرسە، نەک لە دەنگۆ', 'ar' => 'اسأل المسؤول لا الإشاعة'],
                        'body' => [
                            'en' => 'Admission officers, not marketing staff, sat at every desk. Entry scores and tuition were posted on the booth wall.',
                            'ku' => 'بەرپرسانی وەرگرتن، نەک ستافی بازاڕگەری، لەسەر هەموو مێزێک دانیشتبوون. نمرەی وەرگرتن و کرێی خوێندن لەسەر دیواری ستاندەکە هەڵواسرابوون.',
                            'ar' => 'جلس على كل طاولة مسؤولو قبول لا موظفو تسويق. وعُلّقت درجات القبول والرسوم على جدار الجناح.',
                        ],
                        'outcome' => [
                            'en' => '9 institutions, all with admission staff present',
                            'ku' => '9 دامەزراوە، هەموویان بە ئامادەبوونی ستافی وەرگرتن',
                            'ar' => '9 مؤسسات، جميعها بحضور موظفي القبول',
                        ],
                    ],
                ],
                'organizer_name' => 'Avin Qadir',
                'organizer_role' => [
                    'en' => 'Organizer and Co-Founder of Next Step',
                    'ku' => 'ڕێکخەر و هاوبەشی دامەزرێنەری هەنگاوی داهاتوو',
                    'ar' => 'المنظِّمة والشريكة المؤسِّسة لـ Next Step',
                ],
                'speech_where' => [
                    'en' => 'Opening remarks, 3 October 2023, Main hall',
                    'ku' => 'وتاری کردنەوە، 3ی تشرینی یەکەمی 2023، هۆڵی سەرەکی',
                    'ar' => 'كلمة الافتتاح، 3 تشرين الأول 2023، القاعة الرئيسية',
                ],
                'speech_quote' => [
                    'en' => 'A student should not have to know someone to find out what a degree costs.',
                    'ku' => 'قوتابییەک نابێت پێویستی بەوە بێت کەسێک بناسێت بۆ زانینی ئەوەی بڕوانامەیەک چەند تێدەچێت.',
                    'ar' => 'لا ينبغي أن يحتاج الطالب إلى معرفة أحدهم ليعرف كم تكلّف الشهادة.',
                ],
                'speech' => ['en' => '<p>This started with a photocopied list. A teacher in Chamchamal showed us the sheet her students were using to choose a university: eleven names, no fees, no entry scores, no contact details, photocopied so many times that half of it was unreadable.</p><p>We asked nine institutions to bring an admission officer and a table for two days, and to write their entry score and tuition on the wall behind them. All nine agreed. That is the whole of the first edition.</p><p>Four thousand two hundred people came. We had planned for a thousand. Next year we will need a programme, and rooms, and a plan for the parents who stood in the corridor because we had not thought to make space for them.</p>'],
                'speaker_count' => ['en' => '7 speakers', 'ku' => '7 قسەکەر', 'ar' => '7 متحدثين'],
                'speakers' => [
                    ['name' => 'Avin Qadir', 'role' => 'Organizer and Co-Founder', 'org' => 'Next Step', 'role2' => 'Opening', 'accent' => '#B64698'],
                    ['name' => 'Ari Salih Mahmood', 'role' => 'Admissions Registrar', 'org' => 'Zankoline / MOHE', 'role2' => 'Speaker', 'accent' => '#2C4BE0'],
                    ['name' => 'Nazdar Omer Faraj', 'role' => 'School Counsellor', 'org' => 'Sulaimani Directorate of Education', 'role2' => 'Speaker', 'accent' => '#B64698'],
                    ['name' => 'Dr. Kamaran Aziz', 'role' => 'Dean of Admissions', 'org' => 'University of Sulaimani', 'role2' => 'Speaker', 'accent' => '#2C4BE0'],
                ],
                'panel_note' => ['en' => '4 seminars, 1 panel, 2 workshops', 'ku' => '4 سیمینار، 1 پانێل، 2 وۆرکشۆپ', 'ar' => '4 ندوات وجلسة واحدة وورشتان'],
                'panels' => [
                    ['day' => 'Day 1', 'time' => '12:00', 'type' => 'Seminar', 'hall' => 'Main hall', 'title' => 'Choosing a university: the four questions to ask at every desk', 'who' => 'Next Step guidance team', 'attendance' => '310'],
                    ['day' => 'Day 1', 'time' => '15:00', 'type' => 'Seminar', 'hall' => 'Main hall', 'title' => 'How placement scores are calculated', 'who' => 'Ari Salih Mahmood, Zankoline', 'attendance' => '280'],
                    ['day' => 'Day 2', 'time' => '12:00', 'type' => 'Panel', 'hall' => 'Main hall', 'title' => 'What I would choose again', 'who' => 'Four recent graduates', 'attendance' => '240'],
                    ['day' => 'Day 2', 'time' => '15:00', 'type' => 'Workshop', 'hall' => 'Side room', 'title' => 'Filling the application form', 'who' => 'Next Step guidance team', 'attendance' => '150'],
                ],
                'sponsor_note' => [
                    'en' => '11 partners, sponsors and exhibitors supported the first edition.',
                    'ku' => '11 هاوبەش، سپۆنسەر و بەشداربوو پشتیوانی خولی یەکەمیان کرد.',
                    'ar' => 'دعم الدورة الأولى 11 شريكاً وراعياً وعارضاً.',
                ],
                'sponsor_tiers' => [
                    ['tier' => ['en' => 'Strategic partner', 'ku' => 'هاوبەشی ستراتیژی', 'ar' => 'الشريك الاستراتيجي'], 'accent' => '#2C4BE0', 'note' => ['en' => 'Convening institution'], 'logos' => ['MOHE']],
                    ['tier' => ['en' => 'Supporters', 'ku' => 'پشتیوانەکان', 'ar' => 'الداعمون'], 'accent' => '#2C4BE0', 'note' => ['en' => 'Founding supporters'], 'logos' => ['Click Iraq', 'Sulaimani Chamber of Commerce']],
                    ['tier' => ['en' => 'Sponsors', 'ku' => 'سپۆنسەرەکان', 'ar' => 'الرعاة'], 'accent' => '#B64698', 'note' => ['en' => 'Founding sponsors'], 'logos' => ['Asiacell', 'IT Academy', 'Byan Group']],
                    ['tier' => ['en' => 'Media partner', 'ku' => 'هاوبەشی میدیا', 'ar' => 'الشريك الإعلامي'], 'accent' => '#4A4B4D', 'note' => ['en' => 'Coverage'], 'logos' => ['NRT']],
                ],
            ],
        ];
    }
}
