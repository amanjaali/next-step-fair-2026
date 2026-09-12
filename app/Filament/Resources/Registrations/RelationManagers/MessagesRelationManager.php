<?php

namespace App\Filament\Resources\Registrations\RelationManagers;

use App\Models\Message;
use App\Services\Messaging\MessageDispatcher;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** Everything we tried to send this registrant, and what happened to it. */
class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    protected static ?string $title = 'Delivery log';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('channel')->badge(),
                TextColumn::make('template_key')->label('Template')->wrap(),
                TextColumn::make('locale')->label(__('admin.fields.language'))->badge()->color('gray'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        Message::STATUS_DELIVERED, Message::STATUS_READ => 'success',
                        Message::STATUS_SENT => 'info',
                        Message::STATUS_FAILED => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('error')->wrap()->toggleable()->placeholder('—'),
                TextColumn::make('created_at')->dateTime('j M H:i')->sortable(),
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
            ]);
    }
}
