<?php

namespace App\Filament\Resources\ConferenceRsvps;

use App\Filament\Resources\ConferenceRsvps\Pages\ListConferenceRsvps;
use App\Filament\Resources\ConferenceRsvps\Pages\ViewConferenceRsvp;
use App\Filament\Resources\ConferenceRsvps\Tables\ConferenceRsvpsTable;
use App\Filament\Resources\Registrations\RelationManagers\CheckInsRelationManager;
use App\Filament\Resources\Registrations\RelationManagers\MessagesRelationManager;
use App\Filament\Resources\Registrations\Schemas\RegistrationInfolist;
use App\Models\Registration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Conference RSVPs — government and official delegates.
 *
 * Separate from the fair queue: these are approved by the protocol team, carry an
 * institution on the badge, and are confirmed by e-mail rather than WhatsApp.
 */
class ConferenceRsvpResource extends Resource
{
    protected static ?string $model = Registration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.registrations');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.conference');
    }

    public static function getModelLabel(): string
    {
        return 'conference RSVP';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('track', Registration::TRACK_CONFERENCE);
    }

    /** Pending approvals are the number the protocol team needs to see. */
    public static function getNavigationBadge(): ?string
    {
        $pending = static::getEloquentQuery()->where('status', Registration::STATUS_PENDING)->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function infolist(Schema $schema): Schema
    {
        return RegistrationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConferenceRsvpsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            MessagesRelationManager::class,
            CheckInsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListConferenceRsvps::route('/'),
            'view' => ViewConferenceRsvp::route('/{record}'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view-registrations') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
