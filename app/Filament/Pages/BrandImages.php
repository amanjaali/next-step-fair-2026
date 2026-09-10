<?php

namespace App\Filament\Pages;

use App\Filament\Forms\Components\ImageUpload;
use App\Models\Setting;
use App\Support\StorageLink;
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

        $this->checkUploadsCanBeSeen();
    }

    /**
     * Uploads are served through public/storage, and that link is per-install.
     *
     * Without it every upload on this screen saves correctly and shows as a
     * blank box — here, in the header, on the badge, everywhere — while the
     * marks that ship with the site carry on working, which makes it look like
     * the uploaded file is at fault. So: repair it silently if this machine
     * lets us, and if it does not, say so on the screen where the symptom
     * appears rather than leaving somebody to re-upload the same logo.
     */
    private function checkUploadsCanBeSeen(): void
    {
        if (StorageLink::repair()) {
            return;
        }

        Notification::make()
            ->title(__('admin.images.link_broken'))
            ->body(__('admin.images.link_broken_body', ['problem' => StorageLink::problem()]))
            ->danger()
            ->persistent()
            ->send();
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
                        $this->slot('mohe', __('admin.images.mohe'), __('admin.images.partner_help')),
                        $this->slot('krg', __('admin.images.krg'), __('admin.images.partner_help')),
                        // No file ships for this one: the association joined
                        // after the design was drawn, so its mark appears in
                        // the header, the footer and the partners page the
                        // moment it is uploaded here, and nowhere before.
                        $this->slot('ksa', __('admin.images.ksa'), __('admin.images.ksa_help')),
                    ]),

                Section::make(__('admin.images.share'))
                    ->description(__('admin.images.share_help'))
                    ->schema([
                        $this->slot('share_default', __('admin.images.share_image'), __('admin.images.share_image_help')),
                    ]),
            ]);
    }

    /** One upload box. See ImageUpload for the types, the size and the SVG rule. */
    private function slot(string $key, string $label, ?string $help = null): FileUpload
    {
        // A partner mark goes on the badge as well as the page, and the badge
        // cannot take an SVG — so those three boxes accept pictures only.
        $upload = in_array($key, ['mohe', 'krg', 'ksa'], true)
            ? ImageUpload::mark("brand.{$key}")
            : ImageUpload::logo("brand.{$key}");

        return $upload
            ->label($label)
            ->helperText($help ?? __('admin.images.slot_help'))
            ->directory('brand')
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
        $before = Setting::get('brand_images', []);
        $after = array_filter($data['brand'] ?? []);

        Setting::put('brand_images', $after, 'images');

        Notification::make()->title(__('admin.notify.saved'))->success()->send();

        // Saving is the moment somebody looks at the box and expects a picture.
        $this->checkUploadsCanBeSeen();

        /*
         * Nothing has to be rebuilt for this to take effect any more. Every
         * place a mark appears — the header, the footer, the partners page, and
         * every form of the badge — reads it when it draws, so an upload is
         * live on the next page load. Badges already issued keep the artwork
         * they were made with until somebody runs nextstep:regenerate-badges,
         * which is the one thing worth saying here.
         */
        if ($before !== $after) {
            Notification::make()
                ->title(__('admin.images.reissue_badges'))
                ->body(__('admin.images.reissue_badges_body'))
                ->warning()
                ->persistent()
                ->send();
        }
    }
}
