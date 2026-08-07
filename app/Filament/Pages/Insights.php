<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DemandByCountryWidget;
use App\Filament\Widgets\DemandByFieldWidget;
use App\Filament\Widgets\EngagementLeaderboardWidget;
use App\Filament\Widgets\MatchQualityWidget;
use App\Filament\Widgets\SupplyGapWidget;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * The post-event report, available during the event.
 *
 * Kept as its own page rather than folded into the dashboard: the dashboard
 * answers "how is registration going", this answers "what did we learn". They are
 * read by different people at different times.
 */
class Insights extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.insights';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.platform');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.insights.title');
    }

    public function getTitle(): string
    {
        return __('admin.insights.title');
    }

    public function getSubheading(): ?string
    {
        return __('admin.insights.subtitle');
    }

    public function getWidgets(): array
    {
        return [
            MatchQualityWidget::class,
            DemandByFieldWidget::class,
            SupplyGapWidget::class,
            DemandByCountryWidget::class,
            EngagementLeaderboardWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return ['sm' => 1, 'lg' => 2];
    }
}
