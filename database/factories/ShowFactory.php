<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Show>
 */
class ShowFactory extends Factory
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
            'name' => Str::title($this->faker->words(rand(3, 6), true)),
            'original_name' => Str::title($this->faker->words(rand(3, 6), true)),
            'first_air_date' => $this->faker->dateTimeBetween('-10 years')->format('Y-m-d'),
            'overview' => $this->faker->paragraph,
            'origin_country' => strtolower($this->faker->countryISOAlpha3),
        ];
    }
}
