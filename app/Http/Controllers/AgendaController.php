<?php

namespace App\Http\Controllers;

use App\Models\EventSession;
use App\Services\CalendarService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AgendaController extends Controller
{
    /**
     * The day's programme, split by track rather than run together.
     *
     * Day 1 carries both programmes: a policy conference for invited delegates
     * and the expo that anybody can walk into. Listed as one stream they are
     * indistinguishable — a student reads down the page and finds a closed
     * roundtable for ministry delegations sitting between two of their own
     * workshops. Two labelled sections, each saying who it is for, is the whole
     * fix; days 2 and 3 have only the expo, so they get no heading at all.
     */
    public function index(Request $request): View
    {
        $day = $this->day($request);
        $type = $request->string('type')->toString();
        $track = $request->string('track')->toString();

        $query = EventSession::published()->forYear(2026)->forDay($day)
            ->with(['hall', 'speakers'])
            ->orderBy('starts_at');

        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }

        // Still honoured as a deep link, though the page no longer offers it as a
        // filter — the sections say what the chips used to.
        if ($track && $track !== 'all') {
            $query->where('track', $track);
        }

        $sessions = $query->get();

        // Conference first: on Day 1 it opens the event, and it is the half a
        // visitor is most likely to have arrived on this page not expecting.
        $groups = $sessions->groupBy('track')->sortKeysUsing(
            fn ($a, $b) => array_search($a, ['conference', 'fair'], true) <=> array_search($b, ['conference', 'fair'], true)
        );

        return view('agenda.index', [
            'navKey' => 'agenda',
            'title' => __('site.pages.agenda.title').' — '.config('nextstep.event.name'),
            'day' => $day,
            'sessions' => $sessions,
            'groups' => $groups,
            // Only worth heading the sections when there is more than one of them.
            'showTrackHeadings' => $groups->count() > 1,
            // Value => label, so the chips read in the visitor's language while the
            // links still carry the value the column actually holds.
            'typeOptions' => collect(['all' => __('site.common.all')])->merge(
                EventSession::published()->forYear(2026)->forDay($day)
                    ->distinct()->orderBy('type')->pluck('type')
                    ->mapWithKeys(fn (string $t) => [$t => EventSession::labelForType($t)])
            )->all(),
            'activeType' => $type ?: 'all',
            'activeTrack' => $track ?: 'all',
            'totalCount' => EventSession::published()->forYear(2026)->where('bookable', true)->count(),
        ]);
    }

    /** The seminars, panels and workshops catalogue, across all three days. */
    public function seminars(Request $request): View
    {
        $topic = $request->string('topic')->toString();

        $sessions = EventSession::published()->forYear(2026)
            ->whereIn('type', ['Seminar', 'Panel', 'Workshop', 'Roundtable'])
            ->when($topic, fn ($q) => $q->where('topic', $topic))
            ->with(['hall', 'speakers'])
            ->orderBy('day')->orderBy('starts_at')
            ->get()
            ->groupBy('day');

        return view('agenda.seminars', [
            'navKey' => 'agenda',
            'title' => __('site.footer.links.seminars').' — '.config('nextstep.event.name'),
            'sessionsByDay' => $sessions,
        ]);
    }

    public function ics(Request $request, CalendarService $calendar): StreamedResponse
    {
        $sessions = EventSession::published()->forYear(2026)->with('hall')->orderBy('day')->orderBy('starts_at')->get();

        return $calendar->downloadAgenda($sessions);
    }

    public function pdf(Request $request): Response
    {
        $sessionsByDay = EventSession::published()->forYear(2026)
            ->with(['hall', 'speakers'])->orderBy('day')->orderBy('starts_at')->get()->groupBy('day');

        return Pdf::loadView('agenda.pdf', [
            'sessionsByDay' => $sessionsByDay,
            'locale' => app()->getLocale(),
        ])->setPaper('a4')->download('next-step-fair-2026-agenda.pdf');
    }

    private function day(Request $request): int
    {
        $day = (int) $request->integer('day', 1);

        return in_array($day, array_keys(config('nextstep.event.days')), true) ? $day : 1;
    }
}
