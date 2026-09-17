<?php

namespace App\Filament\Widgets;

use App\Services\BoothScanInsightService;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;

/** Exhibitors ranked by desk-QR traffic. */
class BoothScanLeaderboardWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 2;

    public function getHeading(): string
    {
        return __('admin.insights.booth_leaderboard');
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->records(fn () => app(BoothScanInsightService::class)->leaderboard())
            ->columns([
                Tables\Columns\TextColumn::make('organization')->label(__('admin.insights.institution'))->wrap(),
                Tables\Columns\TextColumn::make('booth')->label(__('admin.insights.booth')),
                Tables\Columns\TextColumn::make('scans')->label(__('admin.insights.booth_total'))->numeric(),
                Tables\Columns\TextColumn::make('identified')->label(__('admin.insights.booth_identified'))
                    ->numeric()->badge()->color('success'),
            ])
            ->paginated([10, 25, 50]);
    }
}
