<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\ReservationsFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Para la prueba creo componentes usando la nueva sintaxis por medio de atributos.
 * @link https://laravel.com/docs/13.x/eloquent#mass-assignment
 */
#[Fillable(['meeting_room_id', 'user_id', 'start_time', 'end_time', 'title', 'description', 'status', 'version'])]
#[SoftDeletes]
class Reservations extends Model
{
    /** @use HasFactory<ReservationsFactory> */
    use HasFactory;

    /**
     * Cuando se lean los datos convertimos automaticamente a los tipos de datos que se han definido en el arreglo.
     * @return array<string, string>
     * @link https://laravel.com/docs/13.x/eloquent-mutators#attribute-casting
     */
    protected function casts(): array {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'version' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Relaciones de la tabla reservations se utiliza el retorno de tipo en las funciones por seguridad y controlar el tipo de dato que debe regresar.
     * @link https://laravel.com/docs/13.x/eloquent-relationships#defining-relationships
     * @return BelongsTo<MeetingRooms>
     * @return BelongsTo<User>
     * @return HasMany<ReservationVersions>
     */
    public function meetingRoom(): BelongsTo
    {
        return $this->belongsTo(MeetingRooms::class, 'meeting_room_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ReservationVersions::class, 'reservation_id');
    }

    /**
     * Siguiendo los principios del polimorfismo en donde una función puede ser utilizada para muchas cosas, creo una relación morphMany se obtienen los logs asociados a la reserva.
     * Al nombrar la función model en Logs laravel utilizas las columnas model_type y model_id para obtener el modelo asociado.
     * @link https://laravel.com/docs/13.x/eloquent-relationships#polymorphic-relations
     * @return MorphMany<Logs>
     */
    public function logs(): MorphMany
    {
        return $this->morphMany(Logs::class, 'model');
    }

    /**
     * Scope donde se obtienen las reservas con los filtros sobre salas, estatus y fechas.
     * La operación puede o no contener todos los filtros en caso de no enviar el valor del filtro se retorna un null y no se ejecuta el filtro
     * @param Builder<Reservations> $query
     * @param array<string, mixed> $filters
     * @return Builder
     */
    public function scopeFilter(Builder $query, $filters): Builder
    {
        return $query
            ->when($filters['room_id'] ?? null, fn ($q, $room) => $q->where('meeting_room_id', $room))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->whereIn('status', (array)$status))
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->where('end_time', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->where('start_time', '<=', $to));
    }

    /**
     * Scope donde se obtienen las reservas con los filtros sobre fechas y estatus.
     * Este scope reutilizable es como se pide en el punto 2.2 Query Scope reutilizable.
     * @param Builder<Reservations> $query
     * @param string|null $from
     * @param string|null $to
     * @param array<string> $statuses
     * @return Builder
     */
    public function scopeByDateAndStatus(Builder $query, $from = null, $to = null, $statuses = []): Builder {
        return $query
            ->when($from, fn ($q) => $q->where('start_time', '>=', $from))
            ->when($to, fn ($q) => $q->where('end_time', '<=', $to))
            ->when(!empty($statuses), fn ($q) => $q->whereIn('status', (array) $statuses));
    }

    
}
