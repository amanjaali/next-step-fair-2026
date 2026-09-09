<?php

namespace App\Filament\Forms\Components;

use App\Support\Svg;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * The upload box used for every logo and photograph in the dashboard.
 *
 * Two things it does that the plain component does not.
 *
 * It accepts SVG, because that is what a designer sends and what stays sharp in
 * a header — and it cleans the file the moment it lands. An SVG is a document,
 * not a picture: it can carry script, event handlers and references to other
 * people's servers, and it is served from our own origin. See App\Support\Svg
 * for what survives.
 *
 * And it states its limits. Filament's default is any image at any size, which
 * lets through a file larger than PHP itself will accept — and that failure has
 * no error attached to it, so the box simply sits there and uploading looks
 * broken.
 */
class ImageUpload
{
    public const TYPES = ['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'];

    /**
     * A partner mark has to survive the badge as well as the website, so SVG is
     * not offered here.
     *
     * The badge picture people receive on WhatsApp is composed by GD, which
     * cannot read SVG. It can be converted first, but only on a machine that
     * has Imagick or a converter, and most do not — so an SVG partner logo is
     * perfect everywhere on the site and absent from the one thing three
     * thousand people are sent. Refusing it at the door beats explaining it
     * afterwards.
     */
    public const MARK_TYPES = ['image/png', 'image/jpeg', 'image/webp'];

    /** A logo or brand mark: small, and usually a transparent PNG. */
    public static function logo(string $name): FileUpload
    {
        return self::base($name)->maxSize(4096);
    }

    /** A partner's mark, which also has to be drawable onto a badge. */
    public static function mark(string $name): FileUpload
    {
        return self::base($name)->maxSize(4096)->acceptedFileTypes(self::MARK_TYPES);
    }

    /** A photograph: the same rules, more room. */
    public static function photo(string $name): FileUpload
    {
        return self::base($name)->maxSize(8192);
    }

    private static function base(string $name): FileUpload
    {
        return FileUpload::make($name)
            ->image()
            ->acceptedFileTypes(self::TYPES)
            ->disk('public')
            ->saveUploadedFileUsing(function (TemporaryUploadedFile $file, FileUpload $component): ?string {
                $disk = $component->getDiskName();

                $path = $file->store($component->getDirectory() ?? '', ['disk' => $disk]);

                if (! is_string($path) || $path === '') {
                    return null;
                }

                /*
                 * Cleaned in place, before anything can link to it. An SVG that
                 * turns out to be nothing but script cleans down to nothing, and
                 * a file with no drawing left in it is dropped rather than
                 * stored as an empty picture.
                 */
                if (str_ends_with(strtolower($path), '.svg')) {
                    $absolute = Storage::disk($disk)->path($path);

                    if (! Svg::cleanFile($absolute)) {
                        Storage::disk($disk)->delete($path);

                        return null;
                    }
                }

                return $path;
            });
    }
}
