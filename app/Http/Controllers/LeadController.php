<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** B2B enquiries: "exhibit with us" and sponsorship, routed to the sales team. */
class LeadController extends Controller
{
    public function exhibit(): View
    {
        return view('pages.exhibit', [
            'navKey' => 'sponsors',
            'title' => __('site.pages.exhibit.title').' — '.config('nextstep.event.name'),
            'tierTable' => Setting::get('sponsor_tier_table', ['head' => [], 'rows' => []]),
        ]);
    }

    public function storeExhibit(Request $request): RedirectResponse
    {
        return $this->store($request, 'exhibit', __('site.pages.exhibit.sent'));
    }

    public function storeSponsor(Request $request): RedirectResponse
    {
        return $this->store($request, 'sponsor', __('site.pages.exhibit.sent'));
    }

    private function store(Request $request, string $type, string $message): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'organization' => ['required', 'string', 'max:190'],
            'role' => ['nullable', 'string', 'max:120'],
            'message' => ['nullable', 'string', 'max:4000'],
            'tier' => ['nullable', 'string', 'max:40'],
            'booth_size' => ['nullable', 'string', 'max:40'],
        ]);

        Lead::create([
            'type' => $type,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'organization' => $data['organization'],
            'role' => $data['role'] ?? null,
            'message' => $data['message'] ?? null,
            'meta' => array_filter([
                'tier' => $data['tier'] ?? null,
                'booth_size' => $data['booth_size'] ?? null,
            ]),
            'locale' => app()->getLocale(),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('status', $message);
    }
}
