<?php

namespace App\Filament\Resources\MessageTemplates;

use App\Filament\Resources\MessageTemplates\Pages\CreateMessageTemplate;
use App\Filament\Resources\MessageTemplates\Pages\EditMessageTemplate;
use App\Filament\Resources\MessageTemplates\Pages\ListMessageTemplates;
use App\Models\MessageTemplate;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Template bodies per key, channel and language.
 *
 * `approval_status` mirrors Meta: nothing on the WhatsApp track can be sent until
 * the matching template name is approved in that exact language.
 */
class MessageTemplateResource extends Resource
{
    protected static ?string $model = MessageTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.messaging');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.templates');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Template')->columns(3)->schema([
                TextInput::make('key')->required(),
                Select::make('channel')->options(['whatsapp' => 'WhatsApp', 'email' => 'Email', 'sms' => 'SMS'])->required(),
                Select::make('locale')->options(['en' => 'English', 'ku' => 'Kurdish', 'ar' => 'Arabic'])->required(),
                TextInput::make('name')->columnSpan(2),
                TextInput::make('meta_template_name')->label('Name approved by Meta'),
                TextInput::make('subject')->label('Subject (email only)')->columnSpanFull(),
                Textarea::make('body')
                    ->rows(10)
                    ->required()
                    ->helperText('Use :name, :days, :ticket and friends. The order here must match {{1}}, {{2}}… in the Meta submission.')
                    ->columnSpanFull(),
                TagsInput::make('variables')->label('Variables, in order')->columnSpanFull(),
                Select::make('approval_status')
                    ->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'])
                    ->default('pending')
                    ->required(),
                Toggle::make('active')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('key')
            ->groups(['key'])
            ->columns([
                TextColumn::make('key')->weight('semibold')->searchable()->sortable(),
                TextColumn::make('channel')->badge(),
                TextColumn::make('locale')->badge()->color('gray'),
                TextColumn::make('meta_template_name')->label('Meta name')->placeholder('—')->toggleable(),
                TextColumn::make('approval_status')
                    ->label('Approval')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('body')->limit(60)->wrap()->toggleable(),
                TextColumn::make('updated_at')->dateTime('j M Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('channel')->options(['whatsapp' => 'WhatsApp', 'email' => 'Email']),
                SelectFilter::make('locale')->options(['en' => 'English', 'ku' => 'Kurdish', 'ar' => 'Arabic']),
                SelectFilter::make('approval_status')->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected']),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMessageTemplates::route('/'),
            'create' => CreateMessageTemplate::route('/create'),
            'edit' => EditMessageTemplate::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage-templates') ?? false;
    }
}
