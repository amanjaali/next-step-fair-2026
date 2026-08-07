<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * A branded QR code the team generates for a poster, a school visit, a booth or
 * a social campaign. Scans resolve through /q/{code}, which counts the scan,
 * attaches the UTM parameters and forwards to the target page — so the funnel
 * can attribute registrations back to the piece of print that produced them.
 */
class QrCampaign extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $campaign) {
            $campaign->code = $campaign->code ?: strtolower(Str::random(7));
        });
    }

    public function scans(): HasMany
    {
        return $this->hasMany(QrScan::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function shortUrl(): string
    {
        return url('/q/'.$this->code);
    }

    /** The destination with campaign attribution appended. */
    public function targetWithUtm(): string
    {
        $params = array_filter([
            'utm_source' => $this->utm_source,
            'utm_medium' => $this->utm_medium ?: $this->medium,
            'utm_campaign' => $this->utm_campaign ?: $this->code,
        ]);

        if (! $params) {
            return $this->target_url;
        }

        $separator = str_contains($this->target_url, '?') ? '&' : '?';

        return $this->target_url.$separator.http_build_query($params);
    }

    public function isUsable(): bool
    {
        return $this->active && (! $this->expires_at || $this->expires_at->isFuture());
    }

    public function uniqueScans(): int
    {
        return $this->scans()->where('is_unique', true)->count();
    }
}
