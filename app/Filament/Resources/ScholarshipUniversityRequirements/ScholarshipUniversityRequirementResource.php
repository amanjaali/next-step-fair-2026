<?php

namespace App\Filament\Resources\ScholarshipUniversityRequirements;

use App\Filament\Resources\ScholarshipUniversityRequirements\Pages\CreateScholarshipUniversityRequirement;
use App\Filament\Resources\ScholarshipUniversityRequirements\Pages\EditScholarshipUniversityRequirement;
use App\Filament\Resources\ScholarshipUniversityRequirements\Pages\ListScholarshipUniversityRequirements;
use App\Filament\Support\Translatable;
use App\Models\ScholarshipUniversityRequirement;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * What a student must read and tick before picking a given university on the
 * National Scholarship form — one entry per university slug, shared by both
 * the hand-curated founding/donor universities and any partner added through
 * Opportunities.
 *
 * A university with nothing entered here simply shows no requirements box and
 * no checkbox: this is additive, not a second gate every university needs.
 */
class ScholarshipUniversityRequirementResource extends Resource
{
    protected static ?string $model = ScholarshipUniversityRequirement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.scholarship_university_requirements');
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.scholarship_university_requirement_singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.scholarship_university_requirements');
    }

    /** name => slug, for every university currently known to the scholarship pages. */
    public static function universityOptions(): array
    {
        return collect(ns_scholarship_universities())
            ->mapWithKeys(fn (array $u) => [$u['slug'] => $u['name']])
            ->all();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.scholarship_requirements.which'))->schema([
                Select::make('university_slug')
                    ->label(__('admin.scholarship_requirements.university'))
                    ->options(fn () => self::universityOptions())
                    ->searchable()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->disabledOn('edit')
                    ->helperText(__('admin.scholarship_requirements.university_help')),
            ]),

            Section::make(__('admin.scholarship_requirements.content'))->schema([
                Translatable::tabs(
                    fields: [
                        'requirements' => 'What the student must read and agree to before choosing this university',
                    ],
                    kinds: ['requirements' => 'editor'],
                ),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('university_slug')
                    ->label(__('admin.scholarship_requirements.university'))
                    ->formatStateUsing(fn (string $state) => self::universityOptions()[$state] ?? $state)
                    ->weight('semibold')
                    ->searchable(),
                TextColumn::make('updated_at')->label(__('admin.fields.updated_at'))->dateTime('j M Y')->sortable(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScholarshipUniversityRequirements::route('/'),
            'create' => CreateScholarshipUniversityRequirement::route('/create'),
            'edit' => EditScholarshipUniversityRequirement::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage-content') ?? false;
    }
}
