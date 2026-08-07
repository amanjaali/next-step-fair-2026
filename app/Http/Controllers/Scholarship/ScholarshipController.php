<?php

namespace App\Http\Controllers\Scholarship;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * The public face of the National Scholarship Program.
 *
 * Everything here is readable without an account: what the award is, who can
 * have it, which universities and departments hold seats, how the committee
 * scores, and who sits on it. A student should be able to decide whether this is
 * for them before being asked to sign into anything.
 */
class ScholarshipController extends Controller
{
    public function home(): View
    {
        return $this->page('scholarship.home', 'home', [
            'regions' => config('scholarship.regions'),
            'universities' => config('scholarship.universities'),
        ]);
    }

    public function about(): View
    {
        return $this->page('scholarship.about', 'about');
    }

    public function guidelines(): View
    {
        return $this->page('scholarship.guidelines', 'guidelines', [
            'rubric' => config('scholarship.rubric'),
            'documents' => config('scholarship.documents'),
            'timeline' => config('scholarship.timeline'),
        ]);
    }

    public function universities(): View
    {
        return $this->page('scholarship.universities', 'universities', [
            'universities' => config('scholarship.universities'),
        ]);
    }

    public function university(string $slug): View
    {
        $universities = collect(config('scholarship.universities'));
        $university = $universities->firstWhere('slug', $slug);

        abort_if($university === null, 404);

        return $this->page('scholarship.university', 'universities', [
            'university' => $university,
        ]);
    }

    public function region(string $code): View
    {
        $code = strtoupper($code);
        $region = config("scholarship.regions.{$code}");

        abort_if($region === null, 404);

        // Which departments a student from here could actually take a seat in.
        $universities = collect(config('scholarship.universities'))
            ->filter(fn ($u) => $u['city'] !== null)
            ->values()
            ->all();

        return $this->page('scholarship.region', 'home', [
            'code' => $code,
            'region' => $region,
            'universities' => $universities,
        ]);
    }

    public function committee(): View
    {
        return $this->page('scholarship.committee', 'guidelines', [
            'members' => collect(config('scholarship.committee'))->groupBy('side'),
            'rubric' => config('scholarship.rubric'),
        ]);
    }

    public function recipients(): View
    {
        return $this->page('scholarship.recipients', 'recipients', [
            'regions' => config('scholarship.regions'),
        ]);
    }

    /**
     * Every scholarship page shares the same shell, and every one of them needs to
     * know who is reading it — the call to action is different for a signed-in
     * student, a signed-in parent, and a stranger.
     */
    private function page(string $view, string $navKey, array $data = []): View
    {
        /** @var Registration|null $attendee */
        $attendee = Auth::guard('attendee')->user();

        return view($view, array_merge([
            'navKey' => null,
            'scholarshipNav' => $navKey,
            'title' => __("scholarship.$navKey.title").' — '.__('scholarship.name'),
            'attendee' => $attendee,
            'application' => $attendee?->scholarshipApplication(),
            'cycle' => config('scholarship.cycle'),
        ], $data));
    }
}
