<?php

namespace App\Filament\Resources\Organizations;

use App\Filament\Resources\Organizations\Pages\CreateOrganization;
use App\Filament\Resources\Organizations\Pages\EditOrganization;
use App\Filament\Resources\Organizations\Pages\ListOrganizations;
use App\Filament\Support\Translatable;
use App\Models\Hall;
use App\Models\Organization;
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

/** Universities, institutes, exhibitors, partners and sponsors, in one place. */
class OrganizationResource extends Resource
{
    protected static ?string $model = Organization::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.partners');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.organizations');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Organisation')->schema([
                Translatable::tabs(
                    fields: ['name' => 'Name', 'description' => 'One-line description', 'badge' => 'Tier chip'],
                    kinds: ['description' => 'textarea'],
                ),
            ]),

            Section::make('Placement')->columns(3)->schema([
                Select::make('kind')
                    ->options([
                        Organization::KIND_UNIVERSITY => 'University',
                        Organization::KIND_INSTITUTE => 'Institute',
                        Organization::KIND_EXHIBITOR => 'Exhibitor',
                        Organization::KIND_STRATEGIC => 'Strategic partner',
                        Organization::KIND_SUPPORTER => 'Institutional supporter',
                        Organization::KIND_SPONSOR => 'Sponsor',
                        Organization::KIND_MEDIA => 'Media partner',
                    ])
                    ->required(),
                Select::make('tier')
                    ->options(['platinum' => 'Platinum', 'gold' => 'Gold', 'silver' => 'Silver', 'bronze' => 'Bronze'])
                    ->helperText('Sponsors only.'),
                TextInput::make('website')->url(),
                TextInput::make('booth')->label('Booth code'),
                Select::make('hall_id')
                    ->label('Hall')
                    ->options(fn () => Hall::all()->mapWithKeys(fn (Hall $h) => [$h->id => $h->t('name')])),
                TextInput::make('country')->default('IQ')->maxLength(4),
                TextInput::make('since_year')->numeric()->label('Supporting since'),
                TextInput::make('year')->numeric()->default(2026)->required(),
                TextInput::make('sort')->numeric()->default(0),
                Toggle::make('published')->default(true),
                FileUpload::make('logo_path')->label('Logo')->image()->directory('logos')->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('name')
                    ->formatStateUsing(fn (Organization $record) => $record->t('name'))
                    ->weight('semibold')
                    ->wrap()
                    ->searchable(query: fn ($query, $search) => $query->where('name', 'like', "%{$search}%")),
                TextColumn::make('kind')->badge(),
                TextColumn::make('tier')->badge()->color('warning')->placeholder('—'),
                TextColumn::make('booth')->placeholder('—'),
                TextColumn::make('year')->sortable(),
                TextColumn::make('published')->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Published' : 'Hidden')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('kind')->multiple()->options([
                    Organization::KIND_UNIVERSITY => 'University',
                    Organization::KIND_INSTITUTE => 'Institute',
                    Organization::KIND_EXHIBITOR => 'Exhibitor',
                    Organization::KIND_STRATEGIC => 'Strategic partner',
                    Organization::KIND_SUPPORTER => 'Supporter',
                    Organization::KIND_SPONSOR => 'Sponsor',
                    Organization::KIND_MEDIA => 'Media partner',
                ]),
                SelectFilter::make('year')->options(fn () => Organization::distinct()->pluck('year', 'year')->all()),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrganizations::route('/'),
            'create' => CreateOrganization::route('/create'),
            'edit' => EditOrganization::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyPermission(['manage-content', 'manage-sponsors']) ?? false;
    }
}
