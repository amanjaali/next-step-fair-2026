<?php

namespace App\Filament\Resources\OtpiqTemplates;

use App\Filament\Resources\OtpiqTemplates\Pages\EditOtpiqTemplate;
use App\Filament\Resources\OtpiqTemplates\Pages\ListOtpiqTemplates;
use App\Models\OtpiqTemplate;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/** OTPIQ dashboard template name and id per logical key × locale — both editable. */
class OtpiqTemplateResource extends Resource
{
    protected static ?string $model = OtpiqTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?int $navigationSort = 3;

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
            Section::make()->schema([
                Placeholder::make('logical_key')
                    ->label(__('admin.otpiq.logical_key'))
                    ->content(fn (?OtpiqTemplate $record): string => $record?->logical_key ?? ''),
                Placeholder::make('locale')
                    ->label(__('admin.otpiq.locale'))
                    ->content(fn (?OtpiqTemplate $record): string => strtoupper($record?->locale ?? '')),
                TextInput::make('name')
                    ->label(__('admin.otpiq.template_name'))
                    ->required()
                    ->helperText(__('admin.otpiq.template_name_help')),
                TextInput::make('provider_id')
                    ->label(__('admin.otpiq.provider_id'))
                    ->required()
                    ->helperText(__('admin.otpiq.provider_id_help')),
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
                TextColumn::make('locale')->badge()->label(__('admin.otpiq.locale')),
                TextInputColumn::make('name')
                    ->label(__('admin.otpiq.template_name'))
                    ->searchable()
                    ->rules(['required', 'string', 'max:255']),
                TextInputColumn::make('provider_id')
                    ->label(__('admin.otpiq.provider_id'))
                    ->searchable()
                    ->rules(['required', 'string', 'max:255']),
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
