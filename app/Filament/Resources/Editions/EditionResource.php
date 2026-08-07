<?php

namespace App\Filament\Resources\Editions;

use App\Filament\Resources\Editions\Pages\CreateEdition;
use App\Filament\Resources\Editions\Pages\EditEdition;
use App\Filament\Resources\Editions\Pages\ListEditions;
use App\Filament\Support\Translatable;
use App\Models\Edition;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * A past edition. Adding 2027 is a row here — the year is a route parameter and
 * the archive template reads whatever this record contains.
 */
class EditionResource extends Resource
{
    protected static ?string $model = Edition::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.editions');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Edition')->columns(3)->schema([
                TextInput::make('year')->numeric()->required()->unique(ignoreRecord: true),
                TextInput::make('edition_no')->label('Edition number')->numeric()->required(),
                TextInput::make('organizer_name')->label('Organiser'),
                Toggle::make('published')->default(true),
                FileUpload::make('cover_path')->label('Cover')->image()->directory('editions'),
                FileUpload::make('report_path')->label('Impact report (PDF)')->directory('reports'),
            ]),

            Section::make('Headline copy')->schema([
                Translatable::tabs(
                    fields: [
                        'edition_label' => 'Edition label',
                        'dates_label' => 'Dates',
                        'venue_label' => 'Venue',
                        'headline' => 'Headline',
                        'summary' => 'Summary',
                        'theme_title' => 'Themes heading',
                    ],
                    kinds: ['summary' => 'textarea'],
                    columns: 2,
                ),
            ]),

            Section::make("Organiser's address")->schema([
                Translatable::tabs(
                    fields: [
                        'organizer_role' => 'Role',
                        'speech_where' => 'Where and when',
                        'speech_quote' => 'Pull quote',
                        'speech' => 'Full address',
                    ],
                    kinds: ['speech_quote' => 'textarea', 'speech' => 'editor'],
                ),
            ]),

            Section::make('Figures and blocks')->schema([
                Repeater::make('stats')
                    ->schema([
                        TextInput::make('k.en')->label('Label (EN)'),
                        TextInput::make('k.ku')->label('Label (KU)'),
                        TextInput::make('k.ar')->label('Label (AR)'),
                        TextInput::make('v')->label('Value'),
                    ])
                    ->columns(4)
                    ->defaultItems(0)
                    ->columnSpanFull(),

                Repeater::make('panels')
                    ->label('Panels & seminars')
                    ->schema([
                        TextInput::make('day'),
                        TextInput::make('time'),
                        TextInput::make('type'),
                        TextInput::make('hall'),
                        TextInput::make('title')->columnSpan(2),
                        TextInput::make('who')->columnSpan(2),
                        TextInput::make('attendance'),
                    ])
                    ->columns(4)
                    ->defaultItems(0)
                    ->columnSpanFull(),

                Repeater::make('speakers')
                    ->label('That year\'s speakers')
                    ->schema([
                        TextInput::make('name'),
                        TextInput::make('role'),
                        TextInput::make('org'),
                        TextInput::make('role2')->label('Billing'),
                    ])
                    ->columns(4)
                    ->defaultItems(0)
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('year', 'desc')
            ->columns([
                TextColumn::make('year')->weight('semibold')->sortable(),
                TextColumn::make('edition_label')->formatStateUsing(fn (Edition $r) => $r->t('edition_label')),
                TextColumn::make('headline')->formatStateUsing(fn (Edition $r) => $r->t('headline'))->wrap(),
                TextColumn::make('organizer_name')->label('Organiser'),
                TextColumn::make('published')->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Published' : 'Hidden')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEditions::route('/'),
            'create' => CreateEdition::route('/create'),
            'edit' => EditEdition::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage-content') ?? false;
    }
}
