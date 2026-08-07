<?php

namespace App\Http\Controllers\Institution;

use App\Http\Controllers\Controller;
use App\Models\Field;
use App\Models\InstitutionUser;
use App\Models\Interaction;
use App\Models\MatchScore;
use App\Models\Organization;
use App\Models\Sector;
use App\Services\Matching\MatchEngine;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * What a university sees and does.
 *
 * Three jobs: describe what you teach, see who wants it, and record who you spoke
 * to. The third is what turns a match into a lead, and it is the only one that has
 * to work on a phone at a noisy desk — which is why the scanner is deliberately the
 * simplest screen in the portal.
 *
 * Everything is scoped to the signed-in user's own organisation. A recruiter never
 * sees another institution's leads, and never sees a student who has not agreed to
 * be introduced.
 */
class PortalController extends Controller
{
    public function __construct(
        private readonly MatchEngine $matcher,
        private readonly TicketService $tickets,
    ) {}

    public function dashboard(): View
    {
        $organization = $this->organization();

        $interactions = $organization->interactions();
        $scans = (clone $interactions)->ofType(Interaction::TYPE_BOOTH_SCAN);

        return view('institution.dashboard', [
            'navKey' => null,
            'title' => __('institution.dashboard.title').' — '.config('nextstep.event.name'),
            'organization' => $organization,
            'completeness' => $organization->profileCompleteness(),
            'stats' => [
                'matches' => $organization->matches()->count(),
                'strong' => $organization->matches()->where('score', '>=', 75)->count(),
                'scans' => (clone $scans)->distinct('registration_id')->count('registration_id'),
                'follow_up' => (clone $interactions)->where('follow_up', true)->count(),
            ],
            // What the students who match this institution actually want — the
            // single most useful number for planning next year's stand.
            'topFields' => $this->demandAmongMatches($organization),
            'recentScans' => (clone $scans)->with('registration')->latest('id')->limit(8)->get(),
        ]);
    }

    /* --------------------------------------------------------- the profile -- */

    public function editProfile(): View
    {
        $organization = $this->organization();

        return view('institution.profile', [
            'navKey' => null,
            'title' => __('institution.profile.title').' — '.config('nextstep.event.name'),
            'organization' => $organization,
            'sectors' => Sector::with('fields')->orderBy('sort')->get(),
            'selected' => $organization->fields()
                ->get()
                ->groupBy('id')
                ->map(fn ($rows) => $rows->pluck('pivot.level')->all()),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $organization = $this->organization();
        $levels = config('taxonomy.degree_levels');

        $data = $request->validate([
            'description' => ['nullable', 'array'],
            'description.*' => ['nullable', 'string', 'max:2000'],
            'website' => ['nullable', 'url', 'max:190'],
            'city' => ['nullable', 'string', 'max:120'],
            'campus_countries' => ['nullable', 'array'],
            'campus_countries.*' => [Rule::in(config('taxonomy.countries'))],
            'degree_levels' => ['nullable', 'array'],
            'degree_levels.*' => [Rule::in($levels)],
            'languages' => ['nullable', 'array'],
            'languages.*' => [Rule::in(config('taxonomy.languages'))],
            'tuition_min' => ['nullable', 'integer', 'min:0', 'max:200000'],
            'tuition_max' => ['nullable', 'integer', 'min:0', 'max:200000'],
            'offers_scholarships' => ['nullable', 'boolean'],
            'min_grade_band' => ['nullable', Rule::in(array_keys(config('taxonomy.grade_bands')))],
            'intake_capacity' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'application_deadline' => ['nullable', 'date'],
            'contact_email' => ['nullable', 'email', 'max:190'],
            'contact_phone' => ['nullable', 'string', 'max:32'],
            'recruitment_target' => ['nullable', 'integer', 'min:0', 'max:100000'],
            // fields[field_id][] = level
            'fields' => ['nullable', 'array'],
            'fields.*' => ['array'],
            'fields.*.*' => [Rule::in($levels)],
        ]);

        $organization->forceFill([
            'description' => array_filter($data['description'] ?? []),
            'website' => $data['website'] ?? null,
            'city' => $data['city'] ?? null,
            'campus_countries' => $data['campus_countries'] ?? [],
            'degree_levels' => $data['degree_levels'] ?? [],
            'languages' => $data['languages'] ?? [],
            'tuition_min' => $data['tuition_min'] ?? null,
            'tuition_max' => $data['tuition_max'] ?? null,
            'offers_scholarships' => (bool) ($data['offers_scholarships'] ?? false),
            'min_grade_band' => $data['min_grade_band'] ?? null,
            'intake_capacity' => $data['intake_capacity'] ?? null,
            'application_deadline' => $data['application_deadline'] ?? null,
            'contact_email' => $data['contact_email'] ?? $organization->contact_email,
            'contact_phone' => $data['contact_phone'] ?? null,
            'recruitment_goals' => ['target_students' => $data['recruitment_target'] ?? null],
            'profile_completed_at' => now(),
        ])->save();

        // Rebuild the programme list: one row per field and level taught.
        $sync = [];
        foreach ($data['fields'] ?? [] as $fieldId => $fieldLevels) {
            foreach (array_unique($fieldLevels) as $level) {
                $sync[] = [
                    'organization_id' => $organization->id,
                    'field_id' => (int) $fieldId,
                    'level' => $level,
                    'scholarship' => (bool) ($data['offers_scholarships'] ?? false),
                    'tuition_min' => $data['tuition_min'] ?? null,
                    'tuition_max' => $data['tuition_max'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('field_organization')->where('organization_id', $organization->id)->delete();
        if ($sync) {
            DB::table('field_organization')->insert($sync);
        }

        // What they teach has changed, so who they match has changed with it.
        $this->matcher->forOrganization($organization->fresh());

        return redirect()->route('portal.dashboard')->with('status', __('institution.profile.saved'));
    }

    /* ------------------------------------------------------------- students -- */

    /**
     * Students who match, and who agreed to be introduced.
     *
     * Two separate gates. Consent decides whether contact details are shown at
     * all; a student who declined still appears in the counts, because an
     * institution is entitled to know how much demand exists for what it teaches,
     * just not to know who those people are.
     */
    public function students(Request $request): View
    {
        $organization = $this->organization();

        $matches = $organization->matches()
            ->with(['registration.fields'])
            ->when($request->filled('min'), fn ($q) => $q->where('score', '>=', (int) $request->input('min')))
            ->when($request->filled('field'), fn ($q) => $q->whereHas(
                'registration.fields',
                fn ($f) => $f->where('fields.id', (int) $request->input('field'))
            ))
            ->orderByDesc('score')
            ->paginate(30)
            ->withQueryString();

        return view('institution.students', [
            'navKey' => null,
            'title' => __('institution.students.title').' — '.config('nextstep.event.name'),
            'organization' => $organization,
            'matches' => $matches,
            'fields' => $organization->fields()->get()->unique('id'),
            'sharedCount' => $organization->matches()
                ->whereHas('registration', fn ($q) => $q->where('share_with_institutions', true))->count(),
        ]);
    }

    /** Everyone this institution actually spoke to, with notes. */
    public function leads(Request $request): View
    {
        $organization = $this->organization();

        return view('institution.leads', [
            'navKey' => null,
            'title' => __('institution.leads.title').' — '.config('nextstep.event.name'),
            'organization' => $organization,
            'interactions' => $organization->interactions()
                ->with(['registration.fields', 'field'])
                ->when($request->boolean('follow_up'), fn ($q) => $q->where('follow_up', true))
                ->latest('occurred_at')
                ->paginate(30)
                ->withQueryString(),
        ]);
    }

    /* -------------------------------------------------------- booth scanner -- */

    public function scanner(): View
    {
        return view('institution.scanner', [
            'navKey' => null,
            'title' => __('institution.scanner.title').' — '.config('nextstep.event.name'),
            'organization' => $this->organization(),
        ]);
    }

    /**
     * Record a badge scanned at the desk.
     *
     * Idempotent per student per day: a recruiter who scans the same badge twice
     * while talking has not met two people, and the leaderboard must not reward
     * them as though they had.
     */
    public function scan(Request $request): JsonResponse
    {
        $request->validate([
            'ticket' => ['required', 'string'],
            'sig' => ['nullable', 'string'],
            'field_id' => ['nullable', 'exists:fields,id'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $registration = $this->tickets->resolve(
            $request->string('ticket')->toString(),
            $request->string('sig')->toString() ?: null,
        );

        if (! $registration) {
            return response()->json(['state' => 'invalid', 'message' => __('institution.scanner.invalid')], 422);
        }

        $organization = $this->organization();
        $day = $this->currentDay();

        $existing = Interaction::where('registration_id', $registration->id)
            ->where('organization_id', $organization->id)
            ->where('type', Interaction::TYPE_BOOTH_SCAN)
            ->when($day, fn ($q) => $q->where('day', $day))
            ->first();

        $interaction = $existing ?: Interaction::create([
            'registration_id' => $registration->id,
            'organization_id' => $organization->id,
            'type' => Interaction::TYPE_BOOTH_SCAN,
            'day' => $day,
            'institution_user_id' => Auth::guard('institution')->id(),
            'occurred_at' => now(),
        ]);

        $interaction->forceFill(array_filter([
            'field_id' => $request->input('field_id'),
            'rating' => $request->input('rating'),
            'notes' => $request->input('notes'),
        ]))->save();

        $match = MatchScore::where('registration_id', $registration->id)
            ->where('organization_id', $organization->id)->first();

        return response()->json([
            'state' => $existing ? 'already' : 'ok',
            'name' => $registration->full_name,
            'ticket' => $registration->ticket_ref,
            'city' => $registration->city,
            'fields' => $registration->fields->map(fn (Field $f) => $f->t('name'))->all(),
            'level' => $registration->degree_level,
            'match' => $match?->score,
            'shared' => (bool) $registration->share_with_institutions,
        ]);
    }

    /* ------------------------------------------------------------- helpers -- */

    /** Which day of the fair it is, or null outside the run. */
    private function currentDay(): ?int
    {
        foreach (config('nextstep.event.days') as $number => $meta) {
            if (now(config('nextstep.event.timezone'))->isSameDay(Carbon::parse($meta['date']))) {
                return (int) $number;
            }
        }

        return null;
    }

    /** The fields most wanted by the students this institution matches. */
    private function demandAmongMatches(Organization $organization): Collection
    {
        return DB::table('matches')
            ->join('field_registration', 'field_registration.registration_id', '=', 'matches.registration_id')
            ->join('fields', 'fields.id', '=', 'field_registration.field_id')
            ->where('matches.organization_id', $organization->id)
            ->groupBy('fields.id', 'fields.name')
            ->select('fields.id', 'fields.name', DB::raw('COUNT(*) as students'))
            ->orderByDesc('students')
            ->limit(8)
            ->get()
            ->map(fn ($row) => [
                'field' => Field::find($row->id)?->t('name') ?? '',
                'students' => (int) $row->students,
            ]);
    }

    private function organization(): Organization
    {
        /** @var InstitutionUser $user */
        $user = Auth::guard('institution')->user();

        abort_unless($user->organization_id, 403);

        return $user->organization;
    }
}
