<?php

namespace App\Filament\Widgets;

use App\Models\QrScan;
use App\Models\Registration;
use Filament\Widgets\Widget;

/**
 * Scan → form started → OTP verified → confirmed.
 *
 * "Form started" is every registration row that exists at all, since a row is
 * only written once someone has completed step 4 and submitted; the drop between
 * it and "verified" is the OTP round-trip, which is the number worth watching.
 */
class ConversionFunnelWidget extends Widget
{
    protected string $view = 'filament.widgets.conversion-funnel';

    protected int|string|array $columnSpan = 1;

    public function getSteps(): array
    {
        $scans = QrScan::count();
        $started = Registration::count();
        $verified = Registration::whereNotNull('verified_at')
            ->orWhere('track', Registration::TRACK_CONFERENCE)
            ->count();
        $confirmed = Registration::active()->count();

        $top = max($scans, $started, 1);

        return [
            ['label' => __('admin.widgets.page_views'), 'value' => $scans, 'percent' => round($scans / $top * 100)],
            ['label' => __('admin.widgets.form_started'), 'value' => $started, 'percent' => round($started / $top * 100)],
            ['label' => __('admin.widgets.otp_verified'), 'value' => $verified, 'percent' => round($verified / $top * 100)],
            ['label' => __('admin.widgets.confirmed'), 'value' => $confirmed, 'percent' => round($confirmed / $top * 100)],
        ];
    }
}
