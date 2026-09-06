<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * The logos and marks that appear on every page.
 *
 * Photographs belong to the thing they picture — a speaker's portrait is on the
 * speaker, an edition's cover is on the edition — and each of those already has
 * its own screen. What had no screen at all were the logos in the header and the
 * footer, which were committed into public/assets and could only be changed by
 * editing the repository.
 *
 * Every slot here falls back to the shipped file, so an empty form is the site
 * exactly as designed and clearing an upload restores it rather than leaving a
 * gap in the header of every page.
 */
class BrandImages extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.brand-images';

    /** @var array<string, mixed> */
    public array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.images.nav');
    }

    public function getTitle(): string
    {
        return __('admin.images.title');
    }

    public function getSubheading(): ?string
    {
        return __('admin.images.subtitle');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage-content') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill(['brand' => Setting::get('brand_images', [])]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make(__('admin.images.logos'))
                    ->description(__('admin.images.logos_help'))
                    ->columns(2)
                    ->schema([
                        $this->slot('logo_dark', __('admin.images.logo_dark'), __('admin.images.logo_dark_help')),
                        $this->slot('logo_light', __('admin.images.logo_light'), __('admin.images.logo_light_help')),
                    ]),

                Section::make(__('admin.images.partners'))
                    ->description(__('admin.images.partners_help'))
                    ->columns(2)
                    ->schema([
                        $this->slot('mohe', __('admin.images.mohe')),
                        $this->slot('krg', __('admin.images.krg')),
                    ]),

                Section::make(__('admin.images.share'))
                    ->description(__('admin.images.share_help'))
                    ->schema([
                        $this->slot('share_default', __('admin.images.share_image'), __('admin.images.share_image_help')),
                    ]),
            ]);
    }

    private function slot(string $key, string $label, ?string $help = null): FileUpload
    {
        return FileUpload::make("brand.{$key}")
            ->label($label)
            ->helperText($help ?? __('admin.images.slot_help'))
            ->image()
            ->directory('brand')
            ->disk('public')
            ->columnSpanFull();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')->label(__('admin.images.save'))->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::put('brand_images', array_filter($data['brand'] ?? []), 'images');

        Notification::make()->title(__('admin.notify.saved'))->success()->send();
    }
}
