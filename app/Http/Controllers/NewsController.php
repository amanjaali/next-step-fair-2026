<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->string('category')->toString();
        $year = $request->string('year')->toString();

        $query = Post::published()->news()->with('category')->latest('published_at');

        if ($category && $category !== 'all') {
            $query->whereHas('category', fn ($q) => $q->where('slug', $category));
        }

        if ($year && $year !== 'all') {
            $query->where('year', (int) $year);
        }

        $pinned = Post::published()->news()->with('category')->where('pinned', true)
            ->latest('published_at')->first();

        $posts = $query->when($pinned && ! $category && ! $year, fn ($q) => $q->where('id', '!=', $pinned->id))
            ->paginate(9)->withQueryString();

        return view('news.index', [
            'navKey' => 'news',
            'title' => __('site.pages.news.title').' — '.config('nextstep.event.name'),
            'pinned' => ($category || $year) ? null : $pinned,
            'posts' => $posts,
            'categories' => Category::where('type', 'news')->orderBy('sort')->get(),
            'years' => Post::published()->news()->distinct()->orderByDesc('year')->pluck('year'),
            'activeCategory' => $category ?: 'all',
            'activeYear' => $year ?: 'all',
        ]);
    }

    public function show(Post $post): View
    {
        abort_unless($post->type === Post::TYPE_NEWS && $post->status === 'published', 404);

        $post->increment('views');

        return view('news.show', [
            'navKey' => 'news',
            'title' => $post->t('title').' — '.config('nextstep.event.name'),
            'description' => strip_tags($post->t('excerpt')),
            'ogType' => 'article',
            'ogImage' => $post->coverUrl(),
            'post' => $post->load('category'),
            'related' => Post::published()->news()->with('category')
                ->where('id', '!=', $post->id)->latest('published_at')->take(3)->get(),
        ]);
    }
}
