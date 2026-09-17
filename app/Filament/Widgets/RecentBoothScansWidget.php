<?php

namespace App\Filament\Widgets;

use App\Services\BoothScanInsightService;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;

/** The desk-QR scans that weren't anonymous — who, at which exhibitor, when. */
class RecentBoothScansWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 2;

    public function getHeading(): string
    {
        return __('admin.insights.booth_recent');
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->records(fn () => app(BoothScanInsightService::class)->recentIdentifiedScans())
            ->columns([
                Tables\Columns\TextColumn::make('visitor')->label(__('admin.insights.visitor'))->wrap(),
                Tables\Columns\TextColumn::make('organization')->label(__('admin.insights.institution'))->wrap(),
                Tables\Columns\TextColumn::make('day')->label(__('admin.fields.days'))->placeholder('—'),
                Tables\Columns\TextColumn::make('scanned_at')->label(__('admin.insights.scanned_at'))->dateTime(),
            ])
            ->paginated([10, 25, 50]);
    }
}
