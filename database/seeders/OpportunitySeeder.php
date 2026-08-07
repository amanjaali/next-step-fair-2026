<?php

namespace Database\Seeders;

use App\Models\Opportunity;
use App\Models\Organization;
use Illuminate\Database\Seeder;

/**
 * Worked examples of what a partner brings to the board.
 *
 * A ministry scholarship, a university offer negotiated through Next Step, an
 * employer internship, a workshop with places left. They exist so the team can
 * see the shape a good entry takes before writing their own, and so the section
 * is not an empty box on the day it ships.
 */
class OpportunitySeeder extends Seeder
{
    public function run(): void
    {
        $auis = Organization::where('slug', 'like', 'american-university%')->first();
        $ukh = Organization::where('slug', 'like', '%kurdistan-hew%')->first();

        $items = [
            [
                'slug' => 'mohe-public-university-scholarship',
                'kind' => Opportunity::KIND_SCHOLARSHIP,
                'partner_name' => 'Ministry of Higher Education and Scientific Research',
                'audience' => Opportunity::AUDIENCE_GRADE12,
                'featured' => true,
                'places' => 120,
                'closes_at' => now()->addDays(24)->toDateString(),
                'action_url' => 'https://mhe-krg.org/',
                'en' => [
                    'title' => 'MOHE scholarship for public university places',
                    'summary' => 'One hundred and twenty funded places across public universities in the Kurdistan Region, for grade 12 students with an average of 80% or above.',
                    'body' => '<p>The ministry funds tuition and a monthly stipend for the full length of the degree. Places are allocated by exam average within each province, and the subjects are set each year against where the region has shortages.</p><p>Applications go directly to the ministry. Next Step lists it here because our students ask about it more than anything else, and because the deadline is easy to miss.</p>',
                    'eligibility' => '<ul><li>Grade 12 completed at a school in the Kurdistan Region</li><li>National exam average of 80% or above</li><li>Starting an undergraduate degree this academic year</li></ul>',
                    'action_label' => 'Apply on the ministry site',
                ],
                'ku' => [
                    'title' => 'خوێندنگای بەخشینی وەزارەت بۆ شوێنەکانی زانکۆی حکومی',
                    'summary' => 'سەد و بیست شوێنی خەرجکراو لە زانکۆ حکومییەکانی هەرێمی کوردستان، بۆ قوتابیانی پۆلی ١٢ بە تێکڕای ٨٠٪ یان زیاتر.',
                    'body' => '<p>وەزارەت کرێی خوێندن و خەرجی مانگانە بۆ درێژایی بڕوانامەکە دەدات. شوێنەکان بەپێی تێکڕای تاقیکردنەوە لە هەر پارێزگایەکدا دابەش دەکرێن، و بوارەکان هەموو ساڵێک بەپێی کەمووکوڕییەکانی هەرێم دیاری دەکرێن.</p>',
                    'eligibility' => '<ul><li>پۆلی ١٢ لە قوتابخانەیەکی هەرێمی کوردستان تەواو کرابێت</li><li>تێکڕای تاقیکردنەوەی نیشتمانی ٨٠٪ یان زیاتر</li><li>لەم ساڵی خوێندنەدا دەست بە بەکالۆریۆس دەکات</li></ul>',
                    'action_label' => 'لە ماڵپەڕی وەزارەت داوا بکە',
                ],
                'ar' => [
                    'title' => 'منحة الوزارة لمقاعد الجامعات الحكومية',
                    'summary' => 'مئة وعشرون مقعداً ممولاً في الجامعات الحكومية بإقليم كردستان، لطلاب الصف الثاني عشر بمعدل 80% فأعلى.',
                    'body' => '<p>تغطي الوزارة الرسوم ومخصصاً شهرياً طوال مدة الدراسة. تُوزَّع المقاعد حسب معدل الامتحان داخل كل محافظة، وتُحدَّد التخصصات سنوياً وفق حاجة الإقليم.</p>',
                    'eligibility' => '<ul><li>إتمام الصف الثاني عشر في مدرسة داخل إقليم كردستان</li><li>معدل الامتحان الوطني 80% فأعلى</li><li>بدء دراسة البكالوريوس هذا العام</li></ul>',
                    'action_label' => 'قدّم على موقع الوزارة',
                ],
            ],
            [
                'slug' => 'auis-next-step-tuition-offer',
                'kind' => Opportunity::KIND_OFFER,
                'organization_id' => $auis?->id,
                'partner_name' => 'American University of Iraq, Sulaimani',
                'audience' => Opportunity::AUDIENCE_GRADE12,
                'featured' => true,
                'places' => 25,
                'closes_at' => now()->addDays(9)->toDateString(),
                'action_url' => 'https://auis.edu.krd/',
                'en' => [
                    'title' => '40% tuition reduction for Next Step students',
                    'summary' => 'Agreed with Next Step for this cycle only: a 40% reduction on first-year tuition for twenty-five students who apply through us.',
                    'body' => '<p>The reduction applies to the first year and continues at 25% for the remaining years if the student holds a GPA of 3.0. It is not combinable with a full scholarship.</p><p>Mention Next Step on the application form. The university checks the name against our registration list, which is why you have to be registered to use it.</p>',
                    'eligibility' => '<ul><li>Registered with Next Step before applying</li><li>Grade 12 average of 85% or above</li><li>Applying for an undergraduate programme starting this year</li></ul>',
                    'action_label' => 'Open the university application',
                ],
                'ku' => [
                    'title' => 'داشکاندنی ٤٠٪ی کرێی خوێندن بۆ قوتابیانی Next Step',
                    'summary' => 'تەنها بۆ ئەم خولە لەگەڵ Next Step ڕێککەوتووە: داشکاندنی ٤٠٪ لەسەر کرێی ساڵی یەکەم بۆ بیست و پێنج قوتابی کە لە ڕێگای ئێمەوە داوا دەکەن.',
                    'body' => '<p>داشکاندنەکە بۆ ساڵی یەکەمە و بە ٢٥٪ بەردەوام دەبێت بۆ ساڵانی ماوە ئەگەر قوتابییەکە GPAی ٣.٠ی هەبێت. لەگەڵ خوێندنگایەکی بەخشینی تەواودا کۆناکرێتەوە.</p>',
                    'eligibility' => '<ul><li>پێش داواکاری لە Next Step تۆمارکرابێت</li><li>تێکڕای پۆلی ١٢ی ٨٥٪ یان زیاتر</li><li>داوای پرۆگرامێکی بەکالۆریۆس دەکات کە ئەم ساڵ دەست پێ دەکات</li></ul>',
                    'action_label' => 'داواکاری زانکۆکە بکەرەوە',
                ],
                'ar' => [
                    'title' => 'خصم 40% على الرسوم لطلاب Next Step',
                    'summary' => 'مُتفق عليه مع Next Step لهذه الدورة فقط: خصم 40% على رسوم السنة الأولى لخمسة وعشرين طالباً يتقدمون من خلالنا.',
                    'body' => '<p>يسري الخصم على السنة الأولى ويستمر بنسبة 25% لبقية السنوات إذا حافظ الطالب على معدل 3.0. ولا يُجمع مع منحة كاملة.</p>',
                    'eligibility' => '<ul><li>التسجيل في Next Step قبل التقديم</li><li>معدل الصف الثاني عشر 85% فأعلى</li><li>التقديم على برنامج بكالوريوس يبدأ هذا العام</li></ul>',
                    'action_label' => 'افتح استمارة الجامعة',
                ],
            ],
            [
                'slug' => 'ukh-engineering-summer-school',
                'kind' => Opportunity::KIND_PROGRAMME,
                'organization_id' => $ukh?->id,
                'partner_name' => 'University of Kurdistan Hewlêr',
                'audience' => Opportunity::AUDIENCE_STUDENTS,
                'places' => 40,
                'closes_at' => now()->addDays(52)->toDateString(),
                'action_url' => 'https://ukh.edu.krd/',
                'en' => [
                    'title' => 'Free two-week engineering summer school',
                    'summary' => 'Forty places on a two-week programme in the engineering labs, free including lunch and transport from Sulaimani and Duhok.',
                    'body' => '<p>Two weeks of laboratory work, a group project and a session with admissions on what an engineering application actually needs. It is aimed at students deciding whether engineering is for them.</p>',
                    'eligibility' => '<ul><li>Registered with Next Step</li><li>In grade 11 or 12, or a recent graduate</li><li>Able to attend all ten weekdays</li></ul>',
                    'action_label' => 'Reserve a place',
                ],
                'ku' => [
                    'title' => 'خولی هاوینەی ئەندازیاری بۆ دوو هەفتە، بەخۆڕایی',
                    'summary' => 'چل شوێن لە پرۆگرامێکی دوو هەفتەیی لە تاقیگەکانی ئەندازیاری، بەخۆڕایی لەگەڵ نانی نیوەڕۆ و گواستنەوە لە سلێمانی و دهۆکەوە.',
                    'body' => '<p>دوو هەفتە کاری تاقیگە، پڕۆژەیەکی گروپی و دانیشتنێک لەگەڵ بەشی وەرگرتن لەسەر ئەوەی داواکارییەکی ئەندازیاری بەڕاستی چی دەوێت.</p>',
                    'eligibility' => '<ul><li>لە Next Step تۆمارکراوە</li><li>لە پۆلی ١١ یان ١٢، یان دەرچووی نوێ</li><li>دەتوانێت هەر دە ڕۆژی کارەکە ئامادە بێت</li></ul>',
                    'action_label' => 'شوێنێک حجز بکە',
                ],
                'ar' => [
                    'title' => 'مدرسة صيفية مجانية في الهندسة لمدة أسبوعين',
                    'summary' => 'أربعون مكاناً في برنامج من أسبوعين داخل مختبرات الهندسة، مجاناً مع الغداء والنقل من السليمانية ودهوك.',
                    'body' => '<p>أسبوعان من العمل المختبري ومشروع جماعي وجلسة مع قسم القبول حول ما يتطلبه طلب الهندسة فعلاً.</p>',
                    'eligibility' => '<ul><li>مسجّل في Next Step</li><li>في الصف الحادي عشر أو الثاني عشر، أو خريج حديث</li><li>القدرة على الحضور طوال الأيام العشرة</li></ul>',
                    'action_label' => 'احجز مكاناً',
                ],
            ],
            [
                'slug' => 'kurdistan-tech-internship',
                'kind' => Opportunity::KIND_INTERNSHIP,
                'partner_name' => 'Kurdistan Technology Group',
                'audience' => Opportunity::AUDIENCE_STUDENTS,
                'places' => 15,
                'closes_at' => now()->addDays(38)->toDateString(),
                'action_url' => 'https://example.com/internship',
                'en' => [
                    'title' => 'Paid summer internships in software and data',
                    'summary' => 'Fifteen paid eight-week internships in Sulaimani and Erbil, open to first and second-year university students.',
                    'body' => '<p>Eight weeks alongside a working team, with a mentor and a small project of your own. Interns who finish well are offered part-time work through the academic year.</p>',
                    'eligibility' => '<ul><li>Registered with Next Step</li><li>In the first or second year of a university degree</li><li>Any subject — the work is taught from the start</li></ul>',
                    'action_label' => 'Apply for an internship',
                ],
                'ku' => [
                    'title' => 'ڕاهێنانی هاوینەی خەرجکراو لە نەرمەکاڵا و دراوە',
                    'summary' => 'پازدە ڕاهێنانی خەرجکراوی هەشت هەفتەیی لە سلێمانی و هەولێر، کراوە بۆ قوتابیانی ساڵی یەکەم و دووەمی زانکۆ.',
                    'body' => '<p>هەشت هەفتە لەگەڵ تیمێکی کارا، لەگەڵ ڕێنمایکارێک و پڕۆژەیەکی بچووکی خۆت. ئەو ڕاهێنەرانەی باش تەواو دەکەن کاری پارەکاتی پێشکەش دەکرێن بە درێژایی ساڵی خوێندن.</p>',
                    'eligibility' => '<ul><li>لە Next Step تۆمارکراوە</li><li>لە ساڵی یەکەم یان دووەمی زانکۆ</li><li>هەر بوارێک — کارەکە لە سەرەتاوە فێر دەکرێت</li></ul>',
                    'action_label' => 'داوای ڕاهێنان بکە',
                ],
                'ar' => [
                    'title' => 'تدريب صيفي مدفوع في البرمجيات والبيانات',
                    'summary' => 'خمسة عشر تدريباً مدفوعاً لمدة ثمانية أسابيع في السليمانية وأربيل، لطلاب السنة الأولى والثانية.',
                    'body' => '<p>ثمانية أسابيع مع فريق عامل، بمرشد ومشروع صغير خاص بك. ويُعرض على المتميزين عمل بدوام جزئي خلال العام الدراسي.</p>',
                    'eligibility' => '<ul><li>مسجّل في Next Step</li><li>في السنة الأولى أو الثانية من الجامعة</li><li>أي تخصص — يُدرَّس العمل من البداية</li></ul>',
                    'action_label' => 'قدّم على التدريب',
                ],
            ],
            [
                'slug' => 'parents-choosing-a-university-evening',
                'kind' => Opportunity::KIND_WORKSHOP,
                'partner_name' => 'Next Step',
                'audience' => Opportunity::AUDIENCE_PARENTS,
                'places' => 200,
                'closes_at' => now()->addDays(30)->toDateString(),
                'en' => [
                    'title' => 'An evening for parents: choosing a university',
                    'summary' => 'What the Zankoline form actually does, how private and public places differ in cost, and the questions worth asking at a stand.',
                    'summary_note' => null,
                    'body' => '<p>Two hours, in Kurdish, with time for questions. Run by Next Step with admissions staff from three universities in the room.</p>',
                    'eligibility' => '<ul><li>Parents and guardians registered with Next Step</li></ul>',
                    'action_label' => 'Save a seat',
                ],
                'ku' => [
                    'title' => 'ئێوارەیەک بۆ دایک و باوک: هەڵبژاردنی زانکۆ',
                    'summary' => 'فۆرمی زانکۆلاین بەڕاستی چی دەکات، شوێنی ئەهلی و حکومی لە تێچوودا چۆن جیاوازن، و ئەو پرسیارانەی شیاون لە ستاندێک بکرێن.',
                    'body' => '<p>دوو کاتژمێر، بە کوردی، لەگەڵ کاتی پرسیار. لەلایەن Next Stepەوە بەڕێوە دەچێت لەگەڵ کارمەندانی وەرگرتنی سێ زانکۆ لە ژوورەکەدا.</p>',
                    'eligibility' => '<ul><li>دایک و باوک و سەرپەرشتیارانی لە Next Step تۆمارکراو</li></ul>',
                    'action_label' => 'شوێنێک پاشەکەوت بکە',
                ],
                'ar' => [
                    'title' => 'أمسية لأولياء الأمور: كيف تختار جامعة',
                    'summary' => 'ما تفعله استمارة زانكولاين فعلاً، والفرق في الكلفة بين المقاعد الأهلية والحكومية، والأسئلة التي تستحق أن تُطرح عند الجناح.',
                    'body' => '<p>ساعتان بالكردية مع وقت للأسئلة. تديرها Next Step بحضور موظفي قبول من ثلاث جامعات.</p>',
                    'eligibility' => '<ul><li>أولياء الأمور المسجَّلون في Next Step</li></ul>',
                    'action_label' => 'احجز مقعداً',
                ],
            ],
        ];

        foreach ($items as $sort => $item) {
            $translations = ['en' => $item['en'], 'ku' => $item['ku'], 'ar' => $item['ar']];

            Opportunity::updateOrCreate(['slug' => $item['slug']], [
                'kind' => $item['kind'],
                'organization_id' => $item['organization_id'] ?? null,
                'partner_name' => $item['partner_name'],
                'audience' => $item['audience'],
                'featured' => $item['featured'] ?? false,
                'published' => true,
                'places' => $item['places'] ?? null,
                'closes_at' => $item['closes_at'] ?? null,
                'action_url' => $item['action_url'] ?? null,
                'sort' => $sort,
                'title' => $this->field($translations, 'title'),
                'summary' => $this->field($translations, 'summary'),
                'body' => $this->field($translations, 'body'),
                'eligibility' => $this->field($translations, 'eligibility'),
                'action_label' => $this->field($translations, 'action_label'),
            ]);
        }
    }

    /** ['en' => …, 'ku' => …, 'ar' => …] for one translatable field. */
    private function field(array $translations, string $key): array
    {
        return collect($translations)
            ->map(fn ($set) => $set[$key] ?? '')
            ->filter()
            ->all();
    }
}
