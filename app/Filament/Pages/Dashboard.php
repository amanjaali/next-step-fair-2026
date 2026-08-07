<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CheckinRateWidget;
use App\Filament\Widgets\ConversionFunnelWidget;
use App\Filament\Widgets\RegistrationsByCityWidget;
use App\Filament\Widgets\RegistrationsChartWidget;
use App\Filament\Widgets\RegistrationStatsWidget;
use App\Filament\Widgets\SessionPopularityWidget;
use App\Filament\Widgets\TrafficSourceWidget;
use Filament\Pages\Dashboard as BaseDashboard;

/**
 * The dashboard reads top to bottom the way the team works: the headline
 * numbers, then the curve, then where people come from, then the day itself.
 */
class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            RegistrationStatsWidget::class,
            RegistrationsChartWidget::class,
            ConversionFunnelWidget::class,
            RegistrationsByCityWidget::class,
            TrafficSourceWidget::class,
            CheckinRateWidget::class,
            SessionPopularityWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return ['sm' => 1, 'lg' => 3];
    }
}
