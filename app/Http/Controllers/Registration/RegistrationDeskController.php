<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use App\Models\Registration;
use App\Models\RegistrationAudit;
use App\Services\BadgeService;
use App\Services\RegistrationConfirmer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * The registration desk: where volunteers issue and correct visitor passes.
 *
 * A name and a phone number, same as the public "Just attending" flow — the
 * desk version of the same real registration. It's confirmed, badged and
 * checked in for today on the spot, so nobody queues twice during rush hours.
 * If the visitor later fills in the full student form on their own phone with
 * this same number, that form completes this record rather than duplicating
 * it — see FairRegistrationController::store().
 */
class RegistrationDeskController extends Controller
{
    /** The fields a volunteer can set, whether creating or correcting a record. */
    private const EDITABLE_FIELDS = ['full_name', 'phone_country', 'phone'];

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
     * Browse the pre-registered visitor list, or search it by name/phone/ticket.
     *
     * With no query, this is every visitor pass on file — desk-issued and
     * self-service alike. With a query, it narrows the same list.
     */
    public function search(Request $request): JsonResponse
    {
        $term = trim((string) $request->input('q'));
        $digits = preg_replace('/\D/', '', $term);

        $query = Registration::query()->fair()->active()->where('type', Registration::TYPE_VISITOR);

        if ($term !== '') {
            $query->where(function ($q) use ($term, $digits) {
                $q->where('full_name', 'like', "%{$term}%")
                    ->orWhere('ticket_ref', 'like', strtoupper($term).'%');

                if ($digits !== '') {
                    $q->orWhere('phone_hash', Registration::hashValue(ltrim($digits, '0')));
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

        // One phone number, one registration, one badge — same rule the public
        // quick-pass form enforces, so the desk can't accidentally duplicate
        // someone who already has a pass.
        if (Registration::fair()->active()->wherePhone($data['phone'])->exists()) {
            return response()->json(['message' => __('registration_desk.phone_already_registered')], 422);
        }

        $registration = new Registration($data);
        $registration->track = Registration::TRACK_FAIR;
        $registration->type = Registration::TYPE_VISITOR;
        $registration->locale = app()->getLocale();
        $registration->is_walk_in = true;
        $registration->created_by = auth()->id();
        // A visitor pass is valid for the whole run; there is nothing to choose.
        $registration->days = array_keys(config('nextstep.event.days'));
        $registration->status = Registration::STATUS_DRAFT;
        $registration->consented_at = now();
        $registration->consent_ip = $request->ip();
        $registration->consents = ['terms' => true, 'whatsapp' => true];
        $registration->save();

        // Same confirmation the public quick-pass and full form use: badge
        // generated, WhatsApp confirmation queued.
        app(RegistrationConfirmer::class)->confirm($registration);
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

            if ($registration->badgeIssued() && $changedFields->contains('full_name')) {
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

    /** This desk only manages fair visitor passes — never a student/parent record or a conference RSVP. */
    private function assertDeskManaged(Registration $registration): void
    {
        abort_unless($registration->isFair() && $registration->type === Registration::TYPE_VISITOR, 404);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'min:3', 'max:120'],
            'phone_country' => ['required', 'string', 'max:8', 'in:'.implode(',', array_keys(config('nextstep.phone.countries')))],
            'phone' => ['required', 'string', 'regex:/^0?[0-9]{9,12}$/'],
        ]);

        // Strip a leading zero before it's stored — left in, it survives into the
        // WhatsApp number (phone_country + phone) as an extra digit and the badge
        // never arrives. Same normalisation QuickPassController applies.
        $data['phone'] = ltrim(preg_replace('/\D/', '', $data['phone']), '0');

        return $data;
    }

    /** Checks the visitor in for today only — other days still need a gate scan. */
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
            'phone' => $registration->maskedPhone(),
            'ticket' => $registration->ticket_ref,
            'status' => $registration->status,
            'is_walk_in' => (bool) $registration->is_walk_in,
            'checked_in_today' => $registration->relationLoaded('checkIns')
                ? $registration->isCheckedInOn($today)
                : $registration->checkIns()->where('day', $today)->exists(),
        ];

        if (! $full) {
            return $base;
        }

        return array_merge($base, [
            'phone_country' => $registration->phone_country,
            'phone' => $registration->phone, // the real, editable number — overrides the masked list value above
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
