<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Para esté modelo utilizo la sintaxis anterior para el uso de soft deletes, fillable y casts.
 */
#[Fillable(['permission_id', 'role_id'])]
#[Timestamps(false)]
#[Incrementing(false)]
#[Table('permission_roles')]
#[PrimaryKey('null')]
class PermissionRoles extends Model
{
    /**
     * La tabla tiene una relación de muchos a muchos en donde un permiso puede tener muchos roles y un rol puede tener muchos permisos.
     * @return BelongsTo<Permissions>
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permissions::class, 'permission_id');
    }

    /**
     * Relacion de uno a muchos en donde un rol puede tener muchos permisos.
     * @return BelongsTo<Roles>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Roles::class, 'role_id');
    }
}
