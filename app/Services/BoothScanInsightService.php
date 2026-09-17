<?php

namespace App\Services;

use App\Models\Organization;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The desk-QR side of the fair: who tapped which exhibitor's card, and whether
 * we know who they were.
 *
 * Deliberately separate from {@see \App\Services\Matching\InsightService}: that
 * service answers "did a known student visit an exhibitor" (a badge scan), this
 * answers "how much foot traffic did a desk QR draw" — a card is tapped by
 * plenty of people who never sign in, and both figures matter for different
 * reasons.
 */
class BoothScanInsightService
{
    /** Headline numbers for the stat cards. */
    public function stats(): array
    {
        $total = DB::table('booth_scans')->count();
        $identified = DB::table('booth_scans')->whereNotNull('registration_id')->count();

        return [
            'total' => $total,
            'identified' => $identified,
            'anonymous' => $total - $identified,
            'identified_percent' => $total > 0 ? round($identified / $total * 100) : 0,
            'exhibitors_with_no_scans' => Organization::where('qr_scan_count', 0)->count(),
        ];
    }

    /**
     * Every exhibitor with at least one scan, ranked by desk-QR traffic, with
     * how much of it was identified. Unbounded — the table itself paginates.
     *
     * @return Collection<int, array{organization:string, booth:string, scans:int, identified:int}>
     */
    public function leaderboard(): Collection
    {
        $identified = DB::table('booth_scans')
            ->whereNotNull('registration_id')
            ->select('organization_id', DB::raw('COUNT(*) as identified'))
            ->groupBy('organization_id')
            ->pluck('identified', 'organization_id');

        return Organization::where('qr_scan_count', '>', 0)
            ->orderByDesc('qr_scan_count')
            ->get(['id', 'name', 'booth', 'qr_scan_count'])
            ->map(fn (Organization $org) => [
                'organization' => $org->t('name'),
                'booth' => $org->booth ?: '—',
                'scans' => $org->qr_scan_count,
                'identified' => (int) ($identified[$org->id] ?? 0),
            ]);
    }

    /**
     * Every scan where the visitor was actually signed in — the only ones that
     * can be named rather than just counted. Unbounded — the table paginates.
     *
     * @return Collection<int, array{visitor:string, organization:string, day:?int, scanned_at:string}>
     */
    public function recentIdentifiedScans(): Collection
    {
        return DB::table('booth_scans')
            ->join('registrations', 'registrations.id', '=', 'booth_scans.registration_id')
            ->join('organizations', 'organizations.id', '=', 'booth_scans.organization_id')
            ->whereNotNull('booth_scans.registration_id')
            ->orderByDesc('booth_scans.scanned_at')
            ->select([
                'registrations.full_name',
                'organizations.name as org_name',
                'booth_scans.day',
                'booth_scans.scanned_at',
            ])
            ->get()
            ->map(fn ($row) => [
                'visitor' => $row->full_name,
                'organization' => $this->localised($row->org_name),
                'day' => $row->day,
                'scanned_at' => $row->scanned_at,
            ]);
    }

    /** Translatable JSON columns come back raw from the query builder. */
    private function localised(?string $json): string
    {
        $decoded = json_decode((string) $json, true);

        if (! is_array($decoded)) {
            return (string) $json;
        }

        return $decoded[app()->getLocale()]
            ?? $decoded[config('app.fallback_locale')]
            ?? (string) reset($decoded);
    }
}
