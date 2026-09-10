<?php

namespace App\Services\Matching;

use App\Models\Field;
use App\Models\MatchScore;
use App\Models\Organization;
use App\Models\Registration;
use Illuminate\Support\Collection;

/**
 * Scores how well one student and one institution fit each other.
 *
 * The design principle is that a match must be explainable. Every component is
 * kept in `reasons` so the student is told "they teach two of your three fields,
 * in English, in Türkiye" rather than being handed a number and asked to trust it.
 * A recommendation nobody can interrogate is a recommendation nobody acts on.
 *
 * Weights live in config/taxonomy.php. Field overlap dominates deliberately:
 * everything else is a constraint on a decision that is fundamentally about what
 * someone wants to study.
 */
class MatchEngine
{
    /**
     * Recompute every match for one student.
     *
     * Pass a preloaded institution collection when scoring many students in a
     * row (seeders, rebuild jobs) so each call does not re-query the catalogue.
     *
     * @param  Collection<int, Organization>|null  $institutions
     * @return int number of matches stored
     */
    public function forRegistration(Registration $registration, ?Collection $institutions = null): int
    {
        return $this->forRegistrations(collect([$registration]), $institutions);
    }

    /**
     * Recompute matches for a batch of students, loading the institution
     * catalogue once and writing in bulk so long seeds stay under wait_timeout.
     *
     * @param  Collection<int, Registration>  $registrations
     * @param  Collection<int, Organization>|null  $institutions
     * @return int number of match rows stored
     */
    public function forRegistrations(Collection $registrations, ?Collection $institutions = null): int
    {
        $institutions ??= Organization::matchable()->with('fields')->get();
        $threshold = (int) config('taxonomy.match_threshold');
        $now = now();
        $rows = [];
        $keptByRegistration = [];

        foreach ($registrations as $registration) {
            if (! $registration->canBeMatched()) {
                continue;
            }

            $wanted = $registration->relationLoaded('fields')
                ? $registration->fields
                : $registration->fields()->get();
            $kept = [];

            foreach ($institutions as $institution) {
                $result = $this->score($registration, $institution, $wanted);

                if ($result['score'] < $threshold) {
                    continue;
                }

                $rows[] = [
                    'registration_id' => $registration->id,
                    'organization_id' => $institution->id,
                    'score' => $result['score'],
                    'reasons' => json_encode($result['reasons']),
                    'computed_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $kept[] = $institution->id;
            }

            $keptByRegistration[$registration->id] = $kept;
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            MatchScore::upsert(
                $chunk,
                ['registration_id', 'organization_id'],
                ['score', 'reasons', 'computed_at', 'updated_at'],
            );
        }

        // Drop matches that no longer clear the threshold — a student who changes
        // their mind should not keep yesterday's recommendations.
        foreach ($keptByRegistration as $registrationId => $kept) {
            MatchScore::where('registration_id', $registrationId)
                ->whereNotIn('organization_id', $kept ?: [0])
                ->delete();
        }

        return count($rows);
    }

    /** Recompute every student's matches against one institution. */
    public function forOrganization(Organization $organization): int
    {
        if (! $organization->canBeMatched()) {
            MatchScore::where('organization_id', $organization->id)->delete();

            return 0;
        }

        $count = 0;

        Registration::fair()->active()
            ->whereNotNull('degree_level')
            ->whereHas('fields')
            ->with('fields')
            ->chunkById(200, function (Collection $batch) use ($organization, &$count) {
                $threshold = (int) config('taxonomy.match_threshold');
                $now = now();
                $rows = [];

                foreach ($batch as $registration) {
                    $result = $this->score($registration, $organization, $registration->fields);

                    if ($result['score'] < $threshold) {
                        MatchScore::where('registration_id', $registration->id)
                            ->where('organization_id', $organization->id)->delete();

                        continue;
                    }

                    $rows[] = [
                        'registration_id' => $registration->id,
                        'organization_id' => $organization->id,
                        'score' => $result['score'],
                        'reasons' => json_encode($result['reasons']),
                        'computed_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $count++;
                }

                if ($rows !== []) {
                    MatchScore::upsert(
                        $rows,
                        ['registration_id', 'organization_id'],
                        ['score', 'reasons', 'computed_at', 'updated_at'],
                    );
                }
            });

        return $count;
    }

    /**
     * The score itself.
     *
     * @param  Collection<int, Field>  $wanted  fields the student named, ranked
     * @return array{score:int, reasons:array<string, mixed>}
     */
    public function score(Registration $registration, Organization $institution, Collection $wanted): array
    {
        $w = config('taxonomy.match_weights');
        $offered = $institution->fields;
        $reasons = [];
        $points = 0;

        /* ------------------------------------------------------------- fields */
        // Weighted by rank: a first choice is worth more than a third. Overlap is
        // measured against what the student asked for, not what the university
        // teaches, so a large university does not out-score a specialist one
        // simply by offering everything.
        $offeredIds = $offered->pluck('id')->unique();
        $matchedFields = $wanted->filter(fn (Field $f) => $offeredIds->contains($f->id));

        if ($matchedFields->isEmpty()) {
            // No shared field: nothing else can rescue it.
            return ['score' => 0, 'reasons' => []];
        }

        $rankWeight = fn (Field $f) => 1 / max(1, (int) ($f->pivot->rank ?? 1));
        $earned = $matchedFields->sum($rankWeight);
        $possible = max(0.001, $wanted->sum($rankWeight));

        $points += $w['field'] * min(1, $earned / $possible);
        $reasons['fields'] = $matchedFields->map(fn (Field $f) => $f->t('name'))->values()->all();

        /* -------------------------------------------------------------- level */
        $levels = $offered->where(fn ($f) => $wanted->pluck('id')->contains($f->id))
            ->pluck('pivot.level')->unique();

        if ($registration->degree_level && $levels->contains($registration->degree_level)) {
            $points += $w['level'];
            $reasons['level'] = $registration->degree_level;
        }

        /* ------------------------------------------------------------ country */
        $wantedCountries = collect($registration->preferred_countries ?? [])->reject(fn ($c) => $c === 'undecided');
        $campus = collect($institution->campus_countries ?: [$institution->country]);

        if ($wantedCountries->isEmpty()) {
            // No preference stated is not a mismatch — award half rather than zero,
            // so an undecided student is not pushed toward home institutions.
            $points += $w['country'] / 2;
        } elseif ($wantedCountries->intersect($campus)->isNotEmpty()) {
            $points += $w['country'];
            $reasons['country'] = $wantedCountries->intersect($campus)->values()->all();
        }

        /* ------------------------------------------------------------- budget */
        if ($registration->budget_band && $registration->budget_band !== 'undecided') {
            if ($this->budgetFits($registration->budget_band, $institution)) {
                $points += $w['budget'];
                $reasons['budget'] = $registration->budget_band;
            } elseif ($institution->offers_scholarships) {
                // A scholarship is the reason an "unaffordable" university is still
                // worth a conversation, so it earns part of the budget weight.
                $points += $w['budget'] / 2;
                $reasons['scholarship'] = true;
            }
        } else {
            $points += $w['budget'] / 2;
        }

        /* ----------------------------------------------------------- language */
        $languages = collect($institution->languages ?? []);
        if ($registration->language_preference && $languages->contains($registration->language_preference)) {
            $points += $w['language'];
            $reasons['language'] = $registration->language_preference;
        } elseif ($registration->language_preference === null) {
            $points += $w['language'] / 2;
        }

        /* -------------------------------------------------------------- grade */
        // Entry requirements cut both ways: a student who clears the bar gets the
        // points, and one who does not is not hidden — the institution simply
        // ranks lower, because a conversation may still be worth having.
        if ($registration->grade_band && $institution->min_grade_band) {
            if ($this->gradeClears($registration->grade_band, $institution->min_grade_band)) {
                $points += $w['grade'];
                $reasons['meets_entry'] = true;
            }
        } else {
            $points += $w['grade'] / 2;
        }

        return [
            'score' => (int) min(100, round($points)),
            'reasons' => $reasons,
        ];
    }

    /** Does the institution have anything inside the student's price band? */
    private function budgetFits(string $band, Organization $institution): bool
    {
        $bands = config('taxonomy.budget_bands');
        [$min, $max] = $bands[$band] ?? [null, null];

        if ($max === null && $min === null) {
            return true;
        }

        $fee = $institution->tuition_min;

        if ($fee === null) {
            return true;   // unknown fee is not a reason to exclude
        }

        return $max === null ? true : $fee <= $max;
    }

    /** Does the student's grade band reach the institution's minimum? */
    private function gradeClears(string $student, string $required): bool
    {
        $bands = config('taxonomy.grade_bands');
        $studentFloor = $bands[$student][0] ?? null;
        $requiredFloor = $bands[$required][0] ?? null;

        if ($studentFloor === null || $requiredFloor === null) {
            return true;   // "not yet sat" is not a failure
        }

        return $studentFloor >= $requiredFloor;
    }
}
