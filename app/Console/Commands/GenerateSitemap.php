<?php

namespace App\Console\Commands;

use App\Models\Edition;
use App\Models\MediaAlbum;
use App\Models\Post;
use App\Models\Speaker;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

/** XML sitemap covering all three languages. */
class GenerateSitemap extends Command
{
    protected $signature = 'nextstep:sitemap';

    protected $description = 'Write public/sitemap.xml for every page in every language';

    public function handle(): int
    {
        $sitemap = Sitemap::create();
        $locales = array_keys(config('nextstep.locales'));

        $static = [
            'home' => 1.0, 'about' => 0.6, 'fair' => 0.8, 'conference' => 0.8,
            'agenda' => 0.9, 'seminars' => 0.6, 'speakers' => 0.8,
            'universities' => 0.8, 'exhibitors' => 0.7, 'floorplan' => 0.6,
            'sponsors' => 0.6, 'news' => 0.8, 'blog' => 0.7, 'media' => 0.6,
            'archive' => 0.6, 'sdg' => 0.7, 'reports' => 0.5, 'scholarships' => 0.6,
            'contact' => 0.5, 'exhibit' => 0.5, 'privacy' => 0.3, 'terms' => 0.3,
            'press' => 0.4, 'register.fair' => 1.0, 'register.conference' => 0.9,
        ];

        foreach ($locales as $locale) {
            foreach ($static as $name => $priority) {
                $sitemap->add(
                    Url::create(route($name, ['locale' => $locale]))
                        ->setPriority($priority)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                );
            }

            foreach (Post::published()->get() as $post) {
                $sitemap->add(
                    Url::create(route($post->type === Post::TYPE_BLOG ? 'blog.show' : 'news.show', [
                        'locale' => $locale, 'post' => $post,
                    ]))->setLastModificationDate($post->updated_at)->setPriority(0.6)
                );
            }

            foreach (Speaker::published()->get() as $speaker) {
                $sitemap->add(Url::create(route('speakers.show', ['locale' => $locale, 'speaker' => $speaker]))->setPriority(0.5));
            }

            foreach (Edition::where('published', true)->get() as $edition) {
                $sitemap->add(Url::create(route('archive.show', ['locale' => $locale, 'year' => $edition->year]))->setPriority(0.5));
            }

            foreach (MediaAlbum::where('published', true)->get() as $album) {
                $sitemap->add(Url::create(route('media.album', ['locale' => $locale, 'album' => $album]))->setPriority(0.4));
            }
        }

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('sitemap.xml written with '.count($sitemap->getTags()).' URLs.');

        return self::SUCCESS;
    }
}
