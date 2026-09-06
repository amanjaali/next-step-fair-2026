<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * News and blog share a table and differ in voice, not in structure:
 * news is organisational, the blog is student-facing guidance.
 */
class Post extends Model
{
    use HasTranslatableContent, SoftDeletes;

    public const TYPE_NEWS = 'news';

    public const TYPE_BLOG = 'blog';

    public array $translatable = [
        'title', 'standfirst', 'excerpt', 'body', 'cover_caption',
        'author_role', 'quote', 'quote_by',
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'facts' => 'array',
            'gallery' => 'array',
            'pinned' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $post) {
            $post->slug = $post->slug ?: Str::slug($post->getTranslation('title', 'en', false) ?: Str::random(8));
            $post->year = $post->year ?: ($post->published_at?->year ?? now()->year);
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->where('published_at', '<=', now());
    }

    public function scopeNews(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_NEWS);
    }

    public function scopeBlog(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_BLOG);
    }

    public function readingTime(): int
    {
        return ns_reading_time($this->t('body') ?: $this->t('excerpt'));
    }

    public function accent(): string
    {
        return $this->category?->accent ?: '#B64698';
    }

    /** rgba border for the category chip, matching the design's card treatment. */
    public function accentBorder(): string
    {
        [$r, $g, $b] = sscanf($this->accent(), '#%02x%02x%02x');

        return "rgba($r,$g,$b,0.4)";
    }

    public function coverUrl(): ?string
    {
        return ns_uploaded($this->cover_path);
    }

    /**
     * Table of contents from the h2s in the body — long blog posts get one,
     * news articles do not need it.
     */
    public function tableOfContents(): array
    {
        $body = $this->t('body');
        if (! $body || ! preg_match_all('/<h2[^>]*>(.*?)<\/h2>/is', $body, $matches)) {
            return [];
        }

        return collect($matches[1])
            ->map(fn ($h) => ['title' => trim(strip_tags($h)), 'anchor' => Str::slug(strip_tags($h))])
            ->filter(fn ($h) => $h['title'] !== '')
            ->values()
            ->all();
    }

    /** Adds ids to h2s so the table of contents can link into the body. */
    public function bodyWithAnchors(): string
    {
        return preg_replace_callback('/<h2([^>]*)>(.*?)<\/h2>/is', function ($m) {
            return '<h2 id="'.Str::slug(strip_tags($m[2])).'"'.$m[1].'>'.$m[2].'</h2>';
        }, $this->t('body') ?: '');
    }
}
