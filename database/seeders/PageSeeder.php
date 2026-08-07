<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Legal and standing pages. Sections are stored as structured JSON per language
 * so the admin edits them section by section rather than as one wall of HTML.
 */
class PageSeeder extends Seeder
{
    public function run(): void
    {
        $this->privacy();
        $this->terms();
        $this->pressKit();
        $this->about();
        $this->tracks();
    }

    /** @param array<int, array{n:string, h:array, p:array, list?:array}> $sections */
    private function page(string $key, array $attributes, array $sections): void
    {
        Page::updateOrCreate(['key' => $key], $attributes + ['sections' => $sections, 'published' => true]);
    }

    private function privacy(): void
    {
        $this->page('privacy', [
            'kicker' => ['en' => 'Legal', 'ku' => 'یاسایی', 'ar' => 'قانوني'],
            'title' => ['en' => 'Privacy policy', 'ku' => 'سیاسەتی تایبەتمەندی', 'ar' => 'سياسة الخصوصية'],
            'standfirst' => [
                'en' => 'What we collect when you register, why we need it, how long we keep it, and how to have it deleted.',
                'ku' => 'کاتێک تۆمار دەکەیت چی کۆدەکەینەوە، بۆچی پێویستمانە، چەند ڕایدەگرین، و چۆن دەیسڕیتەوە.',
                'ar' => 'ما نجمعه عند تسجيلك، ولماذا نحتاجه، وكم نحتفظ به، وكيف تطلب حذفه.',
            ],
            'updated_on' => '2026-06-18',
            'aside' => [
                'title' => ['en' => 'Data requests', 'ku' => 'داواکاری داتا', 'ar' => 'طلبات البيانات'],
                'body' => [
                    'en' => 'To see, correct or delete the data we hold on you, write to us with the phone number or email you registered with.',
                    'ku' => 'بۆ بینین، ڕاستکردنەوە یان سڕینەوەی ئەو داتایەی لای ئێمەیە، بە هەمان ژمارە یان ئیمەیڵی تۆمارکردنت بۆمان بنووسە.',
                    'ar' => 'لعرض بياناتك أو تصحيحها أو حذفها، راسلنا من الرقم أو البريد الذي سجّلت به.',
                ],
                'contact' => 'privacy@nextstepfair.com',
            ],
            'foot_note' => [
                'en' => 'This policy is published in Kurdish, Arabic and English. Where versions differ, the Kurdish text is authoritative.',
                'ku' => 'ئەم سیاسەتە بە کوردی، عەرەبی و ئینگلیزی بڵاو دەکرێتەوە. لە کاتی جیاوازیدا، دەقی کوردی بڕیاردەرە.',
                'ar' => 'تُنشر هذه السياسة بالكردية والعربية والإنجليزية. وعند الاختلاف، يُعتد بالنص الكردي.',
            ],
        ], [
            [
                'n' => '01',
                'h' => ['en' => 'Who we are', 'ku' => 'ئێمە کێین', 'ar' => 'من نحن'],
                'p' => [[
                    'en' => 'Next Step Organization is an education initiative registered in the Kurdistan Region of Iraq, based in Sulaimani. We organise Next Step Fair and the Day 1 conference, and we are the data controller for everything described here.',
                    'ku' => 'ڕێکخراوی هەنگاوی داهاتوو دەستپێشخەرییەکی پەروەردەییە کە لە هەرێمی کوردستانی عێراق تۆمارکراوە و بنکەی لە سلێمانییە. پێشانگای هەنگاوی داهاتوو و کۆنفرانسی ڕۆژی یەکەم ڕێک دەخەین، و بەرپرسی کۆنترۆڵی داتاین بۆ هەموو ئەوەی لێرەدا باسکراوە.',
                    'ar' => 'منظمة Next Step مبادرة تعليمية مسجّلة في إقليم كوردستان العراق ومقرها السليمانية. ننظّم معرض Next Step ومؤتمر اليوم الأول، ونحن الجهة المتحكمة بالبيانات لكل ما هو موصوف هنا.',
                ]],
            ],
            [
                'n' => '02',
                'h' => ['en' => 'What we collect', 'ku' => 'چی کۆدەکەینەوە', 'ar' => 'ما الذي نجمعه'],
                'p' => [[
                    'en' => 'We collect only what the registration form asks for. Nothing is bought from third parties and nothing is inferred about you.',
                    'ku' => 'تەنها ئەوە کۆدەکەینەوە کە فۆرمی تۆمارکردن داوای دەکات. هیچ شتێک لە لایەنی سێیەم نەکڕدراوە و هیچ شتێک دەربارەت خەمڵێنراو نییە.',
                    'ar' => 'نجمع فقط ما تطلبه استمارة التسجيل. لا نشتري شيئاً من أطراف ثالثة ولا نستنتج عنك شيئاً.',
                ]],
                'list' => [
                    [
                        'en' => 'Fair registration: name, date of birth, phone number, city, preferred language, school and stream, intended field of study, days attending, and the sessions you save.',
                        'ku' => 'تۆمارکردنی پێشانگا: ناو، بەرواری لەدایکبوون، ژمارەی مۆبایل، شار، زمانی پەسەند، قوتابخانە و لق، بواری خوێندنی مەبەست، ڕۆژەکانی بەشداری، و ئەو دانیشتنانەی پاشەکەوتیان دەکەیت.',
                        'ar' => 'التسجيل في المعرض: الاسم وتاريخ الميلاد ورقم الهاتف والمدينة ولغة التواصل والمدرسة والفرع ومجال الدراسة المقصود وأيام الحضور والجلسات التي تحفظها.',
                    ],
                    [
                        'en' => 'Conference RSVP: name, title, institution, official email, mobile number, city, delegation size and interpretation needs.',
                        'ku' => 'بەشداری کۆنفرانس: ناو، پلە، دامەزراوە، ئیمەیڵی فەرمی، ژمارەی مۆبایل، شار، قەبارەی شاند و پێویستی وەرگێڕان.',
                        'ar' => 'تأكيد حضور المؤتمر: الاسم والمنصب والمؤسسة والبريد الرسمي ورقم الهاتف والمدينة وحجم الوفد واحتياجات الترجمة.',
                    ],
                    [
                        'en' => 'Optional: email address, gender, and how you heard about the fair.',
                        'ku' => 'ئارەزوومەندانە: ئیمەیڵ، ڕەگەز، و چۆن پێشانگاکەت ناسی.',
                        'ar' => 'اختياري: البريد الإلكتروني والجنس وكيف عرفت عن المعرض.',
                    ],
                    [
                        'en' => 'Automatically: page views and referral source through GA4 and the platform pixels, in aggregate.',
                        'ku' => 'خۆکارانە: بینینی لاپەڕە و سەرچاوەی گەیشتن لە ڕێگەی GA4 و پیکسڵەکانەوە، بە کۆکراوەیی.',
                        'ar' => 'تلقائياً: مشاهدات الصفحات ومصدر الإحالة عبر GA4 وبيكسلات المنصات، بشكل إجمالي.',
                    ],
                ],
            ],
            [
                'n' => '03',
                'h' => ['en' => 'Why we need it', 'ku' => 'بۆچی پێویستمانە', 'ar' => 'لماذا نحتاجه'],
                'p' => [[
                    'en' => 'Your phone number is the delivery channel for your badge, so it has to be verified before a ticket is issued. Your city and school let us plan shuttle routes and staffing. Session picks drive the reminder you receive 15 minutes before a saved session, and tell us which rooms to make larger next year.',
                    'ku' => 'ژمارەی مۆبایلەکەت کەناڵی گەیاندنی باجەکەتە، بۆیە پێش دەرکردنی بلیت دەبێت پشتڕاست بکرێتەوە. شار و قوتابخانەکەت یارمەتیمان دەدەن ڕێڕەوی پاس و ژمارەی ستاف پلان دابنێین. هەڵبژاردنی دانیشتنەکان ئەو بیرخەرەوەیە دەخوڵقێنن کە 15 خولەک پێش دانیشتنی پاشەکەوتکراو پێت دەگات، و پێمان دەڵێن ساڵی داهاتوو کام ژوور گەورەتر بکەین.',
                    'ar' => 'رقم هاتفك هو قناة إيصال بطاقتك، لذا يجب التحقق منه قبل إصدار التذكرة. مدينتك ومدرستك تساعداننا في تخطيط خطوط الحافلات وتوزيع الفريق. واختياراتك للجلسات تولّد التذكير الذي يصلك قبل 15 دقيقة من الجلسة المحفوظة، وتخبرنا أي القاعات نوسّع العام المقبل.',
                ]],
                'list' => [
                    [
                        'en' => 'Issuing and re-issuing your QR badge.',
                        'ku' => 'دەرکردن و دووبارە دەرکردنەوەی باجی QRەکەت.',
                        'ar' => 'إصدار بطاقة QR وإعادة إصدارها.',
                    ],
                    [
                        'en' => 'Sending event reminders and day-of directions on WhatsApp or by email.',
                        'ku' => 'ناردنی بیرخەرەوەی ڕووداو و ڕێنمایی ڕۆژی ڕووداو لە واتسئاپ یان بە ئیمەیڵ.',
                        'ar' => 'إرسال تذكيرات الحدث وإرشادات اليوم عبر واتساب أو البريد.',
                    ],
                    [
                        'en' => 'Check-in at the gate, and counting attendance per day.',
                        'ku' => 'چوونەژوورەوە لە دەروازە، و ژماردنی بەشداری بۆ هەر ڕۆژێک.',
                        'ar' => 'تسجيل الدخول عند البوابة وحساب الحضور لكل يوم.',
                    ],
                    [
                        'en' => 'Aggregate reporting to partners, ministries and in the annual impact report.',
                        'ku' => 'ڕاپۆرتی کۆکراوە بۆ هاوبەشەکان، وەزارەتەکان و لە ڕاپۆرتی ساڵانەی کاریگەریدا.',
                        'ar' => 'تقارير إجمالية للشركاء والوزارات وفي تقرير الأثر السنوي.',
                    ],
                ],
            ],
            [
                'n' => '04',
                'h' => ['en' => 'Consent', 'ku' => 'ڕەزامەندی', 'ar' => 'الموافقة'],
                'p' => [[
                    'en' => 'You give consent explicitly, per purpose, with a timestamp recorded against your registration. Consent to WhatsApp updates is required because it is how the badge reaches you. Consent to appear in event photography is optional and can be withdrawn at any time. For anyone under 18, written release from a parent or guardian is required before any identifiable photograph is published.',
                    'ku' => 'ڕەزامەندی بە ڕوونی دەدەیت، بۆ هەر مەبەستێک، لەگەڵ تۆمارکردنی کات لەگەڵ تۆمارەکەت. ڕەزامەندی بۆ نوێکارییەکانی واتسئاپ پێویستە چونکە باجەکە بەم ڕێگایە پێت دەگات. ڕەزامەندی بۆ دەرکەوتن لە وێنەکانی ڕووداو ئارەزوومەندانەیە و لە هەر کاتێکدا دەکرێت پاشگەز بکرێتەوە. بۆ هەرکەسێکی خوار 18 ساڵ، ڕەزامەندی نووسراوی دایک و باوک یان سەرپەرشتیار پێویستە پێش بڵاوکردنەوەی هەر وێنەیەکی ناسراو.',
                    'ar' => 'تمنح موافقتك صراحة، ولكل غرض على حدة، مع تسجيل وقتها في سجل تسجيلك. موافقة تحديثات واتساب مطلوبة لأنها وسيلة وصول البطاقة إليك. أما الموافقة على الظهور في تصوير الحدث فاختيارية ويمكن سحبها في أي وقت. ولمن هم دون 18 عاماً، يلزم إذن خطي من ولي الأمر قبل نشر أي صورة يمكن التعرف فيها على الشخص.',
                ]],
            ],
            [
                'n' => '05',
                'h' => ['en' => 'Who sees your data', 'ku' => 'کێ داتاکەت دەبینێت', 'ar' => 'من يطّلع على بياناتك'],
                'p' => [[
                    'en' => 'Your personal data is never sold, and never shared with exhibitors, universities or sponsors. Partners receive counts and aggregates only — never a list of names or numbers.',
                    'ku' => 'داتای کەسیت هەرگیز نافرۆشرێت، و هەرگیز لەگەڵ بەشداربووان، زانکۆکان یان سپۆنسەرەکان هاوبەش ناکرێت. هاوبەشەکان تەنها ژمارە و کۆکراوە وەردەگرن — هەرگیز لیستی ناو یان ژمارە نا.',
                    'ar' => 'لا تُباع بياناتك الشخصية أبداً، ولا تُشارك مع العارضين أو الجامعات أو الرعاة. يتلقى الشركاء أعداداً وإجماليات فقط — لا قوائم أسماء أو أرقام.',
                ]],
                'list' => [
                    [
                        'en' => 'Our own staff, under role-based access, for registration and check-in.',
                        'ku' => 'ستافی خۆمان، بە دەستگەیشتنی ڕۆڵ-بنەما، بۆ تۆمارکردن و چوونەژوورەوە.',
                        'ar' => 'فريقنا، ضمن صلاحيات محددة بالدور، للتسجيل وتسجيل الدخول.',
                    ],
                    [
                        'en' => 'Meta WhatsApp Cloud API, to deliver your badge and reminders.',
                        'ku' => 'Meta WhatsApp Cloud API، بۆ گەیاندنی باج و بیرخەرەوەکان.',
                        'ar' => 'واجهة Meta WhatsApp Cloud، لإيصال بطاقتك وتذكيراتك.',
                    ],
                    [
                        'en' => 'Our email provider, for conference confirmations.',
                        'ku' => 'دابینکەری ئیمەیڵمان، بۆ پشتڕاستکردنەوەی کۆنفرانس.',
                        'ar' => 'مزوّد البريد لدينا، لتأكيدات المؤتمر.',
                    ],
                    [
                        'en' => 'Our hosting and database provider, where the data is stored encrypted.',
                        'ku' => 'دابینکەری هۆستینگ و بنکەدراوەمان، کە داتاکە بە شێوەی شفرکراو پاشەکەوت دەکرێت.',
                        'ar' => 'مزوّد الاستضافة وقاعدة البيانات، حيث تُخزَّن البيانات مشفّرة.',
                    ],
                ],
            ],
            [
                'n' => '06',
                'h' => ['en' => 'How long we keep it', 'ku' => 'چەند ڕایدەگرین', 'ar' => 'مدة الاحتفاظ'],
                'p' => [[
                    'en' => 'Registration records are kept for 24 months after the edition, so that a returning student does not start from scratch and so that year-on-year attendance can be reported. Check-in logs are kept for 12 months. After that, records are deleted or irreversibly anonymised.',
                    'ku' => 'تۆمارەکانی تۆمارکردن 24 مانگ دوای خولەکە ڕادەگیرێن، بۆ ئەوەی قوتابییەکی گەڕاوە لە سفرەوە دەست پێنەکاتەوە و بۆ ئەوەی بەشداری ساڵ بە ساڵ ڕاپۆرت بکرێت. لۆگی چوونەژوورەوە 12 مانگ ڕادەگیرێت. دوای ئەوە، تۆمارەکان دەسڕدرێنەوە یان بە شێوەیەکی نەگەڕاوە نەناسراو دەکرێن.',
                    'ar' => 'تُحفظ سجلات التسجيل 24 شهراً بعد الدورة، كي لا يبدأ الطالب العائد من الصفر ولتمكين مقارنة الحضور سنة بسنة. وتُحفظ سجلات الدخول 12 شهراً. بعد ذلك تُحذف السجلات أو تُجهَّل بصورة لا رجعة فيها.',
                ]],
            ],
            [
                'n' => '07',
                'h' => ['en' => 'Your rights', 'ku' => 'مافەکانت', 'ar' => 'حقوقك'],
                'p' => [[
                    'en' => 'You can ask to see the data we hold on you, correct it, or have it deleted, and we will respond within 30 days. Deleting your registration cancels your badge. You can stop WhatsApp messages at any time by replying STOP, which does not cancel your registration.',
                    'ku' => 'دەتوانیت داوا بکەیت ئەو داتایە ببینیت کە لای ئێمەیە، ڕاستی بکەیتەوە، یان بیسڕیتەوە، و لە ماوەی 30 ڕۆژدا وەڵامت دەدەینەوە. سڕینەوەی تۆمارەکەت باجەکەت هەڵدەوەشێنێتەوە. لە هەر کاتێکدا دەتوانیت بە وەڵامدانەوەی STOP نامەکانی واتسئاپ ڕابگریت، ئەمە تۆمارەکەت هەڵناوەشێنێتەوە.',
                    'ar' => 'يمكنك طلب الاطلاع على بياناتك أو تصحيحها أو حذفها، وسنرد خلال 30 يوماً. حذف تسجيلك يلغي بطاقتك. ويمكنك إيقاف رسائل واتساب في أي وقت بالرد بكلمة STOP، وهذا لا يلغي تسجيلك.',
                ]],
            ],
            [
                'n' => '08',
                'h' => ['en' => 'Security', 'ku' => 'پاراستن', 'ar' => 'الأمان'],
                'p' => [[
                    'en' => 'Personal data is encrypted at rest. Badge downloads use signed, expiring links. QR codes contain a signed ticket identifier only — never your name, phone number or ID — so a leaked badge image cannot expose your details.',
                    'ku' => 'داتای کەسی لە کاتی پاشەکەوتدا شفر دەکرێت. داگرتنی باج بەستەری واژۆکراو و کاتدار بەکاردەهێنێت. کۆدەکانی QR تەنها ناسنامەی واژۆکراوی بلیت لەخۆ دەگرن — هەرگیز ناو، ژمارەی مۆبایل یان ناسنامەت نا — بۆیە وێنەیەکی باجی دەرچوو ناتوانێت زانیارییەکانت ئاشکرا بکات.',
                    'ar' => 'البيانات الشخصية مشفّرة أثناء التخزين. وتنزيلات البطاقات تستخدم روابط موقّعة تنتهي صلاحيتها. ورموز QR تحمل معرّف تذكرة موقّعاً فقط — لا اسمك ولا هاتفك ولا هويتك — لذا لا تكشف صورة بطاقة مسرّبة أي بيانات عنك.',
                ]],
            ],
        ]);
    }

    private function terms(): void
    {
        $this->page('terms', [
            'kicker' => ['en' => 'Legal', 'ku' => 'یاسایی', 'ar' => 'قانوني'],
            'title' => ['en' => 'Terms and conditions', 'ku' => 'مەرج و ڕێساکان', 'ar' => 'الشروط والأحكام'],
            'standfirst' => [
                'en' => 'The rules for registering, attending and exhibiting at Next Step Fair 2026.',
                'ku' => 'ڕێساکانی تۆمارکردن، بەشداری و پێشانگاکردن لە پێشانگای هەنگاوی داهاتوو 2026.',
                'ar' => 'قواعد التسجيل والحضور والعرض في معرض Next Step 2026.',
            ],
            'updated_on' => '2026-06-18',
            'aside' => [
                'title' => ['en' => 'Questions', 'ku' => 'پرسیارەکان', 'ar' => 'أسئلة'],
                'body' => [
                    'en' => 'Anything not covered here, or a case you are unsure about, goes to the organising team before the event.',
                    'ku' => 'هەر شتێک لێرەدا باس نەکرابێت، یان دۆخێک دڵنیا نەبیت لێی، پێش ڕووداوەکە بۆ تیمی ڕێکخستن بنێرە.',
                    'ar' => 'أي أمر لا تغطيه هذه الشروط، أو حالة لست متأكداً منها، يُوجَّه إلى فريق التنظيم قبل الحدث.',
                ],
                'contact' => 'info@nextstepfair.com',
            ],
            'foot_note' => [
                'en' => 'These terms are governed by the laws of the Kurdistan Region of Iraq.',
                'ku' => 'ئەم مەرجانە بەپێی یاساکانی هەرێمی کوردستانی عێراق بەڕێوە دەبرێن.',
                'ar' => 'تخضع هذه الشروط لقوانين إقليم كوردستان العراق.',
            ],
        ], [
            [
                'n' => '01',
                'h' => ['en' => 'Registration and entry', 'ku' => 'تۆمارکردن و چوونەژوورەوە', 'ar' => 'التسجيل والدخول'],
                'p' => [[
                    'en' => 'Entry to the fair is free for students, graduates, parents and teachers. Registration is per person: one phone number, one registration, one badge. Conference attendance is by RSVP and may require approval by the protocol team before a badge is issued.',
                    'ku' => 'چوونەژوورەوە بۆ پێشانگا بەخۆڕاییە بۆ قوتابی، دەرچوو، دایک و باوک و مامۆستایان. تۆمارکردن بۆ هەر کەسێکە: یەک ژمارەی مۆبایل، یەک تۆمارکردن، یەک باج. بەشداری کۆنفرانس بە داواکاری پێشوەختەیە و لەوانەیە پێش دەرکردنی باج پەسەندکردنی تیمی پرۆتۆکۆلی بوێت.',
                    'ar' => 'الدخول إلى المعرض مجاني للطلبة والخريجين وأولياء الأمور والمعلمين. والتسجيل فردي: رقم هاتف واحد، تسجيل واحد، بطاقة واحدة. أما حضور المؤتمر فبتأكيد مسبق وقد يتطلب موافقة فريق المراسم قبل إصدار البطاقة.',
                ]],
            ],
            [
                'n' => '02',
                'h' => ['en' => 'Your badge', 'ku' => 'باجەکەت', 'ar' => 'بطاقتك'],
                'p' => [[
                    'en' => 'Your QR badge is personal and not transferable. It is checked once per day at the gate. A badge presented by someone other than the registrant may be cancelled on the spot. If you lose it, staff can find your registration by name or phone number at the registration desk.',
                    'ku' => 'باجی QRەکەت کەسییە و ناگوازرێتەوە. ڕۆژی جارێک لە دەروازە پشکنین دەکرێت. ئەو باجەی لەلایەن کەسێکی تر لە تۆمارکەر پیشان بدرێت لەوانەیە دەستبەجێ هەڵبوەشێنرێتەوە. ئەگەر ونت کرد، ستاف دەتوانن بە ناو یان ژمارەی مۆبایل تۆمارەکەت لە مێزی تۆمارکردن بدۆزنەوە.',
                    'ar' => 'بطاقة QR شخصية وغير قابلة للتحويل. تُفحص مرة واحدة يومياً عند البوابة. وقد تُلغى البطاقة فوراً إذا قدّمها شخص غير صاحب التسجيل. وإذا فقدتها، يستطيع الفريق إيجاد تسجيلك بالاسم أو رقم الهاتف عند مكتب التسجيل.',
                ]],
            ],
            [
                'n' => '03',
                'h' => ['en' => 'Programme changes', 'ku' => 'گۆڕانکاری پرۆگرام', 'ar' => 'تغييرات البرنامج'],
                'p' => [[
                    'en' => 'Sessions, speakers, timings and hall allocations may change. We publish changes on the agenda page and notify anyone who saved an affected session. Saving a session reserves a reminder, not a seat: seating is allocated on arrival and rooms fill.',
                    'ku' => 'دانیشتن، قسەکەران، کاتەکان و دابەشکردنی هۆڵ لەوانەیە بگۆڕێن. گۆڕانکارییەکان لە لاپەڕەی خشتەدا بڵاو دەکەینەوە و ئاگاداری هەرکەسێک دەکەینەوە کە دانیشتنێکی کاریگەری لێکراوی پاشەکەوت کردبێت. پاشەکەوتکردنی دانیشتن بیرخەرەوە دەگرێت، نەک کورسی: کورسی بەپێی هاتن دابەش دەکرێت و ژوورەکان پڕ دەبن.',
                    'ar' => 'قد تتغير الجلسات والمتحدثون والتوقيتات وتوزيع القاعات. ننشر التغييرات في صفحة البرنامج ونُعلم كل من حفظ جلسة متأثرة. وحفظ الجلسة يحجز تذكيراً لا مقعداً: المقاعد تُوزع عند الوصول والقاعات تمتلئ.',
                ]],
            ],
            [
                'n' => '04',
                'h' => ['en' => 'Conduct in the venue', 'ku' => 'ڕەفتار لە شوێنەکەدا', 'ar' => 'السلوك داخل المكان'],
                'p' => [[
                    'en' => 'The venue is a school-age environment. Harassment, filming people without their agreement, obstructing exhibitor desks, and selling or promoting inside the halls without a booth are all grounds for removal. Staff instructions in an evacuation are to be followed immediately.',
                    'ku' => 'شوێنەکە ژینگەیەکی تەمەنی قوتابخانەیە. بێزارکردن، وێنەگرتنی خەڵک بەبێ ڕەزامەندییان، ڕێگرتن لە مێزی بەشداربووان، و فرۆشتن یان بانگەشەکردن لە ناو هۆڵەکاندا بەبێ ستاند، هەموویان هۆکاری دەرکردنن. لە کاتی چۆڵکردندا دەبێت دەستبەجێ ڕێنمایی ستاف جێبەجێ بکرێت.',
                    'ar' => 'المكان بيئة لطلبة في سن المدرسة. التحرش، وتصوير الأشخاص دون موافقتهم، وإعاقة طاولات العارضين، والبيع أو الترويج داخل القاعات دون جناح، كلها أسباب للإخراج. ويجب اتباع تعليمات الفريق فوراً عند الإخلاء.',
                ]],
            ],
            [
                'n' => '05',
                'h' => ['en' => 'Photography and filming', 'ku' => 'وێنەگرتن و فیلمکردن', 'ar' => 'التصوير والفيديو'],
                'p' => [[
                    'en' => 'The event is photographed and filmed. Signage marks camera areas at every entrance. If you do not want to appear, tell any staff member and you will be given a marker. Identifiable minors are only published with written release from a parent or guardian.',
                    'ku' => 'ڕووداوەکە وێنەی لێ دەگیرێت و فیلم دەکرێت. تابلۆ ناوچەکانی کامێرا لە هەموو دەروازەیەکدا نیشان دەدەن. ئەگەر نەتەوێت دەربکەویت، بە هەر ئەندامێکی ستاف بڵێ و نیشانەیەکت پێدەدرێت. منداڵانی ناسراو تەنها بە ڕەزامەندی نووسراوی دایک و باوک یان سەرپەرشتیار بڵاو دەکرێنەوە.',
                    'ar' => 'يُصوَّر الحدث فوتوغرافياً وفيديو. وتُعلَّم مناطق التصوير بلافتات عند كل مدخل. إن لم ترغب بالظهور، أخبر أي موظف وستحصل على علامة مميزة. ولا يُنشر القاصرون الذين يمكن التعرف عليهم إلا بإذن خطي من ولي الأمر.',
                ]],
            ],
            [
                'n' => '06',
                'h' => ['en' => 'Exhibitors and sponsors', 'ku' => 'بەشداربووان و سپۆنسەرەکان', 'ar' => 'العارضون والرعاة'],
                'p' => [[
                    'en' => 'Booth allocation, artwork deadlines and deliverables are set out in the exhibitor agreement. Artwork submitted after the stated deadline may not be produced. Booths must be staffed for the full published opening hours, and may not be dismantled before the close of the final day.',
                    'ku' => 'دابەشکردنی ستاند، کۆتا کاتی ئارتوۆرک و بەرهەمەکان لە ڕێککەوتننامەی بەشداربووان دیاری کراون. ئەو ئارتوۆرکەی دوای کۆتا کاتی دیاریکراو بنێردرێت لەوانەیە بەرهەم نەهێنرێت. دەبێت ستاندەکان بە درێژایی کاتی کردنەوەی بڵاوکراوە ستافیان هەبێت، و پێش کۆتایی ڕۆژی دوایی هەڵناوەشێنرێنەوە.',
                    'ar' => 'توزيع الأجنحة ومواعيد التصاميم والمخرجات محددة في اتفاقية العارض. وقد لا تُنتج التصاميم المرسلة بعد الموعد المعلن. ويجب أن تكون الأجنحة مزوّدة بموظفين طوال ساعات العمل المعلنة، ولا يجوز تفكيكها قبل انتهاء اليوم الأخير.',
                ]],
            ],
            [
                'n' => '07',
                'h' => ['en' => 'Liability', 'ku' => 'بەرپرسیارێتی', 'ar' => 'المسؤولية'],
                'p' => [[
                    'en' => 'We are not responsible for personal property lost in the venue, for statements made by exhibitors about admission or funding, or for admission decisions taken by any institution. Advice given at the fair is guidance, not an offer of a place.',
                    'ku' => 'ئێمە بەرپرسیار نین لە موڵکی کەسی ونبوو لە شوێنەکەدا، لە قسەی بەشداربووان دەربارەی وەرگرتن یان خەرجکردن، یان لە بڕیارەکانی وەرگرتنی هەر دامەزراوەیەک. ئەو ڕاوێژەی لە پێشانگادا دەدرێت ڕێنماییە، نەک پێشکەشکردنی شوێن.',
                    'ar' => 'لسنا مسؤولين عن الممتلكات الشخصية المفقودة في المكان، ولا عن تصريحات العارضين بشأن القبول أو التمويل، ولا عن قرارات القبول التي تتخذها أي مؤسسة. والنصائح المقدَّمة في المعرض إرشاد وليست عرض مقعد.',
                ]],
            ],
            [
                'n' => '08',
                'h' => ['en' => 'Cancellation', 'ku' => 'هەڵوەشاندنەوە', 'ar' => 'الإلغاء'],
                'p' => [[
                    'en' => 'If the event is postponed or cancelled, registrations carry over to the rescheduled dates and sponsors are dealt with under the terms of their agreement. Entry is free, so no refunds arise for attendees.',
                    'ku' => 'ئەگەر ڕووداوەکە دوابخرێت یان هەڵبوەشێنرێتەوە، تۆمارکردنەکان بۆ بەرواری نوێ دەگوازرێنەوە و سپۆنسەرەکان بەپێی مەرجەکانی ڕێککەوتننامەکەیان مامەڵەیان لەگەڵ دەکرێت. چوونەژوورەوە بەخۆڕاییە، بۆیە هیچ گەڕاندنەوەی پارە بۆ بەشداربووان نییە.',
                    'ar' => 'إذا أُجّل الحدث أو أُلغي، تُنقل التسجيلات إلى المواعيد الجديدة ويُعامل الرعاة وفق شروط اتفاقياتهم. والدخول مجاني، فلا تنشأ أي مبالغ مستردة للحاضرين.',
                ]],
            ],
        ]);
    }

    private function pressKit(): void
    {
        $this->page('press', [
            'kicker' => ['en' => 'For media', 'ku' => 'بۆ ڕاگەیاندن', 'ar' => 'للإعلام'],
            'title' => ['en' => 'Press kit', 'ku' => 'پاکەتی ڕاگەیاندن', 'ar' => 'الملف الصحفي'],
            'standfirst' => [
                'en' => 'Logos, photography, the fact sheet and accreditation for Next Step Fair 2026.',
                'ku' => 'لۆگۆ، وێنە، پەڕەی زانیاری و مۆڵەت بۆ پێشانگای هەنگاوی داهاتوو 2026.',
                'ar' => 'الشعارات والصور وورقة الحقائق والاعتماد لمعرض Next Step 2026.',
            ],
            'updated_on' => '2026-07-22',
            'aside' => [
                'title' => ['en' => 'Media contact', 'ku' => 'پەیوەندی ڕاگەیاندن', 'ar' => 'اتصال إعلامي'],
                'body' => [
                    'en' => 'Interview requests with the organiser, ministry speakers or participating universities go through the media team.',
                    'ku' => 'داواکاری چاوپێکەوتن لەگەڵ ڕێکخەر، قسەکەرانی وەزارەت یان زانکۆ بەشداربووەکان لە ڕێگەی تیمی میدیاوە دەڕۆن.',
                    'ar' => 'تُقدَّم طلبات المقابلات مع المنظّم أو متحدثي الوزارة أو الجامعات المشاركة عبر الفريق الإعلامي.',
                ],
                'contact' => 'media@nextstepfair.com',
            ],
        ], [
            [
                'n' => '01',
                'h' => ['en' => 'How to name us', 'ku' => 'چۆن ناومان ببەیت', 'ar' => 'كيف تسمّينا'],
                'p' => [[
                    'en' => 'Use Next Step in body copy after first mention, and Next Step Fair in formal, partner and official contexts. Never NSF, never Nextstep. The tagline, Our Next Chapter, is only used as part of the logo lockup and is never rewritten per edition.',
                    'ku' => 'لە دەقی سەرەکیدا دوای یەکەم ئاماژە «Next Step» بەکاربهێنە، و لە دۆخە فەرمی و هاوبەشەکاندا «Next Step Fair». هەرگیز NSF نا، هەرگیز Nextstep نا. دروشمەکە، Our Next Chapter، تەنها وەک بەشێک لە لۆگۆکە بەکاردێت و هەرگیز بۆ هەر خولێک دووبارە نانووسرێتەوە.',
                    'ar' => 'استخدم Next Step في المتن بعد الذكر الأول، وNext Step Fair في السياقات الرسمية وسياقات الشراكة. لا تستخدم NSF ولا Nextstep أبداً. أما الشعار النصي Our Next Chapter فيُستخدم ضمن الشعار فقط ولا يُعاد كتابته لكل دورة.',
                ]],
            ],
            [
                'n' => '02',
                'h' => ['en' => 'Using the logo', 'ku' => 'بەکارهێنانی لۆگۆ', 'ar' => 'استخدام الشعار'],
                'p' => [[
                    'en' => 'Always place the supplied artwork. The wordmark is customised and must never be re-typeset or rebuilt from a font.',
                    'ku' => 'هەمیشە ئەو ئارتوۆرکە دابنێ کە پێت دراوە. وشەنیشانەکە تایبەتە و هەرگیز نابێت دووبارە بنووسرێتەوە یان لە فۆنتێکەوە دروست بکرێتەوە.',
                    'ar' => 'استخدم دائماً الملف المزوَّد. الاسم المكتوب مصمَّم خصيصاً ولا يجوز إعادة صفّه أو بناؤه من خط جاهز.',
                ]],
                'list' => [
                    [
                        'en' => 'Keep clear space of at least the height of the magenta square on every side.',
                        'ku' => 'لە هەموو لایەکەوە بۆشاییەک بەلایەنی کەمەوە بە بەرزی چوارگۆشە ماجێنتاکە بهێڵەوە.',
                        'ar' => 'اترك مساحة فارغة لا تقل عن ارتفاع المربع الماجنتا من كل جانب.',
                    ],
                    [
                        'en' => 'Minimum size: 120 px wide on screen, 28 mm in print, for the primary lockup.',
                        'ku' => 'بچووکترین قەبارە: 120 px پانی لەسەر شاشە، 28 mm لە چاپدا، بۆ لۆگۆی سەرەکی.',
                        'ar' => 'الحد الأدنى للحجم: 120 بكسل عرضاً على الشاشة و28 ملم في الطباعة للشعار الرئيسي.',
                    ],
                    [
                        'en' => 'Never stretch, rotate, recolour, outline, or place the mark on a gradient or busy photograph.',
                        'ku' => 'هەرگیز لۆگۆکە درێژ مەکە، مەیسووڕێنە، ڕەنگی مەگۆڕە، دەوری مەکێشە، یان لەسەر گرادیەنت یان وێنەیەکی قەرەباڵغ دایمەنێ.',
                        'ar' => 'لا تمدد الشعار ولا تدوّره ولا تغيّر لونه ولا تحدّده بإطار ولا تضعه على تدرّج لوني أو صورة مزدحمة.',
                    ],
                    [
                        'en' => 'When co-branding, separate logos with a 1 px rule and match optical height.',
                        'ku' => 'لە کاتی هاوبراندینگدا، لۆگۆکان بە هێڵێکی 1 px جیا بکەرەوە و بەرزی بینراویان یەکسان بکە.',
                        'ar' => 'عند العلامات المشتركة، افصل الشعارات بخط 1 بكسل ووحّد الارتفاع البصري.',
                    ],
                ],
            ],
            [
                'n' => '03',
                'h' => ['en' => 'Photography and credit', 'ku' => 'وێنە و ئاماژە', 'ar' => 'الصور والإسناد'],
                'p' => [[
                    'en' => 'Photography in this kit is cleared for editorial use in coverage of the fair. Credit as "Next Step Fair 2026". Images may be cropped but not composited, filtered or overlaid with other branding. Identifiable students in released images have written consent on file.',
                    'ku' => 'وێنەکانی ئەم پاکەتە بۆ بەکارهێنانی ڕۆژنامەوانی لە پۆشینی پێشانگادا ڕێگەپێدراون. ئاماژە بە «Next Step Fair 2026» بکە. دەکرێت وێنەکان ببڕدرێن بەڵام تێکەڵ ناکرێن، فلتەریان بۆ ناکرێت و براندی تر لەسەریان دانانرێت. ئەو قوتابییە ناسراوانەی لە وێنە بڵاوکراوەکاندان ڕەزامەندی نووسراویان لە تۆماردایە.',
                    'ar' => 'صور هذا الملف مصرّح باستخدامها تحريرياً في تغطية المعرض. أسندها إلى "Next Step Fair 2026". يجوز اقتصاص الصور، لكن لا يجوز تركيبها أو تطبيق فلاتر عليها أو وضع علامات أخرى فوقها. والطلبة الذين يمكن التعرف عليهم في الصور المصرّح بها لديهم موافقات خطية محفوظة.',
                ]],
            ],
        ]);
    }

    private function about(): void
    {
        $this->page('about', [
            'kicker' => ['en' => 'About', 'ku' => 'دەربارە', 'ar' => 'من نحن'],
            'title' => ['en' => 'Next Step Organization', 'ku' => 'ڕێکخراوی هەنگاوی داهاتوو', 'ar' => 'منظمة Next Step'],
            'standfirst' => [
                'en' => 'An education initiative in Sulaimani that puts institutions and students in the same room, for free, once a year.',
                'ku' => 'دەستپێشخەرییەکی پەروەردەیی لە سلێمانی کە ساڵی جارێک، بەخۆڕایی، دامەزراوەکان و قوتابیان لە یەک ژوور کۆدەکاتەوە.',
                'ar' => 'مبادرة تعليمية في السليمانية تجمع المؤسسات والطلبة في قاعة واحدة، مجاناً، مرة كل عام.',
            ],
            'aside' => [
                'title' => ['en' => 'Contact', 'ku' => 'پەیوەندی', 'ar' => 'التواصل'],
                'body' => [
                    'en' => 'The organising office is in Sulaimani and answers within two working days.',
                    'ku' => 'ئۆفیسی ڕێکخستن لە سلێمانییە و لە ماوەی دوو ڕۆژی کاردا وەڵام دەداتەوە.',
                    'ar' => 'مكتب التنظيم في السليمانية ويرد خلال يومي عمل.',
                ],
                'contact' => 'info@nextstepfair.com',
            ],
        ], [
            [
                'n' => '01',
                'h' => ['en' => 'Mission', 'ku' => 'ئامانج', 'ar' => 'الرسالة'],
                'p' => [[
                    'en' => 'A student should not have to know someone to find out what a degree costs. Next Step exists so that entry scores, tuition, deadlines and funding are answered by the people who decide them, in one hall, at no cost to the student.',
                    'ku' => 'قوتابییەک نابێت پێویستی بەوە بێت کەسێک بناسێت بۆ زانینی ئەوەی بڕوانامەیەک چەند تێدەچێت. هەنگاوی داهاتوو بۆ ئەوە هەیە کە نمرەی وەرگرتن، کرێی خوێندن، کۆتا کاتەکان و خەرجکردن لەلایەن ئەو کەسانەوە وەڵام بدرێنەوە کە بڕیاریان لەسەر دەدەن، لە یەک هۆڵدا، بەبێ هیچ تێچوویەک بۆ قوتابی.',
                    'ar' => 'لا ينبغي أن يحتاج الطالب إلى معرفة أحدهم ليعرف كم تكلّف الشهادة. وُجد Next Step ليجيب من يتخذون القرار عن درجات القبول والرسوم والمواعيد والتمويل، في قاعة واحدة، دون أي كلفة على الطالب.',
                ]],
            ],
            [
                'n' => '02',
                'h' => ['en' => 'History', 'ku' => 'مێژوو', 'ar' => 'التاريخ'],
                'p' => [[
                    'en' => 'The first edition in 2023 was two days, nine institutions and 4,200 visitors. 2024 added a seminar programme and registered parents in their own right. 2025 drew 14,600 visitors, opened the first scholarship desk and registered the organisation as an SDG Acceleration Action with UN DESA. 2026 runs for three days and adds a policy conference on the opening day.',
                    'ku' => 'خولی یەکەم لە 2023 دوو ڕۆژ بوو، نۆ دامەزراوە و 4,200 سەردانکەر. لە 2024 پرۆگرامی سیمینار زیاد کرا و دایک و باوکان بە سەربەخۆیی تۆمار کران. لە 2025 ژمارەی 14,600 سەردانکەری بەخۆیەوە بینی، یەکەم مێزی سکۆلەرشیپی کردەوە و ڕێکخراوەکەی وەک کردەی خێراکردنی SDG لای UN DESA تۆمار کرد. 2026 سێ ڕۆژ بەردەوام دەبێت و کۆنفرانسێکی سیاسەتی لە ڕۆژی کردنەوەدا زیاد دەکات.',
                    'ar' => 'كانت الدورة الأولى عام 2023 يومين وتسع مؤسسات و4,200 زائر. وأضافت 2024 برنامج ندوات وسجّلت أولياء الأمور بصفتهم المستقلة. واستقطبت 2025 عدد 14,600 زائر وافتتحت أول مكتب للمنح وسجّلت المنظمة كمبادرة لتسريع أهداف التنمية المستدامة لدى UN DESA. أما 2026 فتمتد ثلاثة أيام وتضيف مؤتمراً للسياسات في يوم الافتتاح.',
                ]],
            ],
            [
                'n' => '03',
                'h' => ['en' => 'Team', 'ku' => 'تیم', 'ar' => 'الفريق'],
                'p' => [[
                    'en' => 'The fair is run by Next Step Organization under Avin Qadir, Organizer and Co-Founder, with a guidance team of counsellors who spend the spring in schools across Sulaimani and Halabja before the September edition.',
                    'ku' => 'پێشانگاکە لەلایەن ڕێکخراوی هەنگاوی داهاتووەوە بەڕێوە دەبرێت بە سەرپەرشتی ئاڤین قادر، ڕێکخەر و هاوبەشی دامەزرێنەر، لەگەڵ تیمێکی ڕێنمایی ڕاوێژکاران کە بەهار لە قوتابخانەکانی سلێمانی و هەڵەبجە بەسەردەبەن پێش خولی ئەیلول.',
                    'ar' => 'يدير المعرض منظمة Next Step بقيادة آفين قادر، المنظِّمة والشريكة المؤسِّسة، مع فريق إرشاد من المرشدين يقضون الربيع في مدارس السليمانية وحلبجة قبل دورة أيلول.',
                ]],
            ],
        ]);
    }

    private function tracks(): void
    {
        $this->page('fair', [
            'kicker' => ['en' => 'The Expo · 3 days', 'ku' => 'پێشانگا · سێ ڕۆژ', 'ar' => 'المعرض · ثلاثة أيام'],
            'title' => ['en' => 'The Fair', 'ku' => 'پێشانگا', 'ar' => 'المعرض'],
            'standfirst' => [
                'en' => 'Three days on the exhibition floor: universities and institutes at staffed desks, seminars, workshops, tournaments and the Zankoline support desks.',
                'ku' => 'سێ ڕۆژ لەسەر شانۆی پێشانگا: زانکۆ و پەیمانگاکان لەسەر مێزی ستافدار، سیمینار، وۆرکشۆپ، پێشبڕکێ و مێزەکانی یارمەتی زانکۆلاین.',
                'ar' => 'ثلاثة أيام في صالة العرض: جامعات ومعاهد على طاولات مزوّدة بموظفين، وندوات وورش عمل ومسابقات ومكاتب دعم زانكۆلاین.',
            ],
            'aside' => [
                'title' => ['en' => 'Exhibiting with us', 'ku' => 'پیشاندان لەگەڵمان', 'ar' => 'العرض معنا'],
                'body' => [
                    'en' => 'Desks for the 2026 floor are allocated from March. Universities, institutes and commercial exhibitors apply through the same form.',
                    'ku' => 'مێزەکانی شانۆی 2026 لە ئازارەوە دابەش دەکرێن. زانکۆ، پەیمانگا و پیشاندەرە بازرگانییەکان لە هەمان فۆرمەوە داوا دەکەن.',
                    'ar' => 'تُوزّع الطاولات لصالة 2026 اعتباراً من آذار. تتقدّم الجامعات والمعاهد والعارضون التجاريون عبر الاستمارة نفسها.',
                ],
                'contact' => 'exhibit@nextstepfair.com',
            ],
        ], [
            [
                'n' => '01',
                'h' => ['en' => 'What happens on the floor', 'ku' => 'لەسەر شانۆکە چی ڕوودەدات', 'ar' => 'ما يجري في الصالة'],
                'p' => [[
                    'en' => 'Admission officers, not marketing staff, sit at every desk, with entry scores and tuition posted on the booth wall. The Zankoline clinic runs continuously in Hall C: bring your grades and your phone, and staff sit with you until the form is submitted.',
                    'ku' => 'بەرپرسانی وەرگرتن، نەک ستافی بازاڕگەری، لەسەر هەموو مێزێک دادەنیشن، لەگەڵ نمرەی وەرگرتن و کرێی خوێندن کە لەسەر دیواری ستاندەکە هەڵواسراون. کلینیکی زانکۆلاین بەردەوام لە هۆڵی C بەڕێوە دەچێت: نمرەکانت و مۆبایلەکەت بهێنە، ستاف لەگەڵت دادەنیشن تا فۆرمەکە دەنێردرێت.',
                    'ar' => 'يجلس على كل طاولة مسؤولو قبول لا موظفو تسويق، مع عرض درجات القبول والرسوم على جدار الجناح. وتعمل عيادة زانكۆلاین باستمرار في القاعة C: أحضر درجاتك وهاتفك، ويجلس معك الفريق حتى إرسال الاستمارة.',
                ], [
                    'en' => 'Nothing on the floor is a sales pitch you have to sit through. Walk up to a desk, ask what a programme actually costs and what grade it takes, and leave with an answer. Most students spend two to three hours across a single day; families who come for the seminars usually take two days.',
                    'ku' => 'هیچ شتێک لەسەر شانۆکە پێشکەشکردنێکی فرۆشتن نییە کە ناچار بیت گوێی لێ بگریت. بڕۆ بۆ لای مێزێک، بپرسە پرۆگرامێک بە ڕاستی چەندی دەوێت و چ نمرەیەکی پێویستە، و بە وەڵامەوە بڕۆ. زۆربەی خوێندکاران دوو بۆ سێ کاتژمێر لە یەک ڕۆژدا بەسەردەبەن؛ ئەو خێزانانەی بۆ سیمینارەکان دێن زۆرجار دوو ڕۆژ دەخایەنن.',
                    'ar' => 'لا شيء في الصالة عرض بيع مضطر لسماعه. اقترب من أي طاولة، واسأل كم يكلّف البرنامج فعلاً وأي درجة يتطلّب، واخرج بجواب. يقضي معظم الطلبة ساعتين إلى ثلاث في يوم واحد، أما العائلات التي تحضر للندوات فتأخذ يومين عادةً.',
                ]],
                'list' => [],
            ],
            [
                'n' => '02',
                'h' => ['en' => 'Who exhibits', 'ku' => 'کێ پیشان دەدات', 'ar' => 'من يعرض'],
                'p' => [[
                    'en' => 'The floor is built around institutions that can give a student a real answer on the day. Commercial exhibitors sit in a separate zone so an admissions conversation is never confused with a sales one.',
                    'ku' => 'شانۆکە لەدەوری ئەو دامەزراوانە بنیات نراوە کە دەتوانن هەمان ڕۆژ وەڵامێکی ڕاستەقینە بە خوێندکار بدەن. پیشاندەرە بازرگانییەکان لە ناوچەیەکی جیاواز دادەنیشن تا گفتوگۆی وەرگرتن هەرگیز لەگەڵ گفتوگۆی فرۆشتن تێکەڵ نەبێت.',
                    'ar' => 'بُنيت الصالة حول مؤسسات تستطيع إعطاء الطالب جواباً حقيقياً في اليوم نفسه. ويجلس العارضون التجاريون في منطقة منفصلة كي لا يختلط حديث القبول بحديث البيع.',
                ]],
                'list' => [
                    [
                        'en' => 'Universities and institutes from the Kurdistan Region and federal Iraq, with admissions staff at the desk',
                        'ku' => 'زانکۆ و پەیمانگاکانی هەرێمی کوردستان و عێراقی فیدراڵ، لەگەڵ ستافی وەرگرتن لەسەر مێزەکە',
                        'ar' => 'جامعات ومعاهد من إقليم كوردستان والعراق الاتحادي، مع موظفي القبول على الطاولة',
                    ],
                    [
                        'en' => 'International universities from Türkiye, Jordan, the Gulf, Europe and North America',
                        'ku' => 'زانکۆی نێودەوڵەتی لە تورکیا، ئوردن، کەنداو، ئەوروپا و ئەمریکای باکوور',
                        'ar' => 'جامعات دولية من تركيا والأردن والخليج وأوروبا وأمريكا الشمالية',
                    ],
                    [
                        'en' => 'Scholarship providers, language-test centres and student-finance desks',
                        'ku' => 'دابینکەرانی سکۆلەرشیپ، ناوەندەکانی تاقیکردنەوەی زمان و مێزەکانی دارایی خوێندکاران',
                        'ar' => 'جهات المنح ومراكز اختبارات اللغة ومكاتب التمويل الطلابي',
                    ],
                    [
                        'en' => 'Vocational institutes, technical training providers and employers recruiting school leavers',
                        'ku' => 'پەیمانگا پیشەییەکان، دابینکەرانی ڕاهێنانی تەکنیکی و خاوەنکارانی وەرگرتنی دەرچووانی قوتابخانە',
                        'ar' => 'المعاهد المهنية ومزوّدو التدريب التقني وأصحاب العمل الباحثون عن خرّيجي المدارس',
                    ],
                ],
            ],
            [
                'n' => '03',
                'h' => ['en' => 'The three halls', 'ku' => 'سێ هۆڵەکە', 'ar' => 'القاعات الثلاث'],
                'p' => [[
                    'en' => 'The Cultural Factory is laid out as three connected halls on one level. Gate A is the main entrance and is step-free; the floor plan is printed at every gate and available on this site before you travel.',
                    'ku' => 'کارگەی کولتوری وەک سێ هۆڵی پێکەوەبەستراو لەسەر یەک ئاست ڕێکخراوە. دەروازەی A دەروازەی سەرەکییە و بێ پلیکانەیە؛ پلانی شانۆکە لەسەر هەموو دەروازەیەک چاپ کراوە و پێش گەشتکردن لەم ماڵپەڕەدا بەردەستە.',
                    'ar' => 'يتوزّع مصنع الثقافة على ثلاث قاعات متصلة في مستوى واحد. البوابة A هي المدخل الرئيسي وخالية من الدرج، وخريطة الصالة مطبوعة عند كل بوابة ومتاحة على هذا الموقع قبل سفرك.',
                ]],
                'list' => [
                    [
                        'en' => 'Hall A — international universities and the largest desks',
                        'ku' => 'هۆڵی A — زانکۆ نێودەوڵەتییەکان و گەورەترین مێزەکان',
                        'ar' => 'القاعة A — الجامعات الدولية وأكبر الطاولات',
                    ],
                    [
                        'en' => 'Hall B — regional and Iraqi institutions, plus the conference on Day 1',
                        'ku' => 'هۆڵی B — دامەزراوە هەرێمی و عێراقییەکان، لەگەڵ کۆنفرانسەکە لە ڕۆژی یەکەم',
                        'ar' => 'القاعة B — المؤسسات الإقليمية والعراقية، إضافة إلى المؤتمر في اليوم الأول',
                    ],
                    [
                        'en' => 'Hall C — Zankoline clinic, scholarship desk, workshops and the seminar rooms',
                        'ku' => 'هۆڵی C — کلینیکی زانکۆلاین، مێزی سکۆلەرشیپ، وۆرکشۆپەکان و ژوورەکانی سیمینار',
                        'ar' => 'القاعة C — عيادة زانكۆلاین ومكتب المنح وورش العمل وقاعات الندوات',
                    ],
                ],
            ],
            [
                'n' => '04',
                'h' => ['en' => 'Seminars, workshops and tournaments', 'ku' => 'سیمینار، وۆرکشۆپ و پێشبڕکێ', 'ar' => 'ندوات وورش ومسابقات'],
                'p' => [[
                    'en' => 'Around forty sessions run across the three days: application clinics, subject talks, parent sessions on financing a degree, and student competitions. Seating is allocated on arrival, so bookmark the sessions you want and come ten minutes early.',
                    'ku' => 'نزیکەی چل دانیشتن بە درێژایی سێ ڕۆژەکە بەڕێوە دەچن: کلینیکی داواکاری، وتاری بابەتی، دانیشتنی دایک و باوکان دەربارەی دابینکردنی خەرجی خوێندن، و پێشبڕکێی خوێندکاران. کورسییەکان بەپێی هاتن دابەش دەکرێن، بۆیە ئەو دانیشتنانەی دەتەوێت نیشانە بکە و دە خولەک زوتر وەرە.',
                    'ar' => 'تُقام نحو أربعين جلسة على مدى الأيام الثلاثة: عيادات تقديم الطلبات، ومحاضرات تخصّصية، وجلسات لأولياء الأمور حول تمويل الدراسة، ومسابقات طلابية. تُوزّع المقاعد عند الوصول، فاحفظ الجلسات التي تريدها واحضر قبل عشر دقائق.',
                ]],
                'list' => [],
            ],
            [
                'n' => '05',
                'h' => ['en' => 'Planning your visit', 'ku' => 'پلاندانان بۆ سەردانەکەت', 'ar' => 'التخطيط لزيارتك'],
                'p' => [[
                    'en' => 'Entry is free for students and parents, but registration is not optional: your QR badge is what gets you through the gate without queueing at the desk. Register before you travel, and save the WhatsApp message — the badge lives in that chat.',
                    'ku' => 'چوونەژوورەوە بۆ خوێندکاران و دایک و باوکان بەخۆڕاییە، بەڵام تۆمارکردن ئیختیاری نییە: باجی QRەکەت ئەوەیە کە بەبێ ڕیزگرتن لەسەر مێزەکە لە دەروازە تێدەپەڕیت. پێش گەشتکردن تۆمار بکە، و نامەی واتساپەکە پاشەکەوت بکە — باجەکە لەو چاتەدا دەمێنێتەوە.',
                    'ar' => 'الدخول مجاني للطلبة وأولياء الأمور، لكن التسجيل ليس اختيارياً: بطاقة QR هي ما يُدخلك من البوابة دون الوقوف في طابور المكتب. سجّل قبل سفرك، واحفظ رسالة واتساب — فالبطاقة تبقى في تلك المحادثة.',
                ]],
                'list' => [
                    [
                        'en' => 'Bring your grades — a photo on your phone is enough for most desks',
                        'ku' => 'نمرەکانت بهێنە — وێنەیەک لەسەر مۆبایلەکەت بۆ زۆربەی مێزەکان بەسە',
                        'ar' => 'أحضر درجاتك — صورة على هاتفك تكفي لمعظم الطاولات',
                    ],
                    [
                        'en' => 'Bring ID if you plan to sit a language test or open a scholarship file',
                        'ku' => 'ناسنامە بهێنە ئەگەر بەتەمای تاقیکردنەوەی زمان یان کردنەوەی فایلی سکۆلەرشیپیت',
                        'ar' => 'أحضر هويتك إن كنت تنوي أداء اختبار لغة أو فتح ملف منحة',
                    ],
                    [
                        'en' => 'Parking behind Hall C is free; the Gate A entrance is step-free',
                        'ku' => 'پارکینگی پشتی هۆڵی C بەخۆڕاییە؛ دەروازەی A بێ پلیکانەیە',
                        'ar' => 'الموقف خلف القاعة C مجاني، ومدخل البوابة A خالٍ من الدرج',
                    ],
                    [
                        'en' => 'Day 1 is the busiest; Day 3 afternoons are the quietest if you want unhurried conversations',
                        'ku' => 'ڕۆژی یەکەم قەرەباڵغترینە؛ دوانیوەڕۆی ڕۆژی سێیەم هێمنترینە ئەگەر گفتوگۆی بێپەلەت دەوێت',
                        'ar' => 'اليوم الأول هو الأزحم، وبعد ظهر اليوم الثالث هو الأهدأ إن أردت حديثاً على مهل',
                    ],
                ],
            ],
        ]);

        $this->page('conference', [
            'kicker' => ['en' => 'The Conference · Day 1', 'ku' => 'کۆنفرانس · ڕۆژی یەکەم', 'ar' => 'المؤتمر · اليوم الأول'],
            'title' => ['en' => 'The Conference', 'ku' => 'کۆنفرانس', 'ar' => 'المؤتمر'],
            'standfirst' => [
                'en' => '28 September, Hall B. A one-day policy programme for ministries, missions, university leadership and official delegations, with simultaneous interpretation in every session.',
                'ku' => '28ی ئەیلول، هۆڵی B. پرۆگرامێکی سیاسەتی یەک ڕۆژ بۆ وەزارەتەکان، نوێنەرایەتییەکان، سەرکردایەتی زانکۆ و شاندە فەرمییەکان، لەگەڵ وەرگێڕانی هاوکات لە هەموو دانیشتنێکدا.',
                'ar' => '28 أيلول، القاعة B. برنامج سياسات ليوم واحد للوزارات والبعثات وقيادات الجامعات والوفود الرسمية، مع ترجمة فورية في كل جلسة.',
            ],
            'aside' => [
                'title' => ['en' => 'Protocol office', 'ku' => 'نووسینگەی پڕۆتۆکۆل', 'ar' => 'مكتب المراسم'],
                'body' => [
                    'en' => 'Delegation lists, invitation letters for travel authorisation and seating requests are handled by the protocol desk.',
                    'ku' => 'لیستی شاندەکان، نامەی بانگهێشت بۆ مۆڵەتی گەشت و داواکاری شوێنی دانیشتن لەلایەن مێزی پڕۆتۆکۆلەوە بەڕێوە دەبرێن.',
                    'ar' => 'يتولّى مكتب المراسم قوائم الوفود ورسائل الدعوة لأغراض تصاريح السفر وطلبات الجلوس.',
                ],
                'contact' => 'conference@nextstepfair.com',
            ],
        ], [
            [
                'n' => '01',
                'h' => ['en' => 'Who attends', 'ku' => 'کێ ئامادە دەبێت', 'ar' => 'من يحضر'],
                'p' => [[
                    'en' => 'The conference is invitation-led and seats around 220 delegates. It is a working day for people who set or implement higher-education policy in the region, not a ceremonial opening — the opening ceremony is a separate item on the morning agenda.',
                    'ku' => 'کۆنفرانسەکە بە بانگهێشت بەڕێوە دەچێت و نزیکەی 220 نوێنەر جێگا دەکاتەوە. ڕۆژێکی کارە بۆ ئەو کەسانەی سیاسەتی خوێندنی باڵا لە هەرێمدا دادەنێن یان جێبەجێی دەکەن، نەک کردنەوەیەکی ڕێورەسمی — ڕێورەسمی کردنەوە بڕگەیەکی جیاوازە لە بەرنامەی بەیانی.',
                    'ar' => 'المؤتمر بالدعوة ويتّسع لنحو 220 مندوباً. وهو يوم عمل لمن يرسمون سياسات التعليم العالي في الإقليم أو ينفّذونها، لا افتتاحاً بروتوكولياً — فحفل الافتتاح بند منفصل في جدول الصباح.',
                ]],
                'list' => [
                    [
                        'en' => 'Ministries and government bodies of the Kurdistan Regional Government and federal Iraq',
                        'ku' => 'وەزارەت و دەزگا حکومییەکانی حکومەتی هەرێمی کوردستان و عێراقی فیدراڵ',
                        'ar' => 'وزارات وهيئات حكومية من حكومة إقليم كوردستان والعراق الاتحادي',
                    ],
                    [
                        'en' => 'University presidents, deans and admissions leadership',
                        'ku' => 'سەرۆکی زانکۆکان، ڕاگرەکان و سەرکردایەتی وەرگرتن',
                        'ar' => 'رؤساء الجامعات والعمداء وقيادات القبول',
                    ],
                    [
                        'en' => 'Diplomatic missions, cultural institutes and international education agencies',
                        'ku' => 'نوێنەرایەتییە دیپلۆماسییەکان، پەیمانگا کولتوورییەکان و ئاژانسە پەروەردەیییە نێودەوڵەتییەکان',
                        'ar' => 'البعثات الدبلوماسية والمعاهد الثقافية ووكالات التعليم الدولية',
                    ],
                    [
                        'en' => 'Accredited media, seated in a dedicated press area beside Hall B',
                        'ku' => 'میدیای ڕەزامەندیپێدراو، لە ناوچەیەکی تایبەتی ڕۆژنامەوانی تەنیشت هۆڵی B دادەنیشن',
                        'ar' => 'إعلام معتمد، يجلس في منطقة صحفية مخصّصة بجوار القاعة B',
                    ],
                ],
            ],
            [
                'n' => '02',
                'h' => ['en' => 'Themes', 'ku' => 'بابەتەکان', 'ar' => 'المحاور'],
                'p' => [[
                    'en' => 'Access and placement at scale, accreditation and the cross-border recognition of Kurdistan Region qualifications, and the skills the region needs by 2030. The afternoon roundtable is closed to invited delegations; its output is a short recommendations note published with the edition impact report.',
                    'ku' => 'دەستگەیشتن و دابەشکردن لە ئاستێکی بەرفراوان، متمانەپێکراوی و دانپێدانانی سنووربەزێن بە بڕوانامەکانی هەرێمی کوردستان، و ئەو شارەزاییانەی هەرێم تا 2030 پێویستی پێیانە. مێزە گردەکەی دوانیوەڕۆ داخراوە بۆ شاندە بانگهێشتکراوەکان؛ دەرئەنجامەکەی تێبینییەکی کورتی ڕاسپاردەیە کە لەگەڵ ڕاپۆرتی کاریگەری خولەکەدا بڵاو دەکرێتەوە.',
                    'ar' => 'الالتحاق والتوزيع على نطاق واسع، والاعتماد الأكاديمي والاعتراف بشهادات إقليم كوردستان عبر الحدود، والمهارات التي يحتاجها الإقليم بحلول 2030. والطاولة المستديرة بعد الظهر مغلقة على الوفود المدعوة، ومخرجها مذكرة توصيات قصيرة تُنشر مع تقرير أثر الدورة.',
                ]],
                'list' => [
                    [
                        'en' => 'Access and placement: moving 14,000 school leavers into the right programme each year',
                        'ku' => 'دەستگەیشتن و دابەشکردن: گواستنەوەی 14,000 دەرچووی قوتابخانە بۆ پرۆگرامی گونجاو لە ساڵێکدا',
                        'ar' => 'الالتحاق والتوزيع: نقل 14,000 خرّيج مدرسة إلى البرنامج المناسب كل عام',
                    ],
                    [
                        'en' => 'Accreditation and recognition of Kurdistan Region qualifications abroad',
                        'ku' => 'متمانەپێکراوی و دانپێدانان بە بڕوانامەکانی هەرێمی کوردستان لە دەرەوە',
                        'ar' => 'الاعتماد والاعتراف بشهادات إقليم كوردستان في الخارج',
                    ],
                    [
                        'en' => 'The skills gap to 2030: what employers need and what the sector currently produces',
                        'ku' => 'بۆشایی شارەزایی تا 2030: خاوەنکاران چییان پێویستە و کەرتەکە ئێستا چی بەرهەم دەهێنێت',
                        'ar' => 'فجوة المهارات حتى 2030: ما يحتاجه أصحاب العمل وما ينتجه القطاع حالياً',
                    ],
                    [
                        'en' => 'Financing study: scholarships, student finance and the cost of studying abroad',
                        'ku' => 'دابینکردنی خەرجی خوێندن: سکۆلەرشیپ، دارایی خوێندکاران و تێچووی خوێندن لە دەرەوە',
                        'ar' => 'تمويل الدراسة: المنح والتمويل الطلابي وكلفة الدراسة في الخارج',
                    ],
                ],
            ],
            [
                'n' => '03',
                'h' => ['en' => 'How the day runs', 'ku' => 'ڕۆژەکە چۆن بەڕێوە دەچێت', 'ar' => 'كيف يسير اليوم'],
                'p' => [[
                    'en' => 'Registration and coffee from 08:30, opening ceremony at 10:00, two plenary panels before lunch, and the closed roundtable in the afternoon. Delegates who also want to walk the exhibition floor should keep the last hour of the day free — the fair is open until 20:00.',
                    'ku' => 'تۆمارکردن و قاوە لە 08:30، ڕێورەسمی کردنەوە لە 10:00، دوو پانێلی گشتی پێش نانی نیوەڕۆ، و مێزە گردە داخراوەکە لە دوانیوەڕۆ. ئەو نوێنەرانەی دەیانەوێت بەسەر شانۆی پێشانگاکەشدا بڕۆن با کاتژمێری کۆتایی ڕۆژەکە بەتاڵ بهێڵنەوە — پێشانگاکە تا 20:00 کراوەیە.',
                    'ar' => 'التسجيل والقهوة من 08:30، وحفل الافتتاح 10:00، وجلستان عامّتان قبل الغداء، والطاولة المستديرة المغلقة بعد الظهر. وعلى المندوبين الراغبين في جولة داخل صالة العرض إبقاء الساعة الأخيرة من اليوم فارغة — فالمعرض مفتوح حتى 20:00.',
                ]],
                'list' => [],
            ],
            [
                'n' => '04',
                'h' => ['en' => 'Interpretation and access', 'ku' => 'وەرگێڕان و دەستگەیشتن', 'ar' => 'الترجمة وإمكانية الوصول'],
                'p' => [[
                    'en' => 'Every plenary session carries simultaneous interpretation between Kurdish, Arabic and English; headsets are issued against your badge at the Hall B door and returned at the end of the day. Hall B is step-free, and a quiet room is available beside the press area.',
                    'ku' => 'هەموو دانیشتنێکی گشتی وەرگێڕانی هاوکاتی هەیە لە نێوان کوردی، عەرەبی و ئینگلیزیدا؛ هێدفۆنەکان بەرامبەر باجەکەت لە دەرگای هۆڵی B دەدرێن و لە کۆتایی ڕۆژدا دەگەڕێنرێنەوە. هۆڵی B بێ پلیکانەیە، و ژوورێکی هێمن لە تەنیشت ناوچەی ڕۆژنامەوانی بەردەستە.',
                    'ar' => 'تُرافق كل جلسة عامة ترجمة فورية بين الكردية والعربية والإنجليزية، وتُسلَّم السمّاعات مقابل بطاقتك عند باب القاعة B وتُعاد في نهاية اليوم. القاعة B خالية من الدرج، وتتوفّر غرفة هادئة بجوار المنطقة الصحفية.',
                ]],
                'list' => [],
            ],
            [
                'n' => '05',
                'h' => ['en' => 'Invitations and badges', 'ku' => 'بانگهێشت و باجەکان', 'ar' => 'الدعوات والبطاقات'],
                'p' => [[
                    'en' => 'RSVP with your institutional email address and your badge is issued immediately, by email, with the PDF attached. An RSVP from a personal address goes to the protocol team for confirmation first — a delegate badge is an access credential, so we check before issuing one.',
                    'ku' => 'بە ئیمەیڵی دامەزراوەییەکەت وەڵام بدەرەوە و باجەکەت دەستبەجێ بە ئیمەیڵ دەردەچێت، لەگەڵ PDFی هاوپێچ. وەڵامدانەوە لە ناونیشانێکی کەسییەوە سەرەتا بۆ تیمی پڕۆتۆکۆل دەچێت بۆ پشتڕاستکردنەوە — باجی نوێنەر بەڵگەنامەی دەستگەیشتنە، بۆیە پێش دەرکردنی دەیپشکنین.',
                    'ar' => 'أكّد حضورك ببريد مؤسستك وتصدر بطاقتك فوراً عبر البريد الإلكتروني مع ملف PDF مرفق. أما التأكيد من عنوان شخصي فيذهب أولاً إلى فريق المراسم للتحقّق — فبطاقة المندوب وثيقة دخول، ونتحقّق قبل إصدارها.',
                ]],
                'list' => [
                    [
                        'en' => 'Invitation letters for travel authorisation: request at RSVP, allow ten working days',
                        'ku' => 'نامەی بانگهێشت بۆ مۆڵەتی گەشت: لە کاتی وەڵامدانەوەدا داوای بکە، دە ڕۆژی کاری چاوەڕێ بکە',
                        'ar' => 'رسائل الدعوة لتصاريح السفر: اطلبها عند تأكيد الحضور، وامنحها عشرة أيام عمل',
                    ],
                    [
                        'en' => 'Delegations: one RSVP per person, so every badge carries the right name at the gate',
                        'ku' => 'شاندەکان: بۆ هەر کەسێک یەک وەڵامدانەوە، تا هەموو باجێک ناوی دروست لە دەروازە هەڵبگرێت',
                        'ar' => 'الوفود: تأكيد حضور واحد لكل شخص، ليحمل كل بطاقة الاسم الصحيح عند البوابة',
                    ],
                    [
                        'en' => 'Media accreditation is requested on the same form and confirmed separately',
                        'ku' => 'ڕەزامەندی میدیایی لە هەمان فۆرمدا داوا دەکرێت و بە جیا پشتڕاست دەکرێتەوە',
                        'ar' => 'يُطلب الاعتماد الإعلامي في الاستمارة نفسها ويُؤكَّد بشكل منفصل',
                    ],
                ],
            ],
        ]);

        $this->page('scholarships', [
            'kicker' => ['en' => 'Scholarships', 'ku' => 'سکۆلەرشیپ', 'ar' => 'المنح الدراسية'],
            'title' => ['en' => 'Scholarships', 'ku' => 'سکۆلەرشیپ', 'ar' => 'المنح الدراسية'],
            'standfirst' => [
                'en' => 'Named programmes, real deadlines and the documents you need before you start.',
                'ku' => 'پرۆگرامی ناودار، کۆتا کاتی ڕاستەقینە و ئەو بەڵگەنامانەی پێش دەستپێکردن پێویستن.',
                'ar' => 'برامج محددة بالاسم، ومواعيد نهائية حقيقية، والمستندات المطلوبة قبل أن تبدأ.',
            ],
        ], [
            [
                'n' => '01',
                'h' => ['en' => 'At the fair', 'ku' => 'لە پێشانگادا', 'ar' => 'في المعرض'],
                'p' => [[
                    'en' => 'The scholarship desk sits at booth C7 for all three days and handled 1,140 enquiries in 2025. Bring your grades, your ID and any language certificate you already hold.',
                    'ku' => 'مێزی سکۆلەرشیپ لە ستاندی C7 بە درێژایی هەر سێ ڕۆژ دادەنیشێت و لە 2025 ژمارەی 1,140 پرسیاری وەڵام دایەوە. نمرەکانت، ناسنامەکەت و هەر بڕوانامەیەکی زمان کە هەتە بهێنە.',
                    'ar' => 'يقع مكتب المنح في الجناح C7 طوال الأيام الثلاثة، وقد تعامل مع 1,140 استفساراً في 2025. أحضر درجاتك وهويتك وأي شهادة لغة تحملها.',
                ]],
                'list' => [],
            ],
            [
                'n' => '02',
                'h' => ['en' => 'What is on offer', 'ku' => 'چی بەردەستە', 'ar' => 'ما هو المتاح'],
                'p' => [[
                    'en' => 'Scholarships at the fair fall into three groups, and they do not compete with each other — a student can hold a government award and a university fee waiver at the same time.',
                    'ku' => 'سکۆلەرشیپەکانی پێشانگا دەکەونە سێ گروپەوە، و ڕکابەری یەکتر ناکەن — خوێندکارێک دەتوانێت هاوکات خەڵاتی حکومی و لێبووردنی کرێی زانکۆی هەبێت.',
                    'ar' => 'تنقسم المنح في المعرض إلى ثلاث فئات لا تتنافس فيما بينها — إذ يمكن للطالب أن يجمع بين منحة حكومية وإعفاء من رسوم الجامعة.',
                ]],
                'list' => [
                    [
                        'en' => 'Government programmes: KRG and federal awards for study at home and abroad',
                        'ku' => 'پرۆگرامە حکومییەکان: خەڵاتی هەرێم و فیدراڵ بۆ خوێندن لە ناوخۆ و دەرەوە',
                        'ar' => 'برامج حكومية: منح إقليم كوردستان والمنح الاتحادية للدراسة داخلياً وخارجياً',
                    ],
                    [
                        'en' => 'University awards: merit and need-based fee reductions offered at the desk',
                        'ku' => 'خەڵاتی زانکۆ: کەمکردنەوەی کرێ بەپێی شایستەیی و پێویستی کە لەسەر مێزەکە پێشکەش دەکرێت',
                        'ar' => 'منح جامعية: تخفيضات رسوم على أساس الجدارة أو الحاجة تُعرض على الطاولة',
                    ],
                    [
                        'en' => 'Partner and embassy programmes: language, exchange and full-degree funding',
                        'ku' => 'پرۆگرامی هاوبەش و باڵیۆزخانە: دارایی زمان، ئاڵوگۆڕ و بڕوانامەی تەواو',
                        'ar' => 'برامج الشركاء والسفارات: تمويل اللغة والتبادل والدرجة الكاملة',
                    ],
                ],
            ],
            [
                'n' => '03',
                'h' => ['en' => 'Before you apply', 'ku' => 'پێش داواکردن', 'ar' => 'قبل التقديم'],
                'p' => [[
                    'en' => 'Almost every rejected application fails on paperwork, not on grades. Get these ready before the deadline rather than in the week of it — translations and attestations take longer than students expect.',
                    'ku' => 'بەنزیکەیی هەموو داواکارییەکی ڕەتکراوە لەبەر بەڵگەنامە شکست دەهێنێت، نەک لەبەر نمرە. ئەمانە پێش کۆتا کات ئامادە بکە نەک لە هەفتەی کۆتاییدا — وەرگێڕان و پشتڕاستکردنەوە زیاتر لەوەی خوێندکاران چاوەڕێی دەکەن کات دەبات.',
                    'ar' => 'تسقط معظم الطلبات المرفوضة بسبب الأوراق لا بسبب الدرجات. جهّز هذه قبل الموعد النهائي بوقت كافٍ لا في أسبوعه — فالترجمات والتصديقات تستغرق وقتاً أطول مما يتوقّع الطلبة.',
                ]],
                'list' => [
                    [
                        'en' => 'Certified transcript and school-leaving certificate, translated where required',
                        'ku' => 'پێڕستی نمرەی پەسەندکراو و بڕوانامەی تەواوکردنی قوتابخانە، لە شوێنی پێویستدا وەرگێڕدراو',
                        'ar' => 'كشف درجات مصدّق وشهادة إتمام الدراسة، مترجمة عند الحاجة',
                    ],
                    [
                        'en' => 'A valid passport — several programmes will not open a file without one',
                        'ku' => 'پاسپۆرتێکی کاراوە — چەند پرۆگرامێک بەبێ ئەوە فایل ناکەنەوە',
                        'ar' => 'جواز سفر ساري المفعول — عدة برامج لا تفتح ملفاً بدونه',
                    ],
                    [
                        'en' => 'A language test result, or the test date you have booked',
                        'ku' => 'ئەنجامی تاقیکردنەوەی زمان، یان ئەو ڕێککەوتەی تاقیکردنەوە کە حیجزت کردووە',
                        'ar' => 'نتيجة اختبار لغة، أو موعد الاختبار الذي حجزته',
                    ],
                    [
                        'en' => 'One reference letter, ideally from a subject teacher rather than a family friend',
                        'ku' => 'یەک نامەی ڕاسپاردە، باشترە لە مامۆستای بابەتەکەوە بێت نەک هاوڕێیەکی خێزان',
                        'ar' => 'رسالة توصية واحدة، يُفضّل أن تكون من مدرّس المادة لا من صديق للعائلة',
                    ],
                ],
            ],
        ]);
    }
}
