<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'role_id'])]
#[Timestamps(false)]
#[Incrementing(false)]
#[Table('user_roles')]
#[PrimaryKey('null')]
class UserRoles extends Model
{
    /**
     * Se utiliza la tabla como una relación de muchos a muchos en donde un usuario puede tener muchos roles y un rol puede tener muchos usuarios.
     * @return BelongsTo<User>
     * @return BelongsTo<Roles>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Roles::class, 'role_id');
    }
}
