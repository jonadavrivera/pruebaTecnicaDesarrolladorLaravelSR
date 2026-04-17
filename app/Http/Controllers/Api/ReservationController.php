<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendReservationConfirmation;
use App\Models\Logs;
use App\Models\Reservations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\ReservationResource;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;

class ReservationController extends Controller
{
    /**
     * El uso del Gate es para consultar las autorizaciones sobre las acciones que puede realizar el usuario, revisando el policies.
     * @link https://laravel.com/docs/13.x/authorization#policy-responses
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Reservations::class);

        $reservations = Reservations::with(['meetingRoom', 'user'])
            ->filter($request->all())
            ->latest()
            ->paginate();

        return ReservationResource::collection($reservations);
    }

    /**
     *  La función de creación se encarga de primero validar que el usuario tenga permisos para crear una reserva.
     * Por medio del lock del Cache se evita que dos usuarios reserven la misma sala al mismo tiempo.
     */
    public function store(StoreReservationRequest $request)
    {
        Gate::authorize('create', Reservations::class);
        $payload = $request->validated();
        $userId = (int) $request->user()->getAuthIdentifier();

        $lock = Cache::lock('reservation_room_'.$request->meeting_room_id, 10);

        if (! $lock->get()) {
            return response()->json([
                'message' => 'Otro usuario está reservando esta sala, intenta nuevamente'
            ], 429);
        }

        try {

            return DB::transaction(function () use ($request, $lock, $userId, $payload) {

                $result = DB::select('CALL create_reservation(?, ?, ?, ?, ?, ?, ?)', [
                    $userId,
                    $request->meeting_room_id,
                    $request->start_time,
                    $request->end_time,
                    $payload['title'] ?? null,
                    $payload['description'] ?? null,
                    $payload['status'] ?? null,
                ]);

                $reservationId = $result[0]->reservation_id;

                $reservation = Reservations::with(['meetingRoom', 'user'])
                    ->findOrFail($reservationId);

                Logs::create([
                    'user_id' => $userId,
                    'action' => 'create',
                    'model_type' => Reservations::class,
                    'model_id' => $reservation->id,
                    'changes' => [
                        'new' => $reservation->toArray()
                    ],
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                SendReservationConfirmation::dispatch($reservation);

                return response()->json([
                    'message' => 'Reserva creada correctamente',
                    'data' => new ReservationResource($reservation)
                ], 201);

            });

        } catch (\Throwable $e) {

            return response()->json([
                'message' => $e->getMessage()
            ], 409);

        } finally {
            optional($lock)->release();
        }
    }

    public function show($id)
    {
        $reservation = Reservations::with(['meetingRoom', 'user'])
            ->findOrFail($id);

        Gate::authorize('view', $reservation);

        return new ReservationResource($reservation);
    }

    public function update(UpdateReservationRequest $request, $id)
    {
        $reservation = Reservations::findOrFail($id);

        Gate::authorize('update', $reservation);
        $userId = (int) $request->user()->getAuthIdentifier();

        return DB::transaction(function () use ($request, $reservation, $userId) {

            $oldData = $reservation->getOriginal();

            $reservation->update($request->validated());

            Logs::create([
                'user_id' => $userId,
                'action' => 'update',
                'model_type' => Reservations::class,
                'model_id' => $reservation->id,
                'changes' => [
                    'old' => $oldData,
                    'new' => $reservation->fresh()->toArray()
                ],
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return new ReservationResource($reservation);
        });
    }

    public function destroy($id)
    {
        $reservation = Reservations::findOrFail($id);

        Gate::authorize('delete', $reservation);
        $userId = (int) request()->user()->getAuthIdentifier();

        return DB::transaction(function () use ($reservation, $userId) {

            Logs::create([
                'user_id' => $userId,
                'action' => 'delete',
                'model_type' => Reservations::class,
                'model_id' => $reservation->id,
                'changes' => [
                    'old' => $reservation->toArray()
                ],
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            $reservation->delete();

            return response()->json([
                'message' => 'Reserva eliminada correctamente'
            ]);
        });
    }
}
