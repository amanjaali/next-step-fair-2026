<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A member of a university's team.
 *
 * Passwordless, like attendees, but keyed on the institutional e-mail address
 * rather than a phone number — that is the credential a university actually
 * controls, and it is what proves someone speaks for the institution they claim.
 *
 * The address is encrypted with a keyed-hash companion for lookup, exactly as
 * registrant contact details are.
 */
class InstitutionUser extends Model implements AuthenticatableContract
{
    use Authenticatable;

    public const ROLE_OWNER = 'owner';

    public const ROLE_MEMBER = 'member';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'email' => 'encrypted',
            'phone' => 'encrypted',
            'is_active' => 'boolean',
            'last_signed_in_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $user) {
            if ($user->isDirty('email')) {
                $user->email_hash = Registration::hashValue(strtolower((string) $user->email));
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function otps(): HasMany
    {
        return $this->hasMany(InstitutionOtp::class);
    }

    public function scopeWhereEmail(Builder $query, string $email): Builder
    {
        return $query->where('email_hash', Registration::hashValue(strtolower(trim($email))));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    public function firstName(): string
    {
        return str($this->name)->trim()->explode(' ')->first() ?: $this->name;
    }
}
