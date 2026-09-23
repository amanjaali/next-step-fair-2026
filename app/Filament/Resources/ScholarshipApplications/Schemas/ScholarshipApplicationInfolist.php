<?php

namespace App\Filament\Resources\ScholarshipApplications\Schemas;

use App\Filament\Resources\ScholarshipApplications\ScholarshipApplicationResource;
use App\Models\ScholarshipApplication;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * One application, entire, as the committee needs to read it.
 *
 * Everything the student wrote is shown as written — no truncation on the
 * statement or the proposal, because those are the two things being judged and
 * a reviewer who has to click "read more" ends up skimming.
 */
class ScholarshipApplicationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.scholarship.applicant'))
                ->columns(4)
                ->schema([
                    TextEntry::make('registration.full_name')->label(__('admin.fields.name')),
                    TextEntry::make('registration.phone')
                        ->label(__('admin.fields.phone'))
                        ->formatStateUsing(fn (ScholarshipApplication $record) => $record->registration
                            ? $record->registration->phone_country.' '.$record->registration->phone
                            : '—')
                        ->copyable(),
                    TextEntry::make('registration.email')->label(__('admin.fields.email'))->copyable()->placeholder('—'),
                    TextEntry::make('registration.city')->label(__('admin.fields.city'))->placeholder('—'),
                ]),

            Section::make(__('admin.scholarship.where'))
                ->description(__('admin.scholarship.where_help'))
                ->columns(4)
                ->schema([
                    TextEntry::make('region_code')
                        ->label(__('admin.scholarship.region'))
                        ->formatStateUsing(fn (ScholarshipApplication $record) => $record->regionName() ?? '—'),
                    TextEntry::make('district')->label(__('admin.scholarship.district'))->placeholder('—'),
                    TextEntry::make('seats')
                        ->label(__('admin.scholarship.seats'))
                        ->state(fn (ScholarshipApplication $record) => $record->seatsInRegion()),
                    TextEntry::make('cycle')->label(__('admin.scholarship.cycle')),
                ]),

            Section::make(__('admin.scholarship.school'))
                ->columns(4)
                ->schema([
                    TextEntry::make('exam_average')
                        ->label(__('admin.scholarship.average'))
                        ->formatStateUsing(fn (ScholarshipApplication $record) => $record->exam_average
                            ? number_format((float) $record->exam_average, 2).'%'
                            : __('admin.scholarship.pending')),
                    TextEntry::make('exam_status')->label(__('admin.scholarship.results'))->badge()->placeholder('—'),
                    TextEntry::make('stream')->label(__('admin.scholarship.stream'))->placeholder('—'),
                    TextEntry::make('school_name')->label(__('admin.scholarship.school_name'))->placeholder('—'),
                ]),

            Section::make(__('admin.scholarship.choices'))
                ->columns(2)
                ->schema([
                    TextEntry::make('first_choice_university')
                        ->label(__('admin.scholarship.first_choice'))
                        ->formatStateUsing(fn (ScholarshipApplication $record) => trim(
                            ($record->first_choice_university ?? '—').' · '.($record->first_choice_department ?? ''), ' ·')),
                    TextEntry::make('second_choice_university')
                        ->label(__('admin.scholarship.second_choice'))
                        ->placeholder('—')
                        ->formatStateUsing(fn (ScholarshipApplication $record) => $record->second_choice_university
                            ? trim($record->second_choice_university.' · '.($record->second_choice_department ?? ''), ' ·')
                            : '—'),
                    TextEntry::make('external_form_acks')
                        ->label(__('admin.scholarship.external_form_acks'))
                        ->state(fn (ScholarshipApplication $record) => self::externalFormAcks($record))
                        ->placeholder('—')
                        ->listWithLineBreaks()
                        ->columnSpanFull(),
                ]),

            Section::make(__('admin.scholarship.eligibility'))
                ->description(__('admin.scholarship.eligibility_help'))
                ->schema([
                    TextEntry::make('eligibility_verdict')
                        ->label(__('admin.scholarship.verdict'))
                        ->badge()
                        ->state(fn (ScholarshipApplication $record) => __('admin.scholarship.verdicts.'.$record->eligibilityVerdict()))
                        ->color(fn (ScholarshipApplication $record) => match ($record->eligibilityVerdict()) {
                            'pass' => 'success', 'warn' => 'warning', 'fail' => 'danger', default => 'gray',
                        }),
                    TextEntry::make('eligibility')
                        ->label(__('admin.scholarship.answers'))
                        ->state(fn (ScholarshipApplication $record) => self::answers($record))
                        ->listWithLineBreaks()
                        ->columnSpanFull(),
                ]),

            Section::make(__('admin.scholarship.statement'))
                ->schema([
                    TextEntry::make('statement')
                        ->hiddenLabel()
                        ->placeholder('—')
                        ->prose()
                        ->columnSpanFull(),
                ]),

            Section::make(__('admin.scholarship.proposal'))
                ->schema([
                    TextEntry::make('proposal')
                        ->hiddenLabel()
                        ->placeholder('—')
                        ->prose()
                        ->columnSpanFull(),
                ]),

            Section::make(__('admin.scholarship.documents'))
                ->description(__('admin.scholarship.documents_help'))
                ->schema([
                    TextEntry::make('documents')
                        ->hiddenLabel()
                        ->state(fn (ScholarshipApplication $record) => self::documents($record))
                        ->listWithLineBreaks()
                        ->columnSpanFull(),
                    TextEntry::make('choice_forms')
                        ->label(__('admin.scholarship.choice_forms'))
                        ->state(fn (ScholarshipApplication $record) => self::choiceForms($record))
                        ->placeholder('—')
                        ->listWithLineBreaks()
                        ->html()
                        ->columnSpanFull(),
                ]),

            Section::make(__('admin.scholarship.review_so_far'))
                ->columns(4)
                ->schema([
                    TextEntry::make('status')
                        ->label(__('admin.fields.status'))
                        ->badge()
                        ->formatStateUsing(fn (string $state) => ScholarshipApplicationResource::statusOptions()[$state] ?? $state),
                    TextEntry::make('score_total')
                        ->label(__('admin.scholarship.score'))
                        ->state(fn (ScholarshipApplication $record) => $record->scoreTotal())
                        ->formatStateUsing(fn (?float $state) => $state === null ? '—' : $state.' / 100'),
                    TextEntry::make('decision')
                        ->label(__('admin.scholarship.decision'))
                        ->badge()
                        ->placeholder('—')
                        ->formatStateUsing(fn (?string $state) => $state
                            ? (ScholarshipApplicationResource::decisionOptions()[$state] ?? $state)
                            : '—'),
                    TextEntry::make('submitted_at')->label(__('admin.scholarship.submitted'))->dateTime('j M Y, H:i')->placeholder('—'),
                    TextEntry::make('committee_notes')
                        ->label(__('admin.scholarship.notes'))
                        ->placeholder('—')
                        ->prose()
                        ->columnSpanFull(),
                ]),
        ]);
    }

    /** The eligibility questions, each with the answer the student gave. */
    private static function answers(ScholarshipApplication $record): array
    {
        $given = $record->eligibility ?? [];

        return collect(config('scholarship.eligibility'))
            ->map(function (array $question) use ($given) {
                $answer = $given[$question['id']] ?? null;

                $wording = __('scholarship.eligibility.questions.'.$question['id'].'.title');
                $label = $answer === null
                    ? __('admin.scholarship.unanswered')
                    : __('scholarship.eligibility.questions.'.$question['id'].'.options.'.$answer);

                return $wording.' — '.$label;
            })
            ->all();
    }

    /**
     * Which of the four documents are in.
     *
     * Named rather than listed as paths: the committee needs to know whether the
     * judicial record is missing, not where on the disk it sits.
     */
    private static function documents(ScholarshipApplication $record): array
    {
        $held = $record->documents ?? [];

        return collect(config('scholarship.documents'))
            ->map(fn (string $key) => __('scholarship.documents.'.$key.'.name')
                .' — '.(isset($held[$key]) ? __('admin.scholarship.received') : __('admin.scholarship.missing')))
            ->all();
    }

    /**
     * The department's own form, where one was asked for.
     *
     * Linked rather than listed: it is a photograph of paperwork, and the only
     * way to judge it is to look at it. The link is signed and expires — the
     * file sits on the private disk precisely so it is not loose on the web.
     *
     * @return list<string>
     */
    /** Which choices needed the university's own form, and whether it was confirmed. */
    private static function externalFormAcks(ScholarshipApplication $record): array
    {
        return collect(['first', 'second'])
            ->filter(fn (string $slot) => filled($record->{"{$slot}_choice_external_form_url_ack"}))
            ->map(fn (string $slot) => __('admin.scholarship.'.$slot.'_choice').': '
                .__('admin.scholarship.external_form_confirmed', [
                    'url' => $record->{"{$slot}_choice_external_form_url_ack"},
                    'at' => $record->{"{$slot}_choice_external_form_ack_at"}?->format('j M Y, H:i') ?? '—',
                ]))
            ->values()
            ->all();
    }

    private static function choiceForms(ScholarshipApplication $record): array
    {
        $held = $record->documents ?? [];

        return collect(['first', 'second'])
            ->filter(fn (string $slot) => filled($held[$slot.'_choice_form'] ?? null))
            ->map(function (string $slot) use ($held) {
                $url = Storage::disk('local')->temporaryUrl(
                    $held[$slot.'_choice_form'],
                    now()->addMinutes(10),
                );

                return '<a class="fi-link" target="_blank" rel="noopener" href="'.e($url).'">'
                    .e(__('admin.scholarship.'.$slot.'_choice')).'</a>';
            })
            ->values()
            ->all();
    }
}
