<?php

namespace App\Filament\Resources\HomeTrackPoints;

use App\Filament\Resources\HomeTrackPoints\Pages\CreateHomeTrackPoint;
use App\Filament\Resources\HomeTrackPoints\Pages\EditHomeTrackPoint;
use App\Filament\Resources\HomeTrackPoints\Pages\ListHomeTrackPoints;
use App\Filament\Support\Translatable;
use App\Models\HomeTrackPoint;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Bullet points under the Expo and Conference cards on the home page.
 */
class HomeTrackPointResource extends Resource
{
    protected static ?string $model = HomeTrackPoint::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static ?int $navigationSort = 9;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.track_points');
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.track_point');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                Select::make('track')
                    ->label(__('admin.fields.track'))
                    ->options([
                        HomeTrackPoint::TRACK_FAIR => __('admin.home.track_fair'),
                        HomeTrackPoint::TRACK_CONFERENCE => __('admin.home.track_conference'),
                    ])
                    ->required()
                    ->native(false),
                Translatable::tabs(
                    fields: ['label' => __('admin.fields.label')],
                ),
                TextInput::make('sort')
                    ->label(__('admin.fields.sort'))
                    ->numeric()
                    ->default(0)
                    ->required(),
                Toggle::make('published')
                    ->label(__('admin.fields.published'))
                    ->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                TextColumn::make('track')
                    ->label(__('admin.fields.track'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        HomeTrackPoint::TRACK_CONFERENCE => __('admin.home.track_conference'),
                        default => __('admin.home.track_fair'),
                    })
                    ->color(fn (string $state): string => $state === HomeTrackPoint::TRACK_CONFERENCE ? 'info' : 'danger'),
                TextColumn::make('label')
                    ->label(__('admin.fields.label'))
                    ->formatStateUsing(fn (HomeTrackPoint $record) => $record->t('label'))
                    ->weight('semibold')
                    ->wrap(),
                TextColumn::make('sort')->label(__('admin.fields.sort'))->sortable(),
                IconColumn::make('published')->label(__('admin.fields.published'))->boolean(),
            ])
            ->filters([
                SelectFilter::make('track')->options([
                    HomeTrackPoint::TRACK_FAIR => __('admin.home.track_fair'),
                    HomeTrackPoint::TRACK_CONFERENCE => __('admin.home.track_conference'),
                ]),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHomeTrackPoints::route('/'),
            'create' => CreateHomeTrackPoint::route('/create'),
            'edit' => EditHomeTrackPoint::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage-content') ?? false;
    }
}
