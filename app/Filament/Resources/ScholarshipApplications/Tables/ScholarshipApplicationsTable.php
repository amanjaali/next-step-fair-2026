<?php

namespace App\Filament\Resources\ScholarshipApplications\Tables;

use App\Filament\Resources\ScholarshipApplications\ScholarshipApplicationResource;
use App\Models\ScholarshipApplication;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * The queue.
 *
 * Ordered oldest first on purpose: a scholarship queue read newest-first leaves
 * the person who applied on the first day waiting longest, which is the opposite
 * of what an application deadline is supposed to mean.
 */
class ScholarshipApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('submitted_at', 'asc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('registration'))
            ->columns([
                TextColumn::make('registration.full_name')
                    ->label(__('admin.fields.name'))
                    ->weight('semibold')
                    ->searchable()
                    ->description(fn (ScholarshipApplication $record) => $record->school_name),

                TextColumn::make('region_code')
                    ->label(__('admin.scholarship.region'))
                    ->formatStateUsing(fn (ScholarshipApplication $record) => $record->regionName() ?? '—')
                    ->description(fn (ScholarshipApplication $record) => $record->district)
                    ->sortable(),

                TextColumn::make('exam_average')
                    ->label(__('admin.scholarship.average'))
                    ->formatStateUsing(fn (ScholarshipApplication $record) => $record->exam_average
                        ? number_format((float) $record->exam_average, 2).'%'
                        : __('admin.scholarship.pending'))
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('admin.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ScholarshipApplicationResource::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        ScholarshipApplication::STATUS_DECIDED => 'success',
                        ScholarshipApplication::STATUS_INTERVIEW => 'info',
                        ScholarshipApplication::STATUS_SHORTLISTED => 'info',
                        ScholarshipApplication::STATUS_WITHDRAWN => 'gray',
                        ScholarshipApplication::STATUS_DRAFT => 'gray',
                        default => 'warning',
                    })
                    ->sortable(),

                TextColumn::make('decision')
                    ->label(__('admin.scholarship.decision'))
                    ->badge()
                    ->placeholder('—')
                    ->formatStateUsing(fn (?string $state) => $state
                        ? (ScholarshipApplicationResource::decisionOptions()[$state] ?? $state)
                        : '—')
                    ->color(fn (?string $state) => match ($state) {
                        ScholarshipApplication::DECISION_AWARDED => 'success',
                        ScholarshipApplication::DECISION_RESERVE => 'warning',
                        ScholarshipApplication::DECISION_DECLINED => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('score_total')
                    ->label(__('admin.scholarship.score'))
                    ->state(fn (ScholarshipApplication $record) => $record->scoreTotal())
                    ->placeholder('—')
                    ->formatStateUsing(fn (?float $state) => $state === null ? '—' : $state.' / 100'),

                TextColumn::make('submitted_at')
                    ->label(__('admin.scholarship.submitted'))
                    ->dateTime('j M Y')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('admin.fields.status'))
                    ->options(ScholarshipApplicationResource::statusOptions()),

                SelectFilter::make('region_code')
                    ->label(__('admin.scholarship.region'))
                    ->options(ScholarshipApplicationResource::regionOptions()),

                SelectFilter::make('decision')
                    ->label(__('admin.scholarship.decision'))
                    ->options(ScholarshipApplicationResource::decisionOptions()),

                /*
                 * On by default, so the queue is applications rather than students
                 * mid-sentence. Written as "hide", not "include", because a filter
                 * only runs its query while it is switched on — an "include drafts"
                 * toggle left off would run nothing and quietly show everything.
                 */
                Filter::make('hide_drafts')
                    ->label(__('admin.scholarship.hide_drafts'))
                    ->toggle()
                    ->default(true)
                    ->query(fn (Builder $query) => $query->submitted()),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()->label(__('admin.scholarship.review')),

                /*
                 * One button that does the ordinary thing. Most of a committee's
                 * work is moving an application one stage on; making that a
                 * three-click trip through an edit form is how queues stall.
                 */
                Action::make('advance')
                    ->label(fn (ScholarshipApplication $record) => __('admin.scholarship.move_to', [
                        'stage' => ScholarshipApplicationResource::statusOptions()[$record->nextStage()] ?? '',
                    ]))
                    ->icon(Heroicon::OutlinedArrowRight)
                    ->color('primary')
                    ->visible(fn (ScholarshipApplication $record) => $record->nextStage() !== null
                        && $record->nextStage() !== ScholarshipApplication::STATUS_DECIDED)
                    ->requiresConfirmation()
                    ->action(function (ScholarshipApplication $record) {
                        $record->advanceTo($record->nextStage());

                        Notification::make()->title(__('admin.notify.saved'))->success()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('screen')
                        ->label(__('admin.scholarship.bulk_screen'))
                        ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $moved = $records
                                ->filter(fn (ScholarshipApplication $r) => $r->status === ScholarshipApplication::STATUS_SUBMITTED)
                                ->each(fn (ScholarshipApplication $r) => $r->advanceTo(ScholarshipApplication::STATUS_SCREENING))
                                ->count();

                            Notification::make()
                                ->title(__('admin.scholarship.bulk_done', ['count' => $moved]))
                                ->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->emptyStateHeading(__('admin.scholarship.empty'))
            ->emptyStateDescription(__('admin.scholarship.empty_help'));
    }
}
