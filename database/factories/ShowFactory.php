<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Show>
 */
class ShowFactory extends Factory
{
    private array $statuses = ['Returning Series', 'Planned', 'In Production', 'Ended', 'Canceled', 'Pilot'];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'external_id' => fake()->unique()->numberBetween(),
            'name' => fake()->sentence(3),
            'external_updated_at' => fake()->dateTime(),
        ];
    }
}
