<?php

namespace App\Filament\Resources\FeatureCards;

use App\Filament\Resources\FeatureCards\Pages\CreateFeatureCard;
use App\Filament\Resources\FeatureCards\Pages\EditFeatureCard;
use App\Filament\Resources\FeatureCards\Pages\ListFeatureCards;
use App\Filament\Support\Translatable;
use App\Models\FeatureCard;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * "Why attend" cards. The icons are bar compositions taken from the logo's Step
 * element: right angles only, one magenta bar each.
 */
class FeatureCardResource extends Resource
{
    protected static ?string $model = FeatureCard::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?int $navigationSort = 8;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.cards');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                Translatable::tabs(
                    fields: ['title' => 'Title', 'body' => 'Body'],
                    kinds: ['body' => 'textarea'],
                    columns: 2,
                ),
                Textarea::make('icon_path')->label('Icon path (ink)')->rows(2),
                Textarea::make('icon_accent')->label('Icon path (magenta)')->rows(2),
                TextInput::make('sort')->numeric()->default(0),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('title')->formatStateUsing(fn (FeatureCard $r) => $r->t('title'))->weight('semibold'),
                TextColumn::make('body')->formatStateUsing(fn (FeatureCard $r) => $r->t('body'))->wrap()->limit(80),
                TextColumn::make('sort')->sortable(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFeatureCards::route('/'),
            'create' => CreateFeatureCard::route('/create'),
            'edit' => EditFeatureCard::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage-content') ?? false;
    }
}
