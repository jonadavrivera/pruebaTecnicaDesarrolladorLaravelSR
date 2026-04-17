<?php

use App\Jobs\SendReservationConfirmation;
use App\Models\MeetingRooms;
use App\Models\Permissions;
use App\Models\Reservations;
use App\Models\Roles;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;

test('requiere autenticación', function () {
    $response = $this->postJson('/api/reservations', []);

    $response->assertStatus(401);
});

function userWithReservationCreatePermission(): User
{
    $user = User::factory()->create();
    $role = Roles::query()->create([
        'name' => 'Tester',
        'slug' => 'tester-'.uniqid(),
        'is_active' => true,
    ]);
    $permission = Permissions::query()->firstOrCreate(
        ['slug' => 'reservations.create'],
        ['name' => 'Crear reservas', 'is_active' => true]
    );
    $role->permissions()->syncWithoutDetaching([$permission->id]);
    $user->roles()->syncWithoutDetaching([$role->id]);

    return $user;
}

test('valida fechas conflictivas', function () {
    $user = userWithReservationCreatePermission();
    $room = MeetingRooms::factory()->create();

    Reservations::factory()->create([
        'meeting_room_id' => $room->id,
        'user_id' => $user->id,
        'start_time' => '2025-01-01 10:00:00',
        'end_time' => '2025-01-01 12:00:00',
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/reservations', [
        'meeting_room_id' => $room->id,
        'start_time' => '2025-01-01 11:00:00',
        'end_time' => '2025-01-01 13:00:00',
    ]);

    $response->assertStatus(409);
});

test('crea reserva y dispara job', function () {
    Queue::fake();

    $user = userWithReservationCreatePermission();
    $room = MeetingRooms::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/reservations', [
        'meeting_room_id' => $room->id,
        'start_time' => '2025-01-02 10:00:00',
        'end_time' => '2025-01-02 12:00:00',
    ]);

    $response->assertStatus(201);

    Queue::assertPushed(SendReservationConfirmation::class);
});
