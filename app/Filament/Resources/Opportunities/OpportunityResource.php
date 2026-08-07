<?php

namespace App\Filament\Resources\Opportunities;

use App\Filament\Resources\Opportunities\Pages\CreateOpportunity;
use App\Filament\Resources\Opportunities\Pages\EditOpportunity;
use App\Filament\Resources\Opportunities\Pages\ListOpportunities;
use App\Filament\Support\Translatable;
use App\Models\Opportunity;
use App\Models\Organization;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
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

/**
 * What partners bring to Next Step, and the team publishes.
 *
 * The audience field is the one that matters most: an opportunity aimed at grade
 * 12 has no business appearing to a parent, and a board that shows everybody
 * everything stops being read.
 */
class OpportunityResource extends Resource
{
    protected static ?string $model = Opportunity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.opportunities');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.opportunities.content'))->schema([
                Translatable::tabs(
                    fields: [
                        'title' => 'Title',
                        'summary' => 'Summary — one or two sentences on the card',
                        'body' => 'Full description',
                        'eligibility' => 'Who can apply',
                        'action_label' => 'Button label',
                    ],
                    kinds: ['summary' => 'textarea', 'body' => 'editor', 'eligibility' => 'editor-minimal'],
                ),
            ]),

            Section::make(__('admin.opportunities.partner'))->columns(2)->schema([
                Select::make('organization_id')
                    ->label(__('admin.opportunities.organization'))
                    ->options(fn () => Organization::query()->get()->mapWithKeys(fn ($o) => [$o->id => $o->t('name')]))
                    ->searchable()
                    ->helperText(__('admin.opportunities.organization_help')),
                TextInput::make('partner_name')
                    ->label(__('admin.opportunities.partner_name'))
                    ->helperText(__('admin.opportunities.partner_name_help')),
                FileUpload::make('partner_logo_path')
                    ->label(__('admin.opportunities.logo'))
                    ->image()
                    ->directory('opportunities')
                    ->columnSpanFull(),
            ]),

            Section::make(__('admin.opportunities.terms'))->columns(3)->schema([
                Select::make('kind')
                    ->label(__('admin.opportunities.kind'))
                    ->options(collect(Opportunity::kinds())->mapWithKeys(fn ($k) => [$k => __("opportunities.kinds.$k")]))
                    ->default(Opportunity::KIND_OFFER)
                    ->required(),
                Select::make('audience')
                    ->label(__('admin.opportunities.audience'))
                    ->options(collect(Opportunity::audiences())->mapWithKeys(fn ($a) => [$a => __("opportunities.audiences.$a")]))
                    ->default(Opportunity::AUDIENCE_STUDENTS)
                    ->required()
                    ->helperText(__('admin.opportunities.audience_help')),
                TextInput::make('places')->label(__('admin.opportunities.places'))->numeric(),
                DatePicker::make('opens_at')->label(__('admin.opportunities.opens')),
                DatePicker::make('closes_at')
                    ->label(__('admin.opportunities.closes'))
                    ->helperText(__('admin.opportunities.closes_help')),
                TextInput::make('sort')->label(__('admin.fields.sort'))->numeric()->default(0),
                TextInput::make('action_url')
                    ->label(__('admin.opportunities.url'))
                    ->url()
                    ->columnSpan(2)
                    ->helperText(__('admin.opportunities.url_help')),
            ]),

            Section::make()->columns(2)->schema([
                Toggle::make('published')->label(__('admin.fields.published')),
                Toggle::make('featured')
                    ->label(__('admin.opportunities.featured'))
                    ->helperText(__('admin.opportunities.featured_help')),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('title')
                    ->label(__('admin.fields.title'))
                    ->formatStateUsing(fn (Opportunity $r) => $r->t('title'))
                    ->weight('semibold')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('partner_name')->label(__('admin.opportunities.partner'))->limit(28),
                TextColumn::make('kind')
                    ->label(__('admin.opportunities.kind'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => __("opportunities.kinds.$state")),
                TextColumn::make('audience')
                    ->label(__('admin.opportunities.audience'))
                    ->formatStateUsing(fn (string $state) => __("opportunities.audiences.$state")),
                TextColumn::make('closes_at')
                    ->label(__('admin.opportunities.closes'))
                    ->date('j M Y')
                    ->sortable()
                    // Red once it has closed: an expired opportunity still on the
                    // board tells students they were too late for something nobody
                    // took down.
                    ->color(fn (Opportunity $r) => $r->closes_at && $r->closes_at->isPast() ? 'danger' : null),
                TextColumn::make('view_count')->label(__('admin.opportunities.views'))->sortable(),
                // The number a partner actually asks for: not how many read it,
                // but how many went on to their form.
                TextColumn::make('follow_count')->label(__('admin.opportunities.follows'))->sortable(),
                IconColumn::make('published')->label(__('admin.fields.published'))->boolean(),
            ])
            ->filters([
                SelectFilter::make('kind')
                    ->options(collect(Opportunity::kinds())->mapWithKeys(fn ($k) => [$k => __("opportunities.kinds.$k")])),
                SelectFilter::make('audience')
                    ->options(collect(Opportunity::audiences())->mapWithKeys(fn ($a) => [$a => __("opportunities.audiences.$a")])),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOpportunities::route('/'),
            'create' => CreateOpportunity::route('/create'),
            'edit' => EditOpportunity::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage-content') ?? false;
    }
}
