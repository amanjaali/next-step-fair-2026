<?php

namespace App\Filament\Resources\Registrations\Pages;

use App\Filament\Exports\RegistrationExporter;
use App\Filament\Resources\Registrations\RegistrationResource;
use App\Models\Registration;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListRegistrations extends ListRecords
{
    protected static string $resource = RegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->label(__('admin.actions.export'))
                ->exporter(RegistrationExporter::class),
            CreateAction::make()
                ->label(__('admin.actions.walk_in'))
                ->mutateDataUsing(function (array $data) {
                    // Walk-ins are entered by staff at the door and are confirmed
                    // on the spot: there is no OTP round-trip at the desk.
                    $data['track'] = Registration::TRACK_FAIR;
                    $data['is_walk_in'] = true;
                    $data['created_by'] = auth()->id();
                    $data['confirmed_at'] = now();
                    $data['consented_at'] = now();

                    return $data;
                }),
        ];
    }

    /** Tabs are the desk's real workflow, not an afterthought. */
    public function getTabs(): array
    {
        $base = fn () => RegistrationResource::getEloquentQuery();

        return [
            'all' => Tab::make('All')
                ->badge(fn () => $base()->count()),

            'students' => Tab::make('Students')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'student'))
                ->badge(fn () => $base()->where('type', 'student')->count()),

            'parents' => Tab::make('Parents')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'parent'))
                ->badge(fn () => $base()->where('type', 'parent')->count()),

            'visitors' => Tab::make('Visitor passes')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', Registration::TYPE_VISITOR))
                ->badge(fn () => $base()->where('type', Registration::TYPE_VISITOR)->count())
                ->badgeColor('warning'),

            'awaiting_otp' => Tab::make('Awaiting OTP')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Registration::STATUS_AWAITING_OTP))
                ->badge(fn () => $base()->where('status', Registration::STATUS_AWAITING_OTP)->count())
                ->badgeColor('warning'),

            'checked_in' => Tab::make('Checked in')
                ->modifyQueryUsing(fn (Builder $query) => $query->has('checkIns'))
                ->badge(fn () => $base()->has('checkIns')->count())
                ->badgeColor('success'),

            'cancelled' => Tab::make('Cancelled')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Registration::STATUS_CANCELLED)),
        ];
    }
}
