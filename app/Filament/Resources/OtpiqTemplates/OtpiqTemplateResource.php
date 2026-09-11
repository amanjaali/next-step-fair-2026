<?php

namespace App\Filament\Resources\OtpiqTemplates;

use App\Filament\Resources\OtpiqTemplates\Pages\EditOtpiqTemplate;
use App\Filament\Resources\OtpiqTemplates\Pages\ListOtpiqTemplates;
use App\Models\OtpiqTemplate;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/** OTPIQ / Meta WhatsApp template names, ids and send metadata per locale. */
class OtpiqTemplateResource extends Resource
{
    protected static ?string $model = OtpiqTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.messaging');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.otpiq.templates_nav');
    }

    public static function getModelLabel(): string
    {
        return __('admin.otpiq.template');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->schema([
                TextInput::make('logical_key')
                    ->label(__('admin.otpiq.logical_key'))
                    ->required()
                    ->disabled(fn (?OtpiqTemplate $record) => $record !== null),
                Select::make('locale')
                    ->options(['en' => 'English', 'ku' => 'Kurdish', 'ar' => 'Arabic'])
                    ->required()
                    ->disabled(fn (?OtpiqTemplate $record) => $record !== null),
                TextInput::make('name')
                    ->label(__('admin.otpiq.template_name'))
                    ->required()
                    ->columnSpanFull()
                    ->helperText(__('admin.otpiq.template_name_help')),
                TextInput::make('provider_id')
                    ->label(__('admin.otpiq.provider_id'))
                    ->required()
                    ->columnSpanFull(),
                TagsInput::make('body_variables')
                    ->label(__('admin.otpiq.body_variables'))
                    ->helperText(__('admin.otpiq.body_variables_help'))
                    ->columnSpanFull(),
                Toggle::make('header_image')
                    ->label(__('admin.otpiq.header_image')),
                Toggle::make('active')
                    ->label(__('admin.otpiq.active'))
                    ->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('logical_key')
            ->groups(['logical_key'])
            ->columns([
                TextColumn::make('logical_key')->searchable()->sortable(),
                TextColumn::make('locale')->badge(),
                TextColumn::make('name')->searchable()->wrap(),
                TextColumn::make('provider_id')->label(__('admin.otpiq.provider_id'))->toggleable(),
                IconColumn::make('header_image')->boolean()->label(__('admin.otpiq.header_image')),
                IconColumn::make('active')->boolean()->label(__('admin.otpiq.active')),
            ])
            ->filters([
                SelectFilter::make('logical_key')->options(
                    fn () => OtpiqTemplate::query()
                        ->distinct()
                        ->orderBy('logical_key')
                        ->pluck('logical_key', 'logical_key')
                        ->all(),
                ),
                SelectFilter::make('locale')->options(['en' => 'English', 'ku' => 'Kurdish', 'ar' => 'Arabic']),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOtpiqTemplates::route('/'),
            'edit' => EditOtpiqTemplate::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage-templates') ?? false;
    }
}
