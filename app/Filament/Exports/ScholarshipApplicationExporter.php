<?php

namespace App\Filament\Exports;

use App\Models\ScholarshipApplication;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;

/**
 * Every scholarship application field, with presets deciding which columns
 * start ticked. The column checklist still lets the admin adjust any preset.
 */
class ScholarshipApplicationExporter extends Exporter
{
    protected static ?string $model = ScholarshipApplication::class;

    public const PRESETS = [
        'summary' => [
            'name', 'phone', 'region', 'district', 'exam_average',
            'first_choice_university', 'first_choice_department', 'status', 'submitted_at',
        ],
        'review' => [
            'name', 'phone', 'region', 'district', 'exam_average',
            'first_choice_university', 'first_choice_department', 'status', 'submitted_at',
            'eligibility_year', 'eligibility_funded', 'exam_status', 'stream', 'school_name',
            'second_choice_university', 'second_choice_department',
            'third_choice_university', 'third_choice_department',
            'fourth_choice_university', 'fourth_choice_department',
            'fifth_choice_university', 'fifth_choice_department',
            'doc_certificate', 'doc_national_id', 'doc_residence', 'doc_judicial_record',
            'statement_words', 'proposal_words',
            'score_academic', 'score_feasibility', 'score_interview', 'score_total', 'decision',
        ],
        'contacts' => ['name', 'phone', 'email', 'city', 'region', 'status'],
        'everything' => null,
    ];

    /** Which preset this exporter ticks by default; subclasses override it. */
    protected static string $preset = 'everything';

    public static function modifyQuery(Builder $query): Builder
    {
        return $query->with('registration');
    }

    public static function getColumns(): array
    {
        $columns = [
            ExportColumn::make('name')->label('Name')
                ->state(fn (ScholarshipApplication $a) => $a->registration?->full_name),
            ExportColumn::make('ticket')->label('Ticket')
                ->state(fn (ScholarshipApplication $a) => $a->registration?->ticket_ref),
            ExportColumn::make('phone')->label('Phone')
                ->state(fn (ScholarshipApplication $a) => $a->registration?->msisdn()),
            ExportColumn::make('email')->label('Email')
                ->state(fn (ScholarshipApplication $a) => $a->registration?->email),
            ExportColumn::make('city')->label('City')
                ->state(fn (ScholarshipApplication $a) => $a->registration?->city),
            ExportColumn::make('gender')->label('Gender')
                ->state(fn (ScholarshipApplication $a) => $a->registration?->gender),
            ExportColumn::make('date_of_birth')->label('Date of birth')
                ->state(fn (ScholarshipApplication $a) => $a->registration?->date_of_birth?->format('Y-m-d')),
            ExportColumn::make('education_stage')->label('Education stage')
                ->state(fn (ScholarshipApplication $a) => $a->registration?->education_stage),

            ExportColumn::make('eligibility_year')->label('Starts degree in 2026–2027')
                ->state(fn (ScholarshipApplication $a) => self::answer($a, 'year')),
            ExportColumn::make('eligibility_funded')->label('Already holds a full scholarship')
                ->state(fn (ScholarshipApplication $a) => self::answer($a, 'funded')),
            ExportColumn::make('eligibility_passed_at')->label('Eligibility passed at'),

            ExportColumn::make('region')->label('Region')
                ->state(fn (ScholarshipApplication $a) => $a->regionName()),
            ExportColumn::make('district')->label('District'),
            ExportColumn::make('seats')->label('Seats in region')
                ->state(fn (ScholarshipApplication $a) => $a->region_code ? $a->seatsInRegion() : null),

            ExportColumn::make('exam_status')->label('Exam status'),
            ExportColumn::make('exam_average')->label('Exam average'),
            ExportColumn::make('stream')->label('Stream'),
            ExportColumn::make('school_name')->label('School'),
        ];

        foreach (ScholarshipApplication::CHOICE_SLOTS as $slot) {
            $label = ucfirst($slot).' choice';
            $columns[] = ExportColumn::make("{$slot}_choice_university")->label("{$label} — university");
            $columns[] = ExportColumn::make("{$slot}_choice_department")->label("{$label} — department");
        }

        $columns[] = ExportColumn::make('statement_words')->label('Statement word count')
            ->state(fn (ScholarshipApplication $a) => ns_word_count((string) $a->statement));
        $columns[] = ExportColumn::make('proposal_words')->label('Proposal word count')
            ->state(fn (ScholarshipApplication $a) => ns_word_count((string) $a->proposal));
        $columns[] = ExportColumn::make('statement')->label('Personal statement');
        $columns[] = ExportColumn::make('proposal')->label('Problem-solving proposal');

        foreach (config('scholarship.documents') as $key) {
            $name = __('scholarship.documents.'.$key.'.name', [], 'en');
            $columns[] = ExportColumn::make("doc_{$key}")->label("{$name} — uploaded")
                ->state(fn (ScholarshipApplication $a) => filled(($a->documents ?? [])[$key] ?? null) ? 'Yes' : 'No');
            $columns[] = ExportColumn::make("doc_{$key}_link")->label("{$name} — link")
                ->state(fn (ScholarshipApplication $a) => filled(($a->documents ?? [])[$key] ?? null)
                    ? route('admin.scholarship.document', ['application' => $a->id, 'key' => $key])
                    : null);
        }

        array_push($columns,
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('submitted_at')->label('Submitted at'),
            ExportColumn::make('screened_at')->label('Screened at'),
            ExportColumn::make('shortlisted_at')->label('Shortlisted at'),
            ExportColumn::make('interviewed_at')->label('Interviewed at'),
            ExportColumn::make('decided_at')->label('Decided at'),
            ExportColumn::make('score_academic')->label('Score — academic'),
            ExportColumn::make('score_feasibility')->label('Score — feasibility'),
            ExportColumn::make('score_interview')->label('Score — interview'),
            ExportColumn::make('score_total')->label('Score — total'),
            ExportColumn::make('decision')->label('Decision'),
            ExportColumn::make('committee_notes')->label('Committee notes'),
        );

        $ticked = self::PRESETS[static::$preset];

        return array_map(
            fn (ExportColumn $c) => $c->enabledByDefault($ticked === null || in_array($c->getName(), $ticked, true)),
            $columns,
        );
    }

    private static function answer(ScholarshipApplication $a, string $question): ?string
    {
        $value = ($a->eligibility ?? [])[$question] ?? null;

        return $value === null ? null : __("scholarship.eligibility.questions.$question.options.$value", [], 'en');
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Export complete: '.number_format($export->successful_rows).' application(s).';

        if ($failed = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failed).' row(s) failed.';
        }

        return $body;
    }
}
