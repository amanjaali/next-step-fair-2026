<?php

namespace App\Filament\Resources\Broadcasts;

use App\Filament\Resources\Broadcasts\Pages\CreateBroadcast;
use App\Filament\Resources\Broadcasts\Pages\EditBroadcast;
use App\Filament\Resources\Broadcasts\Pages\ListBroadcasts;
use App\Models\Broadcast;
use App\Models\MessageTemplate;
use App\Models\Registration;
use App\Services\Messaging\BroadcastSender;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Segmented sends, e.g. "all Day 2 students in Sulaimani".
 *
 * The audience is a saved filter rather than a frozen list, so the count shown
 * when it is queued is the count at send time, not at composition time.
 */
class BroadcastResource extends Resource
{
    protected static ?string $model = Broadcast::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.messaging');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.broadcasts');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Broadcast')->columns(2)->schema([
                TextInput::make('name')->required()->columnSpanFull(),
                Select::make('channel')->options(['whatsapp' => 'WhatsApp', 'email' => 'Email'])->default('whatsapp')->required(),
                Select::make('template_key')
                    ->label('Template')
                    ->options(fn () => MessageTemplate::distinct()->pluck('key', 'key')->all())
                    ->searchable(),
                Textarea::make('body_override')
                    ->label('Body override')
                    ->rows(6)
                    ->helperText('WhatsApp only accepts approved templates outside a 24-hour window; an override is for e-mail and for the log driver.')
                    ->columnSpanFull(),
                DateTimePicker::make('scheduled_at')->label('Send at'),
            ]),

            Section::make('Audience')->columns(3)->schema([
                Select::make('filters.track')->label('Track')->options(['fair' => 'Fair', 'conference' => 'Conference']),
                Select::make('filters.type')->label('Type')->options([
                    'student' => 'Student', 'parent' => 'Parent',
                    'government' => 'Government', 'official' => 'Official',
                ]),
                Select::make('filters.day')->label('Attending day')->options([1 => 'Day 1', 2 => 'Day 2', 3 => 'Day 3']),
                Select::make('filters.city')
                    ->label('City')
                    ->options(array_combine(config('nextstep.cities'), config('nextstep.cities')))
                    ->searchable(),
                Select::make('filters.locale')->label('Language')->options(['en' => 'English', 'ku' => 'Kurdish', 'ar' => 'Arabic']),
                Select::make('filters.status')->label('Status')->options([
                    Registration::STATUS_CONFIRMED => 'Confirmed',
                    Registration::STATUS_CHECKED_IN => 'Checked in',
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->weight('semibold')->searchable(),
                TextColumn::make('channel')->badge(),
                TextColumn::make('template_key')->label('Template')->placeholder('—'),
                TextColumn::make('audience_count')->label('Audience')->numeric(),
                TextColumn::make('status')->badge()
                    ->color(fn (string $state) => match ($state) {
                        'sent' => 'success',
                        'sending' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('scheduled_at')->dateTime('j M H:i')->placeholder('—'),
                TextColumn::make('messages_count')->counts('messages')->label('Sent'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('send')
                    ->label('Queue now')
                    ->icon('heroicon-m-paper-airplane')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->visible(fn (Broadcast $record) => in_array($record->status, ['draft', 'scheduled'], true))
                    ->action(function (Broadcast $record) {
                        $count = app(BroadcastSender::class)->send($record);

                        Notification::make()
                            ->title(__('admin.notify.resent', ['count' => $count]))
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBroadcasts::route('/'),
            'create' => CreateBroadcast::route('/create'),
            'edit' => EditBroadcast::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('send-messages') ?? false;
    }
}
