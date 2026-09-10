<?php

namespace App\Support;

/**
 * The link that lets an uploaded file be seen.
 *
 * Uploads are written under storage/app/public, which is outside the web root
 * on purpose — nothing in there is reachable until public/storage points at it.
 * Laravel makes that link with `php artisan storage:link`, once, per install.
 *
 * Miss that step and the failure is silent and thoroughly misleading. The file
 * uploads. The path is saved. The dashboard says "Saved". The setting is right,
 * the bytes are on disk, the markup carries the correct URL — and every logo
 * anyone uploads is a blank box, on every page, for everybody. Meanwhile the
 * marks that ship with the site are served straight out of public/assets and
 * carry on working, so it reads as "the logo I uploaded is broken" rather than
 * "this install cannot serve any upload at all".
 *
 * That happened to this project on a live server, so the link is now something
 * the site checks, repairs where it can, and says out loud where it cannot.
 */
class StorageLink
{
    /** Where the link has to be, and what it has to point at. */
    public static function link(): string
    {
        return public_path('storage');
    }

    public static function target(): string
    {
        return storage_path('app/public');
    }

    /**
     * Can an uploaded file actually be served?
     *
     * Deliberately not "is there a symlink": a real directory copied into place
     * by a deployment script works just as well, and a symlink left behind by a
     * previous release — pointing at a path that no longer exists — looks fine
     * to file_exists() and serves nothing. What matters is that the link
     * resolves to the directory uploads are written into.
     */
    public static function isHealthy(): bool
    {
        $link = self::link();

        if (! file_exists($link)) {
            return false;
        }

        $resolved = realpath(is_link($link) ? readlink($link) : $link);

        return $resolved !== false && $resolved === realpath(self::target());
    }

    /**
     * Put it back, if this machine allows it.
     *
     * Cheap, safe and idempotent: it is one symlink, and the only thing it can
     * overwrite is a link that is already pointing somewhere wrong. It fails on
     * hosts where symlink() is disabled or public/ is not writable, which is
     * exactly the case the caller has to be able to report.
     */
    public static function repair(): bool
    {
        if (self::isHealthy()) {
            return true;
        }

        $link = self::link();
        $target = self::target();

        if (! is_dir($target)) {
            @mkdir($target, 0o755, true);
        }

        // A stale or wrongly-aimed link, but never a real directory somebody
        // deliberately put there with files in it.
        if (is_link($link)) {
            @unlink($link);
        } elseif (is_dir($link)) {
            return false;
        }

        try {
            @symlink($target, $link);
        } catch (\Throwable) {
            return false;
        }

        clearstatcache(true, $link);

        return self::isHealthy();
    }

    /** Why it is still broken, in a sentence a person can act on. */
    public static function problem(): ?string
    {
        if (self::isHealthy()) {
            return null;
        }

        $link = self::link();

        if (is_dir($link) && ! is_link($link)) {
            return 'public/storage is a real folder, not a link to storage/app/public.';
        }

        if (! is_writable(dirname($link))) {
            return 'The public folder is not writable, so the link cannot be created.';
        }

        return 'public/storage is missing or points at the wrong place.';
    }
}
