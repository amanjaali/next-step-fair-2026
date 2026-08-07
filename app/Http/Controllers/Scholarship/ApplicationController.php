<?php

namespace App\Http\Controllers\Scholarship;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\ScholarshipApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Applying: the gate, the check, the form, and the status afterwards.
 *
 * Three things have to be true before the form opens — a Next Step ID, a passed
 * eligibility check, and a student who is actually finishing school. The gate
 * shows all three at once with their state, so nobody is bounced between pages
 * discovering one requirement at a time.
 *
 * Everything saves as it goes. A student on a borrowed computer can stop at any
 * point and pick it up later on a phone.
 */
class ApplicationController extends Controller
{
    /** The three things that must be true, and where they stand. */
    public function gate(): View
    {
        $attendee = $this->attendee();
        $application = $attendee?->scholarshipApplication();

        return view('scholarship.gate', [
            'navKey' => null,
            'scholarshipNav' => 'apply',
            'title' => __('scholarship.apply.title').' — '.__('scholarship.name'),
            'attendee' => $attendee,
            'application' => $application,
            'cycle' => config('scholarship.cycle'),
            'hasAccount' => $attendee?->isStudentAccount() ?? false,
            'isEligibleStudent' => $attendee?->canApplyForScholarship() ?? false,
            'checkPassed' => $application?->hasPassedEligibility() ?? false,
        ]);
    }

    /* -------------------------------------------------------- eligibility -- */

    public function eligibility(): View
    {
        $attendee = $this->requireStudent();
        $application = $this->application($attendee);

        return view('scholarship.eligibility', [
            'navKey' => null,
            'scholarshipNav' => 'apply',
            'title' => __('scholarship.eligibility.title').' — '.__('scholarship.name'),
            'attendee' => $attendee,
            'application' => $application,
            'cycle' => config('scholarship.cycle'),
            'questions' => config('scholarship.eligibility'),
            'answers' => $application->eligibility ?? [],
            'verdict' => $application->eligibilityVerdict(),
        ]);
    }

    public function saveEligibility(Request $request): RedirectResponse
    {
        $attendee = $this->requireStudent();
        $application = $this->application($attendee);

        $rules = [];
        foreach (config('scholarship.eligibility') as $question) {
            $rules["answers.{$question['id']}"] = ['required', Rule::in($question['options'])];
        }

        $data = $request->validate($rules, [
            'answers.*.required' => __('scholarship.eligibility.errors.answer_all'),
        ]);

        $application->eligibility = $data['answers'];
        $application->eligibility_passed_at = $application->hasPassedEligibility() ? now() : null;
        $application->save();

        return redirect()->route('scholarship.eligibility')
            ->with('checked', true);
    }

    /* --------------------------------------------------------------- form -- */

    public function form(Request $request): View|RedirectResponse
    {
        $attendee = $this->requireStudent();
        $application = $this->application($attendee);

        // The form is the third gate, not the first: send them back to whichever
        // one they have not cleared rather than showing an empty form.
        if (! $application->hasPassedEligibility()) {
            return redirect()->route('scholarship.apply');
        }

        if ($application->isSubmitted()) {
            return redirect()->route('scholarship.status');
        }

        $step = min(max((int) $request->query('step', $application->step), 1), 4);

        return view('scholarship.apply', [
            'navKey' => null,
            'scholarshipNav' => 'apply',
            'title' => __('scholarship.apply.title').' — '.__('scholarship.name'),
            'attendee' => $attendee,
            'application' => $application,
            'cycle' => config('scholarship.cycle'),
            'step' => $step,
            'regions' => config('scholarship.regions'),
            'universities' => config('scholarship.universities'),
        ]);
    }

    /**
     * Saves one step and moves on.
     *
     * Each step validates only its own fields, so a half-finished application is
     * always saveable and never lost to a rule about a question three screens
     * away that they have not reached yet.
     */
    public function save(Request $request): RedirectResponse
    {
        $attendee = $this->requireStudent();
        $application = $this->application($attendee);

        abort_if($application->isSubmitted(), 403);

        $step = (int) $request->input('step', 1);
        $words = config('scholarship.statement_words');
        $proposalWords = config('scholarship.proposal_words');

        $rules = match ($step) {
            1 => [
                'region_code' => ['required', Rule::in(array_keys(config('scholarship.regions')))],
                'district' => ['required', 'string', 'max:60'],
            ],
            2 => [
                'exam_status' => ['required', Rule::in(['published', 'pending'])],
                'exam_average' => [
                    Rule::requiredIf($request->input('exam_status') === 'published'),
                    'nullable', 'numeric', 'min:0', 'max:100',
                ],
                'stream' => ['required', 'string', 'max:30'],
                'school_name' => ['required', 'string', 'max:190'],
                'first_choice_university' => ['required', 'string', 'max:190'],
                'first_choice_department' => ['required', 'string', 'max:120'],
                'second_choice_university' => ['nullable', 'string', 'max:190'],
                'second_choice_department' => ['nullable', 'string', 'max:120'],
            ],
            3 => [
                'statement' => ['required', 'string', "min:{$words['min']}", 'max:8000'],
                'proposal' => ['required', 'string', "min:{$proposalWords['min']}", 'max:10000'],
            ],
            default => [],
        };

        $data = $request->validate($rules, [
            'region_code.required' => __('scholarship.apply.errors.region'),
            'district.required' => __('scholarship.apply.errors.district'),
            'exam_average.required' => __('scholarship.apply.errors.average'),
            'statement.required' => __('scholarship.apply.errors.statement'),
            'statement.min' => __('scholarship.apply.errors.statement_short'),
            'proposal.required' => __('scholarship.apply.errors.proposal'),
            'proposal.min' => __('scholarship.apply.errors.proposal_short'),
        ]);

        $application->fill($data);
        $application->step = min($step + 1, 4);
        $application->save();

        return redirect()->route('scholarship.apply.form', ['step' => $application->step]);
    }

    /**
     * Submit, and stop being editable.
     *
     * The regional quota is fixed at this moment: it is decided by where grade 12
     * was completed, and letting it move afterwards would let somebody shop for a
     * quota with fewer applicants.
     */
    public function submit(Request $request): RedirectResponse
    {
        $attendee = $this->requireStudent();
        $application = $this->application($attendee);

        abort_if($application->isSubmitted(), 403);

        if (! $application->hasPassedEligibility() || $application->completeness() < 75) {
            return redirect()->route('scholarship.apply.form')
                ->withErrors(['submit' => __('scholarship.apply.errors.incomplete')]);
        }

        $request->validate(['confirm' => ['accepted']], [
            'confirm.accepted' => __('scholarship.apply.errors.confirm'),
        ]);

        $application->forceFill([
            'status' => ScholarshipApplication::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ])->save();

        return redirect()->route('scholarship.status')
            ->with('status', __('scholarship.status.just_submitted'));
    }

    /** Where the application stands, and what happens next. */
    public function status(): View|RedirectResponse
    {
        $attendee = $this->requireStudent();
        $application = $attendee->scholarshipApplication();

        if (! $application) {
            return redirect()->route('scholarship.apply');
        }

        return view('scholarship.status', [
            'navKey' => null,
            'scholarshipNav' => 'status',
            'title' => __('scholarship.status.title').' — '.__('scholarship.name'),
            'attendee' => $attendee,
            'application' => $application,
            'cycle' => config('scholarship.cycle'),
            'rubric' => config('scholarship.rubric'),
        ]);
    }

    /* ------------------------------------------------------------ helpers -- */

    private function attendee(): ?Registration
    {
        $attendee = Auth::guard('attendee')->user();

        return $attendee instanceof Registration ? $attendee : null;
    }

    /** Everything past the gate needs a verified student account. */
    private function requireStudent(): Registration
    {
        $attendee = $this->attendee();

        abort_unless($attendee?->canApplyForScholarship(), 403);

        return $attendee;
    }

    private function application(Registration $attendee): ScholarshipApplication
    {
        return $attendee->scholarshipApplications()->firstOrCreate(
            ['cycle' => config('scholarship.cycle')],
            ['status' => ScholarshipApplication::STATUS_DRAFT],
        );
    }
}
