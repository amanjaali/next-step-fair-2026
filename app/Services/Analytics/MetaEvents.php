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
        if (! MetaSettings::pixelId()) {
            return;
        }

        $request->session()->flash('meta_event', [
            'name' => $eventName,
            'data' => $pixelData,
            'event_id' => $eventId,
        ]);

        if (! MetaSettings::serverEnabled()) {
            return;
        }

        // After the response, without a queue worker: never slows the page, and a
        // failure is logged rather than shown to the student.
        SendMetaConversionEvent::dispatchAfterResponse(self::event(
            $eventName,
            $eventId,
            now()->getTimestamp(),
            $sourceUrl ?? $request->headers->get('referer') ?? $request->fullUrl(),
            self::userData($registration, $request->ip(), $request->userAgent(), $request->cookie('_fbp'), $request->cookie('_fbc')),
            $serverData,
        ));
    }

    /** @return array<string, mixed> */
    public static function event(string $name, string $id, int $time, string $url, array $userData, array $customData): array
    {
        return [
            'event_name' => $name,
            'event_time' => $time,
            'event_id' => $id,
            'action_source' => 'website',
            'event_source_url' => $url,
            'user_data' => $userData,
            'custom_data' => $customData,
        ];
    }

    /** @return array<string, mixed> */
    public static function userData(Registration $registration, ?string $ip, ?string $userAgent, ?string $fbp = null, ?string $fbc = null): array
    {
        $email = $registration->email ? strtolower(trim($registration->email)) : null;
        // Digits with the country code and no leading zeros: 9647701234567.
        $phone = $registration->msisdn() ? preg_replace('/\D/', '', $registration->msisdn()) : null;

        return array_filter([
            'em' => $email ? [hash('sha256', $email)] : null,
            'ph' => $phone ? [hash('sha256', $phone)] : null,
            'external_id' => [hash('sha256', (string) $registration->id)],
            'client_ip_address' => $ip,
            'client_user_agent' => $userAgent,
            'fbp' => $fbp,
            'fbc' => $fbc,
        ]);
    }
}
