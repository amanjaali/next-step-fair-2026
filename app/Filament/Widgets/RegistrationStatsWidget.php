<?php

namespace App\Filament\Widgets;

use App\Models\CheckIn;
use App\Models\Message;
use App\Models\Registration;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** The five numbers the registration desk looks at first. */
class RegistrationStatsWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $total = Registration::active()->count();
        $students = Registration::active()->fair()->where('type', 'student')->count();
        $parents = Registration::active()->fair()->where('type', 'parent')->count();
        $conference = Registration::active()->conference()->count();
        $pending = Registration::conference()->where('status', Registration::STATUS_PENDING)->count();
        $last24h = Registration::where('created_at', '>=', now()->subDay())->count();

        $sent = Message::where('channel', 'whatsapp')->whereIn('status', [
            Message::STATUS_SENT, Message::STATUS_DELIVERED, Message::STATUS_READ, Message::STATUS_FAILED,
        ])->count();
        $failed = Message::where('channel', 'whatsapp')->where('status', Message::STATUS_FAILED)->count();
        $rate = $sent > 0 ? round((($sent - $failed) / $sent) * 100, 1) : 0.0;

        $percent = fn (int $part) => $total > 0 ? round($part / $total * 100) : 0;

        return [
            Stat::make(__('admin.stats.total'), number_format($total))
                ->description(__('admin.stats.in_24h', ['count' => number_format($last24h)]))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),

            Stat::make(__('admin.stats.students'), number_format($students))
                ->description(__('admin.stats.of_total', ['percent' => $percent($students)])),

            Stat::make(__('admin.stats.parents'), number_format($parents))
                ->description(__('admin.stats.of_total', ['percent' => $percent($parents)])),

            Stat::make(__('admin.stats.conference'), number_format($conference))
                ->description(__('admin.stats.pending', ['count' => $pending]))
                ->color($pending > 0 ? 'warning' : 'info'),

            Stat::make(__('admin.stats.delivery'), $rate.'%')
                ->description(__('admin.stats.failed', ['failed' => $failed, 'retried' => Message::where('attempts', '>', 1)->count()]))
                ->color($rate >= 95 ? 'success' : 'warning'),

            Stat::make(__('admin.stats.checked_in'), number_format(CheckIn::whereDate('checked_in_at', today())->count()))
                ->color('success'),
        ];
    }
}
