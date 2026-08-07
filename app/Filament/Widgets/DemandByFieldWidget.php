<?php

namespace App\Filament\Widgets;

use App\Services\Matching\InsightService;
use Filament\Widgets\ChartWidget;

/**
 * Which fields of study are attracting the most interest.
 *
 * Ranked by weighted demand, not raw count: a first choice counts fully, a third
 * choice counts a third. Otherwise a field everyone lists as a fallback outranks
 * one people actually came for.
 */
class DemandByFieldWidget extends ChartWidget
{
    protected ?string $maxHeight = '380px';

    protected int|string|array $columnSpan = 2;

    public function getHeading(): ?string
    {
        return __('admin.insights.demand_by_field');
    }

    public function getDescription(): ?string
    {
        return __('admin.insights.demand_by_field_note');
    }

    protected function getData(): array
    {
        $rows = app(InsightService::class)->demandByField(14);

        return [
            'datasets' => [[
                'label' => __('admin.insights.students'),
                'data' => $rows->pluck('students')->all(),
                'backgroundColor' => '#B64698',
            ]],
            'labels' => $rows->pluck('field')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return ['indexAxis' => 'y', 'plugins' => ['legend' => ['display' => false]]];
    }
}
