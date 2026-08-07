<?php

namespace App\Http\Controllers\Attendee;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAttendeeProfileRequest;
use App\Models\EventSession;
use App\Models\Registration;
use App\Services\QrCodeService;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * "My Next Step" — everything one attendee has done, in one place.
 *
 * The badge and QR, the sessions they picked, the days they turned up, and the
 * messages the system sent them. It is the answer to "what did I sign up for
 * again?", which is otherwise a message to the organisers.
 */
class ProfileController extends Controller
{
    public function __construct(
        private readonly TicketService $tickets,
        private readonly QrCodeService $qr,
    ) {}

    public function show(): View
    {
        $registration = $this->attendee();

        return view('attendee.profile', [
            'navKey' => null,
            'title' => __('attendee.profile.title').' — '.config('nextstep.event.name'),
            'registration' => $registration,
            'qr' => $registration->badgeIssued()
                ? $this->qr->pngDataUri($this->tickets->verifyUrl($registration))
                : null,
            'saved' => $this->savedSessions($registration),
            // The column is checked_in_at. SQLite quietly treats an unknown column
            // in ORDER BY as a string literal, so `scanned_at` here passed every
            // test and only failed on MySQL — which is what production runs.
            'checkIns' => $registration->checkIns()->orderBy('checked_in_at')->get(),
            'messages' => $registration->messages()->latest('id')->limit(20)->get(),
        ]);
    }

    /**
     * The details they can change themselves.
     *
     * A registration form is deliberately short — it is the last thing standing
     * between somebody and a badge. This is where the rest goes in, at their own
     * pace, once there is nothing riding on it.
     */
    public function edit(): View
    {
        $registration = $this->attendee();

        return view('attendee.edit', [
            'navKey' => null,
            'title' => __('attendee.edit.title').' — '.config('nextstep.event.name'),
            'registration' => $registration,
            'cities' => config('nextstep.cities'),
            'stages' => config('nextstep.education_stages'),
            'locales' => config('nextstep.locales'),
        ]);
    }

    public function update(UpdateAttendeeProfileRequest $request): RedirectResponse
    {
        $registration = $this->attendee();
        $data = $request->validated();

        if ($request->boolean('remove_photo')) {
            $this->deletePhoto($registration);
            $registration->photo_path = null;
        }

        if ($request->hasFile('photo')) {
            // Replacing means the old file has no owner and no way to be reached
            // again, so it goes rather than sitting on the disk for ever.
            $this->deletePhoto($registration);
            $registration->photo_path = $request->file('photo')->store('attendees', 'public');
        }

        $registration->fill([
            'full_name' => $data['full_name'],
            'city' => $data['city'],
            'locale' => $data['locale'],
            'email' => $data['email'] ?? $registration->email,
            'school_name' => $data['school_name'] ?? null,
            'education_stage' => $data['education_stage'] ?? null,
            'position' => $data['position'] ?? $registration->position,
            'organization' => $data['organization'] ?? $registration->organization,
        ]);

        // Left blank means "leave it alone", not "clear it".
        if (filled($data['password'] ?? null)) {
            $registration->password = $data['password'];
        }

        $registration->profile_completed_at ??= now();
        $registration->save();

        return redirect()->route('me')->with('status', __('attendee.edit.saved'));
    }

    private function deletePhoto(Registration $registration): void
    {
        if ($registration->photo_path) {
            Storage::disk('public')->delete($registration->photo_path);
        }
    }

    /** The personal agenda: what they picked, and everything they could pick. */
    public function agenda(): View
    {
        $registration = $this->attendee();

        return view('attendee.agenda', [
            'navKey' => 'agenda',
            'title' => __('attendee.agenda.title').' — '.config('nextstep.event.name'),
            'registration' => $registration,
            'saved' => $registration->savedSessions()->pluck('event_sessions.id')->all(),
            'days' => EventSession::published()
                ->forYear((int) config('nextstep.event.year'))
                ->where('bookable', true)
                ->with(['hall', 'speakers'])
                ->orderBy('day')->orderBy('starts_at')
                ->get()
                ->groupBy('day'),
        ]);
    }

    /**
     * Add or remove one session.
     *
     * Deliberately open to guests: a visitor who has not registered yet still gets
     * to press the button, and their choice is held in the session while they
     * register. Losing that click is what made the agenda feel disconnected from
     * registration in the first place.
     */
    public function toggle(Request $request, EventSession $session): RedirectResponse
    {
        if (! Auth::guard('attendee')->check()) {
            $request->session()->put('attendee.pending_session', $session->id);

            return redirect()->route('attendee.join')
                ->with('status', __('attendee.agenda.sign_in_to_save', ['session' => $session->t('title')]));
        }

        $registration = $this->attendee();

        if ($registration->savedSessions()->where('event_sessions.id', $session->id)->exists()) {
            $registration->savedSessions()->detach($session->id);
            $message = __('attendee.agenda.removed');
        } else {
            $registration->savedSessions()->attach($session->id);
            $message = __('attendee.agenda.added');
        }

        return back()->with('status', $message);
    }

    /**
     * The fork a guest hits when they press "add to my agenda": register, or sign
     * in if they already have a badge. Both routes end back at the agenda.
     */
    public function join(): View
    {
        return view('attendee.join', [
            'navKey' => null,
            'title' => __('attendee.join.title').' — '.config('nextstep.event.name'),
            'pendingSession' => ($id = session('attendee.pending_session'))
                ? EventSession::find($id)
                : null,
        ]);
    }

    private function attendee(): Registration
    {
        /** @var Registration $registration */
        $registration = Auth::guard('attendee')->user();

        return $registration->load('savedSessions');
    }

    /** @return Collection<int, Collection<int, EventSession>> */
    private function savedSessions(Registration $registration)
    {
        return $registration->savedSessions()
            ->with('hall')
            ->orderBy('day')->orderBy('starts_at')
            ->get()
            ->groupBy('day');
    }
}
