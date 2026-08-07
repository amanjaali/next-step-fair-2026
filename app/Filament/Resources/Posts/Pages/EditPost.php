<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label(__('admin.actions.preview'))
                ->icon('heroicon-m-eye')
                ->url(fn (Post $record) => $record->type === Post::TYPE_BLOG
                    ? route('blog.show', ['locale' => 'en', 'post' => $record])
                    : route('news.show', ['locale' => 'en', 'post' => $record]))
                ->openUrlInNewTab(),
            DeleteAction::make(),
        ];
    }
}
