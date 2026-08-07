<?php

namespace App\Filament\Widgets;

use App\Models\Registration;
use Filament\Widgets\ChartWidget;

/** Where registrants are coming from — this drives the shuttle-bus plan. */
class RegistrationsByCityWidget extends ChartWidget
{
    protected ?string $maxHeight = '300px';

    public function getHeading(): ?string
    {
        return __('admin.widgets.by_city');
    }

    protected function getData(): array
    {
        $rows = Registration::active()
            ->selectRaw('city, count(*) as total')
            ->whereNotNull('city')
            ->groupBy('city')
            ->orderByDesc('total')
            ->limit(10)
            ->pluck('total', 'city');

        return [
            'datasets' => [[
                'label' => __('admin.stats.total'),
                'data' => $rows->values()->all(),
                'backgroundColor' => '#B64698',
            ]],
            'labels' => $rows->keys()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['x' => ['beginAtZero' => true]],
        ];
    }
}
