<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Delivery-status callbacks from the WhatsApp Cloud API.
 *
 * Meta posts sent / delivered / read / failed per message id; each one updates
 * the delivery log so the admin can see what actually reached a registrant.
 */
class WhatsAppWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        // Meta's subscription handshake.
        if ($request->isMethod('get')) {
            $verifyToken = config('whatsapp.cloud_api.webhook_verify_token');

            if ($request->query('hub_verify_token') === $verifyToken) {
                return response((string) $request->query('hub_challenge'));
            }

            return response('Forbidden', 403);
        }

        foreach ($request->input('entry', []) as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                foreach ($change['value']['statuses'] ?? [] as $status) {
                    $this->applyStatus($status);
                }
            }
        }

        return response('OK');
    }

    private function applyStatus(array $status): void
    {
        $message = Message::where('provider_message_id', $status['id'] ?? null)->first();

        if (! $message) {
            return;
        }

        $timestamp = isset($status['timestamp'])
            ? now()->setTimestamp((int) $status['timestamp'])
            : now();

        match ($status['status'] ?? '') {
            'sent' => $message->forceFill(['status' => Message::STATUS_SENT, 'sent_at' => $timestamp])->save(),
            'delivered' => $message->forceFill(['status' => Message::STATUS_DELIVERED, 'delivered_at' => $timestamp])->save(),
            'read' => $message->forceFill(['status' => Message::STATUS_READ, 'read_at' => $timestamp])->save(),
            'failed' => $message->forceFill([
                'status' => Message::STATUS_FAILED,
                'failed_at' => $timestamp,
                'error' => data_get($status, 'errors.0.title', 'Delivery failed'),
            ])->save(),
            default => null,
        };
    }
}
