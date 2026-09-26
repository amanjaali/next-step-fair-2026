<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScholarshipApplication;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** A permanent, admin-only link to one uploaded document, used in exports. */
class ScholarshipDocumentController extends Controller
{
    public function __invoke(ScholarshipApplication $application, string $key): StreamedResponse
    {
        abort_unless(auth()->user()?->can('review-scholarships'), 403);

        $allowed = array_merge(
            config('scholarship.documents'),
            array_map(fn (string $slot) => "{$slot}_choice_form", ScholarshipApplication::CHOICE_SLOTS),
        );
        abort_unless(in_array($key, $allowed, true), 404);

        $path = ($application->documents ?? [])[$key] ?? null;
        abort_unless(filled($path) && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }
}
