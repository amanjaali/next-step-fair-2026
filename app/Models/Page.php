<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableContent;
use Illuminate\Database\Eloquent\Model;

/** Long-form editable pages: privacy, terms, press kit, about and the two tracks. */
class Page extends Model
{
    use HasTranslatableContent;

    public array $translatable = ['kicker', 'title', 'standfirst', 'body', 'foot_note'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'sections' => 'array',
            'aside' => 'array',
            'updated_on' => 'date',
            'published' => 'boolean',
        ];
    }

    /**
     * Sections are stored as [{n, h: {en,ku,ar}, p: [{en,ku,ar}], list: [...]}]
     * so a policy reads in the visitor's language with an English fallback.
     */
    public function localisedSections(): array
    {
        $locale = app()->getLocale();
        $fallback = config('app.fallback_locale');

        $pick = function ($value) use ($locale, $fallback) {
            if (! is_array($value)) {
                return (string) $value;
            }

            return (string) ($value[$locale] ?? $value[$fallback] ?? reset($value) ?: '');
        };

        return collect($this->sections ?? [])->map(function ($section) use ($pick) {
            return [
                'n' => $section['n'] ?? '',
                'h' => $pick($section['h'] ?? ''),
                'p' => collect($section['p'] ?? [])->map($pick)->filter()->values()->all(),
                'list' => collect($section['list'] ?? [])->map($pick)->filter()->values()->all(),
            ];
        })->all();
    }

    public function localisedAside(): array
    {
        $locale = app()->getLocale();
        $fallback = config('app.fallback_locale');
        $aside = $this->aside ?? [];

        $pick = fn ($v) => is_array($v) ? ($v[$locale] ?? $v[$fallback] ?? reset($v) ?: '') : (string) $v;

        return [
            'title' => $pick($aside['title'] ?? ''),
            'body' => $pick($aside['body'] ?? ''),
            'contact' => $aside['contact'] ?? '',
        ];
    }
}
