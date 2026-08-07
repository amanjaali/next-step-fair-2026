<?php

namespace App\Http\Controllers;

use App\Models\Speaker;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpeakerController extends Controller
{
    public function index(Request $request): View
    {
        $year = (int) $request->integer('year', 2026);
        $track = $request->string('track')->toString();
        $type = $request->string('type')->toString();

        $query = Speaker::published()->forYear($year)->with('sessions')->orderBy('sort');

        if ($track && $track !== 'all') {
            $query->where('track', $track);
        }

        if ($type && $type !== 'all') {
            $type === 'international'
                ? $query->where('country', '!=', 'IQ')
                : $query->where('speaker_type', $type);
        }

        $speakers = $query->get();

        // "2026 speakers to be announced" degrades to the previous year's list
        // rather than rendering an empty page.
        $fallbackYear = null;
        if ($speakers->isEmpty() && $year === 2026) {
            $fallbackYear = Speaker::published()->max('year');
            if ($fallbackYear) {
                $speakers = Speaker::published()->forYear($fallbackYear)->orderBy('sort')->get();
            }
        }

        return view('speakers.index', [
            'navKey' => 'speakers',
            'title' => __('site.pages.speakers.title').' — '.config('nextstep.event.name'),
            'speakers' => $speakers,
            'total' => Speaker::published()->forYear($year)->count(),
            'activeTrack' => $track ?: 'all',
            'activeType' => $type ?: 'all',
            'fallbackYear' => $fallbackYear,
        ]);
    }

    public function show(Speaker $speaker): View
    {
        abort_unless($speaker->published, 404);

        $speaker->load(['sessions.hall']);

        return view('speakers.show', [
            'navKey' => 'speakers',
            'title' => $speaker->t('name').' — '.config('nextstep.event.name'),
            'description' => strip_tags($speaker->t('role').', '.$speaker->t('organization')),
            'speaker' => $speaker,
            'related' => Speaker::published()->forYear($speaker->year)
                ->where('id', '!=', $speaker->id)->orderBy('sort')->take(4)->get(),
        ]);
    }
}
