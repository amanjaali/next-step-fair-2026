<?php

namespace App\Http\Controllers;

use App\Models\MediaAlbum;
use App\Models\MediaItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(Request $request): View
    {
        return view('media.index', [
            'navKey' => 'media',
            'title' => __('site.pages.media.title').' — '.config('nextstep.event.name'),
            'albums' => MediaAlbum::where('published', true)->withCount('items')
                ->orderByDesc('year')->orderBy('sort')->take(6)->get(),
            'videos' => MediaItem::whereIn('type', ['video', 'reel'])->orderByDesc('year')->take(6)->get(),
            'years' => MediaAlbum::where('published', true)->distinct()->orderByDesc('year')->pluck('year'),
        ]);
    }

    public function photos(Request $request): View
    {
        $year = $request->string('year')->toString();
        $category = $request->string('category')->toString();

        $albums = MediaAlbum::where('published', true)->withCount('items')
            ->when($year && $year !== 'all', fn ($q) => $q->where('year', (int) $year))
            ->when($category && $category !== 'all', fn ($q) => $q->where('category', $category))
            ->orderByDesc('year')->orderBy('day')->orderBy('sort')
            ->get();

        return view('media.photos', [
            'navKey' => 'media',
            'title' => __('site.pages.media.photos').' — '.config('nextstep.event.name'),
            'albums' => $albums,
            'years' => MediaAlbum::where('published', true)->distinct()->orderByDesc('year')->pluck('year'),
            'categories' => MediaAlbum::where('published', true)->distinct()->orderBy('category')->pluck('category'),
            'activeYear' => $year ?: 'all',
            'activeCategory' => $category ?: 'all',
        ]);
    }

    public function album(MediaAlbum $album): View
    {
        abort_unless($album->published, 404);

        return view('media.album', [
            'navKey' => 'media',
            'title' => $album->t('title').' — '.config('nextstep.event.name'),
            'album' => $album->load('items'),
        ]);
    }

    public function videos(Request $request): View
    {
        $year = $request->string('year')->toString();

        $videos = MediaItem::whereIn('type', ['video', 'reel'])
            ->when($year && $year !== 'all', fn ($q) => $q->where('year', (int) $year))
            ->orderByDesc('year')->orderBy('sort')->get()->groupBy('type');

        return view('media.videos', [
            'navKey' => 'media',
            'title' => __('site.pages.media.videos').' — '.config('nextstep.event.name'),
            'videos' => $videos->get('video', collect()),
            'reels' => $videos->get('reel', collect()),
            'years' => MediaItem::whereIn('type', ['video', 'reel'])->distinct()->orderByDesc('year')->pluck('year'),
            'activeYear' => $year ?: 'all',
        ]);
    }
}
