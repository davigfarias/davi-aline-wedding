<?php

namespace Database\Factories;

use App\Models\Family;
use App\Models\Guest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Guest>
 */
class GuestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'family_id' => Family::factory(),
            'name' => fake()->name(),
            'is_attending' => null,
            'responded_at' => null,
        ];
    }

    /**
     * The guest confirmed they are attending.
     */
    public function attending(): static
    {
        return $this->state(fn (): array => [
            'is_attending' => true,
            'responded_at' => now(),
        ]);
    }

    /**
     * The guest declined the invitation.
     */
    public function declined(): static
    {
        return $this->state(fn (): array => [
            'is_attending' => false,
            'responded_at' => now(),
        ]);
    }
}
