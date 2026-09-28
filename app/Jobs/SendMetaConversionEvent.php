<?php

namespace App\Jobs;

use App\Services\Analytics\MetaConversionsClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * One event to Meta's Conversions API, run after the page has been sent. A
 * failure is logged and shown on the admin's Meta tracking page, never thrown.
 */
class SendMetaConversionEvent implements ShouldQueue
{
    use Queueable;

    /** @param array<string, mixed> $event */
    public function __construct(public array $event) {}

    public function handle(MetaConversionsClient $client): void
    {
        $client->send([$this->event]);
    }
}
