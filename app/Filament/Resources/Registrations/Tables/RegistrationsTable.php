<?php

namespace App\Filament\Resources\Registrations\Tables;

use App\Filament\Exports\RegistrationExporter;
use App\Filament\Support\RegistrationActions;
use App\Models\Registration;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * The registration desk's working table.
 *
 * Search covers name, ticket reference, phone and e-mail — the last two through
 * their keyed hashes, since the values themselves are encrypted at rest.
 */
class RegistrationsTable
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
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label(__('admin.fields.phone'))
                    ->formatStateUsing(fn (Registration $record) => $record->phone_country.' '.$record->phone)
                    ->copyable()
                    // The column is encrypted, so the search runs against the hash.
                    // A full number matches; a partial one cannot, by design.
                    ->searchable(query: fn (Builder $query, string $search) => $query
                        ->orWhere('phone_hash', Registration::phoneHash($search)))
                    ->toggleable(),

                TextColumn::make('email')
                    ->label(__('admin.fields.email'))
                    ->copyable()
                    ->searchable(query: fn (Builder $query, string $search) => $query
                        ->orWhere('email_hash', Registration::hashValue(strtolower($search))))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('type')
                    ->label(__('admin.fields.type'))
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        Registration::TYPE_PARENT => 'gray',
                        Registration::TYPE_VISITOR => 'warning',
                        default => 'primary',
                    }),

                TextColumn::make('city')
                    ->label(__('admin.fields.city'))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('days')
                    ->label(__('admin.fields.days'))
                    ->formatStateUsing(fn (Registration $record) => implode(', ', $record->dayList()))
                    ->toggleable(),

                TextColumn::make('locale')
                    ->label(__('admin.fields.language'))
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label(__('admin.fields.status'))
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        Registration::STATUS_CONFIRMED => 'success',
                        Registration::STATUS_CHECKED_IN => 'info',
                        Registration::STATUS_PENDING, Registration::STATUS_AWAITING_OTP => 'warning',
                        Registration::STATUS_CANCELLED, Registration::STATUS_REJECTED => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('check_ins_count')
                    ->label(__('admin.fields.checked_in'))
                    ->counts('checkIns')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray')
                    ->toggleable(),

                TextColumn::make('ticket_ref')
                    ->label(__('admin.fields.ticket'))
                    ->fontFamily('mono')
                    ->copyable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('admin.fields.registered'))
                    ->dateTime('j M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('admin.fields.type'))
                    ->options([
                        Registration::TYPE_STUDENT => 'Student',
                        Registration::TYPE_PARENT => 'Parent',
                        Registration::TYPE_VISITOR => 'Visitor pass',
                    ]),

                SelectFilter::make('status')
                    ->label(__('admin.fields.status'))
                    ->multiple()
                    ->options([
                        Registration::STATUS_AWAITING_OTP => 'Awaiting OTP',
                        Registration::STATUS_CONFIRMED => 'Confirmed',
                        Registration::STATUS_CHECKED_IN => 'Checked in',
                        Registration::STATUS_CANCELLED => 'Cancelled',
                    ]),

                SelectFilter::make('city')
                    ->label(__('admin.fields.city'))
                    ->multiple()
                    ->options(array_combine(config('nextstep.cities'), config('nextstep.cities'))),

                SelectFilter::make('locale')
                    ->label(__('admin.fields.language'))
                    ->options(['en' => 'English', 'ku' => 'Kurdish', 'ar' => 'Arabic']),

                Filter::make('day')
                    ->schema([
                        Select::make('day')
                            ->label(__('admin.fields.days'))
                            ->options([1 => 'Day 1', 2 => 'Day 2', 3 => 'Day 3']),
                    ])
                    ->query(fn (Builder $query, array $data) => $data['day']
                        ? $query->whereJsonContains('days', (int) $data['day'])
                        : $query),

                Filter::make('registered_between')
                    ->schema([
                        DatePicker::make('from')->label('Registered from'),
                        DatePicker::make('until')->label('Registered until'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))),

                TernaryFilter::make('checked_in')
                    ->label(__('admin.fields.checked_in'))
                    ->queries(
                        true: fn (Builder $query) => $query->has('checkIns'),
                        false: fn (Builder $query) => $query->doesntHave('checkIns'),
                    ),

                TernaryFilter::make('is_walk_in')->label('Walk-in'),
            ])
            ->recordActions([
                ViewAction::make(),
                ActionGroup::make([
                    RegistrationActions::resend(),
                    RegistrationActions::regenerateBadge(),
                    RegistrationActions::downloadBadge(),
                    RegistrationActions::cancel(),
                ]),
                RegistrationActions::delete(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    RegistrationActions::resendBulk(),
                    RegistrationActions::regenerateBadgeBulk(),
                    RegistrationActions::cancelBulk(),
                    RegistrationActions::deleteBulk(),
                    ExportBulkAction::make()->exporter(RegistrationExporter::class),
                ]),
            ]);
    }
}
