<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'slug', 'is_active'])] 
class Permissions extends Model
{
    /**
     * Se crea una relación de muchos a muchos en donde un permiso puede tener muchos roles y un rol puede tener muchos permisos.
     * @return BelongsToMany<Roles>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Roles::class, 'permission_roles', 'permission_id', 'role_id');
    }

    public function scopeActive(Builder $query, bool $isActive = true): Builder
    {
        return $query->where('is_active', $isActive);
    }

    public function scopeWithSlugs(Builder $query, array|string|null $slugs): Builder
    {
        $slugList = collect((array) $slugs)
            ->filter(fn ($slug) => filled($slug))
            ->values();

        if ($slugList->isEmpty()) {
            return $query;
        }

        return $query->whereIn('slug', $slugList->all());
    }
}
