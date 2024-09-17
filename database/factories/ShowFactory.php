<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
            'external_id' => $this->faker->unique()->numberBetween(),
            'name' => Str::title($this->faker->words(rand(3, 6), true)),
            'original_name' => Str::title($this->faker->words(rand(3, 6), true)),
            'status' => strtolower(array_rand($this->statuses)),
            'first_air_date' => $this->faker->dateTimeBetween('-10 years')->format('Y-m-d'),
            'overview' => $this->faker->paragraph,
            'origin_country' => strtolower($this->faker->countryISOAlpha3),
        ];
    }
}
