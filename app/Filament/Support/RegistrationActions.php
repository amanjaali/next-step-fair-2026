<?php

namespace App\Filament\Support;

use App\Mail\RsvpConfirmation;
use App\Models\Registration;
use App\Services\BadgeService;
use App\Services\Messaging\MessageDispatcher;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;

/**
 * The desk's day-to-day operations, shared by the fair and conference tables:
 * approve, cancel, resend, regenerate a badge.
 *
 * Each one is available as a row action and as a bulk action, because the desk
 * works one registrant at a time and the registration manager works in batches.
 */
class RegistrationActions
{
    /** Approves pending conference RSVPs and issues their badges. */
    public static function approve(): Action
    {
        return Action::make('approve')
            ->label(__('admin.actions.approve'))
            ->icon('heroicon-m-check-badge')
            ->color('success')
            ->visible(fn (Registration $record) => $record->status === Registration::STATUS_PENDING)
            ->requiresConfirmation()
            ->action(fn (Registration $record) => static::applyApproval(collect([$record])));
    }

    public static function approveBulk(): BulkAction
    {
        return BulkAction::make('approve')
            ->label(__('admin.actions.approve'))
            ->icon('heroicon-m-check-badge')
            ->color('success')
            ->requiresConfirmation()
            ->deselectRecordsAfterCompletion()
            ->action(fn (Collection $records) => static::applyApproval($records));
    }

    public static function cancel(): Action
    {
        return Action::make('cancel')
            ->label(__('admin.actions.cancel'))
            ->icon('heroicon-m-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->schema([
                TextInput::make('reason')
                    ->label('Reason')
                    ->maxLength(190),
            ])
            ->action(function (Registration $record, array $data) {
                $record->forceFill([
                    'status' => Registration::STATUS_CANCELLED,
                    'cancelled_at' => now(),
                    'cancellation_reason' => $data['reason'] ?? null,
                ])->save();

                Notification::make()->title(__('admin.notify.cancelled', ['count' => 1]))->success()->send();
            });
    }

    public static function cancelBulk(): BulkAction
    {
        return BulkAction::make('cancel')
            ->label(__('admin.actions.cancel'))
            ->icon('heroicon-m-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records) {
                $records->each->forceFill([
                    'status' => Registration::STATUS_CANCELLED,
                    'cancelled_at' => now(),
                ])->each->save();

                Notification::make()->title(__('admin.notify.cancelled', ['count' => $records->count()]))->success()->send();
            });
    }

    /** Re-queues the confirmation on whichever channel the track uses. */
    public static function resend(): Action
    {
        return Action::make('resend')
            ->label(fn (Registration $record) => $record->isFair() ? __('admin.actions.resend_whatsapp') : __('admin.actions.resend_email'))
            ->icon('heroicon-m-paper-airplane')
            ->color('info')
            ->requiresConfirmation()
            ->action(fn (Registration $record) => static::applyResend(collect([$record])));
    }

    public static function resendBulk(): BulkAction
    {
        return BulkAction::make('resend')
            ->label(__('admin.actions.resend_whatsapp'))
            ->icon('heroicon-m-paper-airplane')
            ->color('info')
            ->requiresConfirmation()
            ->deselectRecordsAfterCompletion()
            ->action(fn (Collection $records) => static::applyResend($records));
    }

    public static function regenerateBadge(): Action
    {
        return Action::make('regenerate')
            ->label(__('admin.actions.regenerate_badge'))
            ->icon('heroicon-m-arrow-path')
            ->requiresConfirmation()
            ->action(fn (Registration $record) => static::applyRegenerate(collect([$record])));
    }

    public static function regenerateBadgeBulk(): BulkAction
    {
        return BulkAction::make('regenerate')
            ->label(__('admin.actions.regenerate_badge'))
            ->icon('heroicon-m-arrow-path')
            ->requiresConfirmation()
            ->deselectRecordsAfterCompletion()
            ->action(fn (Collection $records) => static::applyRegenerate($records));
    }

    public static function downloadBadge(): Action
    {
        return Action::make('badge')
            ->label(__('admin.actions.download_badge'))
            ->icon('heroicon-m-identification')
            ->visible(fn (Registration $record) => $record->badgeIssued())
            ->url(fn (Registration $record) => route('ticket.pdf', $record->ticket_id))
            ->openUrlInNewTab();
    }

    /* --------------------------------------------------------------- apply */

    private static function applyApproval(Collection|\Illuminate\Support\Collection $records): void
    {
        $badges = app(BadgeService::class);
        $approved = 0;

        foreach ($records as $record) {
            if ($record->status !== Registration::STATUS_PENDING) {
                continue;
            }

            $record->forceFill([
                'status' => Registration::STATUS_CONFIRMED,
                'approved_at' => now(),
                'approved_by' => auth()->id(),
                'confirmed_at' => $record->confirmed_at ?? now(),
            ])->save();

            $badges->generate($record);

            if ($record->email) {
                Mail::to($record->email)->queue(new RsvpConfirmation($record, false));
            }

            $approved++;
        }

        Notification::make()->title(__('admin.notify.approved', ['count' => $approved]))->success()->send();
    }

    private static function applyResend(Collection|\Illuminate\Support\Collection $records): void
    {
        $dispatcher = app(MessageDispatcher::class);
        $sent = 0;

        foreach ($records as $record) {
            if (! $record->badgeIssued()) {
                continue;
            }

            if ($record->isFair()) {
                $dispatcher->whatsapp(
                    $record,
                    'registration_confirmed_'.$record->type,
                    [
                        'name' => $record->firstName(),
                        'days' => $record->daysLabel(),
                        'ticket' => $record->ticket_ref,
                    ],
                    withBadge: true,
                );
            } elseif ($record->email) {
                Mail::to($record->email)->queue(new RsvpConfirmation($record, false));
                $dispatcher->logEmail(
                    $record,
                    'badge_resent',
                    __('notifications.email.badge_resent_subject', [], $record->locale),
                    __('notifications.email.rsvp_badge_note', [], $record->locale),
                );
            }

            $sent++;
        }

        Notification::make()->title(__('admin.notify.resent', ['count' => $sent]))->success()->send();
    }

    private static function applyRegenerate(Collection|\Illuminate\Support\Collection $records): void
    {
        $badges = app(BadgeService::class);
        $count = 0;

        foreach ($records as $record) {
            if (! $record->badgeIssued()) {
                continue;
            }
            $badges->generate($record);
            $count++;
        }

        Notification::make()->title(__('admin.notify.regenerated', ['count' => $count]))->success()->send();
    }
}
