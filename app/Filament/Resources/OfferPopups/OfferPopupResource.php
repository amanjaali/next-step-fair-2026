<?php

namespace App\Filament\Resources\OfferPopups;

use App\Filament\Resources\OfferPopups\Pages\CreateOfferPopup;
use App\Filament\Resources\OfferPopups\Pages\EditOfferPopup;
use App\Filament\Resources\OfferPopups\Pages\ListOfferPopups;
use App\Filament\Support\Translatable;
use App\Models\OfferPopup;
use App\Models\Opportunity;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * The popup on the home page, and the offers inside it.
 *
 * Written for somebody rewriting it before breakfast: everything is on one
 * screen, the offers are a repeater rather than a second resource to navigate
 * to, and switching it on is one toggle.
 *
 * Only one popup shows at a time — the most recently edited live one. Leaving an
 * old popup switched on is therefore harmless, which is the behaviour you want
 * from a thing that gets edited in a hurry.
 */
class OfferPopupResource extends Resource
{
    protected static ?string $model = OfferPopup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static ?int $navigationSort = 4;

    /**
     * The menu says "Home page popup", so every heading and button here says the
     * same. A resource whose nav item and page title disagree makes somebody
     * wonder whether they clicked the right thing.
     */
    protected static ?string $modelLabel = 'popup';

    protected static ?string $pluralModelLabel = 'home page popups';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.popups');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.popups.heading'))->schema([
                Translatable::tabs(
                    fields: [
                        'title' => 'Headline',
                        'intro' => 'Opening line — one sentence, optional',
                        'dismiss_label' => 'Wording on the dismiss button — optional',
                    ],
                    kinds: ['intro' => 'textarea'],
                ),
            ]),

            Section::make(__('admin.popups.offers'))
                ->description(__('admin.popups.offers_help'))
                ->schema([
                    Repeater::make('items')
                        ->relationship()
                        ->label('')
                        ->addActionLabel(__('admin.popups.add_offer'))
                        ->orderColumn('sort')
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title']['en'] ?? null)
                        ->minItems(1)
                        ->defaultItems(1)
                        ->schema([
                            Translatable::tabs(
                                fields: [
                                    'title' => 'Offer',
                                    'body' => 'One or two lines',
                                    'action_label' => 'Link wording — optional',
                                ],
                                kinds: ['body' => 'textarea'],
                            ),
                            TextInput::make('badge')
                                ->label(__('admin.popups.badge'))
                                ->maxLength(60)
                                ->helperText(__('admin.popups.badge_help')),
                            TextInput::make('action_url')
                                ->label(__('admin.popups.url'))
                                ->url()
                                ->helperText(__('admin.popups.url_help')),
                        ]),
                ]),

            // Full width: three fields in a half-width column wrap the date
            // pickers onto two lines each and squeeze the helper text into a
            // ribbon.
            Section::make(__('admin.popups.when'))->columnSpanFull()->columns(3)->schema([
                Select::make('audience')
                    ->label(__('admin.opportunities.audience'))
                    ->options(collect(Opportunity::audiences())->mapWithKeys(fn ($a) => [$a => __("opportunities.audiences.$a")]))
                    ->default(Opportunity::AUDIENCE_EVERYONE)
                    ->helperText(__('admin.popups.audience_help'))
                    ->required(),
                DatePicker::make('starts_at')->label(__('admin.opportunities.opens')),
                DatePicker::make('ends_at')->label(__('admin.opportunities.closes')),

                Toggle::make('active')
                    ->label(__('admin.popups.active'))
                    ->helperText(__('admin.popups.active_help'))
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label(__('admin.fields.title'))
                    ->formatStateUsing(fn (OfferPopup $r) => $r->t('title'))
                    ->weight('semibold')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('items_count')->counts('items')->label(__('admin.popups.offers')),
                TextColumn::make('audience')
                    ->label(__('admin.opportunities.audience'))
                    ->formatStateUsing(fn (string $state) => __("opportunities.audiences.$state")),
                IconColumn::make('active')->label(__('admin.popups.active'))->boolean(),
                TextColumn::make('view_count')->label(__('admin.popups.follows'))->sortable(),
                // The version everybody's browser remembers. Editing bumps it,
                // which is what brings the popup back for people who closed it.
                TextColumn::make('updated_at')->label(__('admin.popups.edited'))->dateTime('j M Y, H:i')->sortable(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOfferPopups::route('/'),
            'create' => CreateOfferPopup::route('/create'),
            'edit' => EditOfferPopup::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage-content') ?? false;
    }
}
