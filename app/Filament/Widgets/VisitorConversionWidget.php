<?php

namespace App\Filament\Widgets;

use App\Services\Matching\InsightService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** How many desk visitor passes turned into a full student registration. */
class VisitorConversionWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $count = app(InsightService::class)->visitorToStudentConversions();

        return [
            Stat::make(__('admin.insights.visitor_conversions'), number_format($count))
                ->description(__('admin.insights.visitor_conversions_note'))
                ->color('primary'),
        ];
    }
}
