<?php

namespace App\Filament\Resources\ScholarshipUniversities;

use App\Filament\Resources\ScholarshipUniversities\Pages\CreateScholarshipUniversity;
use App\Filament\Resources\ScholarshipUniversities\Pages\EditScholarshipUniversity;
use App\Filament\Resources\ScholarshipUniversities\Pages\ListScholarshipUniversities;
use App\Filament\Support\Translatable;
use App\Models\ScholarshipUniversity;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/**
 * Where the scholarship seats are, and in what.
 *
 * Edited here so seat pledges can change between cycles without a deploy. The
 * departments repeater is the whole point: seats are held per department, and
 * that list is what a student checks before applying.
 */
class ScholarshipUniversityResource extends Resource
{
    protected static ?string $model = ScholarshipUniversity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'scholarship university';

    protected static ?string $pluralModelLabel = 'scholarship universities';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.registrations');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.scholarship_universities');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.scholarship_universities.identity'))
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label(__('admin.fields.name'))
                        ->required()
                        ->maxLength(200)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, callable $set, ?ScholarshipUniversity $record) {
                            if ($record?->exists) {
                                return;
                            }
                            $set('slug', Str::slug((string) $state));
                        }),
                    TextInput::make('slug')
                        ->label(__('admin.fields.slug'))
                        ->required()
                        ->maxLength(80)
                        ->unique(ignoreRecord: true)
                        ->helperText(__('admin.scholarship_universities.slug_help')),
                    TextInput::make('city')
                        ->label(__('admin.scholarship_universities.city'))
                        ->required()
                        ->maxLength(80),
                    TextInput::make('language')
                        ->label(__('admin.scholarship_universities.language'))
                        ->required()
                        ->maxLength(80)
                        ->helperText(__('admin.scholarship_universities.language_help')),
                    Select::make('tier')
                        ->label(__('admin.scholarship_universities.tier'))
                        ->options(collect(ScholarshipUniversity::tiers())
                            ->mapWithKeys(fn ($t) => [$t => __("scholarship.tiers.$t")]))
                        ->required()
                        ->default(ScholarshipUniversity::TIER_DONOR),
                    Select::make('housing')
                        ->label(__('admin.scholarship_universities.housing'))
                        ->options(collect(ScholarshipUniversity::housingOptions())
                            ->mapWithKeys(fn ($h) => [$h => __("scholarship.housing.$h")]))
                        ->required()
                        ->default(ScholarshipUniversity::HOUSING_NONE),
                    TextInput::make('founded')
                        ->label(__('admin.scholarship_universities.founded'))
                        ->numeric()
                        ->minValue(1800)
                        ->maxValue(2100),
                    TextInput::make('students')
                        ->label(__('admin.scholarship_universities.students'))
                        ->numeric()
                        ->minValue(0),
                    TextInput::make('sort')
                        ->label(__('admin.fields.sort'))
                        ->numeric()
                        ->default(0)
                        ->helperText(__('admin.scholarship_universities.sort_help')),
                    Toggle::make('published')
                        ->label(__('admin.fields.published'))
                        ->helperText(__('admin.scholarship_universities.published_help'))
                        ->default(true),
                ]),

            Section::make(__('admin.scholarship_universities.about'))
                ->description(__('admin.scholarship_universities.about_help'))
                ->schema([
                    Translatable::tabs(
                        fields: ['about' => 'About this university'],
                        kinds: ['about' => 'textarea'],
                    ),
                ]),

            Section::make(__('admin.scholarship_universities.departments'))
                ->description(__('admin.scholarship_universities.departments_help'))
                ->schema([
                    Repeater::make('departments')
                        ->relationship()
                        ->label('')
                        ->addActionLabel(__('admin.scholarship_universities.add_department'))
                        ->orderColumn('sort')
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => filled($state['name'] ?? null)
                            ? ($state['name'].' · '.($state['seats'] ?? 0))
                            : null)
                        ->minItems(1)
                        ->defaultItems(1)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('admin.scholarship_universities.department_name'))
                                ->required()
                                ->maxLength(120)
                                ->columnSpan(2),
                            TextInput::make('seats')
                                ->label(__('admin.scholarship_universities.seats'))
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->maxValue(40)
                                ->default(1),
                        ])
                        ->columns(3),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->modifyQueryUsing(fn ($query) => $query->with('departments'))
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->wrap(),
                TextColumn::make('city')
                    ->label(__('admin.scholarship_universities.city'))
                    ->sortable(),
                TextColumn::make('tier')
                    ->label(__('admin.scholarship_universities.tier'))
                    ->formatStateUsing(fn (string $state) => __("scholarship.tiers.$state")),
                TextColumn::make('departments_count')
                    ->counts('departments')
                    ->label(__('admin.scholarship_universities.departments')),
                TextColumn::make('seats')
                    ->label(__('admin.scholarship_universities.seats'))
                    ->state(fn (ScholarshipUniversity $record) => $record->departments->sum('seats')),
                IconColumn::make('published')
                    ->label(__('admin.fields.published'))
                    ->boolean(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScholarshipUniversities::route('/'),
            'create' => CreateScholarshipUniversity::route('/create'),
            'edit' => EditScholarshipUniversity::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return ($user?->can('manage-content') || $user?->can('review-scholarships')) ?? false;
    }
}
