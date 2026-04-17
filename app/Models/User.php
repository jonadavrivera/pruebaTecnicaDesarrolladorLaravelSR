<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'status'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Se crea una relación de muchos a muchos en donde un usuario puede tener muchos roles y un rol puede tener muchos usuarios.
     * @return BelongsToMany<Roles>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Roles::class, 'user_roles', 'user_id', 'role_id');
    }

    /**
     * Se crea una relación de uno a muchos en donde un usuario puede tener muchas reservas.
     * @return HasMany<Reservations>
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservations::class);
    }

    /**
     * Se crea una relación de uno a muchos en donde un usuario puede tener muchos logs.
     * @return HasMany<Logs>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(Logs::class);
    }

    /**
     * Verifica si el usuario tiene un permiso específico se utiliza en el policies para saber si el usuario tiene el permiso para realizar las acciones.
     * @param string $permissionSlug
     * @return bool
     */
    public function hasPermission($permissionSlug): bool
{
    return $this->roles()
        ->whereHas('permissions', function ($query) use ($permissionSlug) {
            $query->where('slug', $permissionSlug)
                  ->where('is_active', true);
        })
        ->exists();
}
    /**
     * Se crea una relación de uno a muchos en donde un usuario puede tener muchos cambios en las versiones de las reservas.
     * @return HasMany<ReservationVersions>
     */
    public function reservationVersionChanges(): HasMany
    {
        return $this->hasMany(ReservationVersions::class, 'changed_by');
    }
}
