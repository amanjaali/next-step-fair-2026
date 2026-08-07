<?php

namespace App\Filament\Exports;

use App\Models\Registration;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

/**
 * CSV / Excel export with column selection.
 *
 * Phone and e-mail are decrypted by the model casts on the way out — the export
 * is the one place the desk legitimately needs them, and the file lands behind a
 * signed download URL scoped to the user who asked for it.
 */
class RegistrationExporter extends Exporter
{
    protected static ?string $model = Registration::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('ticket_ref')->label('Ticket'),
            ExportColumn::make('track'),
            ExportColumn::make('type'),
            ExportColumn::make('status'),
            ExportColumn::make('full_name')->label('Full name'),
            ExportColumn::make('phone_country')->label('Country code'),
            ExportColumn::make('phone'),
            ExportColumn::make('email'),
            ExportColumn::make('city'),
            ExportColumn::make('locale')->label('Language'),
            ExportColumn::make('date_of_birth')->label('Date of birth'),
            ExportColumn::make('gender'),
            ExportColumn::make('current_status')->label('Current status'),
            ExportColumn::make('school_name')->label('School'),
            ExportColumn::make('stream'),
            ExportColumn::make('fields_of_study')
                ->label('Fields of study')
                ->state(fn (Registration $r) => implode(', ', $r->fields_of_study ?? [])),
            ExportColumn::make('relationship'),
            ExportColumn::make('children_count')->label('Children attending'),
            ExportColumn::make('position')->label('Title'),
            ExportColumn::make('organization')->label('Institution'),
            ExportColumn::make('department'),
            ExportColumn::make('org_type')->label('Organisation type'),
            ExportColumn::make('delegation_size')->label('Delegation size'),
            ExportColumn::make('interpretation'),
            ExportColumn::make('days')
                ->state(fn (Registration $r) => implode(', ', $r->dayList())),
            ExportColumn::make('reasons')
                ->state(fn (Registration $r) => implode(', ', $r->reasons ?? [])),
            ExportColumn::make('hear_about')->label('Heard about us via'),
            ExportColumn::make('utm_source')->label('Source'),
            ExportColumn::make('utm_campaign')->label('Campaign'),
            ExportColumn::make('checked_in_days')
                ->label('Checked in on')
                ->state(fn (Registration $r) => $r->checkIns->pluck('day')->sort()->implode(', ')),
            ExportColumn::make('created_at')->label('Registered at'),
            ExportColumn::make('confirmed_at')->label('Confirmed at'),
            ExportColumn::make('approved_at')->label('Approved at'),
            ExportColumn::make('cancelled_at')->label('Cancelled at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Export complete: '.number_format($export->successful_rows).' row(s).';

        if ($failed = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failed).' row(s) failed.';
        }

        return $body;
    }
}
