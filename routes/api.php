<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MeetingRoomController;
use App\Http\Controllers\Api\ReservationController;

/**
 * Agrupo las rutas con el auth:sanctum para revisar que el usuario esté autenticado.
 * El throttle:10,1 lo utilizo para limitar el número de peticiones para que no sea mayor a 10 ocasiones por minuto. en caso de que suceda se muestra el error 429.
 */
Route::middleware('auth:sanctum', 'throttle:10,1')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    /**
    * Ruta que genera todos los métodos necesarios para la api
    * Route::apiResource('reservations', ReservationController::class);
    * Para la prueba decido separarlo para tener una vista general sobre todos los métodos que se van a utilizar.
    * Además de tener la posibilidad de agregar un middleware extra al eliminar aprovechando la autorización y que solo el administrador pueda eliminar una reserva.
    */

    Route::get('reservations', [ReservationController::class, 'index']);
    Route::post('reservations', [ReservationController::class, 'store']);
    Route::get('reservations/{id}', [ReservationController::class, 'show']);
    Route::match(['put', 'patch'], 'reservations/{id}', [ReservationController::class, 'update']);
    Route::delete('reservations/{id}', [ReservationController::class, 'destroy']);

    Route::apiResource('meeting-rooms', MeetingRoomController::class);
});