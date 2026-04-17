<?php

namespace Database\Factories;

use App\Models\MeetingRooms;
use App\Models\Reservations;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservations>
 */
class ReservationsFactory extends Factory
{
    protected $model = Reservations::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-1 week', '+1 week');

        return [
            'meeting_room_id' => MeetingRooms::factory(),
            'user_id' => User::factory(),
            'start_time' => $start,
            'end_time' => (clone $start)->modify('+2 hours'),
            'title' => fake()->optional()->sentence(3),
            'description' => null,
            'status' => 'confirmed',
            'version' => 1,
        ];
    }
}
