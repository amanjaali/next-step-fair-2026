<?php

namespace App\Http\Controllers\Checkin;

use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use App\Models\Registration;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * The gate scanner: an installable, mobile-first PWA for staff.
 *
 * Venue Wi-Fi is unreliable when all three halls are full, so the client caches
 * a list of valid tickets and queues scans locally; `sync` reconciles the queue
 * when the connection returns, without ever double-counting a day.
 */
class CheckinController extends Controller
{
    public function __construct(private readonly TicketService $tickets) {}

    /* ------------------------------------------------------------- auth --- */

    public function showLogin(): View
    {
        return view('checkin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, true)) {
            return back()->withErrors(['email' => __('auth.failed')])->onlyInput('email');
        }

        $request->session()->regenerate();
        Auth::user()->forceFill(['last_login_at' => now()])->save();

        return redirect()->route('checkin.index');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('checkin.login');
    }

    /* ---------------------------------------------------------- scanner --- */

    public function index(Request $request): View
    {
        if ($request->filled('day')) {
            $request->session()->put('checkin.day', (int) $request->integer('day'));
        }
        if ($request->filled('gate')) {
            $request->session()->put('checkin.gate', $request->string('gate')->toString());
        }

        return view('checkin.index', [
            'day' => $this->currentDay($request),
            'gate' => $request->session()->get('checkin.gate', Auth::user()->default_gate ?? 'A'),
            'days' => array_keys(config('nextstep.event.days')),
            'todayCount' => CheckIn::whereDate('checked_in_at', today())->count(),
        ]);
    }

    /**
     * Resolve a scanned token.
     *
     * Green: valid and now checked in. Amber: already checked in today, with the
     * timestamp and the staff member. Red: not ours, or cancelled.
     */
    public function scan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ticket' => ['required', 'string'],
            'sig' => ['nullable', 'string'],
            'day' => ['nullable', 'integer'],
            'device_id' => ['nullable', 'string', 'max:64'],
            'scanned_at' => ['nullable', 'date'],
        ]);

        $day = (int) ($data['day'] ?? $this->currentDay($request));
        [$ticket, $signature] = $this->parseToken($data['ticket'], $data['sig'] ?? null);

        $registration = $this->tickets->resolve($ticket, $signature);

        if (! $registration) {
            return response()->json([
                'state' => 'invalid',
                'title' => __('checkin.not_recognised'),
                'detail' => __('checkin.invalid_detail'),
            ]);
        }

        return response()->json($this->checkIn($registration, $day, $request, $data));
    }

    /** Manual entry for a registrant who lost their QR. */
    public function search(Request $request): JsonResponse
    {
        $term = trim((string) $request->input('q'));

        if (mb_strlen($term) < 3) {
            return response()->json(['results' => []]);
        }

        $digits = preg_replace('/\D/', '', $term);

        $results = Registration::query()
            ->active()
            ->where(function ($query) use ($term, $digits) {
                $query->where('full_name', 'like', "%{$term}%")
                    ->orWhere('ticket_ref', 'like', strtoupper($term).'%');

                // Phone and e-mail are encrypted; look them up by keyed hash.
                if ($digits !== '') {
                    $query->orWhere('phone_hash', Registration::hashValue(ltrim($digits, '0')));
                }
                if (str_contains($term, '@')) {
                    $query->orWhere('email_hash', Registration::hashValue(strtolower($term)));
                }
            })
            ->with('checkIns')
            ->limit(12)
            ->get()
            ->map(fn (Registration $r) => [
                'id' => $r->id,
                'name' => $r->full_name,
                'detail' => trim(implode(' · ', array_filter([
                    strtoupper($r->type),
                    $r->organization ?: $r->city,
                    $r->daysLabel(),
                ]))),
                'ticket' => $r->ticket_ref,
                'status' => $r->status,
            ]);

        return response()->json(['results' => $results]);
    }

    public function manual(Request $request, Registration $registration): JsonResponse
    {
        $day = (int) $request->input('day', $this->currentDay($request));

        return response()->json(
            $this->checkIn($registration->load('checkIns'), $day, $request, ['method' => 'manual'])
        );
    }

    /* -------------------------------------------------------- offline ----- */

    /**
     * Tickets the device can validate without a connection. Names are included
     * so the green screen can still say who it is when the venue Wi-Fi drops.
     */
    public function offlineManifest(Request $request): JsonResponse
    {
        $tickets = Registration::active()
            ->whereIn('status', [Registration::STATUS_CONFIRMED, Registration::STATUS_CHECKED_IN])
            ->get(['id', 'ticket_id', 'ticket_ref', 'full_name', 'type', 'track', 'organization', 'city', 'days'])
            ->map(fn (Registration $r) => [
                't' => $r->ticket_id,
                'r' => $r->ticket_ref,
                'n' => $r->full_name,
                'y' => strtoupper($r->type),
                'o' => $r->organization ?: $r->city,
                'd' => $r->dayList(),
            ]);

        return response()->json([
            'day' => $this->currentDay($request),
            'generated_at' => now()->toIso8601String(),
            'tickets' => $tickets,
        ]);
    }

    /** Uploads a queue of offline scans. Idempotent per registration and day. */
    public function sync(Request $request): JsonResponse
    {
        $data = $request->validate([
            'scans' => ['required', 'array', 'max:500'],
            'scans.*.ticket' => ['required', 'string'],
            'scans.*.day' => ['required', 'integer'],
            'scans.*.scanned_at' => ['nullable', 'date'],
            'scans.*.device_id' => ['nullable', 'string', 'max:64'],
        ]);

        $accepted = 0;
        $rejected = [];

        DB::transaction(function () use ($data, $request, &$accepted, &$rejected) {
            foreach ($data['scans'] as $scan) {
                [$ticket] = $this->parseToken($scan['ticket'], null);
                $registration = Registration::where('ticket_id', $ticket)->first();

                if (! $registration || ! $registration->badgeIssued()) {
                    $rejected[] = $scan['ticket'];

                    continue;
                }

                $checkIn = CheckIn::firstOrCreate(
                    ['registration_id' => $registration->id, 'day' => (int) $scan['day']],
                    [
                        'checked_in_at' => isset($scan['scanned_at']) ? Carbon::parse($scan['scanned_at']) : now(),
                        'staff_id' => $request->user()->id,
                        'gate' => $request->session()->get('checkin.gate'),
                        'method' => 'scan',
                        'device_id' => $scan['device_id'] ?? null,
                        'synced_at' => now(),
                    ]
                );

                if ($checkIn->wasRecentlyCreated) {
                    $accepted++;
                    $registration->forceFill(['status' => Registration::STATUS_CHECKED_IN])->save();
                }
            }
        });

        return response()->json(['accepted' => $accepted, 'rejected' => $rejected]);
    }

    /* --------------------------------------------------------------- PWA -- */

    public function manifest(): JsonResponse
    {
        return response()->json([
            'name' => config('nextstep.event.name').' — '.__('checkin.title'),
            'short_name' => 'NS Check-in',
            'start_url' => route('checkin.index'),
            'scope' => url('/checkin'),
            'display' => 'standalone',
            'orientation' => 'portrait',
            'background_color' => '#050708',
            'theme_color' => '#050708',
            'icons' => [[
                'src' => asset('assets/brand/nextstep-transparent-sm.png'),
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any maskable',
            ]],
        ]);
    }

    public function serviceWorker(): Response
    {
        return response(view('checkin.service-worker')->render())
            ->header('Content-Type', 'application/javascript')
            ->header('Service-Worker-Allowed', '/checkin');
    }

    /* ----------------------------------------------------------- helpers -- */

    private function checkIn(Registration $registration, int $day, Request $request, array $data = []): array
    {
        $base = [
            'name' => $registration->full_name,
            'ticket' => $registration->ticket_ref,
            'type' => strtoupper($registration->type),
            'track' => $registration->track,
            'institution' => $registration->organization ?: $registration->city,
            'days' => $registration->daysLabel(),
        ];

        if ($registration->isCancelled()) {
            return $base + [
                'state' => 'cancelled',
                'title' => $registration->full_name,
                'detail' => __('checkin.cancelled_detail'),
            ];
        }

        if ($registration->status === Registration::STATUS_PENDING) {
            return $base + [
                'state' => 'pending',
                'title' => $registration->full_name,
                'detail' => __('checkin.pending_detail'),
            ];
        }

        if (! $registration->badgeIssued()) {
            return $base + [
                'state' => 'invalid',
                'title' => __('checkin.not_recognised'),
                'detail' => __('checkin.invalid_detail'),
            ];
        }

        // Registered for other days only: amber, not a hard reject — the desk decides.
        $days = $registration->dayList();
        if ($days && ! in_array($day, $days, true)) {
            return $base + [
                'state' => 'wrong_day',
                'title' => $registration->full_name,
                'detail' => __('checkin.wrong_day_detail', [
                    'days' => $registration->daysLabel(),
                    'today' => $day,
                ]),
            ];
        }

        $existing = $registration->checkIns->firstWhere('day', $day);

        if ($existing) {
            return $base + [
                'state' => 'already',
                'title' => $registration->full_name,
                'detail' => __('checkin.already_detail', [
                    'time' => $existing->checked_in_at->format('H:i'),
                    'staff' => $existing->staff?->name ?? '—',
                ]),
            ];
        }

        CheckIn::create([
            'registration_id' => $registration->id,
            'day' => $day,
            'checked_in_at' => isset($data['scanned_at']) ? Carbon::parse($data['scanned_at']) : now(),
            'staff_id' => $request->user()->id,
            'gate' => $request->session()->get('checkin.gate', $request->user()->default_gate),
            'method' => $data['method'] ?? 'scan',
            'device_id' => $data['device_id'] ?? null,
            'synced_at' => now(),
        ]);

        $registration->forceFill(['status' => Registration::STATUS_CHECKED_IN])->save();

        return $base + [
            'state' => $registration->isConference() ? 'valid_conference' : 'valid',
            'title' => $registration->full_name,
            'detail' => trim(implode(' · ', array_filter([
                strtoupper($registration->type),
                $registration->organization ?: $registration->city,
                __('site.common.day', ['n' => $day]),
                now()->format('H:i'),
            ]))),
        ];
    }

    /** Event day from the session, falling back to today's date, then Day 1. */
    private function currentDay(Request $request): int
    {
        if ($day = $request->session()->get('checkin.day')) {
            return (int) $day;
        }

        foreach (config('nextstep.event.days') as $number => $meta) {
            if (Carbon::parse($meta['date'])->isToday()) {
                return (int) $number;
            }
        }

        return 1;
    }

    /**
     * Accepts either a bare ticket id or the full verify URL the QR encodes.
     *
     * @return array{0: string, 1: string|null}
     */
    private function parseToken(string $value, ?string $signature): array
    {
        if (! str_contains($value, '/verify/')) {
            return [$value, $signature];
        }

        $path = (string) parse_url($value, PHP_URL_PATH);
        $ticket = basename($path);

        parse_str((string) parse_url($value, PHP_URL_QUERY), $query);

        return [$ticket, $query['sig'] ?? $signature];
    }
}
