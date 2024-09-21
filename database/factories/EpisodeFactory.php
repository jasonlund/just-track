<?php

namespace Database\Factories;

use App\Models\Season;
use App\Models\Show;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Episode>
 */
class EpisodeFactory extends Factory
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
            'show_id' => Season::factory(),
            'season_id' => Season::factory(),
            'number' => $this->faker->numberBetween(1, 30),
            'production_code' => $this->faker->numberBetween(1, 300),
            'name' => Str::title($this->faker->words(rand(3, 6), true)),
            'air_date' => $this->faker->dateTimeBetween('-10 years')->format('Y-m-d'),
            'runtime' => $this->faker->numberBetween(10, 90),
            'overview' => $this->faker->paragraph,
        ];
    }
}
