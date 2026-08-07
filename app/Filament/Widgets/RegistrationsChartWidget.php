<?php

namespace App\Filament\Widgets;

use App\Models\Registration;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

/** Registrations per day, split by track, for the last two weeks. */
class RegistrationsChartWidget extends ChartWidget
{
    protected ?string $heading = null;

    protected int|string|array $columnSpan = 2;

    protected ?string $maxHeight = '260px';

    public function getHeading(): ?string
    {
        return __('admin.widgets.registrations_over_time');
    }

    public function getDescription(): ?string
    {
        return __('admin.widgets.last_14_days');
    }

    protected function getData(): array
    {
        $days = collect(range(13, 0))->map(fn (int $back) => Carbon::today()->subDays($back));

        $count = fn (string $track, Carbon $day) => Registration::where('track', $track)
            ->whereDate('created_at', $day)
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Fair',
                    'data' => $days->map(fn ($day) => $count(Registration::TRACK_FAIR, $day))->all(),
                    'backgroundColor' => '#B64698',
                    'borderColor' => '#B64698',
                ],
                [
                    'label' => 'Conference',
                    'data' => $days->map(fn ($day) => $count(Registration::TRACK_CONFERENCE, $day))->all(),
                    'backgroundColor' => '#2C4BE0',
                    'borderColor' => '#2C4BE0',
                ],
            ],
            'labels' => $days->map(fn ($day) => $day->format('j M'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => ['x' => ['stacked' => true], 'y' => ['stacked' => true, 'beginAtZero' => true]],
            'plugins' => ['legend' => ['position' => 'bottom']],
        ];
    }
}
