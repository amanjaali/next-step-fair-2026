<?php

namespace App\Filament\Resources\Posts;

use App\Filament\Forms\Components\TinyEditor;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Support\Translatable;
use App\Models\Category;
use App\Models\Post;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
 * News and blog. Same table, different voice: news is organisational, the blog
 * is student-facing guidance, and each has its own category set.
 */
class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.posts');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Story')->schema([
                Translatable::tabs(
                    fields: [
                        'title' => 'Headline',
                        'standfirst' => 'Standfirst',
                        'excerpt' => 'Excerpt',
                        'body' => 'Body',
                    ],
                    kinds: [
                        'standfirst' => 'textarea',
                        'excerpt' => 'textarea',
                        'body' => 'editor',
                    ],
                ),
            ]),

            Section::make('Filing')->columns(3)->schema([
                Select::make('type')
                    ->options([Post::TYPE_NEWS => 'News', Post::TYPE_BLOG => 'Blog'])
                    ->default(Post::TYPE_NEWS)
                    ->live()
                    ->required(),

                Select::make('category_id')
                    ->label('Category')
                    ->options(fn ($get) => Category::where('type', $get('type') ?: Post::TYPE_NEWS)
                        ->get()
                        ->mapWithKeys(fn (Category $c) => [$c->id => $c->t('name')]))
                    ->searchable(),

                Select::make('status')
                    ->options(['draft' => 'Draft', 'scheduled' => 'Scheduled', 'published' => 'Published'])
                    ->default('draft')
                    ->required(),

                DateTimePicker::make('published_at')->label('Publish at')->default(now()),

                TextInput::make('slug')
                    ->helperText('Leave empty to generate from the English headline.')
                    ->unique(ignoreRecord: true),

                Toggle::make('pinned')->label('Pin to the top of the newsroom'),
            ]),

            Section::make('Media & attribution')->columns(2)->schema([
                FileUpload::make('cover_path')
                    ->label('Cover image')
                    ->image()
                    ->imageEditor()
                    ->directory('posts')
                    ->columnSpanFull(),

                TextInput::make('cover_placeholder')
                    ->label('Placeholder label')
                    ->helperText('Shown in the grey frame until the photograph is uploaded.'),

                TextInput::make('author_name')->label('Author'),
                Textarea::make('author_bio')->label('Author bio')->rows(2)->columnSpanFull(),

                TinyEditor::make('quote.en')->label('Pull quote (EN)')->minimal()->height('180px')->columnSpanFull(),
                TextInput::make('quote_by.en')->label('Quote attribution (EN)')->columnSpanFull(),

                Repeater::make('facts')
                    ->label('Facts panel')
                    ->schema([
                        TextInput::make('k')->label('Label'),
                        TextInput::make('v')->label('Value'),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->defaultItems(0),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Headline')
                    ->formatStateUsing(fn (Post $record) => $record->t('title'))
                    ->wrap()
                    ->searchable(query: fn ($query, $search) => $query->where('title', 'like', "%{$search}%"))
                    ->weight('semibold'),

                TextColumn::make('type')->badge(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->formatStateUsing(fn (Post $record) => $record->category?->t('name') ?? '—')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('translations')
                    ->label(__('admin.fields.translations'))
                    ->badge()
                    ->state(fn (Post $record) => collect($record->translationMatrix())
                        ->map(fn ($percent, $locale) => strtoupper($locale).' '.$percent.'%')
                        ->values()
                        ->all())
                    ->color(fn (string $state) => str_contains($state, '100%') ? 'success' : 'warning'),

                IconColumn::make('pinned')->boolean()->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'published' => 'success',
                        'scheduled' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('published_at')->dateTime('j M Y')->sortable(),
                TextColumn::make('views')->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')->options([Post::TYPE_NEWS => 'News', Post::TYPE_BLOG => 'Blog']),
                SelectFilter::make('status')->options(['draft' => 'Draft', 'scheduled' => 'Scheduled', 'published' => 'Published']),
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->options(fn () => Category::all()->mapWithKeys(fn (Category $c) => [$c->id => $c->t('name')])),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'edit' => EditPost::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage-content') ?? false;
    }
}
