<?php

namespace Database\Seeders;

use App\Models\OfferPopup;
use App\Models\Opportunity;
use Illuminate\Database\Seeder;

/**
 * One worked popup, switched off.
 *
 * It exists so the team can open it, see the shape a good one takes and turn it
 * on — rather than face an empty screen and guess. Off by default: a seeded
 * popup that greeted every visitor on the first run would be a bad surprise.
 */
class OfferPopupSeeder extends Seeder
{
    public function run(): void
    {
        if (OfferPopup::exists()) {
            return;
        }

        $popup = OfferPopup::create([
            'title' => [
                'en' => 'Three things open to you this week',
                'ku' => 'سێ شت ئەم هەفتەیە بۆت کراوەیە',
                'ar' => 'ثلاثة عروض مفتوحة لك هذا الأسبوع',
            ],
            'intro' => [
                'en' => 'From the ministry and the universities exhibiting at Next Step.',
                'ku' => 'لە وەزارەت و ئەو زانکۆیانەوە کە لە نێکست ستێپ بەشدارن.',
                'ar' => 'من الوزارة والجامعات المشاركة في نكست ستب.',
            ],
            'audience' => Opportunity::AUDIENCE_EVERYONE,
            'active' => false,
        ]);

        $items = [
            [
                'badge' => 'Closes in 9 days',
                'action_url' => 'https://mhe-krg.org/',
                'en' => ['title' => 'MOHE scholarship for public university places', 'body' => 'One hundred and twenty funded places for grade 12 students with an average of 80% or above.', 'action_label' => 'Apply on the ministry site'],
                'ku' => ['title' => 'خوێندنگای بەخشینی وەزارەت بۆ زانکۆ حکومییەکان', 'body' => 'سەد و بیست شوێنی خەرجکراو بۆ قوتابیانی پۆلی ١٢ بە تێکڕای ٨٠٪ یان زیاتر.', 'action_label' => 'لە ماڵپەڕی وەزارەت داوا بکە'],
                'ar' => ['title' => 'منحة الوزارة لمقاعد الجامعات الحكومية', 'body' => 'مئة وعشرون مقعداً ممولاً لطلاب الصف الثاني عشر بمعدل 80% فأعلى.', 'action_label' => 'قدّم على موقع الوزارة'],
            ],
            [
                'badge' => '25 places',
                'action_url' => null,
                'en' => ['title' => '40% tuition reduction, agreed with Next Step', 'body' => 'First-year tuition at the American University of Iraq, Sulaimani, for students who apply through us.'],
                'ku' => ['title' => 'داشکاندنی ٤٠٪ لە کرێی خوێندن، لەگەڵ نێکست ستێپ ڕێککەوتووە', 'body' => 'کرێی ساڵی یەکەم لە زانکۆی ئەمریکی عێراق، سلێمانی، بۆ ئەو قوتابیانەی لە ڕێگای ئێمەوە داوا دەکەن.'],
                'ar' => ['title' => 'خصم 40% على الرسوم، بالاتفاق مع نكست ستب', 'body' => 'رسوم السنة الأولى في الجامعة الأمريكية في العراق، السليمانية، لمن يتقدم عبرنا.'],
            ],
            [
                'badge' => '40 places',
                'action_url' => null,
                'en' => ['title' => 'Free two-week engineering summer school', 'body' => 'In the engineering labs at the University of Kurdistan Hewlêr, lunch and transport included.'],
                'ku' => ['title' => 'خولی هاوینەی ئەندازیاری بۆ دوو هەفتە، بەخۆڕایی', 'body' => 'لە تاقیگەکانی ئەندازیاری لە زانکۆی کوردستان هەولێر، لەگەڵ نان و گواستنەوە.'],
                'ar' => ['title' => 'مدرسة صيفية هندسية مجانية لأسبوعين', 'body' => 'في مختبرات الهندسة بجامعة كردستان هولير، مع الغداء والمواصلات.'],
            ],
        ];

        foreach ($items as $sort => $item) {
            $popup->items()->create([
                'badge' => $item['badge'],
                'action_url' => $item['action_url'],
                'sort' => $sort,
                'title' => ['en' => $item['en']['title'], 'ku' => $item['ku']['title'], 'ar' => $item['ar']['title']],
                'body' => ['en' => $item['en']['body'], 'ku' => $item['ku']['body'], 'ar' => $item['ar']['body']],
                'action_label' => [
                    'en' => $item['en']['action_label'] ?? null,
                    'ku' => $item['ku']['action_label'] ?? null,
                    'ar' => $item['ar']['action_label'] ?? null,
                ],
            ]);
        }
    }
}
