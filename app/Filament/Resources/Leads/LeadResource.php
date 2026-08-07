<?php

namespace App\Filament\Resources\Leads;

use App\Filament\Resources\Leads\Pages\CreateLead;
use App\Filament\Resources\Leads\Pages\EditLead;
use App\Filament\Resources\Leads\Pages\ListLeads;
use App\Models\Lead;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/** Contact, "exhibit with us" and sponsorship enquiries, routed to the team. */
class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.partners');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.leads');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('name')->required(),
                TextInput::make('email')->email()->required(),
                TextInput::make('phone'),
                TextInput::make('organization'),
                TextInput::make('role'),
                Select::make('type')->options(['contact' => 'Contact', 'exhibit' => 'Exhibit', 'sponsor' => 'Sponsor'])->required(),
                Select::make('status')->options(['new' => 'New', 'contacted' => 'Contacted', 'won' => 'Won', 'closed' => 'Closed'])->required(),
                Textarea::make('message')->rows(4)->columnSpanFull(),
                Textarea::make('internal_notes')->label('Internal notes')->rows(3)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->weight('semibold')->searchable(),
                TextColumn::make('organization')->searchable()->placeholder('—'),
                TextColumn::make('email')->copyable(),
                TextColumn::make('type')->badge(),
                TextColumn::make('status')->badge()
                    ->color(fn (string $state) => match ($state) {
                        'won' => 'success', 'contacted' => 'info', 'closed' => 'gray', default => 'warning',
                    }),
                TextColumn::make('created_at')->dateTime('j M Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')->options(['contact' => 'Contact', 'exhibit' => 'Exhibit', 'sponsor' => 'Sponsor']),
                SelectFilter::make('status')->options(['new' => 'New', 'contacted' => 'Contacted', 'won' => 'Won', 'closed' => 'Closed']),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeads::route('/'),
            'create' => CreateLead::route('/create'),
            'edit' => EditLead::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage-leads') ?? false;
    }
}
