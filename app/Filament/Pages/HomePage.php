<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * The front page, editable.
 *
 * Everything else the home page shows already had a screen of its own — the
 * why-attend cards, the SDG goals, the opportunities, the news. The home page's
 * own words and photographs did not: they lived in the translation files, so
 * changing a headline meant changing code.
 *
 * Anything left blank falls back to the wording the site shipped with, which is
 * why the fields open empty rather than pre-filled: an empty box means "as
 * written", not "delete this".
 */
class HomePage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?int $navigationSort = 0;

    protected string $view = 'filament.pages.home-page';

    /** @var array<string, mixed> */
    public array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.home.nav');
    }

    public function getTitle(): string
    {
        return __('admin.home.title');
    }

    public function getSubheading(): ?string
    {
        return __('admin.home.subtitle');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage-content') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'home_content' => Setting::get('home_content', []),
            'counters' => Setting::get('counters', []),
            'event' => Setting::get('event_overrides', []),
            'images' => Setting::get('site_images', []),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make(__('admin.home.hero'))
                    ->description(__('admin.home.hero_help'))
                    ->schema([
                        $this->translated('hero_kicker', __('admin.home.hero_kicker'), 'text', 'site.common.edition_4'),
                        $this->translated('hero_place', __('admin.home.hero_place'), 'text', 'site.common.location'),
                        $this->translated('hero_lead', __('admin.home.hero_lead'), 'textarea'),
                        FileUpload::make('images.home_hero')
                            ->label(__('admin.home.hero_photo'))
                            ->helperText(__('admin.home.hero_photo_help'))
                            ->image()->imageEditor()->directory('site')->disk('public')
                            ->columnSpanFull(),
                    ]),

                Section::make(__('admin.home.event'))
                    ->description(__('admin.home.event_help'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('event.name')
                            ->label(__('admin.home.event_name'))
                            ->placeholder(config('nextstep.event.name')),
                        TextInput::make('event.edition_label')
                            ->label(__('admin.home.edition_label'))
                            ->placeholder(config('nextstep.event.edition_label')),
                        TextInput::make('event.start_date')
                            ->label(__('admin.home.start_date'))->type('date')
                            ->placeholder(config('nextstep.event.start_date')),
                        TextInput::make('event.end_date')
                            ->label(__('admin.home.end_date'))->type('date')
                            ->placeholder(config('nextstep.event.end_date')),
                        TextInput::make('event.venue_name')
                            ->label(__('admin.home.venue_name'))
                            ->placeholder(config('nextstep.event.venue.name')),
                        TextInput::make('event.venue_city')
                            ->label(__('admin.home.venue_city'))
                            ->placeholder(config('nextstep.event.venue.city')),
                        TextInput::make('event.opening_hours')
                            ->label(__('admin.home.opening_hours'))
                            ->placeholder(config('nextstep.event.opening_hours')),
                    ]),

                Section::make(__('admin.home.buttons'))
                    ->description(__('admin.home.buttons_help'))
                    ->schema([
                        $this->translated('cta_fair', __('admin.home.cta_fair'), 'text', 'site.cta.register_fair'),
                        $this->translated('cta_conference', __('admin.home.cta_conference'), 'text', 'site.cta.conference_rsvp'),
                    ]),

                Section::make(__('admin.home.numbers'))
                    ->description(__('admin.home.numbers_help'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('counters.universities')
                            ->label(__('admin.home.universities'))->numeric()->minValue(0),
                        TextInput::make('counters.sessions')
                            ->label(__('admin.home.sessions'))->numeric()->minValue(0),
                    ]),

                Section::make(__('admin.home.tracks'))
                    ->description(__('admin.home.tracks_help'))
                    ->schema([
                        $this->translated('fair_title', __('admin.home.fair_title')),
                        $this->translated('fair_body', __('admin.home.fair_body'), 'textarea'),
                        $this->translated('conf_title', __('admin.home.conf_title')),
                        $this->translated('conf_body', __('admin.home.conf_body'), 'textarea'),
                    ]),

                Section::make(__('admin.home.about'))
                    ->description(__('admin.home.about_help'))
                    ->schema([
                        $this->translated('about_title', __('admin.home.about_title')),
                        $this->translated('about_p1', __('admin.home.about_p1'), 'textarea'),
                        $this->translated('about_p2', __('admin.home.about_p2'), 'textarea'),
                        $this->translated('about_p3', __('admin.home.about_p3'), 'textarea'),
                        FileUpload::make('images.home_about')
                            ->label(__('admin.home.about_photo'))
                            ->helperText(__('admin.home.about_photo_help'))
                            ->image()->imageEditor()->directory('site')->disk('public')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * One field, once per language, each in its own direction.
     *
     * The placeholder carries the wording the site would use if the box is left
     * empty, so an editor can see what they are replacing before they type.
     */
    private function translated(string $key, string $label, string $kind = 'text', ?string $from = null): Tabs
    {
        return Tabs::make($key)
            ->label($label)
            ->columnSpanFull()
            ->tabs(collect(config('nextstep.locales'))->map(
                function (array $config, string $locale) use ($key, $label, $kind, $from) {
                    // Filled in, so the placeholder reads as the page reads
                    // rather than showing :universities to an editor.
                    $shipped = trans($from ?? 'site.home.'.$key, [
                        'universities' => ns_home_counter('universities', 32),
                        'sessions' => ns_home_counter('sessions', 26),
                    ], $locale);
                    $name = "home_content.{$key}.{$locale}";
                    $rtl = $config['dir'] === 'rtl';

                    $field = $kind === 'textarea'
                        ? Textarea::make($name)->rows(3)
                        : TextInput::make($name);

                    return Tab::make($config['code'])->schema([
                        $field
                            ->label($label)
                            ->placeholder(is_string($shipped) ? $shipped : '')
                            ->helperText(__('admin.home.blank_help'))
                            ->extraInputAttributes($rtl ? ['dir' => 'rtl'] : []),
                    ]);
                }
            )->values()->all());
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('admin.home.save'))
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::put('home_content', $data['home_content'] ?? [], 'home');

        // An empty box is "leave the shipped fact alone", never "blank it".
        Setting::put('event_overrides', array_filter(
            $data['event'] ?? [], fn ($v) => trim((string) $v) !== ''
        ), 'event');

        Setting::put('site_images', array_filter($data['images'] ?? []), 'images');

        Setting::put('counters', array_map(
            fn ($v) => (int) $v,
            array_filter($data['counters'] ?? [], fn ($v) => $v !== null && $v !== '')
        ), 'home');

        Notification::make()->title(__('admin.notify.saved'))->success()->send();
    }
}
