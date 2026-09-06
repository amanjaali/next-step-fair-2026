<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'locale', 'phone', 'job_title',
        'avatar_path', 'default_gate', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Check-in staff are deliberately kept out of the admin panel: their account
     * only opens the scanner. Everyone else needs an active account and a role.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return $this->hasAnyRole([
            'Super Admin', 'Registration Manager', 'Content Editor', 'Sponsor Manager',
            'Scholarship Committee',
        ]);
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckIn::class, 'staff_id');
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }
}
