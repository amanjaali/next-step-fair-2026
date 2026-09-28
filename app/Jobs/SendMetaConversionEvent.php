<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/** Posts one event to Meta's Conversions API, off the request so it never slows a student down. */
class SendMetaConversionEvent implements ShouldQueue
{
    use Queueable;

    /** @param array<string, mixed> $event */
    public function __construct(public array $event) {}

    public function handle(): void
    {
        $pixel = config('nextstep.analytics.meta_pixel');
        $token = config('nextstep.analytics.meta_capi_token');

        if (! $pixel || ! $token) {
            return;
        }

        $url = sprintf('https://graph.facebook.com/%s/%s/events', config('nextstep.analytics.meta_graph_version'), $pixel);

        // Runs after the page has been sent, so a failure is logged, never thrown:
        // an exception here has no response left to render into.
        try {
            $response = Http::timeout(15)->asJson()->post($url, array_filter([
                'data' => [$this->event],
                'access_token' => $token,
                'test_event_code' => config('nextstep.analytics.meta_capi_test_code'),
            ]));
        } catch (Throwable $e) {
            Log::error('meta_capi.unreachable', $this->context() + ['error' => $e->getMessage()]);

            return;
        }

        if ($response->failed()) {
            Log::error('meta_capi.rejected', $this->context() + [
                'status' => $response->status(),
                'response' => $response->json() ?? mb_substr($response->body(), 0, 500),
            ]);
        }
    }

    /** @return array<string, mixed> */
    private function context(): array
    {
        return [
            'event_name' => $this->event['event_name'] ?? null,
            'event_id' => $this->event['event_id'] ?? null,
        ];
    }
}
