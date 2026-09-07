<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One update, addressed to one student, waiting in their account.
 *
 * Written by the site rather than by a person: a committee records a decision on
 * the dashboard and this is what carries it to the student. It holds the facts
 * and not the sentence, so the same row reads in whichever language the student
 * opens it in.
 */
class AttendeeNotification extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    public function markRead(): void
    {
        if ($this->isUnread()) {
            $this->forceFill(['read_at' => now()])->save();
        }
    }

    /* ----------------------------------------------------------- the words -- */

    /**
     * The headline, in the reader's language.
     *
     * A decision reads as the decision — "You have been awarded a scholarship",
     * not "your application was updated". Being told there is news and having to
     * click to find out whether you got it is the one thing this must not do.
     */
    public function title(): string
    {
        return __('updates.types.'.$this->wordingKey().'.title', $this->words());
    }

    public function body(): string
    {
        return __('updates.types.'.$this->wordingKey().'.body', $this->words());
    }

    /** Where the notification leads, in the language being read. */
    public function link(): string
    {
        return route($this->route ?: 'me');
    }

    /**
     * The accent the update is drawn in.
     *
     * An award and a refusal are both news, and colouring them the same makes
     * the list unreadable at a glance; colouring a refusal in alarm red makes it
     * crueller than it needs to be. Awarded is teal, everything else is the
     * programme's own magenta.
     */
    public function accent(): string
    {
        return ($this->data['decision'] ?? null) === ScholarshipApplication::DECISION_AWARDED
            ? 'teal'
            : 'magenta';
    }

    /**
     * A decision has wording of its own per outcome; everything else falls back
     * to the wording for its type.
     */
    private function wordingKey(): string
    {
        $decision = $this->data['decision'] ?? null;

        return $this->type === 'scholarship.decision' && $decision
            ? 'scholarship.decision.'.$decision
            : $this->type;
    }

    /** @return array<string, string> */
    private function words(): array
    {
        $data = $this->data ?? [];

        return [
            'cycle' => (string) ($data['cycle'] ?? config('scholarship.cycle')),
            'stage' => isset($data['stage'])
                ? __('scholarship.status.stages.'.$data['stage'].'.title')
                : '',
        ];
    }
}
