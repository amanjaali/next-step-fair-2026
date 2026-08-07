<?php

namespace App\Filament\Widgets;

use App\Services\Matching\InsightService;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;

/**
 * Where demand outruns supply.
 *
 * The most commercially useful number the platform produces: fields hundreds of
 * students want that almost nobody at the fair teaches. This is the exhibitor
 * pitch for next year, and the evidence for a ministry conversation about which
 * programmes the region is short of.
 */
class SupplyGapWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 2;

    public function getHeading(): string
    {
        return __('admin.insights.supply_gaps');
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->records(fn () => app(InsightService::class)->supplyGaps(12))
            ->columns([
                Tables\Columns\TextColumn::make('field')->label(__('admin.insights.field')),
                Tables\Columns\TextColumn::make('students')->label(__('admin.insights.students'))->numeric(),
                Tables\Columns\TextColumn::make('institutions')->label(__('admin.insights.institutions'))->numeric()
                    ->badge()->color(fn ($state) => $state == 0 ? 'danger' : ($state < 3 ? 'warning' : 'success')),
                Tables\Columns\TextColumn::make('ratio')->label(__('admin.insights.per_institution'))->numeric(1),
            ])
            ->paginated(false);
    }
}
