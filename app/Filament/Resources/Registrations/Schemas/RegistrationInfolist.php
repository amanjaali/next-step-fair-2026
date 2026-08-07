<?php

namespace App\Filament\Resources\Registrations\Schemas;

use App\Models\Registration;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/** The full submission, as it was received, plus the ticket's operational state. */
class RegistrationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Ticket')
                ->columns(4)
                ->schema([
                    TextEntry::make('ticket_ref')->label(__('admin.fields.ticket'))->fontFamily('mono')->copyable(),
                    TextEntry::make('status')->label(__('admin.fields.status'))->badge(),
                    TextEntry::make('track')->label(__('admin.fields.track'))->badge()
                        ->color(fn (string $state) => $state === 'conference' ? 'info' : 'primary'),
                    TextEntry::make('type')->label(__('admin.fields.type'))->badge(),
                ]),

            Section::make('Registrant')
                ->columns(3)
                ->schema([
                    TextEntry::make('full_name')->label(__('admin.fields.name')),
                    TextEntry::make('phone')->label(__('admin.fields.phone'))
                        ->formatStateUsing(fn (Registration $record) => $record->phone_country.' '.$record->phone)
                        ->copyable(),
                    TextEntry::make('email')->label(__('admin.fields.email'))->copyable()->placeholder('—'),
                    TextEntry::make('city')->label(__('admin.fields.city')),
                    TextEntry::make('locale')->label(__('admin.fields.language')),
                    TextEntry::make('date_of_birth')->label('Date of birth')->date()->placeholder('—'),
                    TextEntry::make('position')->label(__('admin.fields.position'))->placeholder('—')
                        ->visible(fn (Registration $record) => $record->isConference()),
                    TextEntry::make('organization')->label(__('admin.fields.institution'))->placeholder('—')
                        ->visible(fn (Registration $record) => $record->isConference()),
                    TextEntry::make('org_type')->label('Organisation type')->placeholder('—')
                        ->visible(fn (Registration $record) => $record->isConference()),
                ]),

            Section::make('Submission')
                ->columns(3)
                ->schema([
                    TextEntry::make('days')->label(__('admin.fields.days'))
                        ->formatStateUsing(fn (Registration $record) => $record->daysLabel() ?: '—'),
                    TextEntry::make('reasons')
                        ->formatStateUsing(fn (Registration $record) => implode(', ', $record->reasons ?? []) ?: '—'),
                    TextEntry::make('fields_of_study')->label('Fields of study')
                        ->formatStateUsing(fn (Registration $record) => implode(', ', $record->fields_of_study ?? []) ?: '—'),
                    TextEntry::make('school_name')->label('School')->placeholder('—'),
                    TextEntry::make('stream')->placeholder('—'),
                    TextEntry::make('hear_about')->label(__('admin.fields.source'))->placeholder('—'),
                    TextEntry::make('interpretation')->placeholder('—')
                        ->visible(fn (Registration $record) => $record->isConference()),
                    TextEntry::make('delegation_size')->label('Delegation')->placeholder('—')
                        ->visible(fn (Registration $record) => $record->isConference()),
                    TextEntry::make('notes')->columnSpanFull()->placeholder('—'),
                ]),

            Section::make(__('admin.fields.consents'))
                ->columns(3)
                ->schema([
                    TextEntry::make('consents.terms.at')->label('Terms accepted')->placeholder('—'),
                    TextEntry::make('consents.whatsapp.at')->label('WhatsApp consent')->placeholder('—'),
                    TextEntry::make('consents.photography.given')->label('Photography')
                        ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No'),
                    TextEntry::make('consent_ip')->label('Consent IP')->placeholder('—'),
                    TextEntry::make('utm_source')->label(__('admin.fields.source'))->placeholder('—'),
                    TextEntry::make('utm_campaign')->label('Campaign')->placeholder('—'),
                ]),

            Section::make('Lifecycle')
                ->columns(4)
                ->schema([
                    TextEntry::make('created_at')->label(__('admin.fields.registered'))->dateTime(),
                    TextEntry::make('verified_at')->label('OTP verified')->dateTime()->placeholder('—'),
                    TextEntry::make('confirmed_at')->dateTime()->placeholder('—'),
                    TextEntry::make('approved_at')->dateTime()->placeholder('—'),
                    TextEntry::make('badge_generated_at')->label(__('admin.fields.badge'))->dateTime()->placeholder('—'),
                    TextEntry::make('cancelled_at')->dateTime()->placeholder('—'),
                    TextEntry::make('cancellation_reason')->placeholder('—'),
                    TextEntry::make('creator.name')->label('Entered by')->placeholder('—'),
                ]),
        ]);
    }
}
