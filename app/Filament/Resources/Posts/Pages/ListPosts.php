<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'news' => Tab::make('News')->modifyQueryUsing(fn (Builder $q) => $q->where('type', Post::TYPE_NEWS)),
            'blog' => Tab::make('Blog')->modifyQueryUsing(fn (Builder $q) => $q->where('type', Post::TYPE_BLOG)),
            'drafts' => Tab::make('Drafts')->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'draft')),
        ];
    }
}
