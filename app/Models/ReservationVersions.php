<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Casts;

#[Fillable(['reservation_id', 'version', 'data', 'changed_by', 'change_type'])]
#[Casts(['version' => 'integer', 'data' => 'array'])]   
class ReservationVersions extends Model
{
    /**
     * Relacion de uno a muchos en donde una version puede tener una reserva.
     * @return BelongsTo<Reservations>
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservations::class, 'reservation_id');
    }

    /**
     * Relacion de uno a muchos en donde una version puede tener un usuario.
     * @return BelongsTo<User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
