<?php

namespace Database\Factories;

use App\Models\MeetingRooms;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MeetingRooms>
 */
class MeetingRoomsFactory extends Factory
{
    protected $model = MeetingRooms::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'slug' => fake()->unique()->slug(3),
            'location' => fake()->optional()->streetAddress(),
            'capacity' => fake()->numberBetween(4, 40),
            'status' => 'activo',
        ];
    }
}
