<?php

namespace Database\Seeders;

use App\Models\ScholarshipUniversity;
use Illuminate\Database\Seeder;

/**
 * The universities that hold seats in the National Scholarship Program.
 *
 * Seeded once from the original programme list. After that, seat pledges and
 * descriptions are edited in the dashboard — re-running this seeder does nothing
 * if any university already exists.
 */
class ScholarshipUniversitySeeder extends Seeder
{
    public function run(): void
    {
        if (ScholarshipUniversity::exists()) {
            return;
        }

        foreach ($this->universities() as $index => $row) {
            $departments = $row['departments'];
            unset($row['departments']);

            $university = ScholarshipUniversity::create([
                ...$row,
                'sort' => $index,
                'published' => true,
            ]);

            foreach ($departments as $deptIndex => $department) {
                $university->departments()->create([
                    'name' => $department['name'],
                    'seats' => $department['seats'],
                    'sort' => $deptIndex,
                ]);
            }
        }
    }

    /** @return list<array<string, mixed>> */
    private function universities(): array
    {
        return [
            [
                'slug' => 'auis',
                'name' => 'American University of Iraq, Sulaimani',
                'city' => 'Sulaimani',
                'language' => 'English',
                'tier' => ScholarshipUniversity::TIER_FOUNDING,
                'founded' => 2007,
                'students' => 1600,
                'housing' => ScholarshipUniversity::HOUSING_CONTRIBUTION,
                'about' => [
                    'en' => 'A liberal arts university teaching entirely in English, with a core curriculum every student takes before specialising. It has funded scholarship seats since the programme\'s first cycle.',
                    'ku' => 'زانکۆیەکی هونەرە ئازادەکان کە بە تەواوی بە ئینگلیزی وانە دەڵێتەوە، بە پڕۆگرامێکی بنەڕەتی کە هەموو خوێندکاریەک پێش تایبەتمەندبوون وەریدەگرێت. لە یەکەم خولی پرۆگرامەکەوە کورسی خەرج کردووە.',
                    'ar' => 'جامعة فنون حرة تدرّس بالإنجليزية بالكامل، بمنهج أساسي يدرسه كل طالب قبل التخصص. موّلت مقاعد المنحة منذ الدورة الأولى للبرنامج.',
                ],
                'departments' => [
                    ['name' => 'Computer Science', 'seats' => 2],
                    ['name' => 'Business Administration', 'seats' => 2],
                    ['name' => 'International Studies', 'seats' => 1],
                    ['name' => 'Engineering', 'seats' => 1],
                ],
            ],
            [
                'slug' => 'ukh',
                'name' => 'University of Kurdistan Hewlêr',
                'city' => 'Erbil',
                'language' => 'English',
                'tier' => ScholarshipUniversity::TIER_FOUNDING,
                'founded' => 2006,
                'students' => 1300,
                'housing' => ScholarshipUniversity::HOUSING_NONE,
                'about' => [
                    'en' => 'A public university with English instruction and a research focus on natural resources and computing.',
                    'ku' => 'زانکۆیەکی حکومی بە وانەوتنەوەی ئینگلیزی و سەرنجێکی توێژینەوە لەسەر سەرچاوە سروشتییەکان و کۆمپیوتەر.',
                    'ar' => 'جامعة حكومية تدرّس بالإنجليزية وتركّز بحثياً على الموارد الطبيعية والحوسبة.',
                ],
                'departments' => [
                    ['name' => 'Medicine', 'seats' => 2],
                    ['name' => 'Computer Science & AI', 'seats' => 2],
                    ['name' => 'Natural Resources', 'seats' => 1],
                    ['name' => 'Law', 'seats' => 1],
                ],
            ],
            [
                'slug' => 'komar',
                'name' => 'Komar University of Science and Technology',
                'city' => 'Sulaimani',
                'language' => 'English',
                'tier' => ScholarshipUniversity::TIER_DONOR,
                'founded' => 2013,
                'students' => 2100,
                'housing' => ScholarshipUniversity::HOUSING_CONTRIBUTION,
                'about' => [
                    'en' => 'A science and technology university in Sulaimani with strong pharmacy and dentistry faculties and a working teaching clinic.',
                    'ku' => 'زانکۆیەکی زانست و تەکنەلۆژیا لە سلێمانی بە فەکەڵتی بەهێزی دەرمانسازی و ددانسازی و کلینیکێکی وانەوتنەوەی کارا.',
                    'ar' => 'جامعة علوم وتكنولوجيا في السليمانية بكليتَي صيدلة وطب أسنان قويتين وعيادة تعليمية عاملة.',
                ],
                'departments' => [
                    ['name' => 'Pharmacy', 'seats' => 2],
                    ['name' => 'Dentistry', 'seats' => 1],
                    ['name' => 'Architecture', 'seats' => 1],
                    ['name' => 'Computer Engineering', 'seats' => 1],
                ],
            ],
            [
                'slug' => 'tishk',
                'name' => 'Tishk International University',
                'city' => 'Erbil',
                'language' => 'English',
                'tier' => ScholarshipUniversity::TIER_DONOR,
                'founded' => 2008,
                'students' => 5400,
                'housing' => ScholarshipUniversity::HOUSING_NONE,
                'about' => [
                    'en' => 'A large private university in Erbil with faculties across medicine, engineering and education, and a well-established international exchange programme.',
                    'ku' => 'زانکۆیەکی ئەهلی گەورە لە هەولێر بە فەکەڵتییەکان لە پزیشکی، ئەندازیاری و پەروەردە، و پڕۆگرامێکی ئاڵوگۆڕی نێودەوڵەتی جێگیر.',
                    'ar' => 'جامعة أهلية كبيرة في أربيل بكليات في الطب والهندسة والتربية، وبرنامج تبادل دولي راسخ.',
                ],
                'departments' => [
                    ['name' => 'Medicine', 'seats' => 2],
                    ['name' => 'Civil Engineering', 'seats' => 1],
                    ['name' => 'Education', 'seats' => 1],
                    ['name' => 'Business', 'seats' => 1],
                ],
            ],
            [
                'slug' => 'uhd',
                'name' => 'University of Human Development',
                'city' => 'Sulaimani',
                'language' => 'English & Kurdish',
                'tier' => ScholarshipUniversity::TIER_DONOR,
                'founded' => 2008,
                'students' => 4800,
                'housing' => ScholarshipUniversity::HOUSING_NONE,
                'about' => [
                    'en' => 'A Sulaimani university teaching in English and Kurdish, known for its law faculty and its evening programmes for working students.',
                    'ku' => 'زانکۆیەکی سلێمانی کە بە ئینگلیزی و کوردی وانە دەڵێتەوە، ناسراوە بە فەکەڵتی یاسا و پڕۆگرامە ئێوارانەکانی بۆ خوێندکارانی کارکەر.',
                    'ar' => 'جامعة في السليمانية تدرّس بالإنجليزية والكردية، معروفة بكلية القانون وبرامجها المسائية للطلاب العاملين.',
                ],
                'departments' => [
                    ['name' => 'Law', 'seats' => 2],
                    ['name' => 'Accounting', 'seats' => 1],
                    ['name' => 'English Language', 'seats' => 1],
                    ['name' => 'Computer Science', 'seats' => 1],
                ],
            ],
            [
                'slug' => 'lfu',
                'name' => 'Lebanese French University',
                'city' => 'Erbil',
                'language' => 'English',
                'tier' => ScholarshipUniversity::TIER_DONOR,
                'founded' => 2007,
                'students' => 3600,
                'housing' => ScholarshipUniversity::HOUSING_CONTRIBUTION,
                'about' => [
                    'en' => 'A private university in Erbil with health science and design faculties and a French academic partnership.',
                    'ku' => 'زانکۆیەکی ئەهلی لە هەولێر بە فەکەڵتی زانستی تەندروستی و دیزاین و هاوبەشییەکی ئەکادیمی فەڕەنسی.',
                    'ar' => 'جامعة أهلية في أربيل بكليات العلوم الصحية والتصميم وشراكة أكاديمية فرنسية.',
                ],
                'departments' => [
                    ['name' => 'Dentistry', 'seats' => 2],
                    ['name' => 'Medical Laboratory', 'seats' => 1],
                    ['name' => 'Interior Design', 'seats' => 1],
                    ['name' => 'IT', 'seats' => 1],
                ],
            ],
            [
                'slug' => 'qiu',
                'name' => 'Qaiwan International University',
                'city' => 'Sulaimani',
                'language' => 'English',
                'tier' => ScholarshipUniversity::TIER_DONOR,
                'founded' => 2018,
                'students' => 1200,
                'housing' => ScholarshipUniversity::HOUSING_INCLUDED,
                'about' => [
                    'en' => 'A Sulaimani university delivering UTM Malaysia degrees locally, with engineering and software programmes.',
                    'ku' => 'زانکۆیەکی سلێمانی کە بڕوانامەکانی UTM مالیزیا بە شێوەی ناوخۆیی پێشکەش دەکات، بە پڕۆگرامەکانی ئەندازیاری و نەرمەکاڵا.',
                    'ar' => 'جامعة في السليمانية تمنح شهادات UTM الماليزية محلياً، ببرامج هندسية وبرمجية.',
                ],
                'departments' => [
                    ['name' => 'Mechanical Engineering', 'seats' => 1],
                    ['name' => 'Software Engineering', 'seats' => 2],
                    ['name' => 'Business', 'seats' => 1],
                ],
            ],
            [
                'slug' => 'cue',
                'name' => 'Catholic University in Erbil',
                'city' => 'Erbil',
                'language' => 'English',
                'tier' => ScholarshipUniversity::TIER_DONOR,
                'founded' => 2015,
                'students' => 900,
                'housing' => ScholarshipUniversity::HOUSING_INCLUDED,
                'about' => [
                    'en' => 'A small university with a low staff to student ratio, teaching in English across medicine, pharmacy and international relations.',
                    'ku' => 'زانکۆیەکی بچووک بە ڕێژەی نزمی مامۆستا بۆ خوێندکار، بە ئینگلیزی وانە دەڵێتەوە لە پزیشکی، دەرمانسازی و پەیوەندییە نێودەوڵەتییەکان.',
                    'ar' => 'جامعة صغيرة بنسبة أساتذة إلى طلاب منخفضة، تدرّس بالإنجليزية في الطب والصيدلة والعلاقات الدولية.',
                ],
                'departments' => [
                    ['name' => 'Medicine', 'seats' => 1],
                    ['name' => 'Pharmacy', 'seats' => 1],
                    ['name' => 'International Relations', 'seats' => 1],
                    ['name' => 'Accounting', 'seats' => 1],
                ],
            ],
        ];
    }
}
