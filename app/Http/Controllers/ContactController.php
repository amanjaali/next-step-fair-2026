<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('pages.contact', [
            'navKey' => 'contact',
            'title' => __('site.pages.contact.title').' — '.config('nextstep.event.name'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'organization' => ['nullable', 'string', 'max:190'],
            'subject' => ['nullable', 'string', 'max:190'],
            'message' => ['required', 'string', 'max:4000'],
        ]);

        Lead::create($data + [
            'type' => 'contact',
            'locale' => app()->getLocale(),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('status', __('site.pages.contact.sent'));
    }
}
