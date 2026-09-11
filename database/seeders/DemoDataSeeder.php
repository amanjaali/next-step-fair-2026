<?php

namespace Database\Seeders;

use App\Models\CheckIn;
use App\Models\EventSession;
use App\Models\Message;
use App\Models\QrCampaign;
use App\Models\QrScan;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Realistic demo registrations so the dashboard, the funnel and the check-in
 * scanner all have something to show before the first real registration.
 *
 * Names are Kurdish and Arabic, cities are the real Kurdistan/Iraq list, and
 * phone numbers use the +964 mobile prefixes actually in use.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->qrCampaigns();

        /*
         * Fair Registrations, Conference RSVPs and Messages — demo desk data.
         * Uncomment for a local dashboard preview; leave off on staging/production
         * so real registrations are not mixed with seeded names and phones.
         */
        // if (Registration::count() > 0) {
        //     return;
        // }
        //
        // $sessions = EventSession::where('bookable', true)->pluck('id')->all();
        // $staff = User::where('email', 'gate@nextstepfair.com')->first();
        //
        // // Fair Registrations — students, parents, check-ins, WhatsApp rows.
        // $this->fairRegistrations($sessions, $staff);
        //
        // // Conference RSVPs — delegates, pending/confirmed, email + WhatsApp rows.
        // $this->conferenceRsvps();
        //
        // // Fair registration volume for the dashboard chart (no message rows).
        // $this->registrationHistory();
    }

    private function qrCampaigns(): void
    {
        $staff = User::where('email', 'registration@nextstepfair.com')->first();

        $campaigns = [
            ['School poster — Sulaimani', 'school-poster-slm', 'poster', 'school', '#B64698', 420],
            ['School roadshow handout', 'roadshow-2026', 'print', 'school', '#B64698', 260],
            ['Instagram bio link', 'ig-bio', 'social', 'instagram', '#B64698', 1180],
            ['Conference invitation card', 'conf-invite', 'print', 'protocol', '#2C4BE0', 90],
            ['Booth stand — Hall A', 'hall-a-stand', 'booth', 'venue', '#B64698', 0],
        ];

        foreach ($campaigns as [$name, $code, $medium, $source, $accent, $scans]) {
            $campaign = QrCampaign::updateOrCreate(['code' => $code], [
                'name' => $name,
                'target_url' => str_contains($code, 'conf')
                    ? url('/en/register/conference')
                    : url('/en/register/fair'),
                'medium' => $medium,
                'utm_source' => $source,
                'utm_medium' => $medium,
                'utm_campaign' => $code,
                'accent' => $accent,
                'active' => true,
                'created_by' => $staff?->id,
                'scan_count' => $scans,
            ]);

            // A representative sample of scans so the analytics chart is not empty.
            if ($campaign->scans()->count() === 0 && $scans > 0) {
                $rows = [];
                for ($i = 0; $i < min($scans, 120); $i++) {
                    $rows[] = [
                        'qr_campaign_id' => $campaign->id,
                        'scanned_at' => now()->subDays(random_int(0, 27))->subMinutes(random_int(0, 1439)),
                        'ip_hash' => hash('sha256', 'demo-'.$campaign->id.'-'.$i),
                        'is_unique' => $i % 4 !== 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                QrScan::insert($rows);
            }
        }
    }

    /* --------------------------------------------------------------------- */
    /* Fair Registrations                                                    */
    /* --------------------------------------------------------------------- */

    private function fairRegistrations(array $sessions, ?User $staff): void
    {
        $fair = [
            ['Hemin Karim Salih', '7704112288', 'Sulaimani', [1, 2, 3], 'student', 'ku', 'confirmed', [1, 2]],
            ['Lana Aziz Hama', '7508820091', 'Ranya', [2], 'student', 'ku', 'confirmed', [2]],
            ['Shad Kamal Tahir', '7701189032', 'Chamchamal', [2, 3], 'student', 'ku', 'confirmed', []],
            ['Nadia Sabir Omer', '7519923310', 'Halabja', [3], 'student', 'ar', 'confirmed', []],
            ['Diyar Rebwar Ali', '7502267741', 'Kalar', [1, 2], 'student', 'ku', 'confirmed', [1]],
            ['Rêbin Hoshyar Qadir', '7503311982', 'Erbil', [1], 'student', 'ku', 'confirmed', []],
            ['Sara Muhammed Ameen', '7701442093', 'Sulaimani', [2, 3], 'student', 'ar', 'confirmed', []],
            ['Zhiyan Farhad Karim', '7514420118', 'Duhok', [2], 'student', 'ku', 'awaiting_otp', []],
            ['Aram Jalal Hussein', '7702290014', 'Koya', [1, 3], 'parent', 'ku', 'confirmed', [3]],
            ['Kazhan Nawzad Salam', '7505512240', 'Sulaimani', [2], 'parent', 'ku', 'confirmed', []],
            ['Bushra Abdulrahman Taha', '7511102873', 'Zakho', [3], 'parent', 'ar', 'confirmed', []],
        ];

        foreach ($fair as $i => [$name, $phone, $city, $days, $type, $locale, $status, $checkedDays]) {
            $registration = Registration::create([
                'track' => Registration::TRACK_FAIR,
                'type' => $type,
                'status' => $status,
                'locale' => $locale,
                'full_name' => $name,
                'phone' => $phone,
                'phone_country' => '+964',
                'city' => $city,
                /*
                 * Students sign in with an email and a password, so the demo ones
                 * get a predictable pair — otherwise the signed-in home page, the
                 * opportunities board and the scholarship application cannot be
                 * opened at all after seeding. Demo data only; this seeder has no
                 * business running on production.
                 */
                'email' => $type === 'student' ? 'student'.($i + 1).'@example.com' : null,
                'password' => $type === 'student' ? 'password' : null,
                'education_stage' => $type === 'student' ? 'grade12' : null,
                'date_of_birth' => $type === 'student' ? now()->subYears(18)->subDays($i * 37) : now()->subYears(45)->subDays($i * 51),
                'gender' => $i % 2 === 0 ? 'male' : 'female',
                'current_status' => $type === 'student' ? 'grade12' : null,
                'school_name' => $type === 'student' ? 'Sulaimani Preparatory School for Boys' : null,
                'stream' => $type === 'student' ? 'scientific' : null,
                'fields_of_study' => $type === 'student' ? ['medicine', 'it_ai'] : null,
                'relationship' => $type === 'parent' ? ($i % 2 === 0 ? 'father' : 'mother') : null,
                'children_count' => $type === 'parent' ? '1' : null,
                'child_grade' => $type === 'parent' ? '12th grade' : null,
                'topics' => $type === 'parent' ? ['scholarships', 'zankoline'] : null,
                'days' => $days,
                'reasons' => $type === 'student' ? ['zankoline', 'meet_universities'] : ['support_child', 'panels'],
                'hear_about' => ['instagram', 'school', 'friend', 'tiktok'][$i % 4],
                'consents' => [
                    'terms' => ['given' => true, 'at' => now()->subDays(20 - $i)->toIso8601String()],
                    'whatsapp' => ['given' => true, 'at' => now()->subDays(20 - $i)->toIso8601String()],
                    'photography' => ['given' => $i % 3 !== 0, 'at' => now()->subDays(20 - $i)->toIso8601String()],
                ],
                'consented_at' => now()->subDays(20 - $i),
                'verified_at' => $status === 'awaiting_otp' ? null : now()->subDays(20 - $i),
                'confirmed_at' => $status === 'awaiting_otp' ? null : now()->subDays(20 - $i),
                'badge_generated_at' => $status === 'awaiting_otp' ? null : now()->subDays(20 - $i),
                'created_at' => now()->subDays(20 - $i),
            ]);

            if ($sessions) {
                $registration->savedSessions()->syncWithoutDetaching(array_slice($sessions, $i % 3, 2));
            }

            foreach ($checkedDays as $day) {
                CheckIn::create([
                    'registration_id' => $registration->id,
                    'day' => $day,
                    'checked_in_at' => now()->subDays(2)->setTime(10 + $day, random_int(0, 59)),
                    'staff_id' => $staff?->id,
                    'gate' => 'A',
                    'method' => 'scan',
                ]);
            }

            if ($status !== 'awaiting_otp') {
                $this->seedFairWhatsAppMessage($registration, $name, $type, $locale, $phone, $i === 7);
            }
        }
    }

    /* --------------------------------------------------------------------- */
    /* Conference RSVPs                                                      */
    /* --------------------------------------------------------------------- */

    private function conferenceRsvps(): void
    {
        $conference = [
            ['Dr. Rezan Ahmed Kareem', 'r.kareem@mhe.krd', 'Director General, Scholarships', 'Ministry of Higher Education and Scientific Research', 'Erbil', 'government', 'confirmed'],
            ['Prof. Sara Hiwa Mustafa', 's.hiwa@univsul.edu.iq', 'Vice President for Academic Affairs', 'University of Sulaimani', 'Sulaimani', 'official', 'pending'],
            ['Bahar Jamal Rashid', 'b.rashid@clickiraq.org', 'Programme Director', 'Click Iraq Foundation', 'Sulaimani', 'official', 'confirmed'],
            ['Karwan Aziz Mahmood', 'k.mahmood@sulaimani.gov.krd', 'Head of Education Directorate', 'Sulaimani Directorate of Education', 'Sulaimani', 'government', 'confirmed'],
            ['Layla Hassan Abdullah', 'l.hassan@britishcouncil.org', 'Regional Education Adviser', 'British Council Iraq', 'Erbil', 'official', 'confirmed'],
        ];

        foreach ($conference as $i => [$name, $email, $position, $org, $city, $type, $status]) {
            $registration = Registration::create([
                'track' => Registration::TRACK_CONFERENCE,
                'type' => $type,
                'status' => $status,
                'locale' => $i % 2 === 0 ? 'en' : 'ku',
                'full_name' => $name,
                'email' => $email,
                'phone' => '751'.str_pad((string) (3000000 + $i * 4123), 7, '0', STR_PAD_LEFT),
                'phone_country' => '+964',
                'position' => $position,
                'organization' => $org,
                'org_type' => $type === 'official' ? 'university' : null,
                'city' => $city,
                'days' => [1],
                'delegation_size' => $i === 0 ? 'plus2' : 'self',
                'interpretation' => $i % 2 === 0 ? 'ku' : 'none',
                'invitation_letter' => $i === 0,
                'consents' => [
                    'terms' => ['given' => true, 'at' => now()->subDays(14 - $i)->toIso8601String()],
                    'delegate_list' => ['given' => true, 'at' => now()->subDays(14 - $i)->toIso8601String()],
                ],
                'consented_at' => now()->subDays(14 - $i),
                'confirmed_at' => $status === 'confirmed' ? now()->subDays(14 - $i) : null,
                'approved_at' => $status === 'confirmed' ? now()->subDays(14 - $i) : null,
                'badge_generated_at' => now()->subDays(14 - $i),
                'created_at' => now()->subDays(14 - $i),
            ]);

            $this->seedConferenceMessages($registration, $email, $status, $i);
        }
    }

    /* --------------------------------------------------------------------- */
    /* Messages                                                              */
    /* --------------------------------------------------------------------- */

    /**
     * Fair student/parent confirmations go out on WhatsApp at register time.
     */
    private function seedFairWhatsAppMessage(
        Registration $registration,
        string $name,
        string $type,
        string $locale,
        string $phone,
        bool $failed,
    ): void {
        $sentAt = $registration->confirmed_at ?? now();

        Message::create([
            'registration_id' => $registration->id,
            'channel' => 'whatsapp',
            'template_key' => $type === 'student'
                ? 'registration_confirmed_student'
                : 'registration_confirmed_parent',
            'locale' => $locale,
            'recipient' => '+964'.$phone,
            'preview' => __("notifications.whatsapp.registration_confirmed_$type", [
                'name' => $registration->firstName(),
            ], $locale),
            'status' => $failed ? Message::STATUS_FAILED : Message::STATUS_DELIVERED,
            'error' => $failed ? 'Recipient not on WhatsApp (131026)' : null,
            'queued_at' => $sentAt,
            'sent_at' => $sentAt,
            'delivered_at' => $failed ? null : $sentAt->copy()->addSeconds(4),
            'failed_at' => $failed ? $sentAt->copy()->addSeconds(9) : null,
        ]);
    }

    /**
     * Conference RSVP: email on submit; WhatsApp badge only after backend approval.
     * Pending delegates have no rsvp_confirmed WhatsApp row yet.
     */
    private function seedConferenceMessages(Registration $registration, string $email, string $status, int $i): void
    {
        $sentAt = $registration->created_at ?? now();

        Message::create([
            'registration_id' => $registration->id,
            'channel' => 'email',
            'template_key' => $status === 'confirmed' ? 'rsvp_confirmed' : 'rsvp_pending',
            'locale' => $registration->locale,
            'recipient' => $email,
            'subject' => __('notifications.email.rsvp_subject', [], $registration->locale),
            'preview' => __('notifications.email.rsvp_confirmed', [], $registration->locale),
            'status' => Message::STATUS_DELIVERED,
            'queued_at' => $sentAt,
            'sent_at' => $sentAt,
            'delivered_at' => $sentAt->copy()->addSeconds(6),
        ]);

        if ($status !== 'confirmed') {
            return;
        }

        Message::create([
            'registration_id' => $registration->id,
            'channel' => 'whatsapp',
            'template_key' => 'rsvp_confirmed',
            'locale' => $registration->locale,
            'recipient' => $registration->msisdn(),
            'preview' => __('notifications.whatsapp.rsvp_confirmed', [
                'name' => $registration->firstName(),
                'ticket' => $registration->ticket_ref,
            ], $registration->locale),
            'status' => Message::STATUS_DELIVERED,
            'queued_at' => $registration->approved_at ?? $sentAt,
            'sent_at' => $registration->approved_at ?? $sentAt,
            'delivered_at' => ($registration->approved_at ?? $sentAt)->copy()->addSeconds(8),
        ]);
    }

    /**
     * A fortnight of fair registration history so the dashboard chart has a curve.
     * No message rows — these are volume-only background data.
     */
    private function registrationHistory(): void
    {
        $names = ['Ahmed', 'Rojin', 'Kamaran', 'Shilan', 'Yad', 'Peshraw', 'Awaz', 'Hersh', 'Chnar', 'Dashne'];
        $families = ['Salih', 'Karim', 'Hama', 'Mustafa', 'Abdullah', 'Rashid', 'Faraj', 'Tahir'];
        $cities = config('nextstep.cities');

        for ($day = 27; $day >= 0; $day--) {
            $count = (int) max(1, round(6 + (27 - $day) * 1.4));
            for ($n = 0; $n < $count; $n++) {
                $created = Carbon::now()->subDays($day)->setTime(random_int(8, 22), random_int(0, 59));
                Registration::create([
                    'track' => Registration::TRACK_FAIR,
                    'type' => $n % 7 === 0 ? 'parent' : 'student',
                    'status' => Registration::STATUS_CONFIRMED,
                    'locale' => ['ku', 'ku', 'ar', 'en'][$n % 4],
                    'full_name' => $names[$n % count($names)].' '.$families[($n + $day) % count($families)],
                    'phone' => '7'.random_int(50, 59).random_int(1000000, 9999999),
                    'phone_country' => '+964',
                    'city' => $cities[($n + $day) % 12],
                    'days' => [[1, 2], [2, 3], [1, 2, 3], [2]][$n % 4],
                    'reasons' => ['zankoline', 'meet_universities'],
                    'hear_about' => ['instagram', 'school', 'tiktok', 'friend'][$n % 4],
                    'consents' => [
                        'terms' => ['given' => true, 'at' => $created->toIso8601String()],
                        'whatsapp' => ['given' => true, 'at' => $created->toIso8601String()],
                    ],
                    'consented_at' => $created,
                    'verified_at' => $created,
                    'confirmed_at' => $created,
                    'badge_generated_at' => $created,
                    'created_at' => $created,
                    'updated_at' => $created,
                ]);
            }
        }
    }
}
