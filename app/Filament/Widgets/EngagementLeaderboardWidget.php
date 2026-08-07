<?php

namespace App\Filament\Widgets;

use App\Services\Matching\InsightService;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;

/**
 * Which universities generated the most engagement, and how qualified it was.
 *
 * "Qualified" counts only students whose declared field the institution actually
 * teaches. An exhibitor who scanned four hundred badges at random has a big number
 * and a bad stand; this separates the two.
 */
class EngagementLeaderboardWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 2;

    public function getHeading(): string
    {
        return __('admin.insights.engagement');
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->records(fn () => app(InsightService::class)->engagementLeaderboard(15))
            ->columns([
                Tables\Columns\TextColumn::make('organization')->label(__('admin.insights.institution'))->wrap(),
                Tables\Columns\TextColumn::make('scans')->label(__('admin.insights.scans'))->numeric(),
                Tables\Columns\TextColumn::make('qualified')->label(__('admin.insights.qualified'))->numeric()
                    ->badge()->color('success'),
                Tables\Columns\TextColumn::make('score')->label(__('admin.insights.weighted'))->numeric(),
            ])
            ->paginated(false);
    }
}
