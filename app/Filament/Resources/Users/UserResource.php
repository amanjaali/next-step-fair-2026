<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

/**
 * Staff accounts and their roles: Super Admin, Registration Manager, Content
 * Editor, Check-in Staff and Sponsor Manager.
 */
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.platform');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.users');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('name')->required(),
                TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->dehydrateStateUsing(fn (?string $state) => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->required(fn (string $operation) => $operation === 'create')
                    ->helperText('Leave empty to keep the current password.'),
                TextInput::make('job_title')->label('Job title'),
                Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->required(),
                Select::make('default_gate')->label('Default gate')->options(['A' => 'Gate A', 'B' => 'Gate B', 'C' => 'Gate C']),
                Select::make('locale')->options(['en' => 'English', 'ku' => 'Kurdish', 'ar' => 'Arabic'])->default('en'),
                Toggle::make('is_active')->label('Active')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->weight('semibold')->searchable(),
                TextColumn::make('email')->searchable()->copyable(),
                TextColumn::make('roles.name')->badge()->label('Roles'),
                TextColumn::make('job_title')->placeholder('—')->toggleable(),
                TextColumn::make('last_login_at')->label('Last login')->dateTime('j M H:i')->placeholder('—'),
                TextColumn::make('is_active')->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Disabled')
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage-users') ?? false;
    }
}
