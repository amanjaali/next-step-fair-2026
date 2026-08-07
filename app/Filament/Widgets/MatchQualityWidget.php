<?php

namespace App\Filament\Widgets;

use App\Services\Matching\InsightService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** How well the two sides of the fair actually fit each other. */
class MatchQualityWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $insights = app(InsightService::class);
        $quality = $insights->matchQuality();

        return [
            Stat::make(__('admin.insights.matchable'), number_format($quality['matchable_students']))
                ->description(__('admin.insights.matchable_note'))
                ->color('primary'),

            Stat::make(__('admin.insights.strong_matches'), number_format($quality['students_with_strong_match']))
                ->description(__('admin.insights.average_score', ['score' => $quality['average_score']]))
                ->color('success'),

            Stat::make(__('admin.insights.institutions_matching'), number_format($quality['institutions_matching']))
                ->description(__('admin.insights.institutions_note'))
                ->color('info'),

            Stat::make(__('admin.insights.conversion'), $insights->matchToVisitConversion().'%')
                ->description(__('admin.insights.conversion_note'))
                ->color('warning'),
        ];
    }
}
