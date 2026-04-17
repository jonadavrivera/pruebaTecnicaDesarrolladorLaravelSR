<?php

namespace App\Policies;

use App\Models\Reservations;
use App\Models\User;

/**
 * Para generarlo lo cree con el comando php artisan make:policy ReservationPolicy --model=Reservation
 * conel --model se vincula el modelo al Policies.
 * 
 * En versiones anteriores se agregaba en el archivo AuthServiceProvider.php
 * y se agregaba en el protected $policies y como arreglo el modelo señalando el policies. 
 * Reservation::class => ReservationPolicy::class,
 * En las últimas versiones lo realiza de forma automática
 * @link https://laravel.com/docs/13.x/authorization#creating-policies
 */
class ReservationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('reservations.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Reservations $reservation): bool
    {
        return $user->id === $reservation->user_id
        || $user->hasPermission('reservations.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('reservations.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Reservations $reservation): bool
    {
        return $user->id === $reservation->user_id
            || $user->hasPermission('reservations.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Reservations $reservation): bool
    {
        return $user->hasPermission('reservations.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Reservations $reservation): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Reservations $reservation): bool
    {
        return false;
    }
}
