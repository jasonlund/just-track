<?php

namespace Database\Factories;

use App\Models\Show;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Season>
 */
class SeasonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'external_id' => $this->faker->unique()->numberBetween(),
            'show_id' => Show::factory(),
            'number' => $this->faker->numberBetween(1, 20),
            'name' => Str::title($this->faker->words(rand(3, 6), true)),
            'air_date' => $this->faker->dateTimeBetween('-10 years')->format('Y-m-d'),
            'overview' => $this->faker->paragraph,
        ];
    }
}
