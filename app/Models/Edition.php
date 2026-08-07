<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableContent;
use Illuminate\Database\Eloquent\Model;

/**
 * A past edition. The year is the route parameter, so 2027 needs a row, not code.
 */
class Edition extends Model
{
    use HasTranslatableContent;

    public array $translatable = [
        'edition_label', 'dates_label', 'venue_label', 'headline', 'summary',
        'theme_title', 'speech_where', 'speech_quote', 'speech', 'organizer_role',
        'speaker_count', 'panel_note', 'sponsor_note',
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'stats' => 'array',
            'themes' => 'array',
            'speakers' => 'array',
            'panels' => 'array',
            'sponsor_tiers' => 'array',
            'press_links' => 'array',
            'published' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'year';
    }
}
