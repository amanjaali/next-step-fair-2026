<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use App\Models\Registration;
use App\Models\RegistrationAudit;
use App\Services\BadgeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * The registration desk: where volunteers create and correct walk-in accounts.
 *
 * A record made here is confirmed and checked in for today on the spot — the
 * desk already verified the visitor in person, so nobody needs to queue a
 * second time at a gate scanner during rush hours.
 */
class RegistrationDeskController extends Controller
{
    /** The fields a volunteer can set, whether creating or correcting a record. */
    private const EDITABLE_FIELDS = [
        'full_name', 'type', 'locale', 'phone_country', 'phone', 'email', 'city',
        'date_of_birth', 'gender', 'days', 'school_name', 'relationship',
        'current_status', 'notes',
    ];

    /* ------------------------------------------------------------- auth --- */

    public function showLogin(): View
    {
        return view('registration-desk.login');
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

        return redirect()->route('registration.index');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('registration.login');
    }

    /* -------------------------------------------------------------- desk --- */

    public function index(): View
    {
        return view('registration-desk.index', [
            'urls' => [
                'search' => route('registration.search'),
                // Also the base for {id} show/update/delete: registration.index resolves to /registration.
                'store' => route('registration.index'),
            ],
        ]);
    }

    /**
     * Browse the pre-registered list, or search it by name/phone/ticket/email.
     *
     * With no query, this is the volunteer's roster of people who signed up
     * before the fair — the whole reason for this screen. With a query, it
     * narrows the same list, walk-ins included.
     */
    public function search(Request $request): JsonResponse
    {
        $term = trim((string) $request->input('q'));
        $type = $request->input('type');
        $digits = preg_replace('/\D/', '', $term);

        // The desk only ever creates/edits student and parent accounts — a quick
        // visitor pass has no "type" field on this form and must stay untouched.
        $query = Registration::query()->fair()->active()->whereIn('type', [
            Registration::TYPE_STUDENT, Registration::TYPE_PARENT,
        ]);

        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }

        if ($term !== '') {
            $query->where(function ($q) use ($term, $digits) {
                $q->where('full_name', 'like', "%{$term}%")
                    ->orWhere('ticket_ref', 'like', strtoupper($term).'%');

                if ($digits !== '') {
                    $q->orWhere('phone_hash', Registration::hashValue(ltrim($digits, '0')));
                }
                if (str_contains($term, '@')) {
                    $q->orWhere('email_hash', Registration::hashValue(strtolower($term)));
                }
            });
        }

        $results = $query->with('checkIns')
            ->latest('created_at')
            ->paginate(50)
            ->through(fn (Registration $r) => $this->present($r));

        return response()->json($results);
    }

    public function show(Registration $registration): JsonResponse
    {
        $this->assertDeskManaged($registration);
        $registration->load(['checkIns', 'audits.user']);

        return response()->json($this->present($registration, full: true));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $registration = new Registration($data);
        $registration->track = Registration::TRACK_FAIR;
        $registration->is_walk_in = true;
        $registration->created_by = auth()->id();
        $registration->status = Registration::STATUS_CONFIRMED;
        $registration->consented_at = now();
        $registration->confirmed_at = now();
        $registration->consents = [
            'terms' => ['given' => true, 'at' => now()->toIso8601String()],
            'whatsapp' => ['given' => true, 'at' => now()->toIso8601String()],
        ];
        $registration->save();

        if ($registration->badgeIssued()) {
            app(BadgeService::class)->generate($registration);
        }
        $this->autoCheckInToday($registration);

        return response()->json($this->present($registration->fresh('checkIns'), full: true), 201);
    }

    /** Edits are unrestricted — but every change that lands is logged. */
    public function update(Request $request, Registration $registration): JsonResponse
    {
        $this->assertDeskManaged($registration);
        $data = $this->validated($request);

        $before = collect(self::EDITABLE_FIELDS)
            ->mapWithKeys(fn ($field) => [$field => $registration->getAttribute($field)]);

        $registration->fill($data);
        $changedFields = collect(self::EDITABLE_FIELDS)->filter(fn ($field) => $registration->isDirty($field));

        if ($changedFields->isNotEmpty()) {
            $changes = $changedFields->mapWithKeys(fn ($field) => [$field => [
                'old' => $before[$field],
                'new' => $registration->getAttribute($field),
            ]]);

            $registration->save();

            RegistrationAudit::create([
                'registration_id' => $registration->id,
                'user_id' => auth()->id(),
                'changes' => $changes,
            ]);

            if ($registration->badgeIssued() && $changedFields->intersect(['full_name', 'type', 'days', 'locale'])->isNotEmpty()) {
                app(BadgeService::class)->generate($registration);
            }
        }

        return response()->json($this->present($registration->fresh('checkIns'), full: true));
    }

    /** Self-service cancel: only the volunteer who made the mistake, only within the hour. */
    public function destroy(Registration $registration): JsonResponse
    {
        $this->assertDeskManaged($registration);
        abort_unless($registration->created_by === auth()->id(), 403, __('registration_desk.delete_not_yours'));
        abort_unless($registration->created_at->diffInMinutes(now()) <= 60, 403, __('registration_desk.delete_expired'));

        $registration->delete();

        return response()->json(['deleted' => true]);
    }

    /* ----------------------------------------------------------- helpers -- */

    /** This desk only manages fair student/parent walk-ins — never a visitor pass or a conference RSVP. */
    private function assertDeskManaged(Registration $registration): void
    {
        abort_unless(
            $registration->isFair() && in_array($registration->type, [Registration::TYPE_STUDENT, Registration::TYPE_PARENT], true),
            404
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'type' => ['required', 'in:student,parent'],
            'locale' => ['required', 'in:'.implode(',', array_keys(config('nextstep.locales')))],
            'phone_country' => ['required', 'string', 'max:8', 'in:'.implode(',', array_keys(config('nextstep.phone.countries')))],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:190'],
            'city' => ['required', 'string', 'max:60', 'in:'.implode(',', config('nextstep.cities'))],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:20'],
            'days' => ['required', 'array', 'min:1'],
            'days.*' => ['integer', 'min:1', 'max:3'],
            'school_name' => ['nullable', 'string', 'max:190'],
            'relationship' => ['nullable', 'string', 'max:20'],
            'current_status' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    /** Checks the visitor in for today only — other selected days still need a gate scan. */
    private function autoCheckInToday(Registration $registration): void
    {
        CheckIn::create([
            'registration_id' => $registration->id,
            'day' => Registration::currentEventDay(),
            'checked_in_at' => now(),
            'staff_id' => auth()->id(),
            'method' => 'registration_desk',
            'synced_at' => now(),
        ]);

        $registration->forceFill(['status' => Registration::STATUS_CHECKED_IN])->save();
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Registration $registration, bool $full = false): array
    {
        $today = Registration::currentEventDay();

        $base = [
            'id' => $registration->id,
            'full_name' => $registration->full_name,
            'type' => $registration->type,
            'city' => $registration->city,
            'phone' => $registration->maskedPhone(),
            'ticket' => $registration->ticket_ref,
            'status' => $registration->status,
            'days' => $registration->dayList(),
            'is_walk_in' => (bool) $registration->is_walk_in,
            'checked_in_today' => $registration->relationLoaded('checkIns')
                ? $registration->isCheckedInOn($today)
                : $registration->checkIns()->where('day', $today)->exists(),
        ];

        if (! $full) {
            return $base;
        }

        return array_merge($base, [
            'locale' => $registration->locale,
            'phone_country' => $registration->phone_country,
            'phone' => $registration->phone, // the real, editable number — overrides the masked list value above
            'email' => $registration->email,
            'date_of_birth' => optional($registration->date_of_birth)->toDateString(),
            'gender' => $registration->gender,
            'school_name' => $registration->school_name,
            'relationship' => $registration->relationship,
            'current_status' => $registration->current_status,
            'notes' => $registration->notes,
            'created_by' => $registration->created_by,
            'created_at' => $registration->created_at?->toIso8601String(),
            'can_delete' => $registration->created_by === auth()->id()
                && $registration->created_at->diffInMinutes(now()) <= 60,
            'audits' => $registration->audits->map(fn (RegistrationAudit $a) => [
                'user' => $a->user?->name,
                'changes' => $a->changes,
                'at' => $a->created_at->toIso8601String(),
            ]),
        ]);
    }
}
