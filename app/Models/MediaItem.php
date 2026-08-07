<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaItem extends Model
{
    use HasTranslatableContent;

    /** Alt text is translatable and required on every image. */
    public array $translatable = ['alt', 'caption'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['downloadable' => 'boolean'];
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(MediaAlbum::class, 'media_album_id');
    }

    /** Embed URL for the players we support, so the poster frame stays ours. */
    public function embedUrl(): ?string
    {
        if (! $this->url) {
            return null;
        }

        if (preg_match('~youtu(?:\.be/|be\.com/(?:watch\?v=|embed/|shorts/))([\w-]{11})~', $this->url, $m)) {
            return 'https://www.youtube-nocookie.com/embed/'.$m[1];
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $this->url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return $this->url;
    }
}
