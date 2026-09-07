<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
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
 * The Zankoline guidance desks, as they will actually be staffed.
 *
 * A phone number and the name of whoever is on the desk change between now and
 * the day the form opens, and an area can be added late. None of that should
 * need a developer, so the list itself lives here: add a row and it appears on
 * the page and on the map, remove one and it is gone.
 *
 * The map places each centre from its latitude and longitude, which is why
 * those two fields are on the form. They are filled in for the seven areas
 * already; a new one needs them, and any maps app gives them by dropping a pin.
 */
class ZankolineCentres extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.zankoline-centres';

    /** @var array<string, mixed> */
    public array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.zankoline.nav');
    }

    public function getTitle(): string
    {
        return __('admin.zankoline.title');
    }

    public function getSubheading(): ?string
    {
        return __('admin.zankoline.subtitle');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage-content') ?? false;
    }

    public function mount(): void
    {
        $saved = Setting::get('zankoline_centres', []);

        $this->form->fill([
            'content' => Setting::get('zankoline_content', []),
            // Opened on the shipped seven, so an editor is correcting a list
            // rather than typing one from nothing.
            'centres' => is_array($saved) && $saved !== [] ? $saved : config('zankoline.centres'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make(__('admin.zankoline.words'))
                    ->description(__('admin.zankoline.words_help'))
                    ->schema([
                        $this->translated('title', __('admin.zankoline.page_title')),
                        $this->translated('lead', __('admin.zankoline.page_lead'), true),
                        $this->translated('where_lead', __('admin.zankoline.where_lead'), true),
                        $this->translated('cta_body', __('admin.zankoline.cta_body'), true),
                    ]),

                Section::make(__('admin.zankoline.centres'))
                    ->description(__('admin.zankoline.centres_help'))
                    ->schema([
                        Repeater::make('centres')
                            ->hiddenLabel()
                            ->addActionLabel(__('admin.zankoline.add'))
                            ->itemLabel(fn (array $state): ?string => $state['name']['en'] ?? __('admin.zankoline.new_centre'))
                            ->collapsible()
                            ->collapsed()
                            ->columns(3)
                            ->schema([
                                TextInput::make('name.en')->label(__('admin.zankoline.name_en'))->required(),
                                TextInput::make('name.ku')->label(__('admin.zankoline.name_ku'))
                                    ->extraInputAttributes(['dir' => 'rtl']),
                                TextInput::make('name.ar')->label(__('admin.zankoline.name_ar'))
                                    ->extraInputAttributes(['dir' => 'rtl']),

                                Textarea::make('address.en')->label(__('admin.zankoline.address_en'))->rows(2),
                                Textarea::make('address.ku')->label(__('admin.zankoline.address_ku'))->rows(2)
                                    ->extraInputAttributes(['dir' => 'rtl']),
                                Textarea::make('address.ar')->label(__('admin.zankoline.address_ar'))->rows(2)
                                    ->extraInputAttributes(['dir' => 'rtl']),

                                TextInput::make('person')
                                    ->label(__('admin.zankoline.person'))
                                    ->helperText(__('admin.zankoline.person_help')),
                                TextInput::make('phone')
                                    ->label(__('admin.zankoline.phone'))
                                    ->helperText(__('admin.zankoline.phone_help'))
                                    ->tel(),
                                TextInput::make('maps_url')
                                    ->label(__('admin.zankoline.maps_url'))
                                    ->helperText(__('admin.zankoline.maps_url_help'))
                                    ->url(),

                                TextInput::make('lat')
                                    ->label(__('admin.zankoline.lat'))
                                    ->helperText(__('admin.zankoline.coords_help'))
                                    ->numeric()->minValue(-90)->maxValue(90),
                                TextInput::make('lng')
                                    ->label(__('admin.zankoline.lng'))
                                    ->numeric()->minValue(-180)->maxValue(180),
                                TextInput::make('slug')
                                    ->label(__('admin.zankoline.slug'))
                                    ->helperText(__('admin.zankoline.slug_help')),
                            ]),
                    ]),
            ]);
    }

    /** One box per language, each in its own direction. */
    private function translated(string $key, string $label, bool $long = false): Tabs
    {
        return Tabs::make($key)
            ->label($label)
            ->columnSpanFull()
            ->tabs(collect(config('nextstep.locales'))->map(
                function (array $config, string $locale) use ($key, $label, $long) {
                    $name = "content.{$key}.{$locale}";
                    $rtl = $config['dir'] === 'rtl';

                    // The placeholder is what the page says with the box empty,
                    // so an editor can see what they are replacing.
                    $shipped = trans('zankoline.'.str_replace('_', '.', $key), [], $locale);

                    $field = $long ? Textarea::make($name)->rows(3) : TextInput::make($name);

                    return Tab::make($config['code'])->schema([
                        $field->label($label)
                            ->placeholder(is_string($shipped) ? $shipped : '')
                            ->helperText(__('admin.zankoline.blank_help'))
                            ->extraInputAttributes($rtl ? ['dir' => 'rtl'] : []),
                    ]);
                }
            )->values()->all());
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')->label(__('admin.zankoline.save'))->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::put('zankoline_content', $data['content'] ?? [], 'zankoline');
        Setting::put('zankoline_centres', array_values($data['centres'] ?? []), 'zankoline');

        Notification::make()->title(__('admin.notify.saved'))->success()->send();
    }
}
