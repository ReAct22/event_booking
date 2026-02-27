<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TimeSlot>
 */
class TimeSlotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isPast = fake()->boolean(30); // 30% past

        $start = $isPast
            ? Carbon::now()->subDays(rand(5, 30))
            : Carbon::now()->addDays(rand(1, 30));

        return [
            'event_id' => 1, // override later
            'start_time' => $start,
            'end_time' => $start->copy()->addHours(2),
            'capacity' => 20,
            'remaining_capacity' => 20,
        ];
    }
}
