<?php

namespace App\Filament\Resources\EventSessions;

use App\Filament\Resources\EventSessions\Pages\CreateEventSession;
use App\Filament\Resources\EventSessions\Pages\EditEventSession;
use App\Filament\Resources\EventSessions\Pages\ListEventSessions;
use App\Filament\Support\Translatable;
use App\Models\EventSession;
use App\Models\Hall;
use App\Models\Speaker;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EventSessionResource extends Resource
{
    protected static ?string $model = EventSession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.programme');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.sessions');
    }

    public static function getModelLabel(): string
    {
        return 'session';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Session')->schema([
                Translatable::tabs(
                    fields: [
                        'title' => 'Title',
                        'subtitle' => 'Subtitle',
                        'description' => 'Description',
                        'who' => 'Credit line',
                    ],
                    kinds: ['description' => 'textarea'],
                ),
            ]),

            Section::make('Schedule')->columns(3)->schema([
                Select::make('day')->options([1 => 'Day 1', 2 => 'Day 2', 3 => 'Day 3'])->required(),
                TimePicker::make('starts_at')->seconds(false)->required(),
                TimePicker::make('ends_at')->seconds(false),
                TextInput::make('duration_label')->label('Duration label')->placeholder('90 min'),
                Select::make('type')
                    ->options(array_combine(
                        ['Ceremony', 'Conference', 'Panel', 'Workshop', 'Seminar', 'Roundtable', 'Expo', 'Break'],
                        ['Ceremony', 'Conference', 'Panel', 'Workshop', 'Seminar', 'Roundtable', 'Expo', 'Break'],
                    ))
                    ->required(),
                Select::make('track')->options(['fair' => 'Fair', 'conference' => 'Conference'])->default('fair')->required(),
                Select::make('hall_id')
                    ->label('Hall')
                    ->options(fn () => Hall::all()->mapWithKeys(fn (Hall $h) => [$h->id => $h->t('name')])),
                TextInput::make('hall_label')->label('Hall label override')->placeholder('Halls A & C'),
                TextInput::make('languages')->placeholder('KU · AR · EN'),
                TextInput::make('year')->numeric()->default(2026)->required(),
                Toggle::make('bookable')->label('Can be saved to a personal agenda')->default(true),
                Toggle::make('published')->default(true),
                Select::make('speakers')
                    ->label('Speakers')
                    ->relationship(name: 'speakers')
                    // Names are translatable JSON, so the label comes from the
                    // record rather than from a plucked column.
                    ->getOptionLabelFromRecordUsing(fn (Speaker $record) => $record->t('name'))
                    ->multiple()
                    ->preload()
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('day')
            ->columns([
                TextColumn::make('day')->badge()->color('gray')->sortable(),
                TextColumn::make('starts_at')->label('Time')->formatStateUsing(fn (EventSession $r) => $r->timeLabel())->sortable(),
                TextColumn::make('title')
                    ->formatStateUsing(fn (EventSession $record) => $record->t('title'))
                    ->wrap()
                    ->weight('semibold')
                    ->searchable(query: fn ($query, $search) => $query->where('title', 'like', "%{$search}%")),
                TextColumn::make('type')->badge(),
                TextColumn::make('track')->badge()
                    ->color(fn (string $state) => $state === 'conference' ? 'info' : 'primary'),
                TextColumn::make('hall.code')->label('Hall')->placeholder('—'),
                TextColumn::make('registrations_count')->counts('registrations')->label('Saved by'),
            ])
            ->filters([
                SelectFilter::make('day')->options([1 => 'Day 1', 2 => 'Day 2', 3 => 'Day 3']),
                SelectFilter::make('track')->options(['fair' => 'Fair', 'conference' => 'Conference']),
                SelectFilter::make('type')->options(fn () => EventSession::distinct()->pluck('type', 'type')->all()),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEventSessions::route('/'),
            'create' => CreateEventSession::route('/create'),
            'edit' => EditEventSession::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage-content') ?? false;
    }
}
