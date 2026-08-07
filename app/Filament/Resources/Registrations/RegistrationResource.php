<?php

namespace App\Filament\Resources\Registrations;

use App\Filament\Resources\Registrations\Pages\CreateRegistration;
use App\Filament\Resources\Registrations\Pages\EditRegistration;
use App\Filament\Resources\Registrations\Pages\ListRegistrations;
use App\Filament\Resources\Registrations\Pages\ViewRegistration;
use App\Filament\Resources\Registrations\Schemas\RegistrationForm;
use App\Filament\Resources\Registrations\Schemas\RegistrationInfolist;
use App\Filament\Resources\Registrations\Tables\RegistrationsTable;
use App\Models\Registration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Fair registrations — students and parents.
 *
 * The conference track has its own resource: the two never share a form on the
 * public site, and they should not share a work queue in the admin either.
 */
class RegistrationResource extends Resource
{
    protected static ?string $model = Registration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.registrations');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.fair');
    }

    public static function getModelLabel(): string
    {
        return 'fair registration';
    }

    /** Only the fair track; scoped at the query so every page inherits it. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('track', Registration::TRACK_FAIR);
    }

    public static function getNavigationBadge(): ?string
    {
        return number_format(static::getEloquentQuery()->where('status', Registration::STATUS_CONFIRMED)->count());
    }

    public static function form(Schema $schema): Schema
    {
        return RegistrationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RegistrationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RegistrationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MessagesRelationManager::class,
            RelationManagers\CheckInsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRegistrations::route('/'),
            'create' => CreateRegistration::route('/create'),
            'view' => ViewRegistration::route('/{record}'),
            'edit' => EditRegistration::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view-registrations') ?? false;
    }
}
