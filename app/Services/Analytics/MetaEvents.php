<?php

namespace App\Services\Analytics;

use App\Jobs\SendMetaConversionEvent;
use App\Models\Registration;
use Illuminate\Http\Request;

/**
 * One Meta event, sent from both the browser Pixel and the Conversions API
 * under the same event ID so Meta keeps only one of the pair.
 */
class MetaEvents
{
    /** Queues the Pixel call for the next page and sends the server copy. */
    public function track(
        Request $request,
        string $eventName,
        string $eventId,
        Registration $registration,
        array $pixelData,
        array $serverData,
        ?string $sourceUrl = null,
    ): void {
        if (! config('nextstep.analytics.meta_pixel')) {
            return;
        }

        $request->session()->flash('meta_event', [
            'name' => $eventName,
            'data' => $pixelData,
            'event_id' => $eventId,
        ]);

        if (! config('nextstep.analytics.meta_capi_token')) {
            return;
        }

        // After the response, without a queue worker: never slows the page, and a
        // failure is logged rather than shown to the student.
        SendMetaConversionEvent::dispatchAfterResponse(array_filter([
            'event_name' => $eventName,
            'event_time' => now()->getTimestamp(),
            'event_id' => $eventId,
            'action_source' => 'website',
            'event_source_url' => $sourceUrl ?? $request->headers->get('referer') ?? $request->fullUrl(),
            'user_data' => $this->userData($request, $registration),
            'custom_data' => $serverData,
        ]));
    }

    /** @return array<string, mixed> */
    public function userData(Request $request, Registration $registration): array
    {
        $email = $registration->email ? strtolower(trim($registration->email)) : null;
        // Digits with the country code and no leading zeros: 9647701234567.
        $phone = $registration->msisdn() ? preg_replace('/\D/', '', $registration->msisdn()) : null;

        return array_filter([
            'em' => $email ? [hash('sha256', $email)] : null,
            'ph' => $phone ? [hash('sha256', $phone)] : null,
            'external_id' => [hash('sha256', (string) $registration->id)],
            'client_ip_address' => $request->ip(),
            'client_user_agent' => $request->userAgent(),
            'fbp' => $request->cookie('_fbp'),
            'fbc' => $request->cookie('_fbc'),
        ]);
    }
}
