<?php

namespace App\Filament\Resources\Downloads;

use App\Filament\Resources\Downloads\Pages\CreateDownload;
use App\Filament\Resources\Downloads\Pages\EditDownload;
use App\Filament\Resources\Downloads\Pages\ListDownloads;
use App\Filament\Support\Translatable;
use App\Models\Download;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
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

/** Impact reports, press-kit assets, the sponsorship deck and the floor plan. */
class DownloadResource extends Resource
{
    protected static ?string $model = Download::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownTray;

    protected static ?int $navigationSort = 6;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.downloads');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                Translatable::tabs(
                    fields: ['name' => 'Name', 'kind' => 'Kind', 'description' => 'Description'],
                    kinds: ['description' => 'textarea'],
                    columns: 2,
                ),
                Select::make('group')
                    ->options(['report' => 'Impact report', 'press' => 'Press kit', 'deck' => 'Sponsorship deck', 'floorplan' => 'Floor plan'])
                    ->required(),
                TextInput::make('size_label')->label('Size label')->placeholder('PDF, 6.1 MB'),
                TextInput::make('year')->numeric(),
                TextInput::make('sort')->numeric()->default(0),
                TextInput::make('accent')->default('#B64698'),
                Toggle::make('published')->default(true),
                FileUpload::make('file_path')->label('File')->directory('downloads')->columnSpanFull(),
                TextInput::make('external_url')->label('Or an external URL')->url()->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('name')->formatStateUsing(fn (Download $r) => $r->t('name'))->weight('semibold'),
                TextColumn::make('group')->badge(),
                TextColumn::make('year')->placeholder('—'),
                TextColumn::make('size_label')->label('Size')->placeholder('—'),
                TextColumn::make('downloads')->label('Downloads')->numeric(),
            ])
            ->filters([
                SelectFilter::make('group')->options(['report' => 'Impact report', 'press' => 'Press kit', 'deck' => 'Deck', 'floorplan' => 'Floor plan']),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDownloads::route('/'),
            'create' => CreateDownload::route('/create'),
            'edit' => EditDownload::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage-content') ?? false;
    }
}
