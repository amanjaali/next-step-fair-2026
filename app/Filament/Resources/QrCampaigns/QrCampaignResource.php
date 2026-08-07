<?php

namespace App\Filament\Resources\QrCampaigns;

use App\Filament\Resources\QrCampaigns\Pages\CreateQrCampaign;
use App\Filament\Resources\QrCampaigns\Pages\EditQrCampaign;
use App\Filament\Resources\QrCampaigns\Pages\ListQrCampaigns;
use App\Models\QrCampaign;
use App\Services\QrCodeService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Our own QR generator.
 *
 * Staff create a code per poster, school visit, booth or social campaign; the
 * short link counts the scan and carries UTM parameters through to registration,
 * so the funnel can attribute sign-ups back to the print that produced them.
 */
class QrCampaignResource extends Resource
{
    protected static ?string $model = QrCampaign::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQrCode;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.platform');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.qr');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Campaign')->columns(2)->schema([
                TextInput::make('name')->required()->columnSpanFull(),
                TextInput::make('target_url')
                    ->label('Target')
                    ->url()
                    ->required()
                    ->default(fn () => route('register.fair', ['locale' => 'en']))
                    ->columnSpanFull(),
                TextInput::make('code')
                    ->helperText('Leave empty to generate. This becomes /q/{code}.')
                    ->unique(ignoreRecord: true),
                Select::make('medium')->options([
                    'poster' => 'Poster', 'print' => 'Print', 'social' => 'Social',
                    'booth' => 'Booth', 'school' => 'School visit', 'other' => 'Other',
                ]),
                TextInput::make('utm_source')->label('utm_source'),
                TextInput::make('utm_campaign')->label('utm_campaign'),
                Select::make('accent')
                    ->label('Track colour')
                    ->options(['#B64698' => 'Magenta — fair', '#2C4BE0' => 'Cobalt — conference', '#050708' => 'Ink'])
                    ->default('#B64698'),
                DateTimePicker::make('expires_at'),
                Toggle::make('active')->default(true),
                Textarea::make('description')->rows(2)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->weight('semibold')->searchable(),
                TextColumn::make('code')->fontFamily('mono')->copyable(),
                TextColumn::make('short_url')
                    ->label('Short link')
                    ->state(fn (QrCampaign $record) => $record->shortUrl())
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('medium')->badge()->color('gray')->placeholder('—'),
                TextColumn::make('scan_count')->label('Scans')->numeric()->sortable(),
                TextColumn::make('unique_scans')
                    ->label('Unique')
                    ->state(fn (QrCampaign $record) => $record->uniqueScans()),
                TextColumn::make('registrations_count')
                    ->counts('registrations')
                    ->label('Registrations'),
                TextColumn::make('active')->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Off')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
            ])
            ->recordActions([
                Action::make('download')
                    ->label('Download QR (SVG)')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->action(function (QrCampaign $record) {
                        $svg = app(QrCodeService::class)->svg($record->shortUrl(), 1024);

                        return response()->streamDownload(
                            fn () => print ($svg),
                            'ns-qr-'.$record->code.'.svg',
                            ['Content-Type' => 'image/svg+xml'],
                        );
                    }),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQrCampaigns::route('/'),
            'create' => CreateQrCampaign::route('/create'),
            'edit' => EditQrCampaign::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage-qr-campaigns') ?? false;
    }
}
