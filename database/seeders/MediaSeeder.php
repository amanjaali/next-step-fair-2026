<?php

namespace Database\Seeders;

use App\Models\MediaAlbum;
use App\Models\MediaItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Photo albums by year → day → session, plus the video gallery and the
 * vertical short-form reels. Alt text is required on every image.
 *
 * Strings are concatenated rather than interpolated: an Arabic comma directly
 * after a PHP variable is a valid identifier byte and swallows the variable.
 */
class MediaSeeder extends Seeder
{
    public function run(): void
    {
        $albums = [
            [2025, 1, 'Opening ceremony', 'ڕێوڕەسمی کردنەوە', 'حفل الافتتاح', 8],
            [2025, 1, 'Panels', 'پانێلەکان', 'الجلسات الحوارية', 6],
            [2025, 2, 'Booths', 'ستاندەکان', 'الأجنحة', 10],
            [2025, 2, 'Workshops', 'وۆرکشۆپەکان', 'ورش العمل', 6],
            [2025, 2, 'Awards', 'خەڵاتەکان', 'الجوائز', 5],
            [2024, 1, 'Opening ceremony', 'ڕێوڕەسمی کردنەوە', 'حفل الافتتاح', 6],
            [2024, 2, 'Booths', 'ستاندەکان', 'الأجنحة', 8],
            [2023, 1, 'Behind the scenes', 'لە پشتەوە', 'خلف الكواليس', 5],
        ];

        foreach ($albums as $i => [$year, $day, $en, $ku, $ar, $count]) {
            $album = MediaAlbum::updateOrCreate(
                ['slug' => Str::slug($year.'-day-'.$day.'-'.$en)],
                [
                    'title' => [
                        'en' => $en.' — '.$year,
                        'ku' => $ku.' — '.$year,
                        'ar' => $ar.' — '.$year,
                    ],
                    'description' => [
                        'en' => 'Day '.$day.' of the '.$year.' edition.',
                        'ku' => 'ڕۆژی '.$day.' لە خولی '.$year,
                        'ar' => 'اليوم '.$day.' من دورة '.$year,
                    ],
                    'year' => $year,
                    'day' => $day,
                    'category' => $en,
                    'sort' => $i,
                ]
            );

            for ($n = 1; $n <= $count; $n++) {
                MediaItem::updateOrCreate(
                    ['media_album_id' => $album->id, 'sort' => $n],
                    [
                        'type' => 'photo',
                        'year' => $year,
                        'category' => $en,
                        'downloadable' => true,
                        'alt' => [
                            'en' => $en.' at the '.$year.' edition, photograph '.$n,
                            'ku' => $ku.' لە خولی '.$year.'، وێنەی '.$n,
                            'ar' => $ar.' في دورة '.$year.'، صورة '.$n,
                        ],
                        'caption' => [
                            'en' => 'Next Step Fair '.$year.', Cultural Factory, Sulaimani.',
                            'ku' => 'پێشانگای هەنگاوی داهاتوو '.$year.'، کارگەی کولتوری، سلێمانی.',
                            'ar' => 'معرض Next Step '.$year.'، مصنع الثقافة، السليمانية.',
                        ],
                    ]
                );
            }
        }

        $videos = [
            ['2025 in 90 seconds', '2025 لە 90 چرکەدا', '2025 في 90 ثانية', 2025, 'video'],
            ['Opening ceremony, full recording', 'ڕێوڕەسمی کردنەوە، تۆماری تەواو', 'حفل الافتتاح، التسجيل الكامل', 2025, 'video'],
            ['2024 recap', 'کورتەی 2024', 'ملخص 2024', 2024, 'video'],
            ['A student walks the floor', 'قوتابییەک بەناو هۆڵەکەدا دەڕوات', 'طالب يتجول في الصالة', 2025, 'reel'],
            ['Sixty seconds at the scholarship desk', 'شەست چرکە لەسەر مێزی سکۆلەرشیپ', 'ستون ثانية عند مكتب المنح', 2025, 'reel'],
        ];

        foreach ($videos as $i => [$en, $ku, $ar, $year, $type]) {
            MediaItem::updateOrCreate(
                ['media_album_id' => null, 'sort' => 100 + $i],
                [
                    'type' => $type,
                    'year' => $year,
                    'provider' => 'youtube',
                    'url' => 'https://www.youtube.com/watch?v=00000000000',
                    'category' => $type === 'reel' ? 'Behind the scenes' : 'Opening ceremony',
                    'alt' => ['en' => $en, 'ku' => $ku, 'ar' => $ar],
                    'caption' => ['en' => $en, 'ku' => $ku, 'ar' => $ar],
                ]
            );
        }
    }
}
