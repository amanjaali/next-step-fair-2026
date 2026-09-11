<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Support\WhatsAppLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Delivery reports from OTPIQ.
 *
 * Without these the delivery log only ever says "sent", which is the moment we
 * handed the message over — not the moment it arrived, and not the moment it
 * failed because the number has no WhatsApp on it. The desk needs the
 * difference: "delivered" means stop looking, "failed" means correct the number
 * and resend.
 *
 * OTPIQ post their own sms id, which is what the gateway stored as the provider
 * message id when the send was accepted.
 */
class OtpiqWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $secret = config('whatsapp.otpiq.webhook_secret');

        /*
         * The address is public, so anything arriving at it is a claim, not a
         * fact. Without the shared secret this would let a stranger mark
         * everybody's badge delivered — which reads, at the desk, as "they have
         * it, stop helping them".
         */
        if (! $secret || ! hash_equals($secret, (string) $this->presentedSecret($request))) {
            return response('Forbidden', 403);
        }

        $message = Message::where('provider_message_id', $request->input('smsId'))->first();

        if (! $message) {
            // Not ours, or ours from before a redeploy. Answering 200 stops them
            // retrying something that will never match.
            return response('OK');
        }

        $status = strtolower((string) $request->input('status'));

        $this->apply($message, $status);

        WhatsAppLog::info('otpiq.webhook', [
            'message_id' => $message->id,
            'sms_id' => $request->input('smsId'),
            'status' => $status,
            'new_message_status' => $message->fresh()?->status,
        ]);

        return response('OK');
    }

    private function presentedSecret(Request $request): ?string
    {
        return $request->header('X-Webhook-Secret')
            ?? $request->header('X-Otpiq-Secret')
            ?? $request->input('webhookSecret');
    }

    private function apply(Message $message, string $status): void
    {
        match ($status) {
            'sent', 'pending', 'accepted' => $message->forceFill([
                'status' => Message::STATUS_SENT,
                'sent_at' => $message->sent_at ?? now(),
            ])->save(),

            'delivered', 'success' => $message->forceFill([
                'status' => Message::STATUS_DELIVERED,
                'delivered_at' => now(),
            ])->save(),

            'read' => $message->forceFill([
                'status' => Message::STATUS_READ,
                'read_at' => now(),
            ])->save(),

            'failed', 'undelivered', 'rejected' => $message->forceFill([
                'status' => Message::STATUS_FAILED,
                'failed_at' => now(),
                'error' => 'OTPIQ reported: '.$status,
            ])->save(),

            default => null,
        };
    }
}
