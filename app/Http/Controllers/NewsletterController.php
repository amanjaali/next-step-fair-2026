<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:190'],
            'source' => ['nullable', 'string', 'max:40'],
        ]);

        $existing = NewsletterSubscriber::where('email', strtolower($data['email']))->first();

        if ($existing && ! $existing->unsubscribed_at) {
            return back()->with('newsletter', __('site.newsletter.already'));
        }

        NewsletterSubscriber::updateOrCreate(
            ['email' => strtolower($data['email'])],
            [
                'locale' => app()->getLocale(),
                'source' => $data['source'] ?? 'footer',
                'confirmed_at' => now(),
                'unsubscribed_at' => null,
            ]
        );

        return back()->with('newsletter', __('site.newsletter.thanks'));
    }
}
