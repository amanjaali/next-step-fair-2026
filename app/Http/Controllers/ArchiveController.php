<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Models\MediaAlbum;
use Illuminate\View\View;

/**
 * Past editions. The year is a route parameter, so 2027 needs a row in the
 * editions table and no code change at all.
 */
class ArchiveController extends Controller
{
    public function index(): View
    {
        $editions = Edition::where('published', true)->orderByDesc('year')->get();

        return view('archive.index', [
            'navKey' => 'archive',
            'title' => __('site.pages.archive.title').' — '.config('nextstep.event.name'),
            'editions' => $editions,
        ]);
    }

    public function show(string $year): View
    {
        $year = (int) $year;
        $edition = Edition::where('published', true)->where('year', $year)->firstOrFail();

        return view('archive.show', [
            'navKey' => 'archive',
            'title' => $edition->year.' — '.config('nextstep.event.short_name'),
            'description' => strip_tags($edition->t('summary')),
            'edition' => $edition,
            'years' => Edition::where('published', true)->orderByDesc('year')->pluck('year'),
            'albums' => MediaAlbum::where('published', true)->where('year', $year)
                ->withCount('items')->orderBy('sort')->take(4)->get(),
        ]);
    }
}
