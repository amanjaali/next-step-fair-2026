<?php

namespace App\Filament\Resources\ScholarshipApplications;

use App\Filament\Resources\ScholarshipApplications\Pages\EditScholarshipApplication;
use App\Filament\Resources\ScholarshipApplications\Pages\ListScholarshipApplications;
use App\Filament\Resources\ScholarshipApplications\Pages\ViewScholarshipApplication;
use App\Filament\Resources\ScholarshipApplications\Schemas\ScholarshipApplicationInfolist;
use App\Filament\Resources\ScholarshipApplications\Tables\ScholarshipApplicationsTable;
use App\Models\ScholarshipApplication;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * The committee's screen for the National Scholarship Program.
 *
 * The applications were being written and submitted with nowhere to read them:
 * every answer, document and score was going into the database and no screen in
 * the dashboard listed them. This is that screen.
 *
 * It is deliberately not an editing form for the application. What a student
 * wrote is theirs and is shown as received; what the committee adds — three
 * scores, notes, a stage and a decision — is the only part that can be typed
 * here. Moving the stage is what the student's own tracker reads, so a change
 * made in this screen is visible to them within the minute.
 */
class ScholarshipApplicationResource extends Resource
{
    protected static ?string $model = ScholarshipApplication::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.registrations');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.scholarship');
    }

    /** The queue length, so the committee sees new work without opening it. */
    public static function getNavigationBadge(): ?string
    {
        $waiting = ScholarshipApplication::query()
            ->where('status', ScholarshipApplication::STATUS_SUBMITTED)
            ->count();

        return $waiting > 0 ? (string) $waiting : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    /**
     * Only the committee's own fields.
     *
     * The application is read-only by design — a screen that lets a reviewer
     * retype an applicant's statement is a screen that loses the original.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.scholarship.stage'))
                ->description(__('admin.scholarship.stage_help'))
                ->columns(2)
                ->schema([
                    Select::make('status')
                        ->label(__('admin.fields.status'))
                        ->options(self::statusOptions())
                        ->required()
                        ->live(),
                    Select::make('decision')
                        ->label(__('admin.scholarship.decision'))
                        ->options(self::decisionOptions())
                        ->placeholder(__('admin.scholarship.no_decision'))
                        ->helperText(__('admin.scholarship.decision_help')),
                ]),

            Section::make(__('admin.scholarship.scores'))
                ->description(__('admin.scholarship.scores_help'))
                ->columns(3)
                ->schema([
                    TextInput::make('score_academic')
                        ->label(__('admin.scholarship.score_academic'))
                        ->numeric()->minValue(0)->maxValue(40)->step(0.5)->suffix('/ 40'),
                    TextInput::make('score_feasibility')
                        ->label(__('admin.scholarship.score_feasibility'))
                        ->numeric()->minValue(0)->maxValue(30)->step(0.5)->suffix('/ 30'),
                    TextInput::make('score_interview')
                        ->label(__('admin.scholarship.score_interview'))
                        ->numeric()->minValue(0)->maxValue(30)->step(0.5)->suffix('/ 30'),
                ]),

            Section::make(__('admin.scholarship.notes'))
                ->description(__('admin.scholarship.notes_help'))
                ->schema([
                    Textarea::make('committee_notes')
                        ->label(__('admin.scholarship.notes'))
                        ->rows(6)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ScholarshipApplicationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScholarshipApplicationsTable::configure($table);
    }

    /** @return array<string, string> */
    public static function statusOptions(): array
    {
        return [
            ScholarshipApplication::STATUS_SUBMITTED => __('scholarship.status.stages.submitted.title'),
            ScholarshipApplication::STATUS_SCREENING => __('scholarship.status.stages.screening.title'),
            ScholarshipApplication::STATUS_SHORTLISTED => __('scholarship.status.stages.shortlisted.title'),
            ScholarshipApplication::STATUS_INTERVIEW => __('scholarship.status.stages.interview.title'),
            ScholarshipApplication::STATUS_DECIDED => __('scholarship.status.stages.decided.title'),
            ScholarshipApplication::STATUS_WITHDRAWN => __('admin.scholarship.withdrawn'),
        ];
    }

    /** @return array<string, string> */
    public static function decisionOptions(): array
    {
        return [
            ScholarshipApplication::DECISION_AWARDED => __('admin.scholarship.awarded'),
            ScholarshipApplication::DECISION_RESERVE => __('admin.scholarship.reserve'),
            ScholarshipApplication::DECISION_DECLINED => __('admin.scholarship.declined'),
        ];
    }

    /** @return array<string, string> */
    public static function regionOptions(): array
    {
        return collect(config('scholarship.regions'))
            ->map(fn (array $region) => $region['name'].' ('.$region['seats'].')')
            ->all();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScholarshipApplications::route('/'),
            'view' => ViewScholarshipApplication::route('/{record}'),
            'edit' => EditScholarshipApplication::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('review-scholarships') ?? false;
    }

    public static function canCreate(): bool
    {
        // Applications come from students, never from this screen.
        return false;
    }

    public static function canDelete($record): bool
    {
        // A submitted application is a record of what somebody sent us.
        return false;
    }
}
