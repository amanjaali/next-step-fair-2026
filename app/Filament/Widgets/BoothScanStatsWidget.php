<?php

namespace App\Filament\Widgets;

use App\Services\BoothScanInsightService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** How much foot traffic the exhibitors' desk QR codes actually drew. */
class BoothScanStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $stats = app(BoothScanInsightService::class)->stats();

        return [
            Stat::make(__('admin.insights.booth_total'), number_format($stats['total']))
                ->description(__('admin.insights.booth_total_note'))
                ->color('primary'),

            Stat::make(__('admin.insights.booth_identified'), number_format($stats['identified']))
                ->description(__('admin.insights.booth_identified_note', ['percent' => $stats['identified_percent']]))
                ->color('success'),

            Stat::make(__('admin.insights.booth_anonymous'), number_format($stats['anonymous']))
                ->description(__('admin.insights.booth_anonymous_note'))
                ->color('gray'),

            Stat::make(__('admin.insights.booth_no_scans'), number_format($stats['exhibitors_with_no_scans']))
                ->description(__('admin.insights.booth_no_scans_note'))
                ->color($stats['exhibitors_with_no_scans'] > 0 ? 'warning' : 'success'),
        ];
    }
}
