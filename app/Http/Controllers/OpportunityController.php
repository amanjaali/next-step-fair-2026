<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * The opportunities board.
 *
 * Scholarships, offers and places that partners bring to Next Step. It is the
 * concrete answer to "why make an account?" — so it is shown to people who have
 * one, and everyone else is shown the one sentence that explains what they are
 * missing rather than a teasing list they cannot use.
 */
class OpportunityController extends Controller
{
    public function index(): View
    {
        $attendee = $this->attendee();

        $opportunities = $attendee
            ? Opportunity::live()->for($attendee)->ranked()->with('organization')->get()
            : collect();

        return view('opportunities.index', [
            'navKey' => 'opportunities',
            'title' => __('opportunities.title').' — '.config('nextstep.event.name'),
            'attendee' => $attendee,
            'opportunities' => $opportunities,
            'closingSoon' => $opportunities->filter->isClosingSoon(),
        ]);
    }

    public function show(string $slug): View
    {
        $attendee = $this->attendee();

        $opportunity = Opportunity::live()->where('slug', $slug)->with('organization')->firstOrFail();

        // Counted for the partner, who is entitled to know whether the placement
        // was worth anything. No personal data goes with it.
        $opportunity->incrementQuietly('view_count');

        return view('opportunities.show', [
            'navKey' => 'opportunities',
            'title' => $opportunity->t('title').' — '.config('nextstep.event.name'),
            'attendee' => $attendee,
            'opportunity' => $opportunity,
            'related' => Opportunity::live()->for($attendee)->ranked()
                ->whereKeyNot($opportunity->getKey())->take(3)->get(),
        ]);
    }

    /**
     * Sends them on, and records that it happened.
     *
     * Going out through us rather than straight to the partner is what lets us
     * tell a university how many Next Step students actually reached their form.
     */
    public function go(string $slug): RedirectResponse
    {
        $opportunity = Opportunity::live()->where('slug', $slug)->firstOrFail();

        abort_if(blank($opportunity->action_url), 404);

        $opportunity->incrementQuietly('follow_count');

        return redirect()->away($opportunity->action_url);
    }

    private function attendee(): ?Registration
    {
        $attendee = Auth::guard('attendee')->user();

        return $attendee instanceof Registration ? $attendee : null;
    }
}
