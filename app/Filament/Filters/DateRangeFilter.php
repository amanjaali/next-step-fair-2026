<?php

namespace App\Filament\Filters;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * "Today / Yesterday / Last 7 days / Last 30 days / Custom range" on one date
 * column. Exports follow the table's active filters, so this also decides which
 * rows an export contains.
 */
class DateRangeFilter
{
    public static function make(string $name, string $column, string $label): Filter
    {
        return Filter::make($name)
            ->label($label)
            ->schema([
                Select::make('period')
                    ->label($label)
                    ->placeholder('Any time')
                    ->options([
                        'today' => 'Today',
                        'yesterday' => 'Yesterday',
                        'last_7' => 'Last 7 days',
                        'last_30' => 'Last 30 days',
                        'custom' => 'Custom range',
                    ])
                    ->live(),
                DatePicker::make('from')
                    ->label('From')
                    ->visible(fn (Get $get) => $get('period') === 'custom'),
                DatePicker::make('until')
                    ->label('Until')
                    ->visible(fn (Get $get) => $get('period') === 'custom'),
            ])
            ->query(function (Builder $query, array $data) use ($column): Builder {
                [$from, $until] = self::range($data);

                return $query
                    ->when($from, fn (Builder $q) => $q->where($column, '>=', $from))
                    ->when($until, fn (Builder $q) => $q->where($column, '<=', $until));
            })
            ->indicateUsing(function (array $data) use ($label): ?string {
                [$from, $until] = self::range($data);

                return match (true) {
                    $from && $until => "{$label}: {$from->format('j M Y')} – {$until->format('j M Y')}",
                    (bool) $from => "{$label}: from {$from->format('j M Y')}",
                    (bool) $until => "{$label}: until {$until->format('j M Y')}",
                    default => null,
                };
            });
    }

    /**
     * The chosen period as [from, until].
     *
     * @param  array<string, mixed>  $data
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    public static function range(array $data): array
    {
        $today = now()->startOfDay();

        return match ($data['period'] ?? null) {
            'today' => [$today, null],
            'yesterday' => [$today->copy()->subDay(), $today->copy()->subDay()->endOfDay()],
            'last_7' => [$today->copy()->subDays(6), null],
            'last_30' => [$today->copy()->subDays(29), null],
            'custom' => [
                filled($data['from'] ?? null) ? Carbon::parse($data['from'])->startOfDay() : null,
                filled($data['until'] ?? null) ? Carbon::parse($data['until'])->endOfDay() : null,
            ],
            default => [null, null],
        };
    }
}
