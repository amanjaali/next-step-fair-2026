<?php

namespace App\Filament\Resources\ScholarshipApplications\Pages;

use App\Filament\Exports\ScholarshipApplicationExporter;
use App\Filament\Exports\ScholarshipContactsExporter;
use App\Filament\Exports\ScholarshipReviewExporter;
use App\Filament\Exports\ScholarshipSummaryExporter;
use App\Filament\Resources\ScholarshipApplications\ScholarshipApplicationResource;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListScholarshipApplications extends ListRecords
{
    protected static string $resource = ScholarshipApplicationResource::class;

    public function getSubheading(): ?string
    {
        return __('admin.scholarship.subtitle', ['cycle' => config('scholarship.cycle')]);
    }

    /** @return array<string, class-string> preset label => exporter */
    public static function exportPresets(): array
    {
        return [
            'Summary' => ScholarshipSummaryExporter::class,
            'Review' => ScholarshipReviewExporter::class,
            'Contact list' => ScholarshipContactsExporter::class,
            'Everything' => ScholarshipApplicationExporter::class,
        ];
    }

    public static function canExport(): bool
    {
        return auth()->user()?->hasRole('Super Admin') ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make(collect(self::exportPresets())->map(
                fn (string $exporter, string $label) => ExportAction::make('export_'.str($label)->slug('_'))
                    ->label($label)
                    ->exporter($exporter)
                    ->fileName(fn () => 'scholarship-'.str($label)->slug().'-'.now()->format('Y-m-d-His'))
                    ->authorize(fn () => self::canExport()),
            )->values()->all())
                ->label('Export')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->button()
                ->visible(fn () => self::canExport()),
        ];
    }

    /** Export selected rows, same presets. */
    public static function exportBulkActions(): BulkActionGroup
    {
        return BulkActionGroup::make(collect(self::exportPresets())->map(
            fn (string $exporter, string $label) => ExportBulkAction::make('export_selected_'.str($label)->slug('_'))
                ->label('Export selected — '.$label)
                ->exporter($exporter)
                ->fileName(fn () => 'scholarship-'.str($label)->slug().'-'.now()->format('Y-m-d-His'))
                ->authorize(fn () => self::canExport()),
        )->values()->all())
            ->label('Export selected')
            ->visible(fn () => self::canExport());
    }
}
