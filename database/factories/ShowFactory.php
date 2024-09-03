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
            'year' => $this->faker->dateTimeBetween('-10 years')->format('Y'),
            'overview' => $this->faker->paragraph,
            'original_country' => strtolower($this->faker->countryISOAlpha3),
        ];
    }
}
