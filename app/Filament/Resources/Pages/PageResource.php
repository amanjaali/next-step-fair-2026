<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Filament\Support\Translatable;
use App\Models\Page;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** Privacy, terms, press kit, about and the two track pages. */
class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.pages');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Page')->columns(3)->schema([
                TextInput::make('key')->required()->unique(ignoreRecord: true)
                    ->helperText('privacy, terms, press, about, fair, conference, scholarships'),
                DatePicker::make('updated_on')->label('Shown as last updated'),
                Toggle::make('published')->default(true),
            ]),

            Section::make('Header')->schema([
                Translatable::tabs(
                    fields: ['kicker' => 'Kicker', 'title' => 'Title', 'standfirst' => 'Standfirst', 'foot_note' => 'Footnote'],
                    kinds: ['standfirst' => 'textarea', 'foot_note' => 'textarea'],
                    columns: 2,
                ),
            ]),

            Section::make('Sections')->schema([
                Repeater::make('sections')
                    ->schema([
                        TextInput::make('n')->label('No.')->columnSpan(1),
                        TextInput::make('h.en')->label('Heading (EN)')->columnSpan(3),
                        TextInput::make('h.ku')->label('Heading (KU)')->columnSpan(2),
                        TextInput::make('h.ar')->label('Heading (AR)')->columnSpan(2),
                        Repeater::make('p')
                            ->label('Paragraphs')
                            ->schema([
                                Textarea::make('en')->label('EN')->rows(3),
                                Textarea::make('ku')->label('KU')->rows(3)->extraInputAttributes(['dir' => 'rtl']),
                                Textarea::make('ar')->label('AR')->rows(3)->extraInputAttributes(['dir' => 'rtl']),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->columnSpanFull(),
                        Repeater::make('list')
                            ->label('Bullet list')
                            ->schema([
                                Textarea::make('en')->label('EN')->rows(2),
                                Textarea::make('ku')->label('KU')->rows(2)->extraInputAttributes(['dir' => 'rtl']),
                                Textarea::make('ar')->label('AR')->rows(2)->extraInputAttributes(['dir' => 'rtl']),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->columnSpanFull(),
                    ])
                    ->columns(4)
                    ->itemLabel(fn (array $state) => trim(($state['n'] ?? '').' '.($state['h']['en'] ?? '')))
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull(),
            ]),

            Section::make('Aside')->columns(3)->schema([
                TextInput::make('aside.title.en')->label('Title (EN)'),
                TextInput::make('aside.title.ku')->label('Title (KU)'),
                TextInput::make('aside.title.ar')->label('Title (AR)'),
                Textarea::make('aside.body.en')->label('Body (EN)')->rows(2),
                Textarea::make('aside.body.ku')->label('Body (KU)')->rows(2),
                Textarea::make('aside.body.ar')->label('Body (AR)')->rows(2),
                TextInput::make('aside.contact')->label('Contact')->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')->weight('semibold')->searchable(),
                TextColumn::make('title')->formatStateUsing(fn (Page $r) => $r->t('title')),
                TextColumn::make('translations')
                    ->label(__('admin.fields.translations'))
                    ->badge()
                    ->state(fn (Page $r) => collect($r->translationMatrix())
                        ->map(fn ($p, $l) => strtoupper($l).' '.$p.'%')->values()->all())
                    ->color(fn (string $state) => str_contains($state, '100%') ? 'success' : 'warning'),
                TextColumn::make('updated_on')->date()->placeholder('—'),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage-content') ?? false;
    }
}
