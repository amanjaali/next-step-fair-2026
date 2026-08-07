<?php

namespace App\Filament\Support;

use App\Filament\Forms\Components\TinyEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

/**
 * Per-language content fields.
 *
 * Every translatable field is edited under a tab per language, in that language's
 * own direction, so Kurdish and Arabic are composed right-to-left rather than
 * typed into an LTR box. The tab label carries a completeness dot.
 */
class Translatable
{
    /**
     * @param  array<string, string>  $fields  [attribute => label]
     * @param  array<string, string>  $kinds  [attribute => text|textarea|editor|editor-minimal]
     */
    public static function tabs(array $fields, array $kinds = [], int $columns = 1): Tabs
    {
        $locales = config('nextstep.locales');

        return Tabs::make('translations')
            ->label(__('admin.fields.translations'))
            ->columnSpanFull()
            ->tabs(collect($locales)->map(function (array $config, string $locale) use ($fields, $kinds, $columns) {
                return Tab::make($config['code'])
                    ->badge(fn ($record) => $record ? $record->translationCompleteness($locale).'%' : null)
                    ->badgeColor(fn ($record) => match (true) {
                        ! $record => 'gray',
                        $record->translationCompleteness($locale) === 100 => 'success',
                        $record->translationCompleteness($locale) > 0 => 'warning',
                        default => 'danger',
                    })
                    ->columns($columns)
                    ->schema(collect($fields)->map(function (string $label, string $attribute) use ($locale, $kinds, $config) {
                        $kind = $kinds[$attribute] ?? 'text';
                        $name = "{$attribute}.{$locale}";
                        $rtl = $config['dir'] === 'rtl';

                        return match ($kind) {
                            'editor' => TinyEditor::make($name)
                                ->label($label)
                                ->direction($config['dir'])
                                ->columnSpanFull(),
                            'editor-minimal' => TinyEditor::make($name)
                                ->label($label)
                                ->minimal()
                                ->height('260px')
                                ->direction($config['dir'])
                                ->columnSpanFull(),
                            'textarea' => Textarea::make($name)
                                ->label($label)
                                ->rows(3)
                                ->extraInputAttributes($rtl ? ['dir' => 'rtl'] : [])
                                ->columnSpanFull(),
                            default => TextInput::make($name)
                                ->label($label)
                                ->extraInputAttributes($rtl ? ['dir' => 'rtl'] : []),
                        };
                    })->values()->all());
            })->values()->all());
    }
}
