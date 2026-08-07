<?php

namespace App\Filament\Widgets;

use App\Models\EventSession;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/** Which sessions registrants saved — this decides which rooms grow next year. */
class SessionPopularityWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public function getTableHeading(): string
    {
        return __('admin.widgets.sessions');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(EventSession::query()->withCount('registrations')->orderByDesc('registrations_count')->limit(10))
            ->columns([
                TextColumn::make('day')->badge()->color('gray'),
                TextColumn::make('starts_at')->label('Time')->formatStateUsing(fn (EventSession $r) => $r->timeLabel()),
                TextColumn::make('title')->formatStateUsing(fn (EventSession $r) => $r->t('title'))->wrap()->weight('semibold'),
                TextColumn::make('type')->badge(),
                TextColumn::make('hall.code')->label('Hall')->placeholder('—'),
                TextColumn::make('registrations_count')->label('Saved by')->numeric()->sortable(),
            ])
            ->paginated(false);
    }
}
