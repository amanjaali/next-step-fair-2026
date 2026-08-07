<?php

namespace App\Filament\Widgets;

use App\Models\Registration;
use Filament\Widgets\ChartWidget;

/** Self-reported source, which is the only attribution students actually give. */
class TrafficSourceWidget extends ChartWidget
{
    protected ?string $maxHeight = '300px';

    public function getHeading(): ?string
    {
        return __('admin.widgets.by_source');
    }

    protected function getData(): array
    {
        $rows = Registration::active()
            ->selectRaw('hear_about, count(*) as total')
            ->whereNotNull('hear_about')
            ->groupBy('hear_about')
            ->orderByDesc('total')
            ->pluck('total', 'hear_about');

        return [
            'datasets' => [[
                'data' => $rows->values()->all(),
                'backgroundColor' => ['#B64698', '#2C4BE0', '#0E9B94', '#F2A93B', '#8A1B3C', '#4A4B4D', '#19486A'],
            ]],
            'labels' => $rows->keys()->map(fn ($key) => __("register.options.hear.$key"))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return ['plugins' => ['legend' => ['position' => 'right']]];
    }
}
