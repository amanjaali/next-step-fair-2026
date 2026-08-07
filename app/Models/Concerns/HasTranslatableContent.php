<?php

namespace App\Models\Concerns;

use Spatie\Translatable\HasTranslations;

/**
 * Translatable JSON fields plus the completeness figure the admin shows per row.
 *
 * A record is "complete" when every translatable field has a non-empty value in
 * every configured language, so an editor can see at a glance which of the three
 * languages a story is still missing.
 */
trait HasTranslatableContent
{
    use HasTranslations;

    /** Percentage of translatable fields filled in for a given language. */
    public function translationCompleteness(string $locale): int
    {
        $fields = $this->getTranslatableAttributes();

        if (empty($fields)) {
            return 100;
        }

        $filled = 0;
        foreach ($fields as $field) {
            $value = $this->getTranslation($field, $locale, false);
            if (is_array($value) ? ! empty($value) : filled(strip_tags((string) $value))) {
                $filled++;
            }
        }

        return (int) round($filled / count($fields) * 100);
    }

    /** ['en' => 100, 'ku' => 60, 'ar' => 0] — used by the admin indicator. */
    public function translationMatrix(): array
    {
        $out = [];
        foreach (array_keys(config('nextstep.locales')) as $locale) {
            $out[$locale] = $this->translationCompleteness($locale);
        }

        return $out;
    }

    /**
     * Value in the current language, falling back to English so a half-translated
     * record still renders rather than showing an empty page.
     */
    public function t(string $field): string
    {
        $value = $this->getTranslation($field, app()->getLocale(), false);

        if (blank($value)) {
            $value = $this->getTranslation($field, config('app.fallback_locale'), false);
        }

        return (string) $value;
    }
}
