<?php

namespace App\Filament\Resources\Partners;

use App\Filament\Resources\Partners\Pages\CreatePartner;
use App\Filament\Resources\Partners\Pages\EditPartner;
use App\Filament\Resources\Partners\Pages\ListPartners;
use App\Filament\Support\Translatable;
use App\Models\Organization;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * The partnerships, and the page behind each mark.
 *
 * The ministry's mark sits at the top of every page and the students'
 * association beside it. Both were pictures that went nowhere: a partnership
 * asserted and never explained. This is where each one is written up — who they
 * are, what they do with Next Step, and how to reach them.
 *
 * Separate from the exhibitor directory on purpose. That screen holds hundreds
 * of universities and booths and is worked through by the sponsorship team; this
 * one holds a handful of relationships that the organisation's own leadership
 * cares about the wording of, and it is the only screen where the marks in the
 * header have a meaning attached.
 */
class PartnerResource extends Resource
{
    protected static ?string $model = Organization::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHandRaised;

    protected static ?int $navigationSort = 0;

    protected static ?string $slug = 'partnerships';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.partners');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.partners.nav');
    }

    public static function getModelLabel(): string
    {
        return __('admin.partners.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.partners.plural');
    }

    /** Only the relationships that carry a mark, never the exhibitor list. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereIn('kind', Organization::PROFILED_KINDS);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.partners.who'))
                ->description(__('admin.partners.who_help'))
                ->schema([
                    Translatable::tabs(
                        fields: [
                            'name' => __('admin.partners.name'),
                            'description' => __('admin.partners.one_liner'),
                            'about' => __('admin.partners.about'),
                            'partnership' => __('admin.partners.partnership'),
                        ],
                        kinds: [
                            'description' => 'textarea',
                            'about' => 'editor-minimal',
                            'partnership' => 'editor-minimal',
                        ],
                    ),
                ]),

            Section::make(__('admin.partners.contact'))
                ->description(__('admin.partners.contact_help'))
                ->columns(3)
                ->schema([
                    TextInput::make('website')
                        ->label(__('admin.partners.website'))
                        ->url()
                        ->placeholder('https://'),
                    TextInput::make('public_email')
                        ->label(__('admin.partners.email'))
                        ->email(),
                    TextInput::make('public_phone')
                        ->label(__('admin.partners.phone'))
                        ->tel(),
                ]),

            Section::make(__('admin.partners.mark'))
                ->description(__('admin.partners.mark_help'))
                ->columns(2)
                ->schema([
                    FileUpload::make('logo_path')
                        ->label(__('admin.partners.logo'))
                        ->helperText(__('admin.partners.logo_help'))
                        ->image()->directory('logos')->disk('public')
                        ->columnSpanFull(),

                    TextInput::make('slug')
                        ->label(__('admin.partners.slug'))
                        ->helperText(__('admin.partners.slug_help'))
                        ->required()
                        ->unique(ignoreRecord: true),

                    Select::make('kind')
                        ->label(__('admin.partners.kind'))
                        ->options([
                            Organization::KIND_STRATEGIC => __('site.pages.partner.strategic'),
                            Organization::KIND_SUPPORTER => __('site.pages.partner.supporter'),
                        ])
                        ->default(Organization::KIND_STRATEGIC)
                        ->required(),

                    TextInput::make('since_year')
                        ->label(__('admin.partners.since'))
                        ->numeric()->minValue(2000)->maxValue(2100),

                    TextInput::make('sort')
                        ->label(__('admin.partners.order'))
                        ->helperText(__('admin.partners.order_help'))
                        ->numeric()->default(0),

                    TextInput::make('year')->numeric()->default(2026)->required()
                        ->label(__('admin.partners.year')),

                    Toggle::make('published')
                        ->label(__('admin.partners.published'))
                        ->helperText(__('admin.partners.published_help'))
                        ->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.partners.name'))
                    ->formatStateUsing(fn (Organization $record) => $record->t('name'))
                    ->description(fn (Organization $record) => $record->t('description'))
                    ->weight('semibold')
                    ->wrap(),

                TextColumn::make('kind')
                    ->label(__('admin.partners.kind'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === Organization::KIND_STRATEGIC
                        ? __('site.pages.partner.strategic')
                        : __('site.pages.partner.supporter')),

                /*
                 * The one thing a person on this screen actually wants to know:
                 * is the mark in the header a link yet, or still a picture?
                 */
                IconColumn::make('has_page')
                    ->label(__('admin.partners.page'))
                    ->boolean()
                    ->state(fn (Organization $record) => $record->hasPartnerPage())
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedMinusCircle),

                TextColumn::make('public_phone')->label(__('admin.partners.phone'))->placeholder('—'),
                TextColumn::make('sort')->label(__('admin.partners.order'))->sortable(),
            ])
            ->recordActions([
                EditAction::make(),

                // Straight to what the visitor sees, which is the only way to
                // tell whether the wording reads properly.
                Action::make('view_page')
                    ->label(__('admin.partners.open'))
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->color('gray')
                    ->url(fn (Organization $record) => $record->partnerUrl(), shouldOpenInNewTab: true)
                    ->visible(fn (Organization $record) => $record->hasPartnerPage()),
            ])
            ->emptyStateHeading(__('admin.partners.empty'))
            ->emptyStateDescription(__('admin.partners.empty_help'));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPartners::route('/'),
            'create' => CreatePartner::route('/create'),
            'edit' => EditPartner::route('/{record}/edit'),
        ];
    }

    /** A slug is a URL; it should not have to be typed carefully. */
    public static function slugify(?string $name): string
    {
        return Str::slug((string) $name) ?: 'partner';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyPermission(['manage-content', 'manage-sponsors']) ?? false;
    }
}
