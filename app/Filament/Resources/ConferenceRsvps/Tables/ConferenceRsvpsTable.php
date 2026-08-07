<?php

namespace App\Filament\Resources\ConferenceRsvps\Tables;

use App\Filament\Exports\RegistrationExporter;
use App\Filament\Support\RegistrationActions;
use App\Models\Registration;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ConferenceRsvpsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->persistFiltersInSession()
            ->columns([
                TextColumn::make('full_name')
                    ->label(__('admin.fields.name'))
                    ->weight('semibold')
                    ->description(fn (Registration $record) => $record->position)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('organization')
                    ->label(__('admin.fields.institution'))
                    ->wrap()
                    ->searchable(),

                TextColumn::make('type')
                    ->label(__('admin.fields.type'))
                    ->badge()
                    ->color(fn (string $state) => $state === 'government' ? 'info' : 'gray'),

                TextColumn::make('email')
                    ->label(__('admin.fields.email'))
                    ->copyable()
                    // Encrypted at rest; searched through the keyed hash, so the
                    // whole address matches and a fragment does not.
                    ->searchable(query: fn (Builder $query, string $search) => $query
                        ->orWhere('email_hash', Registration::hashValue(strtolower($search))))
                    ->toggleable(),

                TextColumn::make('city')->label(__('admin.fields.city'))->toggleable(),

                TextColumn::make('delegation_size')->label('Delegation')->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('invitation_letter')
                    ->label('Letter')
                    ->boolean()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label(__('admin.fields.status'))
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        Registration::STATUS_CONFIRMED => 'success',
                        Registration::STATUS_CHECKED_IN => 'info',
                        Registration::STATUS_PENDING => 'warning',
                        Registration::STATUS_CANCELLED, Registration::STATUS_REJECTED => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('admin.fields.registered'))
                    ->dateTime('j M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')->options(['government' => 'Government', 'official' => 'Official']),
                SelectFilter::make('status')->multiple()->options([
                    Registration::STATUS_PENDING => 'Pending approval',
                    Registration::STATUS_CONFIRMED => 'Confirmed',
                    Registration::STATUS_CHECKED_IN => 'Checked in',
                    Registration::STATUS_CANCELLED => 'Cancelled',
                ]),
                SelectFilter::make('org_type')->label('Organisation type')->options([
                    'university' => 'University',
                    'international' => 'International organization',
                    'ngo' => 'NGO',
                    'diplomatic' => 'Diplomatic mission',
                    'private' => 'Private sector',
                    'media' => 'Media',
                    'association' => 'Association',
                ]),
                TernaryFilter::make('invitation_letter')->label('Needs invitation letter'),
                TernaryFilter::make('media_accreditation')->label('Media accreditation'),
                TernaryFilter::make('is_speaking')->label('Speaking'),
                TernaryFilter::make('checked_in')
                    ->label(__('admin.fields.checked_in'))
                    ->queries(
                        true: fn (Builder $query) => $query->has('checkIns'),
                        false: fn (Builder $query) => $query->doesntHave('checkIns'),
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                RegistrationActions::approve(),
                ActionGroup::make([
                    RegistrationActions::resend(),
                    RegistrationActions::regenerateBadge(),
                    RegistrationActions::downloadBadge(),
                    RegistrationActions::cancel(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    RegistrationActions::approveBulk(),
                    RegistrationActions::resendBulk(),
                    RegistrationActions::regenerateBadgeBulk(),
                    RegistrationActions::cancelBulk(),
                    ExportBulkAction::make()->exporter(RegistrationExporter::class),
                ]),
            ]);
    }
}
