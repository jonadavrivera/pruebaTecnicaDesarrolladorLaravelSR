<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Casts;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


#[Fillable(['name', 'slug', 'is_active'])]
#[Casts(['is_active' => 'boolean'])]
#[SoftDeletes]
class Roles extends Model
{
    /**
     * Se crea una relación de muchos a muchos en donde un rol puede tener muchos usuarios y un usuario puede tener muchos roles.
     * @return BelongsToMany<User>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles', 'role_id', 'user_id');
    }

    /**
     * Se crea una relación de muchos a muchos en donde un rol puede tener muchos permisos y un permiso puede tener muchos roles.
     * @return BelongsToMany<Permissions>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permissions::class, 'permission_roles', 'role_id', 'permission_id');
    }

    /**
     * Scope para realizar un filtro sobre los roles activos.
     * @param Builder<Roles> $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
