<?php

namespace App\Services\Matching;

use App\Models\Field;
use App\Models\Interaction;
use App\Models\Organization;
use App\Models\Registration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The questions the fair exists to answer.
 *
 * Every method here is one board-level question. They are deliberately plain
 * aggregate queries rather than a reporting abstraction: the value is in asking
 * the right question, and a query you can read is a query you can defend when
 * someone challenges the number in a meeting.
 */
class InsightService
{
    /**
     * Which fields are attracting the most interest?
     *
     * Weighted by rank, so a hundred people naming Medicine first outranks a
     * hundred who listed it third while thinking about something else.
     *
     * @return Collection<int, array{field:string, sector:string, students:int, weighted:float}>
     */
    public function demandByField(int $limit = 20): Collection
    {
        return DB::table('field_registration')
            ->join('fields', 'fields.id', '=', 'field_registration.field_id')
            ->join('sectors', 'sectors.id', '=', 'fields.sector_id')
            ->join('registrations', 'registrations.id', '=', 'field_registration.registration_id')
            ->whereNull('registrations.deleted_at')
            ->whereNotIn('registrations.status', ['cancelled', 'rejected', 'draft'])
            ->groupBy('fields.id', 'fields.name', 'fields.slug', 'sectors.name')
            ->select([
                'fields.slug',
                'fields.name as field_name',
                'sectors.name as sector_name',
                DB::raw('COUNT(*) as students'),
                DB::raw('SUM(1.0 / field_registration.rank) as weighted'),
            ])
            ->orderByDesc('weighted')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'slug' => $row->slug,
                'field' => $this->localised($row->field_name),
                'sector' => $this->localised($row->sector_name),
                'students' => (int) $row->students,
                'weighted' => round((float) $row->weighted, 1),
            ]);
    }

    /** Which sectors are students most interested in? */
    public function demandBySector(): Collection
    {
        return DB::table('field_registration')
            ->join('fields', 'fields.id', '=', 'field_registration.field_id')
            ->join('sectors', 'sectors.id', '=', 'fields.sector_id')
            ->join('registrations', 'registrations.id', '=', 'field_registration.registration_id')
            ->whereNull('registrations.deleted_at')
            ->groupBy('sectors.id', 'sectors.name', 'sectors.slug')
            ->select([
                'sectors.slug',
                'sectors.name as sector_name',
                DB::raw('COUNT(DISTINCT field_registration.registration_id) as students'),
            ])
            ->orderByDesc('students')
            ->get()
            ->map(fn ($row) => [
                'slug' => $row->slug,
                'sector' => $this->localised($row->sector_name),
                'students' => (int) $row->students,
            ]);
    }

    /**
     * Where demand outruns supply.
     *
     * This is the most commercially useful number the platform produces: fields
     * hundreds of students want that almost nobody at the fair teaches. It is the
     * exhibitor pitch for next year and the evidence for a ministry conversation.
     *
     * @return Collection<int, array{field:string, students:int, institutions:int, ratio:float}>
     */
    public function supplyGaps(int $limit = 15): Collection
    {
        $demand = DB::table('field_registration')
            ->select('field_id', DB::raw('COUNT(*) as students'))
            ->groupBy('field_id')
            ->pluck('students', 'field_id');

        $supply = DB::table('field_organization')
            ->join('organizations', 'organizations.id', '=', 'field_organization.organization_id')
            ->where('organizations.published', true)
            ->select('field_id', DB::raw('COUNT(DISTINCT organization_id) as institutions'))
            ->groupBy('field_id')
            ->pluck('institutions', 'field_id');

        return Field::whereIn('id', $demand->keys())->get()
            ->map(function (Field $field) use ($demand, $supply) {
                $students = (int) ($demand[$field->id] ?? 0);
                $institutions = (int) ($supply[$field->id] ?? 0);

                return [
                    'slug' => $field->slug,
                    'field' => $field->t('name'),
                    'students' => $students,
                    'institutions' => $institutions,
                    // Students per institution offering it. No institutions at all
                    // is the worst case, so it sorts above a merely crowded field.
                    'ratio' => $institutions === 0 ? $students * 1.0 : round($students / $institutions, 1),
                ];
            })
            ->sortByDesc('ratio')
            ->take($limit)
            ->values();
    }

    /** Which countries do students want to study in? */
    public function demandByCountry(): Collection
    {
        return Registration::fair()->active()
            ->whereNotNull('preferred_countries')
            ->pluck('preferred_countries')
            ->flatten()
            ->reject(fn ($c) => blank($c) || $c === 'undecided')
            ->countBy()
            ->sortDesc()
            ->map(fn ($count, $code) => ['country' => $code, 'students' => $count])
            ->values();
    }

    /**
     * Which universities generated the most engagement, and how qualified was it?
     *
     * Engagement is weighted by interaction type — a badge scanned at a desk cost
     * both people time in the same place, a profile view cost nobody anything.
     * "Qualified" counts scans against students whose declared field the
     * institution actually teaches.
     */
    public function engagementLeaderboard(int $limit = 20): Collection
    {
        $weights = config('taxonomy.interaction_weights');

        $rows = DB::table('interactions')
            ->join('organizations', 'organizations.id', '=', 'interactions.organization_id')
            ->groupBy('organizations.id', 'organizations.name', 'interactions.type')
            ->select([
                'organizations.id',
                'organizations.name as org_name',
                'interactions.type',
                DB::raw('COUNT(*) as total'),
                DB::raw('COUNT(DISTINCT interactions.registration_id) as people'),
            ])
            ->get();

        $qualified = DB::table('interactions')
            ->join('field_registration', 'field_registration.registration_id', '=', 'interactions.registration_id')
            ->join('field_organization', function ($join) {
                $join->on('field_organization.field_id', '=', 'field_registration.field_id')
                    ->on('field_organization.organization_id', '=', 'interactions.organization_id');
            })
            ->where('interactions.type', Interaction::TYPE_BOOTH_SCAN)
            ->groupBy('interactions.organization_id')
            ->select('interactions.organization_id', DB::raw('COUNT(DISTINCT interactions.registration_id) as qualified'))
            ->pluck('qualified', 'organization_id');

        return $rows->groupBy('id')->map(function ($group, $id) use ($weights, $qualified) {
            $name = $this->localised($group->first()->org_name);
            $scans = (int) ($group->firstWhere('type', Interaction::TYPE_BOOTH_SCAN)->people ?? 0);

            return [
                'organization_id' => (int) $id,
                'organization' => $name,
                'scans' => $scans,
                'people' => (int) $group->sum('people'),
                'qualified' => (int) ($qualified[$id] ?? 0),
                'score' => (int) $group->sum(fn ($r) => ($weights[$r->type] ?? 5) * $r->people),
            ];
        })->sortByDesc('score')->take($limit)->values();
    }

    /** How well the two sides actually fit, in aggregate. */
    public function matchQuality(): array
    {
        $matchable = Registration::fair()->active()->whereNotNull('degree_level')->whereHas('fields')->count();
        $withMatches = DB::table('matches')->distinct()->count('registration_id');
        $strong = DB::table('matches')->where('score', '>=', 75)->distinct()->count('registration_id');

        return [
            'matchable_students' => $matchable,
            'students_with_matches' => $withMatches,
            'students_with_strong_match' => $strong,
            'average_score' => (int) round((float) DB::table('matches')->avg('score')),
            'institutions_matching' => Organization::matchable()->count(),
        ];
    }

    /** Of the students an institution matched, how many actually came to the desk? */
    public function matchToVisitConversion(): float
    {
        $matched = DB::table('matches')->where('score', '>=', 55)->count();

        if ($matched === 0) {
            return 0.0;
        }

        $visited = DB::table('matches')
            ->join('interactions', function ($join) {
                $join->on('interactions.registration_id', '=', 'matches.registration_id')
                    ->on('interactions.organization_id', '=', 'matches.organization_id');
            })
            ->where('matches.score', '>=', 55)
            ->where('interactions.type', Interaction::TYPE_BOOTH_SCAN)
            ->distinct()
            ->count(DB::raw('matches.id'));

        return round($visited / $matched * 100, 1);
    }

    /** Demand broken down by city — where to send next year's school visits. */
    public function demandByCity(int $limit = 12): Collection
    {
        return Registration::fair()->active()
            ->whereNotNull('city')
            ->select('city', DB::raw('COUNT(*) as students'))
            ->groupBy('city')
            ->orderByDesc('students')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => ['city' => $row->city, 'students' => (int) $row->students]);
    }

    /** What students say they want to do afterwards. */
    public function careerGoals(): Collection
    {
        return Registration::fair()->active()
            ->whereNotNull('career_goal')
            ->select('career_goal', DB::raw('COUNT(*) as students'))
            ->groupBy('career_goal')
            ->orderByDesc('students')
            ->get()
            ->map(fn ($row) => ['goal' => $row->career_goal, 'students' => (int) $row->students]);
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
