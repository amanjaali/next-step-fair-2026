<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\Analytics\MetaBackfill;
use App\Services\Analytics\MetaConversionsClient;
use App\Services\Analytics\MetaEvents;
use App\Services\Analytics\MetaSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/** Meta Pixel and Conversions API, set up from the admin instead of the server. */
class MetaTracking extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?int $navigationSort = 90;

    protected string $view = 'filament.pages.meta-tracking';

    /** @var array<string, mixed> */
    public array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.platform');
    }

    public static function getNavigationLabel(): string
    {
        return 'Meta tracking';
    }

    public function getTitle(): string
    {
        return 'Meta tracking';
    }

    public function getSubheading(): ?string
    {
        return 'Pixel and Conversions API for CompleteRegistration and SubmitApplication.';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('Super Admin') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'pixel_id' => MetaSettings::pixelId(),
            'token' => null,
            'test_code' => Setting::get('meta_capi_test_code'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Settings')->schema([
                    TextInput::make('pixel_id')
                        ->label('Pixel ID')
                        ->required()
                        ->regex('/^\d{10,20}$/')
                        ->helperText('Events Manager → your Pixel → the ID under its name.'),
                    TextInput::make('token')
                        ->label('Conversions API access token')
                        ->password()
                        ->revealable()
                        ->placeholder(fn () => MetaSettings::token() ? 'Saved. Leave empty to keep it.' : 'Not set')
                        ->helperText('Events Manager → your Pixel → Settings → Conversions API → Generate access token. Stored encrypted. Once saved, the last 7 days of submissions and registrations are sent to Meta automatically (retried on each save until it goes through).'),
                    TextInput::make('test_code')
                        ->label('Test event code (optional)')
                        ->helperText('From Events Manager → Test events. While set, server events only appear there, not in reports. Clear it when you are done checking.'),
                ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();
        Setting::put('meta_pixel_id', trim((string) $data['pixel_id']), 'meta');
        Setting::put('meta_capi_test_code', filled($data['test_code'] ?? null) ? trim($data['test_code']) : null, 'meta');

        if (filled($data['token'] ?? null)) {
            MetaSettings::saveToken($data['token']);
        }

        $this->form->fill(['pixel_id' => MetaSettings::pixelId(), 'token' => null, 'test_code' => Setting::get('meta_capi_test_code')]);

        Notification::make()->title('Saved')->success()->send();

        // Automatic until it has gone through once, so a failed first try (a wrong
        // token, say) is retried on the next save rather than forgotten.
        if (MetaSettings::serverEnabled() && ! Setting::get('meta_backfill_done_at')) {
            $this->notifyBackfill(app(MetaBackfill::class)->run(), automatic: true);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('test')
                ->label('Send test event')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->disabled(fn () => ! MetaSettings::serverEnabled())
                ->action(function () {
                    $result = app(MetaConversionsClient::class)->send([MetaEvents::event(
                        'NextStepConnectionTest',
                        'test_'.now()->getTimestamp(),
                        now()->getTimestamp(),
                        url('/'),
                        ['external_id' => [hash('sha256', 'admin-test')], 'client_ip_address' => request()->ip(), 'client_user_agent' => request()->userAgent()],
                        ['source' => 'admin test'],
                    )]);

                    $result['ok']
                        ? Notification::make()->title('Meta received the test event')->body('The Conversions API is working.')->success()->send()
                        : Notification::make()->title('Meta did not accept the test event')->body($result['error'])->danger()->persistent()->send();
                }),
            Action::make('backfill')
                ->label('Resend last 7 days')
                ->icon(Heroicon::OutlinedArrowPath)
                ->disabled(fn () => ! MetaSettings::serverEnabled())
                ->requiresConfirmation()
                ->modalDescription('Sends every SubmitApplication and CompleteRegistration from the last 7 days to Meta. Safe to repeat: Meta counts each one once.')
                ->action(fn () => $this->notifyBackfill(app(MetaBackfill::class)->run(), automatic: false)),
        ];
    }

    /** @param array{ok: bool, submissions: int, registrations: int, received: int, error: ?string} $result */
    private function notifyBackfill(array $result, bool $automatic): void
    {
        if ($result['ok']) {
            Setting::put('meta_backfill_done_at', now()->toIso8601String(), 'meta');
        }

        $what = "{$result['submissions']} application(s) and {$result['registrations']} registration(s) from the last 7 days";

        $result['ok']
            ? Notification::make()
                ->title($automatic ? 'Past events sent to Meta' : 'Resent to Meta')
                ->body("Sent {$what}. Meta received {$result['received']}.")
                ->success()->persistent()->send()
            : Notification::make()
                ->title('Could not send past events to Meta')
                ->body($result['error'].' Fix the setting, then use "Resend last 7 days".')
                ->danger()->persistent()->send();
    }

    /** @return array<string, string> */
    public function statusLines(): array
    {
        $status = MetaSettings::status();

        return [
            'Pixel (browser)' => MetaSettings::pixelId() ? 'On — '.MetaSettings::pixelId() : 'Off',
            'Conversions API (server)' => MetaSettings::serverEnabled() ? 'On' : 'Off — no access token',
            'Test mode' => MetaSettings::testCode() ? 'On — events only show in Test events' : 'Off',
            'Last accepted by Meta' => filled($status['ok_at'] ?? null) ? \Illuminate\Support\Carbon::parse($status['ok_at'])->diffForHumans() : '—',
            'Last error' => filled($status['error'] ?? null)
                ? $status['error'].' ('.\Illuminate\Support\Carbon::parse($status['error_at'])->diffForHumans().')'
                : '—',
        ];
    }
}
