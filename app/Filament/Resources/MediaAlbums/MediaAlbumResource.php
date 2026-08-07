<?php

namespace App\Filament\Resources\MediaAlbums;

use App\Filament\Resources\MediaAlbums\Pages\CreateMediaAlbum;
use App\Filament\Resources\MediaAlbums\Pages\EditMediaAlbum;
use App\Filament\Resources\MediaAlbums\Pages\ListMediaAlbums;
use App\Filament\Support\Translatable;
use App\Models\MediaAlbum;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/** Photo albums by year → day → session. Alt text is required on every image. */
class MediaAlbumResource extends Resource
{
    protected static ?string $model = MediaAlbum::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.albums');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Album')->schema([
                Translatable::tabs(
                    fields: ['title' => 'Title', 'description' => 'Description'],
                    kinds: ['description' => 'textarea'],
                ),
            ]),

            Section::make('Filing')->columns(4)->schema([
                TextInput::make('year')->numeric()->required(),
                Select::make('day')->options([1 => 'Day 1', 2 => 'Day 2', 3 => 'Day 3']),
                Select::make('category')->options(array_combine(
                    ['Opening ceremony', 'Panels', 'Booths', 'Workshops', 'Tournaments', 'Awards', 'Behind the scenes'],
                    ['Opening ceremony', 'Panels', 'Booths', 'Workshops', 'Tournaments', 'Awards', 'Behind the scenes'],
                )),
                Toggle::make('published')->default(true),
                FileUpload::make('cover_path')->label('Cover')->image()->directory('albums')->columnSpanFull(),
            ]),

            Section::make('Photographs')->schema([
                Repeater::make('items')
                    ->relationship()
                    ->schema([
                        FileUpload::make('path')
                            ->label('Image')
                            ->image()
                            ->imageEditor()
                            ->directory('media')
                            ->columnSpan(2),
                        TextInput::make('alt.en')->label('Alt text (EN)')->required(),
                        TextInput::make('alt.ku')->label('Alt text (KU)')->extraInputAttributes(['dir' => 'rtl']),
                        TextInput::make('alt.ar')->label('Alt text (AR)')->extraInputAttributes(['dir' => 'rtl']),
                        Toggle::make('downloadable')->label('Press download'),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->collapsible()
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('year', 'desc')
            ->columns([
                TextColumn::make('title')->formatStateUsing(fn (MediaAlbum $r) => $r->t('title'))->weight('semibold'),
                TextColumn::make('year')->sortable(),
                TextColumn::make('day')->placeholder('—'),
                TextColumn::make('category')->badge()->color('gray')->placeholder('—'),
                TextColumn::make('items_count')->counts('items')->label('Photos'),
            ])
            ->filters([
                SelectFilter::make('year')->options(fn () => MediaAlbum::distinct()->pluck('year', 'year')->all()),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMediaAlbums::route('/'),
            'create' => CreateMediaAlbum::route('/create'),
            'edit' => EditMediaAlbum::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage-media') ?? false;
    }
}
