<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
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
 * The registration desk: where volunteers issue and correct quick accounts.
 *
 * A name, a phone number and a type (visitor, parent or student) — the desk
 * version of the public "Just attending" flow, just with an accurate label
 * for the headcount. It's confirmed and badged on the spot, but not checked
 * in: that still happens at the gate, on purpose, so a walk-in that never
 * actually reaches the fair floor doesn't quietly count as attendance. If the
 * person later fills in the full student form on their own phone with this
 * same number, that form completes this record rather than duplicating it —
 * see FairRegistrationController::store().
 */
class RegistrationDeskController extends Controller
{
    /** The fields a volunteer can set, whether creating or correcting a record. */
    private const EDITABLE_FIELDS = ['full_name', 'type', 'phone_country', 'phone'];

    private const TYPES = [Registration::TYPE_VISITOR, Registration::TYPE_PARENT, Registration::TYPE_STUDENT];

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
     * Browse the desk's list, or search it by name/phone/ticket.
     *
     * With no query, this is every visitor, parent and student record made
     * this way — desk-issued and self-service quick passes alike. With a
     * query, it narrows the same list.
     */
    public function search(Request $request): JsonResponse
    {
        $term = trim((string) $request->input('q'));
        $digits = preg_replace('/\D/', '', $term);

        $query = Registration::query()->fair()->active()->whereIn('type', self::TYPES);

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
        $members = $this->validatedMembers($request);

        // One phone number, one registration, one badge — same rule the public
        // quick-pass form enforces, so the desk can't accidentally duplicate
        // someone who already has a pass. A family sharing one phone is the
        // one deliberate exception: when the volunteer has added members
        // alongside the first person, the number is expected to repeat.
        if ($members === [] && Registration::fair()->active()->wherePhone($data['phone'])->exists()) {
            return response()->json(['message' => __('registration_desk.phone_already_registered')], 422);
        }

        $people = [['full_name' => $data['full_name'], 'type' => $data['type']], ...$members];

        $registrations = collect($people)->map(function (array $person) use ($data, $request) {
            $registration = new Registration([
                'full_name' => $person['full_name'],
                'type' => $person['type'],
                'phone_country' => $data['phone_country'],
                'phone' => $data['phone'],
            ]);
            $registration->track = Registration::TRACK_FAIR;
            $registration->locale = app()->getLocale();
            $registration->is_walk_in = true;
            $registration->created_by = auth()->id();
            // Same-shape record as a quick pass: valid for the whole run, nothing
            // to choose.
            $registration->days = array_keys(config('nextstep.event.days'));
            $registration->status = Registration::STATUS_DRAFT;
            $registration->consented_at = now();
            $registration->consent_ip = $request->ip();
            $registration->consents = ['terms' => true, 'whatsapp' => true];
            $registration->save();

            // Same confirmation the public quick-pass and full form use: badge
            // generated, WhatsApp confirmation queued. Deliberately not checked
            // in — that only happens at the gate, so a walk-in a volunteer made
            // up can never quietly count as someone who actually attended. Each
            // family member gets their own badge and their own check-in later.
            app(RegistrationConfirmer::class)->confirm($registration);

            return $registration->fresh('checkIns');
        });

        return response()->json(
            $registrations->map(fn (Registration $r) => $this->present($r, full: true))->all(),
            201,
        );
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

            if ($registration->badgeIssued() && $changedFields->intersect(['full_name', 'type'])->isNotEmpty()) {
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

    /** This desk only manages fair visitor/parent/student walk-ins — never a conference RSVP. */
    private function assertDeskManaged(Registration $registration): void
    {
        abort_unless($registration->isFair() && in_array($registration->type, self::TYPES, true), 404);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'min:3', 'max:120'],
            'type' => ['required', 'in:'.implode(',', self::TYPES)],
            'phone_country' => ['required', 'string', 'max:8', 'in:'.implode(',', array_keys(config('nextstep.phone.countries')))],
            'phone' => ['required', 'string', $this->phoneRule($request->input('phone_country'))],
        ]);

        // Strip a leading zero before it's stored — left in, it survives into the
        // WhatsApp number (phone_country + phone) as an extra digit and the badge
        // never arrives. Same normalisation QuickPassController applies.
        $data['phone'] = ltrim(preg_replace('/\D/', '', $data['phone']), '0');

        return $data;
    }

    /**
     * Extra people walking up on the same phone as the first — a family, most
     * often. Just a name and a type each; the phone and country come from the
     * primary submission, since that's the number all of them share.
     *
     * @return array<int, array{full_name: string, type: string}>
     */
    private function validatedMembers(Request $request): array
    {
        $validated = $request->validate([
            'members' => ['sometimes', 'array'],
            'members.*.full_name' => ['required', 'string', 'min:3', 'max:120'],
            'members.*.type' => ['required', 'in:'.implode(',', self::TYPES)],
        ]);

        return $validated['members'] ?? [];
    }

    /**
     * Iraqi mobiles are ten digits starting with 7, with an optional leading
     * zero — tight enough to catch a mistyped number at the desk. Every other
     * country code on the form keeps the old, looser shape: this desk has no
     * business policing what a Turkish or UK mobile number looks like.
     */
    private function phoneRule(?string $country): string
    {
        return $country === '+964'
            ? 'regex:/^0?7[0-9]{9}$/'
            : 'regex:/^0?[0-9]{9,12}$/';
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
