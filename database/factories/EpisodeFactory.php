<?php

namespace Database\Factories;

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
            'show_id' => Show::factory(),
            'number' => $this->faker->unique()->numberBetween(1, 100),
            'absolute_number' => $this->faker->unique()->numberBetween(1, 100),
            'season' => $this->faker->numberBetween(1, 10),
            'name' => Str::title($this->faker->words(rand(3, 6), true)),
            'aired' => $this->faker->dateTimeBetween('-10 years')->format('Y-m-d'),
            'runtime' => $this->faker->numberBetween(10, 90),
            'overview' => $this->faker->paragraph,
        ];
    }
}
