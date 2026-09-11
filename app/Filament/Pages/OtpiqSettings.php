<?php

namespace App\Filament\Pages;

use App\Models\OtpiqSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * OTPIQ account credentials and badge send flags — the row in otpiq_settings.
 */
class OtpiqSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.otpiq-settings';

    /** @var array<string, mixed> */
    public array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.messaging');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.otpiq.settings_nav');
    }

    public function getTitle(): string
    {
        return __('admin.otpiq.settings_title');
    }

    public function getSubheading(): ?string
    {
        return __('admin.otpiq.settings_subtitle');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage-templates') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill(OtpiqSetting::singleton()->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make(__('admin.otpiq.account'))
                    ->description(__('admin.otpiq.account_help'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('base_url')
                            ->label(__('admin.otpiq.base_url'))
                            ->url()
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('api_key')
                            ->label(__('admin.otpiq.api_key'))
                            ->password()
                            ->revealable()
                            ->helperText(__('admin.otpiq.api_key_help')),
                        TextInput::make('webhook_secret')
                            ->label(__('admin.otpiq.webhook_secret'))
                            ->password()
                            ->revealable()
                            ->helperText(__('admin.otpiq.webhook_secret_help')),
                        TextInput::make('account_id')
                            ->label(__('admin.otpiq.account_id'))
                            ->required(),
                        TextInput::make('phone_id')
                            ->label(__('admin.otpiq.phone_id'))
                            ->required(),
                    ]),

                Section::make(__('admin.otpiq.badge'))
                    ->description(__('admin.otpiq.badge_help'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('public_url')
                            ->label(__('admin.otpiq.public_url'))
                            ->url()
                            ->required()
                            ->helperText(__('admin.otpiq.public_url_help'))
                            ->columnSpanFull(),
                        TextInput::make('local_header_image')
                            ->label(__('admin.otpiq.local_header_image'))
                            ->url()
                            ->helperText(__('admin.otpiq.local_header_image_help'))
                            ->columnSpanFull(),
                        Toggle::make('send_header_image')
                            ->label(__('admin.otpiq.send_header_image')),
                        Toggle::make('send_button_link')
                            ->label(__('admin.otpiq.send_button_link')),
                        Toggle::make('verify_ssl')
                            ->label(__('admin.otpiq.verify_ssl')),
                    ]),
            ]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')->label(__('admin.otpiq.save'))->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        OtpiqSetting::singleton()->update($data);

        Notification::make()->title(__('admin.notify.saved'))->success()->send();
    }
}
