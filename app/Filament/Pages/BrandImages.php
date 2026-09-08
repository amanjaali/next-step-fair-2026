<?php

namespace App\Filament\Pages;

use App\Filament\Forms\Components\ImageUpload;
use App\Models\Setting;
use App\Support\Svg;
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
        return ImageUpload::logo("brand.{$key}")
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

        /*
         * One place does not follow: the cards people post are pictures built in
         * advance, so a logo uploaded here reaches every page and every badge at
         * once and those eighteen files not at all. The gap is invisible, and
         * "the logo is missing" turns up a week before the fair. Said here, at
         * the moment it becomes true, rather than in a document nobody is
         * reading while uploading a file.
         */
        if ($before !== $after) {
            Notification::make()
                ->title(__('admin.images.rebuild_cards'))
                ->body(__('admin.images.rebuild_cards_body'))
                ->warning()
                ->persistent()
                ->send();
        }

        $this->warnAboutSvgOnBadges($after);
    }

    /**
     * An SVG partner mark cannot always be drawn on the badge.
     *
     * The website, the printed PDF and the share cards all handle SVG; the
     * badge picture sent on WhatsApp is composed by GD, which cannot read one,
     * so it is converted first — and that needs Imagick or a converter on the
     * machine. Where there is none, the mark would simply not appear on the
     * badge, and nothing would say why. This says why, naming the partner, at
     * the moment the file is chosen.
     *
     * @param  array<string, string>  $marks
     */
    private function warnAboutSvgOnBadges(array $marks): void
    {
        if (Svg::canRasterise()) {
            return;
        }

        $affected = collect(['mohe', 'krg', 'ksa'])
            ->filter(fn (string $slot) => str_ends_with(strtolower($marks[$slot] ?? ''), '.svg'))
            ->map(fn (string $slot) => __("admin.images.$slot"))
            ->implode(', ');

        if ($affected === '') {
            return;
        }

        Notification::make()
            ->title(__('admin.images.svg_badge_title'))
            ->body(__('admin.images.svg_badge_body', ['partners' => $affected]))
            ->danger()
            ->persistent()
            ->send();
    }
}
