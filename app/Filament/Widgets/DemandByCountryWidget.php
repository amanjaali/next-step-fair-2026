<?php

namespace App\Filament\Widgets;

use App\Services\Matching\InsightService;
use Filament\Widgets\ChartWidget;

/** Where students want to study — the destination story. */
class DemandByCountryWidget extends ChartWidget
{
    protected ?string $maxHeight = '300px';

    public function getHeading(): ?string
    {
        return __('admin.insights.demand_by_country');
    }

    protected function getData(): array
    {
        $rows = app(InsightService::class)->demandByCountry()->take(10);

        return [
            'datasets' => [[
                'label' => __('admin.insights.students'),
                'data' => $rows->pluck('students')->all(),
                'backgroundColor' => '#2C4BE0',
            ]],
            'labels' => $rows->pluck('country')->map(fn ($c) => __("taxonomy.countries.$c"))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
