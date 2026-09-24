<?php

namespace App\Http\Controllers\Scholarship;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\ScholarshipApplication;
use Closure;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
            'ineligibleReason' => $attendee?->scholarshipIneligibilityReason(),
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

        return redirect()->route('scholarship.eligibility');
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
            'universities' => ns_scholarship_universities(),
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
                'third_choice_university' => ['nullable', 'string', 'max:190'],
                'third_choice_department' => ['nullable', 'string', 'max:120'],
                'fourth_choice_university' => ['nullable', 'string', 'max:190'],
                'fourth_choice_department' => ['nullable', 'string', 'max:120'],
                'fifth_choice_university' => ['nullable', 'string', 'max:190'],
                'fifth_choice_department' => ['nullable', 'string', 'max:120'],
            ],
            // The word counts here are real word counts, not Laravel's string
            // min/max (which measure characters — a mismatch that let a
            // statement well under the intended length through as long as it
            // was long enough in characters). The 8000/10000-character caps
            // stay as a plain payload-size ceiling underneath the word check.
            3 => [
                'statement' => [
                    'required', 'string', 'max:8000',
                    fn (string $attribute, $value, Closure $fail) => ns_word_count($value) < $words['min']
                        && $fail(__('scholarship.apply.errors.statement_short')),
                    fn (string $attribute, $value, Closure $fail) => ns_word_count($value) > $words['max']
                        && $fail(__('scholarship.apply.errors.statement_long')),
                ],
                'proposal' => [
                    'required', 'string', 'max:10000',
                    fn (string $attribute, $value, Closure $fail) => ns_word_count($value) < $proposalWords['min']
                        && $fail(__('scholarship.apply.errors.proposal_short')),
                    fn (string $attribute, $value, Closure $fail) => ns_word_count($value) > $proposalWords['max']
                        && $fail(__('scholarship.apply.errors.proposal_long')),
                ],
            ],
            default => [],
        };

        // A choice with something to read cannot be saved without the box
        // checked — but only if there is something to read: a university
        // nobody has written requirements for asks for no acknowledgement.
        // Applies to every filled slot, not just the required first one.
        if ($step === 2) {
            foreach (ScholarshipApplication::CHOICE_SLOTS as $slot) {
                $university = $request->input("{$slot}_choice_university");
                $department = $request->input("{$slot}_choice_department");

                if (blank($university)) {
                    continue;
                }

                if ($this->requirementsFor($university, $department) !== null) {
                    $rules["{$slot}_choice_ack"] = ['accepted'];
                }

                // A university running its own application form alongside
                // ours — whole university, so this applies no matter which of
                // its departments was picked.
                if ($this->requiresExternalForm($university)) {
                    $rules["{$slot}_choice_external_form_ack"] = ['accepted'];
                }

                // A department that hands out its own paper form cannot be
                // applied to without it. One already on file counts — they
                // are coming back to a saved step, not starting again — but
                // only while the choice is the one it was uploaded for: a
                // form belongs to the department that issued it, and must
                // not follow a student to another.
                if ($this->requiresForm($university, $department)) {
                    $held = $application->documents["{$slot}_choice_form"] ?? null;

                    $rules["{$slot}_choice_form"] = [
                        Rule::requiredIf(blank($held) || $this->choiceChanged($application, $request, $slot)),
                        'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:8192',
                    ];
                }
            }
        }

        $messages = [
            'region_code.required' => __('scholarship.apply.errors.region'),
            'district.required' => __('scholarship.apply.errors.district'),
            'exam_average.required' => __('scholarship.apply.errors.average'),
            'statement.required' => __('scholarship.apply.errors.statement'),
            'proposal.required' => __('scholarship.apply.errors.proposal'),
        ];

        foreach (ScholarshipApplication::CHOICE_SLOTS as $slot) {
            $messages["{$slot}_choice_ack.accepted"] = __("scholarship.apply.errors.{$slot}_choice_ack");
            $messages["{$slot}_choice_external_form_ack.accepted"] = __('scholarship.apply.errors.external_form_ack');
            $messages["{$slot}_choice_form.required"] = __('scholarship.apply.errors.choice_form');
            $messages["{$slot}_choice_form.mimes"] = __('scholarship.apply.errors.choice_form_type');
            $messages["{$slot}_choice_form.max"] = __('scholarship.apply.errors.choice_form_size');
        }

        $validator = validator($request->all(), $rules, $messages);

        // The same seat cannot be listed twice under different choices — a
        // student picking the same university's same department as both
        // their first and third choice is not five real preferences, it is
        // four. A different department at the same university is fine; it is
        // a different seat.
        if ($step === 2) {
            $validator->after(function ($validator) use ($request) {
                $seen = [];

                foreach (ScholarshipApplication::CHOICE_SLOTS as $slot) {
                    $university = $request->input("{$slot}_choice_university");
                    $department = $request->input("{$slot}_choice_department");

                    if (blank($university) || blank($department)) {
                        continue;
                    }

                    $seat = mb_strtolower($university).'|'.mb_strtolower($department);

                    if (isset($seen[$seat])) {
                        $validator->errors()->add(
                            "{$slot}_choice_department",
                            __('scholarship.apply.errors.duplicate_choice'),
                        );
                    }

                    $seen[$seat] = true;
                }
            });
        }

        $data = $validator->validate();

        if ($step === 2) {
            // The ack checkboxes are not real columns — swap them for a
            // timestamped snapshot of exactly what was shown and agreed to,
            // so a requirements text edited later cannot rewrite history.
            foreach (ScholarshipApplication::CHOICE_SLOTS as $slot) {
                unset($data["{$slot}_choice_ack"], $data["{$slot}_choice_external_form_ack"]);
            }

            $documents = $application->documents ?? [];

            foreach (ScholarshipApplication::CHOICE_SLOTS as $slot) {
                // Moving to another department leaves the old department's form
                // behind rather than passing it off as this one's.
                if ($this->choiceChanged($application, $request, $slot) && filled($documents["{$slot}_choice_form"] ?? null)) {
                    Storage::disk('local')->delete($documents["{$slot}_choice_form"]);
                    unset($documents["{$slot}_choice_form"]);
                }

                $text = $this->requirementsFor($data["{$slot}_choice_university"] ?? null, $data["{$slot}_choice_department"] ?? null);
                $data["{$slot}_choice_requirements_ack_at"] = $text !== null ? now() : null;
                $data["{$slot}_choice_requirements_snapshot"] = $text;

                // Same reasoning, for the university's own form: the URL they
                // were actually sent to, not just a flag, so a partner
                // changing their link later does not rewrite what this
                // applicant agreed to.
                $url = $this->requiresExternalForm($data["{$slot}_choice_university"] ?? null)
                    ? $this->externalFormUrl($data["{$slot}_choice_university"] ?? null)
                    : null;
                $data["{$slot}_choice_external_form_ack_at"] = $url !== null ? now() : null;
                $data["{$slot}_choice_external_form_url_ack"] = $url;

                // Somebody's paperwork, so it goes on the private disk and only
                // its path is kept. Replacing one drops the old file rather than
                // leaving a student's document lying about unreferenced.
                if ($file = $request->file("{$slot}_choice_form")) {
                    $key = "{$slot}_choice_form";

                    if (filled($documents[$key] ?? null)) {
                        Storage::disk('local')->delete($documents[$key]);
                    }

                    $documents[$key] = $file->store('scholarship/forms', 'local');
                }

                unset($data["{$slot}_choice_form"]);
            }

            $data['documents'] = $documents;
        }

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

    /**
     * What a student must read for a given university/department choice, or
     * null when nobody has written anything for it — in which case there is
     * nothing to acknowledge and no box to check.
     */
    private function requirementsFor(?string $universityName, ?string $departmentName): ?string
    {
        if (blank($universityName)) {
            return null;
        }

        [$university, $department] = $this->choice($universityName, $departmentName);

        if ($university === null) {
            return null;
        }

        // Both halves of what the form put in front of them: the university's
        // own text and the chosen department's. Snapshotting one would record
        // an acknowledgement of something they were only shown half of.
        $text = collect([$university['requirements'] ?? null, $department['requirements'] ?? null])
            ->filter(fn (?string $part) => filled($part))
            ->implode("\n\n");

        return filled($text) ? $text : null;
    }

    /** Whether this slot now points at a different university or department. */
    private function choiceChanged(ScholarshipApplication $application, Request $request, string $slot): bool
    {
        return $application->{"{$slot}_choice_university"} !== $request->input("{$slot}_choice_university")
            || $application->{"{$slot}_choice_department"} !== $request->input("{$slot}_choice_department");
    }

    /** Whether this department hands out a paper form that has to come back. */
    private function requiresForm(?string $universityName, ?string $departmentName): bool
    {
        [, $department] = $this->choice($universityName, $departmentName);

        return (bool) ($department['requires_form'] ?? false);
    }

    /**
     * Whether this university also runs its own application form — whole
     * university, not per department, the same as the requirements text it
     * sits beside.
     */
    private function requiresExternalForm(?string $universityName): bool
    {
        [$university] = $this->choice($universityName, null);

        return (bool) ($university['requires_external_form'] ?? false);
    }

    private function externalFormUrl(?string $universityName): ?string
    {
        [$university] = $this->choice($universityName, null);

        return $university['external_form_url'] ?? null;
    }

    /**
     * The chosen university and department, exactly as the form listed them.
     *
     * @return array{0: array<string, mixed>|null, 1: array<string, mixed>|null}
     */
    private function choice(?string $universityName, ?string $departmentName): array
    {
        if (blank($universityName)) {
            return [null, null];
        }

        // Looked up several times while saving one step; the list behind it
        // does not change in between.
        $university = collect(once(fn () => ns_scholarship_universities()))
            ->firstWhere('name', $universityName);

        if ($university === null) {
            return [null, null];
        }

        return [
            $university,
            collect($university['departments'] ?? [])->firstWhere('name', $departmentName),
        ];
    }

    private function attendee(): ?Registration
    {
        $attendee = Auth::guard('attendee')->user();

        return $attendee instanceof Registration ? $attendee : null;
    }

    /** Everything past the gate needs a verified student account. */
    /**
     * Nobody meets a 403 here.
     *
     * A bare "403 Forbidden" was what a signed-out visitor got for opening the
     * eligibility link — a white page with no explanation and nowhere to go, on
     * a URL people share with each other. The gate page already lists the three
     * things that have to be true and how far along you are on each, so that is
     * where an unmet requirement belongs.
     */
    private function requireStudent(): Registration
    {
        $attendee = $this->attendee();

        if (! $attendee?->canApplyForScholarship()) {
            throw new HttpResponseException(
                redirect()->route('scholarship.apply')->with('gate_blocked', true)
            );
        }

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
