<?php

namespace App\Filament\Resources\SdgGoals;

use App\Filament\Resources\SdgGoals\Pages\CreateSdgGoal;
use App\Filament\Resources\SdgGoals\Pages\EditSdgGoal;
use App\Filament\Resources\SdgGoals\Pages\ListSdgGoals;
use App\Filament\Support\Translatable;
use App\Models\SdgGoal;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** The five goals Next Step reports against, in official UN colours. */
class SdgGoalResource extends Resource
{
    protected static ?string $model = SdgGoal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?int $navigationSort = 7;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.sdg');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('number')->label('SDG number')->numeric()->required()->unique(ignoreRecord: true),
                TextInput::make('color')->label('Official UN colour')->required(),
                TextInput::make('figure')->label('Headline figure'),
                TextInput::make('sort')->numeric()->default(0),
                Translatable::tabs(
                    fields: ['title' => 'Goal title', 'what' => 'What we do', 'detail' => 'Detail', 'metric' => 'Indicator'],
                    kinds: ['detail' => 'textarea'],
                    columns: 2,
                ),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('number')->label('SDG')->badge(),
                TextColumn::make('title')->formatStateUsing(fn (SdgGoal $r) => $r->t('title'))->weight('semibold'),
                TextColumn::make('figure'),
                TextColumn::make('metric')->formatStateUsing(fn (SdgGoal $r) => $r->t('metric'))->wrap(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSdgGoals::route('/'),
            'create' => CreateSdgGoal::route('/create'),
            'edit' => EditSdgGoal::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage-content') ?? false;
    }
}
