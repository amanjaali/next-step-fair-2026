<?php

namespace App\Filament\Widgets;

use App\Models\QrScan;
use App\Models\Registration;
use Filament\Widgets\Widget;

/**
 * Scan → form started → badge issued → still active.
 *
 * "Form started" is every registration row that exists at all, since a row is
 * only written once someone has submitted. The middle step used to be the OTP
 * round-trip; registration no longer asks for a code, so it counts badges issued
 * instead — the drop there is now walk-up passes begun at the gate and never
 * finished, and cancellations show as the drop to the last step.
 */
class ConversionFunnelWidget extends Widget
{
    protected string $view = 'filament.widgets.conversion-funnel';

    protected int|string|array $columnSpan = 1;

    public function getSteps(): array
    {
        $scans = QrScan::count();
        $started = Registration::count();
        $issued = Registration::whereNotNull('badge_generated_at')->count();
        $confirmed = Registration::active()->count();

        $top = max($scans, $started, 1);

        return [
            ['label' => __('admin.widgets.page_views'), 'value' => $scans, 'percent' => round($scans / $top * 100)],
            ['label' => __('admin.widgets.form_started'), 'value' => $started, 'percent' => round($started / $top * 100)],
            ['label' => __('admin.widgets.badge_issued'), 'value' => $issued, 'percent' => round($issued / $top * 100)],
            ['label' => __('admin.widgets.confirmed'), 'value' => $confirmed, 'percent' => round($confirmed / $top * 100)],
        ];
    }
}
