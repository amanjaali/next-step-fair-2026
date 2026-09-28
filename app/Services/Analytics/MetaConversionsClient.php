<?php

namespace App\Services\Analytics;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/** Posts events to Meta's Conversions API, up to 1,000 per request. */
class MetaConversionsClient
{
    /**
     * @param  list<array<string, mixed>>  $events
     * @return array{ok: bool, received: int, error: ?string}
     */
    public function send(array $events): array
    {
        $pixel = MetaSettings::pixelId();
        $token = MetaSettings::token();

        if (! $pixel || ! $token) {
            return ['ok' => false, 'received' => 0, 'error' => 'No Pixel ID or access token is set.'];
        }

        $url = sprintf('https://graph.facebook.com/%s/%s/events', MetaSettings::graphVersion(), $pixel);
        $received = 0;

        foreach (array_chunk($events, 1000) as $chunk) {
            try {
                $response = Http::timeout(20)->asJson()->post($url, array_filter([
                    'data' => $chunk,
                    'access_token' => $token,
                    'test_event_code' => MetaSettings::testCode(),
                ]));
            } catch (Throwable $e) {
                return $this->failed($chunk, $received, $e->getMessage());
            }

            if ($response->failed()) {
                $reason = $response->json('error.message') ?? mb_substr($response->body(), 0, 300);

                return $this->failed($chunk, $received, "Meta replied {$response->status()}: {$reason}");
            }

            $received += (int) $response->json('events_received', count($chunk));
        }

        MetaSettings::recordSuccess();

        return ['ok' => true, 'received' => $received, 'error' => null];
    }

    /** @param list<array<string, mixed>> $chunk */
    private function failed(array $chunk, int $received, string $error): array
    {
        Log::error('meta_capi.failed', [
            'events' => array_map(fn ($e) => ($e['event_name'] ?? '?').':'.($e['event_id'] ?? '?'), $chunk),
            'error' => $error,
        ]);
        MetaSettings::recordError($error);

        return ['ok' => false, 'received' => $received, 'error' => $error];
    }
}
