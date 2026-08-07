<?php

namespace App\Filament\Resources\NewsletterSubscribers;

use App\Filament\Resources\NewsletterSubscribers\Pages\CreateNewsletterSubscriber;
use App\Filament\Resources\NewsletterSubscribers\Pages\EditNewsletterSubscriber;
use App\Filament\Resources\NewsletterSubscribers\Pages\ListNewsletterSubscribers;
use App\Models\NewsletterSubscriber;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/** Newsletter list, with the page each subscriber signed up from. */
class NewsletterSubscriberResource extends Resource
{
    protected static ?string $model = NewsletterSubscriber::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.partners');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.subscribers');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
                Select::make('locale')->options(['en' => 'English', 'ku' => 'Kurdish', 'ar' => 'Arabic'])->required(),
                TextInput::make('source'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('email')->searchable()->copyable()->weight('semibold'),
                TextColumn::make('locale')->badge()->color('gray'),
                TextColumn::make('source')->placeholder('—'),
                TextColumn::make('created_at')->label('Subscribed')->dateTime('j M Y')->sortable(),
                TextColumn::make('unsubscribed_at')->label('Unsubscribed')->dateTime('j M Y')->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('locale')->options(['en' => 'English', 'ku' => 'Kurdish', 'ar' => 'Arabic']),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNewsletterSubscribers::route('/'),
            'create' => CreateNewsletterSubscriber::route('/create'),
            'edit' => EditNewsletterSubscriber::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage-leads') ?? false;
    }
}
