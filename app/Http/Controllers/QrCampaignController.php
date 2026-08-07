<?php

namespace App\Http\Controllers;

use App\Models\QrCampaign;
use App\Models\QrScan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Campaign QR short links.
 *
 * /q/{code} counts the scan, attaches the campaign's UTM parameters and forwards
 * to the target, so a registration can be attributed back to the poster that
 * produced it. The scanner's IP is hashed: this is a counter, not a profile.
 */
class QrCampaignController extends Controller
{
    public function redirect(Request $request, string $code): RedirectResponse
    {
        $campaign = QrCampaign::where('code', $code)->first();

        if (! $campaign || ! $campaign->isUsable()) {
            return redirect()->route('root');
        }

        $ipHash = hash_hmac('sha256', (string) $request->ip(), (string) config('app.key'));

        QrScan::create([
            'qr_campaign_id' => $campaign->id,
            'scanned_at' => now(),
            'ip_hash' => $ipHash,
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'referrer' => $request->headers->get('referer'),
            // A repeat scan from the same device within the day is not a new person.
            'is_unique' => ! $campaign->scans()
                ->where('ip_hash', $ipHash)
                ->where('scanned_at', '>=', now()->subDay())
                ->exists(),
        ]);

        $campaign->increment('scan_count');

        return redirect()->away($campaign->targetWithUtm());
    }
}
