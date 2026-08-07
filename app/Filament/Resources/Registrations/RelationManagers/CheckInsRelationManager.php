<?php

namespace App\Filament\Resources\Registrations\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** Which days this badge was actually used, at which gate, scanned by whom. */
class CheckInsRelationManager extends RelationManager
{
    protected static string $relationship = 'checkIns';

    protected static ?string $title = 'Check-ins';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('day')
            ->columns([
                TextColumn::make('day')->label('Day')->badge()->color('info'),
                TextColumn::make('checked_in_at')->label('Time')->dateTime('j M H:i'),
                TextColumn::make('gate'),
                TextColumn::make('method')->badge()->color('gray'),
                TextColumn::make('staff.name')->label('Staff')->placeholder('—'),
            ]);
    }
}
