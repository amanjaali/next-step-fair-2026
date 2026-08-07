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
    /** Day tabs, plus filters by track, type, topic and language. */
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

        if ($track && $track !== 'all') {
            $query->where('track', $track);
        }

        $sessions = $query->get();

        return view('agenda.index', [
            'navKey' => 'agenda',
            'title' => __('site.pages.agenda.title').' — '.config('nextstep.event.name'),
            'day' => $day,
            'sessions' => $sessions,
            'types' => EventSession::published()->forYear(2026)->distinct()->orderBy('type')->pluck('type'),
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
