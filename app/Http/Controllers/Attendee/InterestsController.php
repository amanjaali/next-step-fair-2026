<?php

namespace App\Http\Controllers\Attendee;

use App\Http\Controllers\Controller;
use App\Models\Interaction;
use App\Models\Organization;
use App\Models\Registration;
use App\Models\Sector;
use App\Services\Matching\MatchEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * What a student wants, and who can give it to them.
 *
 * This is the exchange the platform runs on: the student answers eight questions
 * and gets a ranked list of universities worth walking to, and Next Step gets the
 * demand data that makes the fair worth measuring. Neither side is asked to give
 * something for nothing.
 *
 * The questions are asked here rather than bolted onto the registration wizard on
 * purpose. Registration has to stay short enough to finish on a phone at a bus
 * stop; this is the screen someone opens when they are actually planning.
 */
class InterestsController extends Controller
{
    public function __construct(private readonly MatchEngine $matcher) {}

    public function edit(): View
    {
        $registration = $this->attendee();

        return view('attendee.interests', [
            'navKey' => null,
            'title' => __('attendee.interests.title').' — '.config('nextstep.event.name'),
            'registration' => $registration,
            'sectors' => Sector::with('fields')->orderBy('sort')->get(),
            'chosen' => $registration->fields()->pluck('fields.id')->all(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $registration = $this->attendee();

        $data = $request->validate([
            'fields' => ['required', 'array', 'min:1', 'max:5'],
            'fields.*' => ['exists:fields,id'],
            'degree_level' => ['required', Rule::in(config('taxonomy.degree_levels'))],
            'preferred_countries' => ['nullable', 'array', 'max:6'],
            'preferred_countries.*' => [Rule::in(config('taxonomy.countries'))],
            'language_preference' => ['nullable', Rule::in(config('taxonomy.languages'))],
            'budget_band' => ['nullable', Rule::in(array_keys(config('taxonomy.budget_bands')))],
            'grade_band' => ['nullable', Rule::in(array_keys(config('taxonomy.grade_bands')))],
            'start_year' => ['nullable', 'integer', 'min:2026', 'max:2032'],
            'career_goal' => ['nullable', Rule::in(config('taxonomy.career_goals'))],
            'share_with_institutions' => ['nullable', 'boolean'],
        ], [
            'fields.required' => __('attendee.interests.errors.fields'),
            'degree_level.required' => __('attendee.interests.errors.level'),
        ]);

        // Order carries meaning: the first field ticked is the first choice, and
        // matching weights it accordingly.
        $ranked = [];
        foreach (array_values($data['fields']) as $index => $fieldId) {
            $ranked[$fieldId] = ['rank' => $index + 1];
        }
        $registration->fields()->sync($ranked);

        $registration->forceFill([
            'degree_level' => $data['degree_level'],
            'preferred_countries' => $data['preferred_countries'] ?? [],
            'language_preference' => $data['language_preference'] ?? null,
            'budget_band' => $data['budget_band'] ?? null,
            'grade_band' => $data['grade_band'] ?? null,
            'start_year' => $data['start_year'] ?? null,
            'career_goal' => $data['career_goal'] ?? null,
            'share_with_institutions' => (bool) ($data['share_with_institutions'] ?? false),
            'profile_completed_at' => now(),
        ])->save();

        $found = $this->matcher->forRegistration($registration->fresh());

        return redirect()->route('me.matches')
            ->with('status', trans_choice('attendee.matches.found', $found, ['count' => $found]));
    }

    /** The recommendations, with the reasons behind each one. */
    public function matches(): View
    {
        $registration = $this->attendee();

        return view('attendee.matches', [
            'navKey' => null,
            'title' => __('attendee.matches.title').' — '.config('nextstep.event.name'),
            'registration' => $registration,
            'matches' => $registration->matches()
                ->with('organization.fields')
                ->orderByDesc('score')
                ->get(),
            'shortlisted' => Interaction::where('registration_id', $registration->id)
                ->where('type', Interaction::TYPE_SHORTLIST)
                ->pluck('organization_id')->all(),
        ]);
    }

    /**
     * Save a university to visit.
     *
     * Recorded as an interaction rather than a private bookmark: it is a genuine
     * signal of interest, and it is what lets the post-event report say how many
     * of the people who shortlisted an institution actually walked to its desk.
     */
    public function shortlist(Request $request, Organization $organization): RedirectResponse
    {
        $registration = $this->attendee();

        $existing = Interaction::where('registration_id', $registration->id)
            ->where('organization_id', $organization->id)
            ->where('type', Interaction::TYPE_SHORTLIST)
            ->first();

        if ($existing) {
            $existing->delete();

            return back()->with('status', __('attendee.matches.removed'));
        }

        Interaction::create([
            'registration_id' => $registration->id,
            'organization_id' => $organization->id,
            'type' => Interaction::TYPE_SHORTLIST,
            'occurred_at' => now(),
        ]);

        return back()->with('status', __('attendee.matches.saved'));
    }

    private function attendee(): Registration
    {
        /** @var Registration $registration */
        $registration = Auth::guard('attendee')->user();

        return $registration;
    }
}
