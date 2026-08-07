<?php

namespace App\Filament\Resources\Halls;

use App\Filament\Resources\Halls\Pages\CreateHall;
use App\Filament\Resources\Halls\Pages\EditHall;
use App\Filament\Resources\Halls\Pages\ListHalls;
use App\Filament\Support\Translatable;
use App\Models\Hall;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** Halls and the booths inside them — the source of the floor plan. */
class HallResource extends Resource
{
    protected static ?string $model = Hall::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.programme');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.halls');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('code')->required()->maxLength(4)->unique(ignoreRecord: true),
                TextInput::make('color')->label('Zone colour')->required(),
                TextInput::make('capacity')->numeric(),
                TextInput::make('sort')->numeric()->default(0),
                Translatable::tabs(
                    fields: ['name' => 'Name', 'meta' => 'Meta line', 'description' => 'Description'],
                    kinds: ['description' => 'textarea'],
                    columns: 2,
                ),
                Repeater::make('booths')
                    ->relationship()
                    ->schema([
                        TextInput::make('code')->required(),
                        TextInput::make('name.en')->label('Name (EN)')->required(),
                        TextInput::make('kind.en')->label('Description (EN)'),
                        TextInput::make('sort')->numeric()->default(0),
                    ])
                    ->columns(4)
                    ->defaultItems(0)
                    ->collapsible()
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('code')->badge(),
                TextColumn::make('name')->formatStateUsing(fn (Hall $r) => $r->t('name'))->weight('semibold'),
                TextColumn::make('meta')->formatStateUsing(fn (Hall $r) => $r->t('meta')),
                TextColumn::make('booths_count')->counts('booths')->label('Booths'),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHalls::route('/'),
            'create' => CreateHall::route('/create'),
            'edit' => EditHall::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage-content') ?? false;
    }
}
