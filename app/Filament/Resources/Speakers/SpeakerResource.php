<?php

namespace App\Filament\Resources\Speakers;

use App\Filament\Resources\Speakers\Pages\CreateSpeaker;
use App\Filament\Resources\Speakers\Pages\EditSpeaker;
use App\Filament\Resources\Speakers\Pages\ListSpeakers;
use App\Filament\Support\Translatable;
use App\Models\Speaker;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SpeakerResource extends Resource
{
    protected static ?string $model = Speaker::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.programme');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.speakers');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Profile')->schema([
                Translatable::tabs(
                    fields: [
                        'name' => 'Name',
                        'role' => 'Title / position',
                        'organization' => 'Institution',
                        'bio' => 'Biography',
                    ],
                    kinds: ['bio' => 'editor-minimal'],
                    columns: 2,
                ),
            ]),

            Section::make('Placement')->columns(3)->schema([
                Select::make('track')
                    ->options(['fair' => 'Fair', 'conference' => 'Conference'])
                    ->default('fair')
                    ->required(),
                Select::make('speaker_type')
                    ->label('Role')
                    ->options([
                        'speaker' => 'Speaker',
                        'panelist' => 'Panelist',
                        'moderator' => 'Moderator',
                        'workshop' => 'Workshop leader',
                    ])
                    ->default('speaker')
                    ->required(),
                TextInput::make('country')->default('IQ')->maxLength(4),
                TextInput::make('year')->numeric()->default(2026)->required(),
                TextInput::make('sort')->numeric()->default(0),
                Toggle::make('featured')->label('Feature on the home page'),
                Toggle::make('published')->default(true),

                FileUpload::make('photo_path')
                    ->label('Headshot')
                    ->image()
                    ->imageEditor()
                    ->imageEditorAspectRatios(['1:1'])
                    ->directory('speakers')
                    ->columnSpanFull(),

                TagsInput::make('topics.en')->label('Topics (EN)')->columnSpanFull(),

                Repeater::make('links')
                    ->schema([
                        TextInput::make('label')->required(),
                        TextInput::make('url')->url()->required(),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->columns([
                ImageColumn::make('photo_path')->label('')->circular()->disk('public'),
                TextColumn::make('name')
                    ->formatStateUsing(fn (Speaker $record) => $record->t('name'))
                    ->weight('semibold')
                    ->searchable(query: fn ($query, $search) => $query->where('name', 'like', "%{$search}%")),
                TextColumn::make('organization')
                    ->label(__('admin.fields.institution'))
                    ->formatStateUsing(fn (Speaker $record) => $record->t('organization'))
                    ->wrap(),
                TextColumn::make('track')->badge()
                    ->color(fn (string $state) => $state === 'conference' ? 'info' : 'primary'),
                TextColumn::make('speaker_type')->label('Role')->badge()->color('gray'),
                TextColumn::make('sessions_count')->counts('sessions')->label('Sessions'),
                TextColumn::make('year')->sortable(),
            ])
            ->filters([
                SelectFilter::make('track')->options(['fair' => 'Fair', 'conference' => 'Conference']),
                SelectFilter::make('year')->options(fn () => Speaker::distinct()->pluck('year', 'year')->all()),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSpeakers::route('/'),
            'create' => CreateSpeaker::route('/create'),
            'edit' => EditSpeaker::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage-content') ?? false;
    }
}
