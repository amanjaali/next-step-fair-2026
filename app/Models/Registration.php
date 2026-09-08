<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * A registration on either track — and the attendee's account.
 *
 * Phone and e-mail are encrypted at rest. Every write also stores a keyed hash of
 * the same value so duplicate checks, admin search and the gate fallback can use
 * an index without the plaintext ever sitting in a queryable column.
 *
 * It is Authenticatable so the `attendee` guard can hold a session for it.
 *
 * Students set a password and sign in with their email: a scholarship application
 * runs for months and has to be reachable from any computer, not only from the
 * phone that received a code. Everyone else has no password at all — a parent or a
 * delegate proves the number once, gets a badge, and never signs in again.
 */
class Registration extends Model implements AuthenticatableContract
{
    use Authenticatable, HasFactory, SoftDeletes;

    public const TRACK_FAIR = 'fair';

    public const TRACK_CONFERENCE = 'conference';

    public const TYPE_STUDENT = 'student';

    public const TYPE_PARENT = 'parent';

    /** Quick pass: attending, counted, but not carrying an agenda or a profile. */
    public const TYPE_VISITOR = 'visitor';

    /* Conference audiences. A ministry, a public body, a company, or a person
       coming on their own account — the last two are new, and the reason the
       form no longer assumes everybody arrives representing an institution. */
    public const TYPE_GOVERNMENT = 'government';

    public const TYPE_OFFICIAL = 'official';

    public const TYPE_PRIVATE = 'private';

    public const TYPE_INDIVIDUAL = 'individual';

    /** @return list<string> */
    public static function conferenceTypes(): array
    {
        return [self::TYPE_GOVERNMENT, self::TYPE_OFFICIAL, self::TYPE_PRIVATE, self::TYPE_INDIVIDUAL];
    }

    public const STATUS_DRAFT = 'draft';

    public const STATUS_AWAITING_OTP = 'awaiting_otp';

    public const STATUS_PENDING = 'pending';        // conference: with the protocol team

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_CHECKED_IN = 'checked_in';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_REJECTED = 'rejected';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'phone' => 'encrypted',
            'email' => 'encrypted',
            'password' => 'hashed',
            'fields_of_study' => 'array',
            'topics' => 'array',
            'days' => 'array',
            'reasons' => 'array',
            'consents' => 'array',
            'date_of_birth' => 'date',
            'invitation_letter' => 'boolean',
            'is_speaking' => 'boolean',
            'media_accreditation' => 'boolean',
            'is_walk_in' => 'boolean',
            'consented_at' => 'datetime',
            'verified_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'approved_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'badge_generated_at' => 'datetime',
            'last_signed_in_at' => 'datetime',
            'preferred_countries' => 'array',
            'share_with_institutions' => 'boolean',
            'profile_completed_at' => 'datetime',
        ];
    }

    /**
     * A visitor pass: name and phone only, no agenda and no profile.
     *
     * It still verifies the number and still issues a QR, so the gate works and the
     * headcount is real; it just asks for nothing else. Upgrading to a full
     * registration later reuses the same record.
     */
    public function isQuickPass(): bool
    {
        return $this->type === self::TYPE_VISITOR;
    }

    /** True once the record holds enough for the personal agenda and profile. */
    public function hasFullProfile(): bool
    {
        return ! $this->isQuickPass();
    }

    /**
     * A Next Step ID: one student account for everything Next Step runs.
     *
     * The expo, the panels, the seminars, the workshops, Zankoline and the
     * scholarship all read this one record. A student registers once, proves the
     * number once, and never fills the same form again.
     */
    public function isStudentAccount(): bool
    {
        return $this->type === self::TYPE_STUDENT && filled($this->password);
    }

    /**
     * Confirmed: the registration is complete and a badge has been issued.
     *
     * This is what unlocks the services. It used to be `verified_at`, back when
     * every registration answered a code — but a phone code is a way of
     * confirming a registration, not the meaning of one, and reading it as the
     * meaning locked students out of the scholarship the day the code was
     * dropped.
     */
    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * This number answered a code.
     *
     * Only ever true where phone verification was switched on — see
     * `nextstep.registration.verify_phone` — and for the visitor passes issued
     * at the gate, which are phone-only and still ask.
     */
    public function phoneVerified(): bool
    {
        return $this->verified_at !== null;
    }

    /**
     * Eligible to apply for the scholarship: a verified student finishing school.
     *
     * Everything else about the application — the region, the average, the
     * documents — is checked inside the scholarship itself.
     */
    public function canApplyForScholarship(): bool
    {
        return $this->isStudentAccount()
            && $this->isConfirmed()
            && in_array($this->education_stage, ['grade12', 'graduate'], true);
    }

    public function getAuthPassword(): string
    {
        return (string) $this->password;
    }

    /**
     * True once we know enough to match them to an institution.
     *
     * A field and a degree level are the minimum: without both, every university
     * looks equally suitable, which is the same as no recommendation at all.
     */
    public function canBeMatched(): bool
    {
        return $this->degree_level !== null && $this->fields()->exists();
    }

    /** How much of the intent profile is filled in, for the nudge on /me. */
    public function intentCompleteness(): int
    {
        $answered = collect([
            $this->fields()->exists(),
            filled($this->degree_level),
            filled($this->preferred_countries),
            filled($this->language_preference),
            filled($this->budget_band),
            filled($this->grade_band),
            filled($this->start_year),
            filled($this->career_goal),
        ])->filter()->count();

        return (int) round($answered / 8 * 100);
    }

    protected static function booted(): void
    {
        static::creating(function (self $registration) {
            $registration->ticket_id ??= (string) Str::uuid();
            $registration->ticket_ref ??= self::makeTicketRef($registration->ticket_id);
        });

        // Keep the lookup hashes in step with the encrypted values on every write.
        static::saving(function (self $registration) {
            if ($registration->isDirty('phone')) {
                $registration->phone_hash = $registration->phone ? self::hashValue($registration->phone) : null;
            }
            if ($registration->isDirty('email')) {
                $registration->email_hash = $registration->email ? self::hashValue(strtolower($registration->email)) : null;
            }
        });
    }

    /* ---------------------------------------------------------------- lookup */

    /** Keyed hash used for duplicate detection and search. */
    public static function hashValue(string $value): string
    {
        return hash_hmac('sha256', trim($value), (string) config('app.key'));
    }

    /**
     * Digits only, no leading zero — the same shape the public forms store, so a
     * number typed at the desk as "0770 123 4567" hashes to the stored value.
     */
    public static function normalisePhone(string $phone): string
    {
        return ltrim(preg_replace('/\D/', '', $phone), '0');
    }

    public static function phoneHash(string $phone): string
    {
        return self::hashValue(self::normalisePhone($phone));
    }

    public function scopeWherePhone(Builder $query, string $phone): Builder
    {
        return $query->where('phone_hash', self::phoneHash($phone));
    }

    public function scopeWhereEmail(Builder $query, string $email): Builder
    {
        return $query->where('email_hash', self::hashValue(strtolower($email)));
    }

    public function scopeFair(Builder $query): Builder
    {
        return $query->where('track', self::TRACK_FAIR);
    }

    public function scopeConference(Builder $query): Builder
    {
        return $query->where('track', self::TRACK_CONFERENCE);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', [self::STATUS_CANCELLED, self::STATUS_REJECTED, self::STATUS_DRAFT]);
    }

    /* ------------------------------------------------------------- relations */

    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckIn::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function otpVerifications(): HasMany
    {
        return $this->hasMany(OtpVerification::class);
    }

    public function savedSessions(): BelongsToMany
    {
        return $this->belongsToMany(EventSession::class, 'registration_session', 'registration_id', 'session_id')
            ->withPivot('reminder_sent_at')
            ->withTimestamps();
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function qrCampaign(): BelongsTo
    {
        return $this->belongsTo(QrCampaign::class);
    }

    /** Fields of study they named, first choice first. */
    public function fields(): BelongsToMany
    {
        return $this->belongsToMany(Field::class, 'field_registration')
            ->withPivot('rank')
            ->withTimestamps()
            ->orderBy('field_registration.rank');
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(Interaction::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(MatchScore::class)->orderByDesc('score');
    }

    public function scholarshipApplications(): HasMany
    {
        return $this->hasMany(ScholarshipApplication::class);
    }

    /** This cycle's application, started or not. */
    public function scholarshipApplication(): ?ScholarshipApplication
    {
        return $this->scholarshipApplications()->forCycle()->first();
    }

    /**
     * The scholarship they were awarded, if they were awarded one.
     *
     * Any cycle, not only the current one: someone awarded in 2026 is a
     * scholarship holder in 2027 too, and the badge on their account should not
     * quietly disappear the day the next cycle opens.
     */
    public function scholarshipAward(): ?ScholarshipApplication
    {
        return $this->scholarshipApplications()
            ->where('status', ScholarshipApplication::STATUS_DECIDED)
            ->where('decision', ScholarshipApplication::DECISION_AWARDED)
            ->latest('decided_at')
            ->first();
    }

    public function isScholar(): bool
    {
        return $this->scholarshipAward() !== null;
    }

    /* -------------------------------------------------------- their updates -- */

    public function notifications(): HasMany
    {
        return $this->hasMany(AttendeeNotification::class)->latest('id');
    }

    /** Counted once per request: the header asks on every page, then the profile. */
    private ?int $unreadCount = null;

    public function unreadUpdates(): int
    {
        return $this->unreadCount ??= $this->notifications()->unread()->count();
    }

    /* --------------------------------------------------------------- helpers */

    /** "8F2C-41A9-D77E" — short enough to read out at the registration desk. */
    public static function makeTicketRef(string $uuid): string
    {
        $clean = strtoupper(substr(str_replace('-', '', $uuid), 0, 12));

        return implode('-', str_split($clean, 4));
    }

    public function isFair(): bool
    {
        return $this->track === self::TRACK_FAIR;
    }

    public function isConference(): bool
    {
        return $this->track === self::TRACK_CONFERENCE;
    }

    public function accent(): string
    {
        return ns_track_accent($this->track);
    }

    public function firstName(): string
    {
        return Str::of($this->full_name)->trim()->explode(' ')->first() ?: $this->full_name;
    }

    /** Their own picture, if they added one. */
    public function photoUrl(): ?string
    {
        return ns_uploaded($this->photo_path);
    }

    /**
     * Initials for the placeholder that stands in for a photo.
     *
     * First and last word, so "Hemin Karim Salih" reads HS rather than HK — a
     * middle name is not what anybody is known by.
     */
    public function initials(): string
    {
        $parts = Str::of($this->full_name)->trim()->explode(' ')->filter()->values();

        if ($parts->isEmpty()) {
            return '?';
        }

        $first = Str::upper(Str::substr($parts->first(), 0, 1));

        return $parts->count() > 1
            ? $first.Str::upper(Str::substr($parts->last(), 0, 1))
            : $first;
    }

    /** Full number in E.164-ish form for the WhatsApp API. */
    public function msisdn(): ?string
    {
        if (! $this->phone) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $this->phone_country.$this->phone);

        return $digits ? '+'.ltrim($digits, '+') : null;
    }

    public function maskedPhone(): string
    {
        return ns_mask_phone($this->phone);
    }

    public function dayList(): array
    {
        return collect($this->days ?? [])->map(fn ($d) => (int) $d)->sort()->values()->all();
    }

    public function daysLabel(): string
    {
        return collect($this->dayList())
            ->map(fn ($d) => __('site.common.day', ['n' => $d]))
            ->implode(', ');
    }

    public function isCheckedInOn(int $day): bool
    {
        return $this->checkIns->contains(fn (CheckIn $c) => $c->day === $day);
    }

    public function typeChip(): string
    {
        return strtoupper($this->type);
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED || $this->cancelled_at !== null;
    }

    /** A badge is only valid once the registration is confirmed and not cancelled. */
    public function badgeIssued(): bool
    {
        return in_array($this->status, [self::STATUS_CONFIRMED, self::STATUS_CHECKED_IN], true);
    }

    public function consentGiven(string $key): bool
    {
        return (bool) data_get($this->consents, $key.'.given', false);
    }
}
