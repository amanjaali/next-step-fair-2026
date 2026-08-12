<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * A slot in the three-day programme. Conference sessions are Day 1 only; the
 * fair programme runs across all three.
 */
class EventSession extends Model
{
    use HasTranslatableContent;

    protected $table = 'event_sessions';

    public array $translatable = ['title', 'subtitle', 'description', 'who'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'day' => 'integer',
            'bookable' => 'boolean',
            'published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $session) {
            $session->slug = $session->slug ?: Str::slug(
                ($session->getTranslation('title', 'en', false) ?: Str::random(8)).'-day-'.$session->day
            );
        });
    }

    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }

    public function speakers(): BelongsToMany
    {
        return $this->belongsToMany(Speaker::class, 'event_session_speaker', 'session_id', 'speaker_id')
            ->withPivot('role', 'sort')
            // Qualified: both `speakers` and the pivot carry a `sort` column.
            ->orderBy('event_session_speaker.sort');
    }

    public function registrations(): BelongsToMany
    {
        return $this->belongsToMany(Registration::class, 'registration_session', 'session_id', 'registration_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }

    public function scopeForDay(Builder $query, int $day): Builder
    {
        return $query->where('day', $day);
    }

    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    public function scopeConference(Builder $query): Builder
    {
        return $query->where('track', 'conference');
    }

    /** Chip colours: cobalt for the conference track, magenta for the fair. */
    public function chipClass(): string
    {
        return $this->track === 'conference' ? 'ns-typechip-conf' : 'ns-typechip-fair';
    }

    /**
     * The kind of session, in the reader's language.
     *
     * `type` is free text the team can add to from the dashboard, so there is no
     * fixed list to translate. The known ones are looked up and anything new
     * falls through to whatever was typed — a Kurdish page showing one English
     * word is better than an empty chip.
     */
    public function typeLabel(): string
    {
        return static::labelForType((string) $this->type);
    }

    public static function labelForType(string $type): string
    {
        $key = 'site.pages.agenda.types.'.Str::slug($type);

        return __($key) === $key ? $type : __($key);
    }

    public function timeLabel(): string
    {
        return Carbon::parse($this->starts_at)->format('H:i');
    }

    public function hallLabel(): string
    {
        return $this->hall_label ?: ($this->hall?->t('name') ?? '');
    }

    /** Absolute start of this session, used for .ics export and reminders. */
    public function startsAtDateTime(): Carbon
    {
        $date = config("nextstep.event.days.{$this->day}.date");

        return Carbon::parse($date.' '.$this->starts_at, config('nextstep.event.timezone'));
    }

    public function endsAtDateTime(): Carbon
    {
        $date = config("nextstep.event.days.{$this->day}.date");

        return $this->ends_at
            ? Carbon::parse($date.' '.$this->ends_at, config('nextstep.event.timezone'))
            : $this->startsAtDateTime()->addHour();
    }
}
