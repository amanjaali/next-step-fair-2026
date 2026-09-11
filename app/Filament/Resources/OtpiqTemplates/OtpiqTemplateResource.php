<?php

namespace App\Filament\Resources\OtpiqTemplates;

use App\Filament\Resources\OtpiqTemplates\Pages\EditOtpiqTemplate;
use App\Filament\Resources\OtpiqTemplates\Pages\ListOtpiqTemplates;
use App\Models\OtpiqTemplate;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/** OTPIQ template name, id, and send shape per logical key × locale — all editable. */
class OtpiqTemplateResource extends Resource
{
    public static function logicalKeyLabel(string $logicalKey): string
    {
        $labels = __('admin.otpiq.logical_keys');

        return is_array($labels) && isset($labels[$logicalKey]) ? $labels[$logicalKey] : $logicalKey;
    }

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
                    ->content(fn (?OtpiqTemplate $record): string => $record
                        ? static::logicalKeyLabel($record->logical_key).' ('.$record->logical_key.')'
                        : ''),
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
            Section::make(__('admin.otpiq.send_shape'))->schema([
                TextInput::make('body_variables')
                    ->label(__('admin.otpiq.body_variables'))
                    ->helperText(__('admin.otpiq.body_variables_help'))
                    ->placeholder('name, days, ticket')
                    ->formatStateUsing(fn (?array $state): string => is_array($state) ? implode(', ', $state) : '')
                    ->dehydrateStateUsing(function (?string $state): ?array {
                        if ($state === null || trim($state) === '') {
                            return [];
                        }

                        return array_values(array_filter(array_map('trim', explode(',', $state))));
                    }),
                Toggle::make('send_header')
                    ->label(__('admin.otpiq.send_header'))
                    ->helperText(__('admin.otpiq.send_header_help')),
                Toggle::make('send_button')
                    ->label(__('admin.otpiq.send_button'))
                    ->helperText(__('admin.otpiq.send_button_help')),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('logical_key')
            ->groups(['logical_key'])
            ->columns([
                TextColumn::make('logical_key')
                    ->label(__('admin.otpiq.logical_key'))
                    ->formatStateUsing(fn (string $state): string => static::logicalKeyLabel($state))
                    ->description(fn (OtpiqTemplate $record): string => $record->logical_key)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('locale')->badge()->label(__('admin.otpiq.locale')),
                TextInputColumn::make('name')
                    ->label(__('admin.otpiq.template_name'))
                    ->searchable()
                    ->rules(['required', 'string', 'max:255']),
                TextColumn::make('body_variables')
                    ->label(__('admin.otpiq.body_variables'))
                    ->formatStateUsing(fn (OtpiqTemplate $record): string => implode(', ', $record->bodySlotNames()) ?: '—'),
                IconColumn::make('send_header')
                    ->label(__('admin.otpiq.send_header'))
                    ->boolean()
                    ->state(fn (OtpiqTemplate $record): bool => $record->wantsHeaderImage()),
                IconColumn::make('send_button')
                    ->label(__('admin.otpiq.send_button'))
                    ->boolean()
                    ->state(fn (OtpiqTemplate $record): bool => $record->wantsButtonLink()),
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
                        ->pluck('logical_key')
                        ->mapWithKeys(fn (string $key) => [$key => static::logicalKeyLabel($key)])
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
