<?php

namespace App\Models;

use Database\Factories\MeetingRoomsFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeetingRooms extends Model
{
    /**
     * Para esté modelo utilizo la sintaxis anterior para el uso de soft deletes, fillable y casts.
     */
    /** @use HasFactory<MeetingRoomsFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'location',
        'capacity',
        'status',
    ];

    protected $casts = [
        'capacity' => 'integer',
    ];

    /**
     * Relación de uno a muchos en donde una sala puede tener muchas reservas con la tabla meeting_rooms y reservations .
     * @return HasMany<Reservations>
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservations::class, 'meeting_room_id');
    }

    /**
     * Scope que se encarga de obtener las salas con el cambio de estatus.
     * Se valida que el estatus no sea un array vacío en caso de serlo se regresa el query sin filtro.
     * @param Builder $query
     * @param array $statuses
     * @return Builder
     */
    public function scopeWithStatuses(Builder $query, $statuses): Builder
    {
        $statuses = collect((array) $statuses)->filter()->values()->all();

        if (empty($statuses)) {
            return $query;
        }

        return $query->whereIn('status', $statuses);
    }

}
