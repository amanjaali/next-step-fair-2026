<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The blog is distinct from news in voice and purpose: news is organisational,
 * the blog is student-facing guidance.
 */
class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->string('category')->toString();

        $posts = Post::published()->blog()->with('category')
            ->when($category && $category !== 'all',
                fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $category)))
            ->latest('published_at')
            ->paginate(9)->withQueryString();

        return view('blog.index', [
            'navKey' => 'news',
            'title' => __('site.pages.blog.title').' — '.config('nextstep.event.name'),
            'posts' => $posts,
            'categories' => Category::where('type', 'blog')->orderBy('sort')->get(),
            'activeCategory' => $category ?: 'all',
        ]);
    }

    public function show(Post $post): View
    {
        abort_unless($post->type === Post::TYPE_BLOG && $post->status === 'published', 404);

        $post->increment('views');

        return view('blog.show', [
            'navKey' => 'news',
            'title' => $post->t('title').' — '.config('nextstep.event.name'),
            'description' => strip_tags($post->t('excerpt')),
            'ogType' => 'article',
            'post' => $post->load('category'),
            'toc' => $post->tableOfContents(),
            'related' => Post::published()->blog()->with('category')
                ->where('id', '!=', $post->id)->latest('published_at')->take(3)->get(),
        ]);
    }
}
