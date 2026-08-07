<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/** Small site-wide values an editor can change without a deploy. */
class Setting extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['value' => 'array'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('ns.settings'));
        static::deleted(fn () => Cache::forget('ns.settings'));
    }

    public static function all_cached(): array
    {
        return Cache::rememberForever('ns.settings', fn () => static::query()->pluck('value', 'key')->all());
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::all_cached()[$key] ?? $default;
    }

    public static function put(string $key, mixed $value, string $group = 'general'): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
    }
}
