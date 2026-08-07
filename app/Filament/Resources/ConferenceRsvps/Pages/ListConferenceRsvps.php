<?php

namespace App\Filament\Resources\ConferenceRsvps\Pages;

use App\Filament\Exports\RegistrationExporter;
use App\Filament\Resources\ConferenceRsvps\ConferenceRsvpResource;
use App\Models\Registration;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListConferenceRsvps extends ListRecords
{
    protected static string $resource = ConferenceRsvpResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()->label(__('admin.actions.export'))->exporter(RegistrationExporter::class),
        ];
    }

    public function getTabs(): array
    {
        $base = fn () => ConferenceRsvpResource::getEloquentQuery();

        return [
            'pending' => Tab::make('Pending approval')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Registration::STATUS_PENDING))
                ->badge(fn () => $base()->where('status', Registration::STATUS_PENDING)->count())
                ->badgeColor('warning'),

            'all' => Tab::make('All')->badge(fn () => $base()->count()),

            'government' => Tab::make('Government')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'government'))
                ->badge(fn () => $base()->where('type', 'government')->count()),

            'official' => Tab::make('Official')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'official'))
                ->badge(fn () => $base()->where('type', 'official')->count()),

            'letters' => Tab::make('Invitation letters')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('invitation_letter', true))
                ->badge(fn () => $base()->where('invitation_letter', true)->count()),

            'media' => Tab::make('Media')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('media_accreditation', true)),
        ];
    }
}
