<?php

namespace App\Filament\Widgets;

use App\Models\CheckIn;
use App\Models\Registration;
use Filament\Widgets\ChartWidget;

/** Registered versus actually attended, per day — the number partners ask for. */
class CheckinRateWidget extends ChartWidget
{
    protected ?string $maxHeight = '260px';

    public function getHeading(): ?string
    {
        return __('admin.widgets.checkin_rate');
    }

    protected function getData(): array
    {
        $days = array_keys(config('nextstep.event.days'));

        $registered = [];
        $attended = [];

        foreach ($days as $day) {
            $registered[] = Registration::active()->whereJsonContains('days', (int) $day)->count();
            $attended[] = CheckIn::where('day', $day)->count();
        }

        return [
            'datasets' => [
                ['label' => 'Registered', 'data' => $registered, 'backgroundColor' => '#E3DFDB'],
                ['label' => 'Checked in', 'data' => $attended, 'backgroundColor' => '#B64698'],
            ],
            'labels' => array_map(fn ($day) => __('site.common.day', ['n' => $day]), $days),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return ['plugins' => ['legend' => ['position' => 'bottom']], 'scales' => ['y' => ['beginAtZero' => true]]];
    }
}
