<?php

namespace App\Filament\Resources\Messages;

use App\Filament\Resources\Messages\Pages\ListMessages;
use App\Models\Message;
use App\Services\Messaging\MessageDispatcher;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * The delivery log: sent / delivered / read / failed per message, with the
 * provider's failure reason and a retry that re-queues the same row.
 */
class MessageResource extends Resource
{
    protected static ?string $model = Message::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInbox;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.messaging');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.messages');
    }

    public static function getNavigationBadge(): ?string
    {
        $failed = static::getEloquentQuery()->where('status', Message::STATUS_FAILED)->count();

        return $failed > 0 ? (string) $failed : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('registration.full_name')
                    ->label('Recipient')
                    ->weight('semibold')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('channel')->badge(),
                TextColumn::make('template_key')->label('Template')->wrap(),
                TextColumn::make('locale')->badge()->color('gray'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        Message::STATUS_DELIVERED, Message::STATUS_READ => 'success',
                        Message::STATUS_SENT => 'info',
                        Message::STATUS_FAILED => 'danger',
                        default => 'warning',
                    })
                    ->sortable(),
                TextColumn::make('attempts')->toggleable(),
                TextColumn::make('error')->wrap()->placeholder('—')->toggleable(),
                TextColumn::make('created_at')->label('Queued')->dateTime('j M H:i')->sortable(),
                TextColumn::make('delivered_at')->dateTime('j M H:i')->placeholder('—')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('channel')->options(['whatsapp' => 'WhatsApp', 'email' => 'Email', 'sms' => 'SMS']),
                SelectFilter::make('status')->multiple()->options([
                    Message::STATUS_QUEUED => 'Queued',
                    Message::STATUS_SENT => 'Sent',
                    Message::STATUS_DELIVERED => 'Delivered',
                    Message::STATUS_READ => 'Read',
                    Message::STATUS_FAILED => 'Failed',
                ]),
                SelectFilter::make('template_key')->label('Template')
                    ->options(fn () => Message::distinct()->pluck('template_key', 'template_key')->filter()->all()),
                Filter::make('failed_only')
                    ->label('Failures only')
                    ->query(fn (Builder $query) => $query->where('status', Message::STATUS_FAILED)),
            ])
            ->recordActions([
                Action::make('retry')
                    ->label(__('admin.actions.retry'))
                    ->icon('heroicon-m-arrow-path')
                    ->visible(fn (Message $record) => $record->channel === 'whatsapp' && $record->isFailed())
                    ->action(function (Message $record) {
                        try {
                            app(MessageDispatcher::class)->retry($record);
                            Notification::make()->title(__('admin.notify.retried', ['count' => 1]))->success()->send();
                        } catch (\Throwable $e) {
                            report($e);
                            Notification::make()->title(__('admin.notify.resent_failed', ['count' => 1]))->danger()->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('retry')
                        ->label(__('admin.actions.retry'))
                        ->icon('heroicon-m-arrow-path')
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records) {
                            $dispatcher = app(MessageDispatcher::class);
                            $count = 0;
                            $failed = 0;

                            foreach ($records->where('channel', 'whatsapp') as $record) {
                                try {
                                    $dispatcher->retry($record);
                                    $count++;
                                } catch (\Throwable $e) {
                                    $failed++;
                                    report($e);
                                }
                            }

                            if ($count === 0) {
                                Notification::make()->title(__('admin.notify.resent_failed', ['count' => $failed ?: 1]))->danger()->send();

                                return;
                            }

                            Notification::make()->title(__('admin.notify.retried', ['count' => $count]))->success()->send();
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListMessages::route('/')];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view-messages') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
